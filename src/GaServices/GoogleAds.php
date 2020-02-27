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

    public function readFile($filePath = null, $sheetIdx = 0, $returnFileObject = FALSE) {
        $retval = array();
        //  Read your Excel workbook
        try {
            $inputFileType = \PHPExcel_IOFactory::identify($filePath);
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($filePath);
        } catch (Exception $e) {
            die('Error loading file "' . pathinfo($filePath, PATHINFO_BASENAME) . '": ' . $e->getMessage());
        }

        //  Get worksheet dimensions
        $sheet = $objPHPExcel->getSheet($sheetIdx);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        //  Loop through each row of the worksheet in turn
        for ($row = 1; $row <= $highestRow; $row++) {
            //  Read a row of data into an array
            $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
            $retval[] = $rowData[0];
        }
        if ($returnFileObject) {
            return array("docObject" => $objPHPExcel, "sheetObject" => $sheet, "data" => $retval);
        } else {
            return $retval;
        }
    }
}