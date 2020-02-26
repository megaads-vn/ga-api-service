<?php
/**
 * Created by PhpStorm.
 * User: KimTung
 * Date: 2/26/2020
 * Time: 11:16 AM
 */

namespace GaServices\Service;

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\Reporting\v201809\DownloadFormat;
use Google\AdsApi\AdWords\Reporting\v201809\ReportDefinition;
use Google\AdsApi\AdWords\Reporting\v201809\ReportDefinitionDateRangeType;
use Google\AdsApi\AdWords\Reporting\v201809\ReportDownloader;
use Google\AdsApi\AdWords\ReportSettingsBuilder;
use Google\AdsApi\AdWords\v201809\cm\Predicate;
use Google\AdsApi\AdWords\v201809\cm\PredicateOperator;
use Google\AdsApi\AdWords\v201809\cm\ReportDefinitionReportType;
use Google\AdsApi\AdWords\v201809\cm\Selector;
use Google\AdsApi\AdWords\v201809\cm\DateRange;
use Google\AdsApi\AdWords\v201809\cm\AdGroupCriterionOperation;
use Google\AdsApi\AdWords\v201809\cm\AdGroupCriterionService;
use Google\AdsApi\AdWords\v201809\cm\BiddableAdGroupCriterion;
use Google\AdsApi\AdWords\v201809\cm\BiddingStrategyConfiguration;
use Google\AdsApi\AdWords\v201809\cm\CpcBid;
use Google\AdsApi\AdWords\v201809\cm\Keyword;
use Google\AdsApi\AdWords\v201809\cm\KeywordMatchType;
use Google\AdsApi\AdWords\v201809\cm\Money;
use Google\AdsApi\AdWords\v201809\cm\NegativeAdGroupCriterion;
use Google\AdsApi\AdWords\v201809\cm\Criterion;
use Google\AdsApi\AdWords\v201809\cm\Operator;
use Google\AdsApi\AdWords\v201809\cm\CampaignCriterion;
use Google\AdsApi\AdWords\v201809\cm\CampaignCriterionOperation;
use Google\AdsApi\AdWords\v201809\cm\CampaignCriterionService;
use Google\AdsApi\AdWords\v201809\cm\Platform;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Common\SoapSettingsBuilder;
use Google\AdsApi\AdWords\v201809\cm\CampaignService;
use Google\AdsApi\AdWords\v201809\cm\AdGroupService;
use Google\AdsApi\AdWords\v201809\cm\OrderBy;
use Google\AdsApi\AdWords\v201809\cm\Paging;
use Google\AdsApi\AdWords\v201809\cm\SortOrder;

class AdwordsService
{

    protected $accountReportField = [
        'AccountCurrencyCode',
        'Clicks',
        'Date',
        'Cost'
    ];

    protected $campaignReportField = [
        'CampaignId',
        'CampaignName',
        'CampaignStatus',
        'Amount',
        'Clicks',
        'Impressions',
        'Cost',
        'AveragePosition',
        'HourOfDay',
        'DayOfWeek',
        'Conversions'
    ];

    protected $campaignReprotField = [
        'CampaignId',
        'CampaignName',
        'CampaignStatus',
        'Amount',
        'Clicks',
        'Impressions',
        'Cost',
        'AveragePosition',
        'HourOfDay',
        'DayOfWeek',
        'Conversions'
    ];

    protected $adgroupReportField = [
        'CampaignId',
        'CampaignName',
        'AdGroupId',
        'AdGroupName',
        'Cost',
        'Impressions',
        'Clicks',
        'CpcBid',
        'AdGroupStatus',
        'Ctr',
        'AveragePosition',
        'HourOfDay',
        'DayOfWeek',
    ];

    protected $criteriaReportField = [
        'CampaignId',
        'AdGroupId',
        'Criteria',
        'Id',
        'Status',
        'CpcBid',
        'QualityScore'
    ];

    protected $keywordReportField = [
        'CampaignId',
        'CampaignName',
        'AdGroupId',
        'AdGroupName',
        'Criteria',
        'QualityScore'
    ];

    public function __construct()
    {
    }

    public function getAccountReportField() {
        return $this->accountReportField;
    }

