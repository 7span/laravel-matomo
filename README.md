## Laravel Matomo

### Documentation and examples 
```php
// Load object
use Matomo;

// Required variables
$matomoUrl = "https://analytics.7span.com/"; // Your matomo URL
$matomoToken = "";                  // Your authentication token

// Matomo object
$matomo = new Matomo($matomoUrl);
$matomo->setTokenAuth($matomoToken);

$matomoAddSiteObject = [
    "method" => "SitesManager.addSite",
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
```