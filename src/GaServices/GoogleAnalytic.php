<?php
/**
 * Created by PhpStorm.
 * User: KimTung
 * Date: 2/26/2020
 * Time: 9:08 AM
 */

namespace GaServices;


class GoogleAnalytic
{
    public function __construct() {
    }

    public function report($name, $keyFileLocation, $filter) {
        $client = $this->gaAuthorzation($name, $keyFileLocation);
        $reports = $this->getReport($client, $filter);
        $items = $this->readReport($reports);

    }

    private function readReport($reports) {
        for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
            $report = $reports[ $reportIndex ];
            $rows = $report->getData()->getRows();
            for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $row = $rows[ $rowIndex ];
                $metrics = $row->getMetrics();
                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                }
            }
        }
    }

    private function getReport($client, $filter) {
        $analytics = new \Google_Service_AnalyticsReporting($client);
        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($filter['from']);
        $dateRange->setEndDate($filter['to']);

        // Create the Metrics object.
        $sessions = new \Google_Service_AnalyticsReporting_Metric();
        $sessions->setMetrics($filter['metrics']);

        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($filter['viewId']);
        $request->setDateRanges($dateRange);
        $request->setMetrics(array($sessions));

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests( array( $request) );
        return $analytics->reports->batchGet( $body );
    }

    private function gaAuthorzation($name, $keyFileLocation) {
        $client = new \Google_Client();
        $client->setApplicationName($name);
        $client->setAuthConfig($keyFileLocation);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        return $client;
    }
}