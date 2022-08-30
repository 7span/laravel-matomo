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
    public static function getPagedCount($matomoAnalyticsId, $slug, $period, $date, $isSubPage = false);
}
