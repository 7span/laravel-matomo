<?php

declare(strict_types=1);

namespace SevenSpan\Matomo;

interface MatomoInterface
{
    /**
     * @return array|mixed
     * 
     * @param string $siteName
     */
    public function addSite(string $siteName);

    /**
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public function removeSite(int $matomoAnalyticsId);

    /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     * 
     */
    public static function getVisitorsData(int $matomoAnalyticsId, string $period, string $date);

    /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getPageWiseViewCount(int $matomoAnalyticsId, string $period, string $date);

    /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getProductPageVisitCount($matomoAnalyticsId, $period, $date);

    /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getCouponRedemptionCount($matomoAnalyticsId, $period, $date);

    /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getFormSubmitCount($matomoAnalyticsId, $period, $date);

     /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getDeviceWiseReport(int $matomoAnalyticsId, string $period, string $date);

     /**
     * @param string $data
     * 
     * @param string $period
     * 
     * @param int|null $matomoAnalyticsId
     *
     * @param string $formSubmissionCount
     *
     * @return array|mixed
     */
    public static function getCounterReport(int $matomoAnalyticsId, string $period, string $date, int $formSubmissionCount = null);
}
