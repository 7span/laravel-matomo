<?php

declare (strict_types = 1);

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
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        $matomoResponse = MatomoHelper::parseMatomoResponse($matomoResponse);
        $data['status'] = 'success';
        $data['statusCode'] = 200;
        $data['data']['siteId'] = $matomoResponse['value'];
        return $data;
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
        $response = MatomoHelper::callApi($apiEndpoint);
        $response = MatomoHelper::parseMatomoResponse($response);
        $data['status'] = 'success';
        $data['statusCode'] = 200;
        return $response;
    }

    /**
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     *
     * Refrance https://developer.matomo.org/api-reference/reporting-api
     */
    public static function getVisitorsData(int $matomoAnalyticsId, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'idSite' => $matomoAnalyticsId,
            'period' => 'range',
            'date' => $date,
            'token_auth' => config('matomo.token'),
            'apiModule' => 'VisitsSummary',
            'apiAction' => 'get',
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = [];
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        $matomoResponse = MatomoHelper::parseMatomoResponse($matomoResponse);
        if (isset($matomoResponse['reportData'])) {
            $reportData = $matomoResponse['reportData'];
            foreach ($reportData as $k => $d) {
                if (!empty($d)) {
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
            $data = MatomoHelper::calculateTotalAndPercentage($data);
        }

        return $data;
    }

    /**
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getPageWiseViewCount(int $matomoAnalyticsId, string $date)
    {
        $views = self::getProcessedReport($matomoAnalyticsId, $date);
        $data = [
            'total_page_views' => 0,
            'unique_page_views' => 0,
            'product_page_views' => self::getProductPageVisitCount($matomoAnalyticsId, $date)['product_page_views'],
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
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getCountryWiseReport(int $matomoAnalyticsId, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'idSite' => $matomoAnalyticsId,
            'period' => 'range',
            'date' => $date,
            'token_auth' => config('matomo.token'),
            'apiModule' => 'UserCountry',
            'apiAction' => 'getCountry',
            'filter_truncate' => '5',
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = [];
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        $matomoResponse = MatomoHelper::parseMatomoResponse($matomoResponse);
        if (isset($matomoResponse['reportData'])) {
            $reportData = $matomoResponse['reportData'];
            foreach ($reportData as $d) {
                $temp = [
                    'name' => $d['label'],
                    'count' => $d['nb_visits'],
                    'percentage' => 0.0,
                    'avg_time_on_site' => $d['avg_time_on_site'],
                ];
                $data[] = $temp;
            }
            $data = MatomoHelper::calculateTotalAndPercentage($data);
        }
        return $data;
    }

    /**
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getBrowserWiseReport(int $matomoAnalyticsId, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'idSite' => $matomoAnalyticsId,
            'period' => 'range',
            'date' => $date,
            'token_auth' => config('matomo.token'),
            'apiModule' => 'DevicesDetection',
            'apiAction' => 'getBrowsers',
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = [];
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        $matomoResponse = MatomoHelper::parseMatomoResponse($matomoResponse);
        if (isset($matomoResponse['reportData'])) {
            $reportData = $matomoResponse['reportData'];
            foreach ($reportData as $k => $d) {
                if (!empty($d)) {
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
            $data = MatomoHelper::calculateTotalAndPercentage($data);
        }
        return $data;
    }

    /**
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getProductPageVisitCount(int $matomoAnalyticsId, string $date)
    {
        $data['product_page_views'] = self::getContentCount('Product+Page', $matomoAnalyticsId, $date);
        return $data;
    }

    /**
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getCouponRedemptionCount(int $matomoAnalyticsId, string $date)
    {
        $data['coupon_redemption_views'] = self::getContentCount('Coupon+Redemption', $matomoAnalyticsId, $date);
        return $data;
    }

    /**
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getFormSubmitCount(int $matomoAnalyticsId, string $date)
    {
        $data['form_submit_views'] = self::getContentCount('Form+Submit', $matomoAnalyticsId, $date);
        return $data;
    }

    /**
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getDeviceWiseReport(int $matomoAnalyticsId, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'idSite' => $matomoAnalyticsId,
            'period' => 'range',
            'date' => $date,
            'token_auth' => config('matomo.token'),
            'apiModule' => 'DevicesDetection',
            'apiAction' => 'getType',
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = [];
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        $matomoResponse = MatomoHelper::parseMatomoResponse($matomoResponse);
        if (isset($matomoResponse['reportData'])) {
            $reportData = $matomoResponse['reportData'];
            foreach ($reportData as $d) {
                $temp = [
                    'name' => $d['label'],
                    'count' => $d['nb_visits'],
                    'percentage' => 0.0,
                    'avg_time_on_site' => $d['avg_time_on_site'],
                ];
                $data[] = $temp;
            }
            $data = MatomoHelper::calculateTotalAndPercentage($data);
        }
        return $data;
    }

    /**
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getDayWiseReport(int $matomoAnalyticsId, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'idSite' => $matomoAnalyticsId,
            'period' => 'day',
            'date' => $date,
            'token_auth' => config('matomo.token'),
            'apiModule' => 'VisitsSummary',
            'apiAction' => 'get',
        ];

        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = [];
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        $matomoResponse = MatomoHelper::parseMatomoResponse($matomoResponse);
        if (isset($matomoResponse['reportData'])) {
            $reportData = $matomoResponse['reportData'];
            foreach ($reportData as $k => $d) {
                if (!empty($d)) {
                    $date = MatomoHelper::parseDateString($k);
                    $temp = [
                        'date' => strtotime($date),
                        'count' => $d['nb_visits'],
                        'percentage' => 0.0,
                        'avg_time_on_site' => $d['avg_time_on_site'],
                    ];
                    $data[] = $temp;
                }
            }
            $data = MatomoHelper::calculateTotalAndPercentage($data);
        }
        return $data;
    }

    /**
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @param int|null $formSubmissionCount
     *
     * @return array|mixed
     */
    public static function getCounterReport(int $matomoAnalyticsId, string $date, int $formSubmissionCount = null)
    {
        $views = $views = self::getProcessedReport($matomoAnalyticsId, $date);
        $data = [
            'views' => 0,
            'unique_views' => 0,
            'form_submissions' => $formSubmissionCount,
            'product_page_views' => self::getProductPageVisitCount($matomoAnalyticsId, $date)['product_page_views'],
            // 'coupons_redeemed' => $this->matomo->campaign->couponRedeemers()->count() ,
            'conversation_ratio' => '0%',
        ];

        if (isset($views['unique_page_views'])) {
            $data['unique_views'] = $views['unique_page_views'];
        }

        if (isset($views['total_page_views'])) {
            $data['views'] = $views['total_page_views'];
        }

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
     * @param bool $isSubPage
     * 
     * @return array|mixed
     */
    public static function getPagedCount(int $matomoAnalyticsId, string $slug, string $date, bool $isSubPage = false){
        $label = MatomoHelper::convertLabelFromSlug($slug, $isSubPage);
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'apiModule' => 'Actions',
            'apiAction' => 'getPageUrls',
            'idSite' => $matomoAnalyticsId,
            'period' => 'range',
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
        $matomoResponse = MatomoHelper::parseMatomoResponse($matomoResponse);
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
     * @param string $date
     *
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    private function getProcessedReport(int $matomoAnalyticsId, string $date)
    {
        $apiParams = [
            'method' => 'API.getProcessedReport',
            'apiModule' => 'API',
            'apiAction' => 'get',
            'idSite' => $matomoAnalyticsId,
            'period' => 'range',
            'date' => $date,
            'token_auth' => config('matomo.token'),
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = [
            'total_page_views' => 0,
            'unique_page_views' => 0,
        ];
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        $matomoResponse = MatomoHelper::parseMatomoResponse($matomoResponse);
        if (isset($matomoResponse['reportTotal'])) {
            $data = [
                'total_page_views' => $matomoResponse['reportTotal']['nb_pageviews'],
                'unique_page_views' => $matomoResponse['reportTotal']['nb_uniq_pageviews'],
            ];
        }

        return $data;
    }

    /**
     * @param string $date
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
    private function getContentCount($contentName, int $matomoAnalyticsId, string $date)
    {
        $apiParams = [
            'method' => 'Contents.getContentNames',
            'apiModule' => 'API',
            'apiAction' => 'get',
            'idSite' => $matomoAnalyticsId,
            'period' => 'range',
            'date' => $date,
            'token_auth' => config('matomo.token'),
            'label' => $contentName,
        ];
        $apiEndpoint = config('matomo.api_uri') . MatomoHelper::generateApiParamStr($apiParams);
        $data = 0;
        $matomoResponse = MatomoHelper::callApi($apiEndpoint);
        $matomoResponse = MatomoHelper::parseMatomoResponse($matomoResponse);
        if (isset($matomoResponse['0']) && !empty($matomoResponse)) {
            $matomoResponse = $matomoResponse['0'];
            if (isset($matomoResponse['segment']) && $matomoResponse['segment'] === 'contentName==' . $contentName) {
                $data = $matomoResponse['nb_impressions'];
            }
        }

        return $data;
    }
}
