<?php
/**
 * Created by PhpStorm.
 * User: KimTung
 * Date: 2/26/2020
 * Time: 2:16 PM
 */

namespace GaServices;

use GaServices\Service\AdwordsService;

abstract class GoogleAds
{

    abstract protected function report($key, $configPath, $filter = []);

    abstract protected function readFileReport($filter = []);


    public function getPath() {
        return dirname(__FILE__) . '/files';
    }

    public function convertToReportDate($date) {
        $retVal = "";
        $array = explode("-", $date);
        foreach ($array as $value) {
            $retVal .= $value;
        }
        return $retVal;
    }

    public function getAdsApiService() {
        return new AdwordsService();
    }
}