# Laravel Matomo

## About

The `7span/laravel-matomo` Laravel package is connect matomo using basic api and gives you the json for the perpare your dashboard.

## Features

 * Create site in matomo.
 * Get analytic data using site id.
 * Get paged data using site id and page label.
 * Remove site in matomo.

## Installation

Via Composer

Require the `7span/laravel-matomo` package in your `composer.json` and update your dependencies:
``` bash
$ composer require 7span/laravel-matomo
```

Publish the config file (optional)

Run `php artisan vendor:publish` to publish the config file if needed.
``` bash
$ php artisan vendor:publish
```

Update your `.env`

Add these variables to your `.env` file and configure it to fit your environment.
``` bash
MATOMO_API_URL="https://your.matomo-install.com"
MATOMO_TOKEN="00112233445566778899aabbccddeeff"
```

That's it!

## Documentation and examples 
```php
// Load object
use SevenSpan\Matomo\Facades\Matomo;

Add Site

$siteName = "Campaign - 1212121212";
$response =  Matomo::addSite($siteName);
return $response;

Remove site

$response =  Matomo::removeSite($matomoAnalyticsId);
return $response;

Get visters data daywise

$matomoAnalyticsId = 16234;
$period = "day";
$date = "2022-07-01,today"; //lastweek, lastMonth, lastYear
$response =  Matomo::getVisitorsData($matomoAnalyticsId, $period, $date);
return $response;

Get page wise view count using range filter

$matomoAnalyticsId = 15744;
$period = "range";
$date = "2021-01-01,today";
$response =  Matomo::getPageWiseViewCount($matomoAnalyticsId, $period, $date);

Get product page visit count using range filter

$matomoAnalyticsId = 15744;
$period = "range";
$date = "2021-01-01,today";
$response =  Matomo::getProductPageVisitCount($matomoAnalyticsId, $period, $date);

Get country wise data using day

$matomoAnalyticsId = 15744;
$period = "day";
$date = "2022-08-01,today"; //lastweek, lastMonth, lastYear
$response =  Matomo::getCountryWise($matomoAnalyticsId, $period, $date);

GetBrowserWise using day

$matomoAnalyticsId = 15744;
$period = "day";
$date = "2022-08-01,today"; //lastweek, lastMonth, lastYear
$response =  Matomo::getBrowserWiseReport($matomoAnalyticsId, $period, $date);
return $response;
