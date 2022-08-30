<?php

declare(strict_types=1);

namespace SevenSpan\Matomo;

use SevenSpan\Matomo\Helpers\MatomoHelper;

class Matomo implements MatomoInterface
{
    /**
     * 
     * @param string $campaignId
     * 
     * @param string $siteName
     * 
     * @return array|mixed
     */
    public function addSite(string $siteName)
    {
        $apiParams = [
            'method' => 'SitesManager.addSite',
            'siteName' => urlencode($siteName),
            'token_auth' => config('matomo.token'),
        ];

        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $matomoResponse =  MatomoHelper::callApi($apiEndpoint);
        MatomoHelper::parseMatomoResponse($matomoResponse);

        return $matomoResponse;
    }

    /**
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public function removeSite(int $matomoAnalyticsId)
    {
        $apiParams = [
            'method' => 'SitesManager.deleteSite',
            'idSite' => $matomoAnalyticsId,
            'token_auth' => config('matomo.token'),
        ];

        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $response =  MatomoHelper::callApi($apiEndpoint);
        $response =  MatomoHelper::parseMatomoResponse($response);

        return $response;
    }

    /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     * 
     * Refrance https://developer.matomo.org/api-reference/reporting-api
     */
    public static function getVisitorsData(int $matomoAnalyticsId, string $period, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'idSite' => $matomoAnalyticsId,
            'period' => $period,
            'date' => $date,
            'token_auth' => config('matomo.token'),
            'apiModule' => 'VisitsSummary',
            'apiAction' => 'get',
        ];
        $apiEndpoint = config('matomo.api_uri') .  MatomoHelper::generateApiParamStr($apiParams);
        $data = [];
        $matomoResponse =  MatomoHelper::callApi($apiEndpoint);
        MatomoHelper::parseMatomoResponse($matomoResponse);
        $matomoResponse = json_decode($matomoResponse, true);
        if (isset($matomoResponse['reportData'])) {
            $reportData = $matomoResponse['reportData'];
            if ($period === 'range') {
                $temp[] = $reportData;
                $reportData = [];
                $reportData = $temp;
            }
            foreach ($reportData as $k =>  $d) {
                if (!empty($d)) {
                    if ($period === 'day') {
                        $date =  MatomoHelper::parseDateString($k);
                        $temp = [
                            'date' => strtotime($date),
                            'count' => $d['nb_visits'],
                            'percentage' => 0.0,
                            'avg_time_on_site' => $d['avg_time_on_site'],
                        ];
                        $data[] = $temp;
                    } elseif ($period === 'week') {
                        $temp =  MatomoHelper::parseDateStringByWeek($k);
                        $temp['count'] = $d['nb_visits'];
                        $temp['percentage'] = 0.0;
                        $temp['avg_time_on_site'] = $d['avg_time_on_site'];
                        $data[] = $temp;
                    } elseif ($period === 'month') {
                        $temp = [
                            'month' => preg_replace("/[^a-zA-Z]+/", "", $k),
                            'year' => substr($k, -4),
                            'count' => $d['nb_visits'],
                            'percentage' => 0.0,
                            'avg_time_on_site' => $d['avg_time_on_site'],
                        ];
                        $data[] = $temp;
                    } elseif ($period === 'year') {
                        $temp = [
                            'year' => $k,
                            'count' => $d['nb_visits'],
                            'percentage' => 0.0,
                            'avg_time_on_site' => $d['avg_time_on_site'],
                        ];
                        $data[] = $temp;
                    } elseif ($period === 'range') {
                        $temp = [
                            'count' => $d['nb_visits'],
                            'percentage' => 0.0,
                            'avg_time_on_site' => $d['avg_time_on_site'],
                        ];
                        $data[] = $temp;
                    } else {
                        return null;
                    }
                }
            }
            $data = MatomoHelper::calculateTotalAndPercentage($data);
        }

