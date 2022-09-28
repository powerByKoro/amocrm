<?php

declare(strict_types=1);

namespace App\Http\Helpers;

use AmoCRM\Client\AmoCRMApiClient;
use App\Models\AmoToken;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use PDOException;

use function bin2hex;
use function header;
use function random_bytes;

/**
 * Класс помощник.
 * Содержит методы для получения нового токена авторизации, сохранения токена,
 * и извлечение токена из БД если он есть.
 */
class AmoHelper
{
    /** @var object */
    protected $apiClient;

    public function __construct(AmoCRMApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @return string|object
     */
    public function getNewToken()
    {
        /**
         * Проверяем есть ли токен в БД
         * Если есть то задаем для ApiClient этот токен.
         * Если нет, то запрашиваем новый токен и сохраняем в БД
         */
        $apiClient = self::getToken($this->apiClient);

        if ($apiClient === null) {
            if (isset($_GET['referer'])) {
                $this->apiClient->setAccountBaseDomain($_GET['referer']);
            }

            if (! isset($_GET['code'])) {
                $state                   = bin2hex(random_bytes(16));
                $_SESSION['oauth2state'] = $state;
                if (isset($_GET['button'])) {
                    echo $this->apiClient->getOAuthClient()->getOAuthButton(
                        [
                            'title'          => 'Установить интеграцию',
                            'compact'        => true,
                            'class_name'     => 'className',
                            'color'          => 'default',
                            'error_callback' => 'handleOauthError',
                            'state'          => $state,
                        ]
                    );
                    die;
                } else {
                    $authorizationUrl = $this->apiClient->getOAuthClient()->getAuthorizeUrl([
                        'state' => $state,
                        'mode'  => 'post_message',
                    ]);
                    header('Location: ' . $authorizationUrl);
                    die();
                }
            } elseif (empty($_GET['state']) || empty($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
                unset($_SESSION['oauth2state']);
                exit('Invalid state');
            }

            /**
             * Ловим обратный код
             */
            try {
                $accessToken = $this->apiClient->getOAuthClient()->getAccessTokenByCode($_GET['code']);

                AmoToken::updateOrCreate([
                    'access_token'  => $accessToken->getToken(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'base_domain'   => $this->apiClient->getAccountBaseDomain(),
                    'expires'       => $accessToken->getExpires(),
                ]);
            } catch (PDOException $e) {
                die('Ошибка при подлкючении к БД');
            } catch (Exception $e) {
                die('Невозможно полуить токен авторизации, Проверьте настройки доступа !');
            }

            $this->apiClient->setAccessToken($accessToken);
            $this->apiClient->setAccountBaseDomain($this->apiClient->getAccountBaseDomain());

            return $this->apiClient;
        } else {
            return $apiClient;
        }
    }

    /**
     * Получение токена из БД
     * Вернет null если токена нет.
     *
     * @return null|object
     */
    public static function getToken(AmoCRMApiClient $apiClient)
    {
        if (AmoToken::all()->first()) {
            $token      = AmoToken::all()->first();
            $baseDomain = $token->base_domain;

            try {
                $token = new AccessToken([
                    'access_token'  => $token->access_token,
                    'refresh_token' => $token->refresh_token,
                    'expires'       => $token->expires,
                ]);
            } catch (Exception $e) {
                die('Невозможно получить данные, попробуйте позднее');
            }

            $apiClient->setAccessToken($token);
            $apiClient->setAccountBaseDomain($baseDomain);

            return $apiClient;
        } else {
            return null;
        }
    }
}
