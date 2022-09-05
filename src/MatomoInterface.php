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
     * @param string $date 
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     * 
     */
    public static function getVisitorsData(int $matomoAnalyticsId, string $date);

    /**
     * @param string $date
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getPageWiseViewCount(int $matomoAnalyticsId, string $date);

    /**
     * @param string $date
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getCountryWiseReport(int $matomoAnalyticsId, string $date);

    /**
     * @param string $date
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getBrowserWiseReport(int $matomoAnalyticsId, string $date);

    /**
     * @param string $date
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getProductPageVisitCount(int $matomoAnalyticsId, string $date);

    /**
     * @param string $date
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getCouponRedemptionCount(int $matomoAnalyticsId, string $date);

    /**
     * @param string $date
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getFormSubmitCount(int $matomoAnalyticsId, string $date);

     /**
     * @param string $date
     * 
     * @param int $matomoAnalyticsId
     *
     * @return array|mixed
     */
    public static function getDeviceWiseReport(int $matomoAnalyticsId, string $date);

     /**
     * @param string $date
     * 
     * @param int|null $matomoAnalyticsId
     *
     * @param string $formSubmissionCount
     *
     * @return array|mixed
     */
    public static function getCounterReport(int $matomoAnalyticsId, string $date, int $formSubmissionCount = null);

    /**
     * @param int $matomoAnalyticsId
     * 
     * @param string $slug
     * 
     * @param string $date
     *
     * @param bool $isSubPage
     * 
     * @return array|mixed
     */
    public static function getPagedCount(int $matomoAnalyticsId, string $slug, string $date, bool $isSubPage = false);
}