        return $data;
    }


    /**
     * @param string $data
     * 
     * @param int $matomoAnalyticsId
     * 
     * @param string $period
     *
     * @return array|mixed
     */
    public static function getPageWiseViewCount(int $matomoAnalyticsId, string $period, string $date)
    {
        $views = self::getProcessedReport($matomoAnalyticsId, $period, $date);
        $data = [
            'total_page_views' => 0,
            'unique_page_views' => 0,
            'product_page_views' => self::getProductPageVisitCount($matomoAnalyticsId, $period, $date)['product_page_views'],
        ];

        if (isset($views['unique_page_views'])) {
            $data['unique_page_views'] = $views['unique_page_views'];
        }

        if (isset($views['total_page_views'])) {
            $data['total_page_views'] = $views['total_page_views'];
        }

        return $data;
    }

    /**
     * @param string $data
     *
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     * 
     * @return array|mixed
     */
    public static function getCountryWiseReport(int $matomoAnalyticsId, string $period, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'idSite' => $matomoAnalyticsId,
            'period' => $period,
            'date' => $date,
            'token_auth' =>  config('matomo.token'),
            'apiModule' => 'UserCountry',
            'apiAction' => 'getCountry',
            'filter_truncate' => '5',
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = [];
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        MatomoHelper::parseMatomoResponse($matomoResponse);
        $matomoResponse = json_decode($matomoResponse, true);
        if (isset($matomoResponse['reportData'])) {
            $reportData = $matomoResponse['reportData'];
            foreach ($reportData as $k => $d) {
                if (!empty($d)) {
                    if ($period === 'day') {
                        foreach ($d as $v) {
                            $date =  MatomoHelper::parseDateString($k);
                            $temp = [
                                'date' => strtotime($date),
                                'name' => $v['label'],
                                'count' => $v['nb_visits'],
                                'percentage' => 0.0,
                                'avg_time_on_site' => $v['avg_time_on_site'],
                            ];
                            $data[] = $temp;
                        }
                    } elseif ($period === 'week') {
                        foreach ($d as $v) {
                            $temp =  MatomoHelper::parseDateStringByWeek($k);
                            $temp['name'] = $v['label'];
                            $temp['count'] = $v['nb_visits'];
                            $temp['percentage'] = 0.0;
                            $temp['avg_time_on_site'] = $v['avg_time_on_site'];
                            $data[] = $temp;
                        }
                    } elseif ($period === 'month') {
                        foreach ($d as $v) {
                            $temp = [
                                'month' => preg_replace("/[^a-zA-Z]+/", "", $k),
                                'name' => $v['label'],
                                'year' => substr($k, -4),
                                'count' => $v['nb_visits'],
                                'percentage' => 0.0,
                                'avg_time_on_site' => $v['avg_time_on_site'],
                            ];
                            $data[] = $temp;
                        }
                    } elseif ($period === 'year') {
                        foreach ($d as $v) {
                            $temp = [
                                'year' => $k,
                                'name' => $v['label'],
                                'count' => $d['nb_visits'],
                                'percentage' => 0.0,
                                'avg_time_on_site' => $d['avg_time_on_site'],
                            ];
                        }
                    } elseif ($period === 'range') {
                        foreach ($d as $v) {
                            $temp = [
                                'name' => $v['label'],
                                'count' => $d['nb_visits'],
                                'percentage' => 0.0,
                                'avg_time_on_site' => $d['avg_time_on_site'],
                            ];
                            $data[] = $temp;
                        }
                    } else {
                        return null;
                    }
                }
            }
            $data = MatomoHelper::calculateTotalAndPercentage($data);
        }
        return $data;
    }

    /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getBrowserWiseReport(int $matomoAnalyticsId, string $period, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'idSite' => $matomoAnalyticsId,
            'period' => $period,
            'date' => $date,
            'token_auth' => config('matomo.token'),
            'apiModule' => 'DevicesDetection',
            'apiAction' => 'getBrowsers',
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = [];
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        MatomoHelper::parseMatomoResponse($matomoResponse);
        $matomoResponse = json_decode($matomoResponse, true);
        if (isset($matomoResponse['reportData'])) {
            $reportData = $matomoResponse['reportData'];
            foreach ($reportData as $k => $d) {
                if (!empty($d)) {
                    if ($period === 'day') {
                        foreach ($d as $v) {
                            $date =  MatomoHelper::parseDateString($k);
                            $temp = [
                                'date' => strtotime($date),
                                'name' => $v['label'],
                                'count' => $v['nb_visits'],
                                'percentage' => 0.0,
                                'avg_time_on_site' => $v['avg_time_on_site'],
                            ];
                            $data[] = $temp;
                        }
                    } elseif ($period === 'week') {
                        foreach ($d as $v) {
                            $temp =  MatomoHelper::parseDateStringByWeek($k);
                            $temp['name'] = $v['label'];
                            $temp['count'] = $v['nb_visits'];
                            $temp['percentage'] = 0.0;
                            $temp['avg_time_on_site'] = $v['avg_time_on_site'];
                            $data[] = $temp;
                        }
                    } elseif ($period === 'month') {
                        foreach ($d as $v) {
                            $temp = [
                                'month' => preg_replace("/[^a-zA-Z]+/", "", $k),
                                'year' => substr($k, -4),
                                'name' => $v['label'],
                                'count' => $v['nb_visits'],
                                'percentage' => 0.0,
                                'avg_time_on_site' => $v['avg_time_on_site'],
                            ];
                            $data[] = $temp;
                        }
                    } elseif ($period === 'year') {
                        foreach ($d as $v) {
                            $temp = [
                                'year' => $k,
                                'count' => $d['nb_visits'],
                                'name' => $v['label'],
                                'percentage' => 0.0,
                                'avg_time_on_site' => $d['avg_time_on_site'],
                            ];
                        }
                    } elseif ($period === 'range') {
                        $temp = [
                            'name' => $d['label'],
                            'count' => $d['nb_visits'],
                            'percentage' => 0.0,
                            'avg_time_on_site' => $d['avg_time_on_site'],
                        ];
                        $data[] = $temp;
                    } else {
                        return null;
                    }
                }
            }
            $data = MatomoHelper::calculateTotalAndPercentage($data);
        }
        return $data;
    }

    /**
     * @param string $data
     * 
     * @param int $matomoAnalyticsId
     * 
     * @param string $period
     *
     * @return array|mixed
     */
    public static function getProductPageVisitCount($matomoAnalyticsId, $period, $date)
    {
        $data['product_page_views'] = self::getContentCount('Product+Page', $matomoAnalyticsId, $period, $date);
        return $data;
    }

    /**
     * @param string $data
     * 
     * @param int $matomoAnalyticsId
     * 
     * @param string $period
     *
     * @return array|mixed
     */
    public static function getCouponRedemptionCount($matomoAnalyticsId, $period, $date)
    {
        $data['coupon_redemption_views'] = self::getContentCount('Product+Page', $matomoAnalyticsId, $period, $date);
        return $data;
    }

    /**
     * @param string $data
     * 
     * @param int $matomoAnalyticsId
     * 
     * @param string $period
     *
     * @return array|mixed
     */
    public static function getFormSubmitCount($matomoAnalyticsId, $period, $date)
    {
        $data['form_submit_views'] = self::getContentCount('Product+Page', $matomoAnalyticsId, $period, $date);
        return $data;
    }

    /**
     * @param int $matomoAnalyticsId
     * 
     * @param string $slug
     * 
     * @param string $period
     *
     * @param string $date
     *
     * @param boolean $isSubPage
     * 
     * @return array|mixed
     */
    public static function getPagedCount($matomoAnalyticsId, $slug, $period, $date, $isSubPage = false){
        $label = MatomoHelper::convertLabelFromSlug($slug, $isSubPage);
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'apiModule' => 'Actions',
            'apiAction' => 'getPageUrls',
            'idSite' => $matomoAnalyticsId,
            'period' => $period,
            'date' => $date,
            'token_auth' =>  config('matomo.token'),
            'module'=> 'API',
            'format' => 'json',
            'label' => $label
        ];
        $result = [
            'total' => 0,
            'unique' => 0,
            'bounce_rate' => 0,
            'avg_time_on_page' => 0,
            'exit_rate' => 0,
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        MatomoHelper::parseMatomoResponse($matomoResponse);
        $matomoResponse = json_decode($matomoResponse, true);
        if (isset($matomoResponse['reportData'])) {
            $result = [
                'total' => $matomoResponse['reportData'][0]['nb_hits'],
                'unique' => $matomoResponse['reportData'][0]['nb_visits'],
                'bounce_rate' => $matomoResponse['reportData'][0]['bounce_rate'],
                'avg_time_on_page' => $matomoResponse['reportData'][0]['avg_time_on_page'],
                'exit_rate' => $matomoResponse['reportData'][0]['exit_rate']
            ];
        }
        return $result;
    }

    /**
     * @param string $data
     * 
     * @param int $matomoAnalyticsId
     * 
     * @param string $period
     *
     * @return array|mixed
     */
    private function getProcessedReport(int $matomoAnalyticsId, string $period, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'apiModule' => 'API',
            'apiAction' => 'get',
            'idSite' => $matomoAnalyticsId,
            'period' => $period,
            'date' => $date,
            'token_auth' => config('matomo.token'),
        ];
        $apiEndpoint = config('matomo.api_uri')  . MatomoHelper::generateApiParamStr($apiParams);
        $data = [
            'total_page_views' => 0,
            'unique_page_views' => 0,
        ];
        $matomoResponse =  MatomoHelper::callApi($apiEndpoint);
        MatomoHelper::parseMatomoResponse($matomoResponse);
        $matomoResponse = json_decode($matomoResponse, true);

        if (isset($matomoResponse['reportTotal'])) {
            $data = [
                'total_page_views' => $matomoResponse['reportTotal']['nb_pageviews'],
                'unique_page_views' => $matomoResponse['reportTotal']['nb_uniq_pageviews'],
            ];
        }

        return $data;
    }

    /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     *
     * @param string $contentName
     *
     * @return array|mixed
     * 
     * https://analytics.page-maker.site/?module=API&method=Contents.getContentNames&idSite=32&period=range&date=2020-07-07,today&format=json&token_auth=c1752db0f01c3f9e4bd18e0bc2fafbc3&label=Product+Page
     * Use the reportTotal from the response as it is all pre calculated
     */
    private function getContentCount($contentName, int $matomoAnalyticsId, string $period, string $date)
    {
        $apiParams = [
            'method' => 'Contents.getContentNames',
            'apiModule' => 'API',
            'apiAction' => 'get',
            'idSite' => $matomoAnalyticsId,
            'period' => $period,
            'date' => $date,
            'token_auth' =>  config('matomo.token'),
            'label' => $contentName,
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = 0;
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        MatomoHelper::parseMatomoResponse($matomoResponse);
        $matomoResponse = json_decode($matomoResponse, true);
        if (isset($matomoResponse['0']) && !empty($matomoResponse)) {
            $matomoResponse = $matomoResponse['0'];
            if (isset($matomoResponse['segment']) && $matomoResponse['segment'] === 'contentName==' . $contentName) {
                $data = $matomoResponse['nb_impressions'];
            }
        }

        return $data;
    }
}
