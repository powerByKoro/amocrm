<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use AmoCRM\Client\AmoCRMApiClient;
use App\Http\Helpers\AmoHelper;
use App\Http\Helpers\ControllerHelper;
use Illuminate\Http\JsonResponse;

use function response;
use function session_start;

/**
 * Класс для работы с Api AmoCrm.
 * Тянет все сделки и кастомные поля, если они у сделки есть.
 *
 * Для начала получает токен авторизации, после чего можно работать с
 * ApiClient объектом у которого доступны все необходимые методы для работы с данными из AmoCrm.
 *
 * @return void
 */
class AmoCrmController extends Controller
{
    public function getLeads(): JsonResponse
    {
        session_start();

        /**
         * Данные для Объекта Амо берутся из интеграции в личном кабнете.
         */
        $apiClient = new AmoCRMApiClient(
            'dbf5b0c2-9df1-4911-893b-e85991dfecd0',
            '1QEZCjpfzZza2InsZdD00jMMiMRgG8ipVfmyLP02p2rwnLVy0qfWGZW4kPkm0vbi',
            'https://a478-91-184-253-160.eu.ngrok.io'
        );

        $apiClient = (new AmoHelper($apiClient))->getNewToken();

        $result = ControllerHelper::getLeads($apiClient);

        if ($result) {
            return response()->json(['status' => true, 'code' => 201, 'msg' => 'Success'], '201');
        } else {
            return response()->json(['status' => false, 'code' => 500, 'msg' => 'Internal error'], '500');
        }
    }
}
