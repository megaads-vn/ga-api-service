<?php
/**
 * Created by PhpStorm.
 * User: KimTung
 * Date: 2/26/2020
 * Time: 11:03 AM
 */

namespace GaServices;

class AdsCostAccount extends GoogleAds
{
    public function __construct(){
    }

    public function report($key = null, $configPath = null, $filter = []) {
        if ($key == null || $configPath == null || count($filter) == 0) {
            throw new \InvalidArgumentException('Missing argument');
        }
        $from = $this->convertToReportDate($filter['from']);
        $to = $this->convertToReportDate($filter['to']);
        $adsApi = $this->getAdsApiService();
        $session = $adsApi->buildSessionAccount($key, $configPath);
        $fields = $adsApi->getAccountReportField();
        $reportFilter = array(
            'reportType' => 'account',
            'path' => $this->getPath(),
            'fileName' => 'report_account_cost_' . $key,
            'fields' => $fields,
            'session' => $session,
            'timeType' => 'CUSTOM_DATE',
            'fromDate' => $from,
            'toDate' => $to
        );
        $adsApi->downloadReport($reportFilter);
        $data = $this->readFileReport($reportFilter);
        $cost = $this->calculatingAdsCost($data);
        return $cost;
    }

    protected function readFileReport($filter = [])
    {
        $filePath = $filter['path'] . '/'. $filter['fileName'] . '.csv';
        $data = \ExcelUtils::readFile($filePath);
        return $data;
    }

    private function calculatingAdsCost($data) {
        $amount = 0;
        foreach($data as $item) {
            if ($item[0] == 'Total') {
                $amount = (floatval($item[3]) / 1000000);
            }
        }
        return $amount;
    }
}