<?php

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;

class ControllerVisitorsVisitors extends Controller
{
    private $apiKey = 'ZtbGqouI2GiAYgXxu3kOjqaOn2rGlOMamDhVgtLaegEmW1LChjTmwePaHnP5s0xE';


    public function update()
    {

        // Проверка наличия GET-параметра и его значения
        if (!isset($_GET['api_key']) || $_GET['api_key'] !== $this->apiKey) {
            // Если ключ не передан или он неверный, возвращаем ошибку доступа
            $response = [
                "status" => "error",
                "message" => "Unauthorized access.",
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            die;
        }

        try {
            $analytics = $this->initializeAnalytics();

            // Получение данных за сегодня
            $todayVisitors = $this->getVisitorsReport($analytics, 'today');

            // Получение данных за все время
            $last30DaysVisitors = $this->getVisitorsReport($analytics, '30daysAgo', 'today');


            // Формирование успешного ответа
            $response = [
                "status" => "success",
                "data" => [
                    "last30DaysVisitors" => (int)$last30DaysVisitors,
                    "todayVisitors" => (int)$todayVisitors,
                ]
            ];
        } catch (Exception $e) {
            // Логирование ошибки
            error_log('Error occurred: ' . $e->getMessage());

            // Формирование ответа с ошибкой
            $response = [
                "status" => "error",
                "error" => $e->getMessage(), // Добавляем сообщение ошибки для диагностики
            ];
        }

        // Вывод JSON-ответа
        header('Content-Type: application/json');
        echo json_encode($response);
        die;
    }

    private function initializeAnalytics()
    {
        return new BetaAnalyticsDataClient([
            'credentials' => 'spring-ship-446316-i0-c2f99801dab0.json', // Ваш JSON файл
        ]);
    }

    private function getVisitorsReport($analytics, $startDate, $endDate = null)
    {
        $endDate = $endDate ?? $startDate; // Если $endDate не указан, используем $startDate

        // Запрос данных
        $response = $analytics->runReport([
            'property' => 'properties/428750006', // Ваш ID ресурса
            'dateRanges' => [
                new DateRange([
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]),
            ],
            'metrics' => [
                new Metric(['name' => 'activeUsers']),
            ],
        ]);

        // Извлечение значения из ответа
        return $response->getRows()[0]->getMetricValues()[0]->getValue();
    }
}
