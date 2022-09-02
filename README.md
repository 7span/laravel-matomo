# Laravel Matomo

## About

The `7span/laravel-matomo` laravel package is connect your project with matomo analytics tool and by using basic api of matomo gives you the json data for your dashboard.

## Usages

 * Create site in matomo.
 * Remove site in matomo.
 * Get visitor analytic data using site id.
 * Get paged analytic data using site id.
 * Get product page visit count analytic data using site id.
 * Get country wise analytic data using site id.
 * Get browser wise analytic data using site id.

## Installation

Via Composer

Run `composer require 7span/laravel-matomo` in your terminal to install the package or require `7span/laravel-matomo` package inyour composer json and install the package


Publish the config file

Run publish command to copy the matomo config file in your project.

```
php artisan vendor:publish --provider="SevenSpan\Matomo\Providers\MatomoServiceProvider" --tag="config"
```

Add your matomo configurations in the config file

```
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Matomo API URI
    |--------------------------------------------------------------------------
    |
    | Matomo API URI.
    |
    */

    'api_uri' => env('MATOMO_API_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Matomo Token
    |--------------------------------------------------------------------------
    |
    | Matomo Access Token.
    |
    */

    'token' => env('MATOMO_TOKEN', '')

];
```

You are ready to go!

## Documentation and examples 

```php

// Load object
use SevenSpan\Matomo\Facades\Matomo;

## Add Site

// $siteName = "Campaign - 1212121212"; (Required)
Matomo::addSite($siteName);

## Remove site

// $matomoAnalyticsId = 1234; (Required)
Matomo::removeSite($matomoAnalyticsId);

## Get visters data beetween range

// $matomoAnalyticsId = 16234; (Required)
// $dates = array("2022-07-01", "2022-07-31"); (Required)

// Note : If you want to get data of month or year, you can pass related date array to $dates parameter.

Matomo::getVisitorsData($matomoAnalyticsId, $dates); // Get visitor data
Matomo::getPageWiseViewCount($matomoAnalyticsId, $dates); // Get page wise view count
Matomo::getProductPageVisitCount($matomoAnalyticsId, $dates); // Get product page visit count
Matomo::getCountryWise($matomoAnalyticsId, $dates); // Get country wise data
Matomo::getBrowserWiseReport($matomoAnalyticsId, $dates); // Get browser wise
