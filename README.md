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
use Matomo;

// Matomo object
$matomo = new Matomo();

$matomoAddSiteObject = [
    "siteName" => "7Span%20Campaign%20-%20001", // urlencode('7Span Campaign - 001')
];
// Create site in matomo
$matomoSite = $matomo->addSite($matomoAddSiteObject);
return $matomoSite;

/*
Success response of matomo add site.
{
    "status":"success",
    "statusCode":"200",
    "data"{
        "siteId":1
    }
}
Failer response of matomo add site.
{
    "status":"fail",
    "statusCode":"400",
    "error"{
        "message":"Message of the error massage"
    }
}
*/

// Create site in matomo
$matomoSite = $matomo->deleteSite($matomoSiteId);
return $matomoSite;

/*
Success response of matomo add site.
{
    "status":"success",
    "statusCode":"200"
}
Failer response of matomo add site.
{
    "status":"fail",
    "statusCode":"400",
    "error"{
        "message":"Message of the error massage"
    }
}
*/
```
