<?php

declare(strict_types=1);

namespace App\Http\Helpers;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMApiHttpClientException;
use AmoCRM\Exceptions\AmoCRMApiNoContentException;
use App\Models\Lead;
use Exception;

use function json_encode;

use const JSON_UNESCAPED_UNICODE;

/**
 * Класс для вынесения бизнес логики из "ручки".
 */
class ControllerHelper
{
    /**
     * Получение списка сделок из аккаунта и сохранение в БД.
     */
    public static function getLeads(AmoCRMApiClient $apiClient): bool
    {
        try {
            try {
                $leads = $apiClient->leads()->get();
            } catch (AmoCRMApiNoContentException $e) {
                die('В аккаунте нет сделок');
            } catch (AmoCRMApiHttpClientException $e) {
                die('Ошибка соединения. проверьте подключение к интернету');
            } catch (Exception $e) {
                die('Произошла неизвестная ошибка, попробуйте позднее');
            }

            foreach ($leads as $lead) {
                $customFieldsValues = $lead->custom_fields_values;
                $customFields       = [];

                if (isset($customFieldsValues)) {
                    foreach ($customFieldsValues as $customFieldsValue) {
                        $customFields[$customFieldsValue->field_name] = $customFieldsValue->values[0]->value;
                    }
                }

                Lead::updateOrCreate([
                    'lead_name'           => $lead->name,
                    'lead_price'          => $lead->price,
                    'responsible_user_id' => $lead->responsible_user_id,
                    'account_id'          => $lead->account_id,
                    'custom_fields'       => json_encode($customFields, JSON_UNESCAPED_UNICODE),
                ]);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
