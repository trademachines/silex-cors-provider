# Silex CORS Provider

[![Build Status](https://travis-ci.org/trademachines/silex-cors-provider.svg?branch=master)](https://travis-ci.org/trademachines/silex-cors-provider)

Service provider for addings CORS capabilities to Silex.

## Usage

There is no way of defining app-wide CORS settings, you have to do this for every single route
or add necessary logic on top of the service provider.

The enable CORS support register the provider first:

    $app->register(new CorsServiceProvider());

Then add CORS support per route:

    $cors = new Cors();
    $cors->allowOrigin('http://my.domain.com');
    $app
      ->put('/posts', function() {})
      ->cors($cors);
