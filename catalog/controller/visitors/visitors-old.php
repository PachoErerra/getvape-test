<?php

use Google\Client;
use Google\Service\AnalyticsReporting;

class ControllerVisitorsVisitors extends Controller
{

    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function update()
    {
        $analytics = $this->initializeAnalytics();
        $reports = $this->getReport($analytics);
        print_r($reports);
        die;
    }


    public function getReport($analytics)
    {
        // Задайте параметры отчета
        $dateRange = new AnalyticsReporting\DateRange();
        $dateRange->setStartDate('7daysAgo');
        $dateRange->setEndDate('today');

        $sessions = new AnalyticsReporting\Metric();
        $sessions->setExpression('ga:sessions');
        $sessions->setAlias('Sessions');

        $request = new AnalyticsReporting\ReportRequest();
        $request->setViewId('428750006');
        $request->setDateRanges($dateRange);
        $request->setMetrics([$sessions]);

        $body = new AnalyticsReporting\GetReportsRequest();
        $body->setReportRequests([$request]);

        return $analytics->reports->batchGet($body);
    }

    public function initializeAnalytics()
    {
        $client = new Client();
        $client->setAuthConfig('spring-ship-446316-i0-c2f99801dab0.json');
        $client->addScope(AnalyticsReporting::ANALYTICS_READONLY);
        return new AnalyticsReporting($client);
    }
}