    public function getCampaignReportField() {
        return $this->campaignReprotField;
    }

    public function getAdgroupReportField() {
        return $this->adgroupReportField;
    }

    public function getCriteriaReportField() {
        return $this->criteriaReportField;
    }

    public function getKeywordReportField() {
        return $this->keywordReportField;
    }

    public function removeElementArrayByValue($values, $array) {
        foreach ($values as $value) {
            if (($key = array_search($value, $array)) !== false) {
                unset($array[$key]);
            }
        }
        return $array;
    }

    public function buildSessionAccount($clientCustomerId, $configPath) {
        // User log
        try {
            $oAuth2Credential = (new OAuth2TokenBuilder())
                ->fromFile($configPath)
                ->build();
            $session = (new AdWordsSessionBuilder())
                ->fromFile($configPath)
                ->withClientCustomerId($clientCustomerId)
                ->withOAuth2Credential($oAuth2Credential)
                ->build();
            return $session;
        } catch (Exception $e) {
            printf("An error has occurred: %s\n", $e->getMessage());
        }
    }

    public function downloadReport($filters) {
        if (!file_exists($filters['path'])) {
            mkdir($filters['path'], 0777, true);
        }
        $filePath = $filters['path'] . '/' . $filters['fileName'] . '.csv';
        if (!file_exists($filePath)) {
            $fp = fopen($filePath, 'w');
            fclose($fp);
        }
        $selector = new Selector();
        $selector->setFields($filters['fields']);
        if ($filters['timeType'] == 'CUSTOM_DATE') {
            $selector->setDateRange(new DateRange($filters['fromDate'], $filters['toDate']));
        }
        if (array_key_exists('predicates', $filters)) {
            $selector->setPredicates($filters['predicates']);
        }
        $reportDefinition = new ReportDefinition();
        $reportDefinition->setSelector($selector);
        $reportDefinition->setReportName('Report #' . uniqid());
        $timeType = $this->getTimeType($filters['timeType']);
        $reportDefinition->setDateRangeType($timeType);
        $reportType = $this->getReportType($filters['reportType']);
        $reportDefinition->setReportType($reportType);
        $reportDefinition->setDownloadFormat(DownloadFormat::CSV);
        $reportDownloader = new ReportDownloader($filters['session']);
        $reportSettingsOverride = (new ReportSettingsBuilder())
            ->includeZeroImpressions(false)
            ->build();
        $reportDownloadResult = $reportDownloader->downloadReport($reportDefinition, $reportSettingsOverride);
        $reportDownloadResult->saveToFile($filePath);
    }

    private function getTimeType($type) {
        $retVal = ReportDefinitionDateRangeType::TODAY;
        if ($type == 'YESTERDAY') {
            $retVal = ReportDefinitionDateRangeType::YESTERDAY;
        }
        if ($type == 'LAST_7_DAYS') {
            $retVal = ReportDefinitionDateRangeType::LAST_7_DAYS;
        }
        if ($type == 'LAST_30_DAYS') {
            $retVal = ReportDefinitionDateRangeType::LAST_30_DAYS;
        }
        if ($type == 'CUSTOM_DATE') {
            $retVal = ReportDefinitionDateRangeType::CUSTOM_DATE;
        }
        if ($type == 'ALL_TIME') {
            $retVal = ReportDefinitionDateRangeType::ALL_TIME;
        }
        return $retVal;
    }

    private function getReportType($type) {
        $retVal = '';
        if ($type == 'keyword') {
            $retVal = ReportDefinitionReportType::KEYWORDS_PERFORMANCE_REPORT;
        }
        if ($type == 'adgroup') {
            $retVal = ReportDefinitionReportType::ADGROUP_PERFORMANCE_REPORT;
        }
        if ($type == 'campaign') {
            $retVal = ReportDefinitionReportType::CAMPAIGN_PERFORMANCE_REPORT;
        }
        if ($type == 'criteria') {
            $retVal = ReportDefinitionReportType::CRITERIA_PERFORMANCE_REPORT;
        }
        if ($type == 'account') {
            $retVal = ReportDefinitionReportType::ACCOUNT_PERFORMANCE_REPORT;
        }
        return $retVal;
    }


}