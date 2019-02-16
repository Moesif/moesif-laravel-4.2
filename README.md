# Moesif Laravel

[![Built For Laravel][ico-built-for]][link-built-for]
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE.md)

Moesif SDK for Laravel 4.2.*

* [Compatibility](#compatibility)
* [Installation](#installation)
* [Configuration](#configuration)

## Compatibility

Currently only compatible with Laravel 4.2.*

## Installation

Via Composer

``` bash
$ composer require moesif/moesif-laravel42
```

After updating composer, add the ServiceProvider to the `providers` array in `config/app.php`

``` php
MoesifLaravel42\MoesifLaravel\ServiceProvider::class,
```

## Configuration

You will need to provide your Moesif Application ID in order to get this working.

You can find your Application ID from the [_Moesif Dashboard_](https://www.moesif.com/) -> _Top Right Menu_ -> _Installation_

If you don't need to change any other config values, you can get away with just setting the appropriate env variable. The necessary env variable for the Application ID is:

```php
MOESIF_APPLICATION_ID
```

The other env variable that you can set is: 
```php
MOESIF_DEBUG
```

Some sensible defaults are set for you in the config file, but if you need to modify it, you can modify it to fit your needs after publishing it with the command below.

```php
php artisan config:publish moesif/moesif-laravel42
```

(DO NOT PUT YOUR APPLICATION ID IN THE CONFIG FILE. USE THE ENV VARIABLE.)

```php
<?php

return array(
    
    /*
    |--------------------------------------------------------------------------
    | Moesif Application ID
    |--------------------------------------------------------------------------
    |
    | This is the Moesif application id.
    |
    */

    'application_id' => getEnv('MOESIF_APPLICATION_ID'),

    /*
    |--------------------------------------------------------------------------
    | Skip
    |--------------------------------------------------------------------------
    |
    | Return true if the event is to be skipped.
    |
    */

    'skip' => function ($request, $response) {
        $host = explode('.', $request->server('HTTP_HOST'));
        return $host[0] != 'api';
    },

    /*
    |--------------------------------------------------------------------------
    | Mask Request Headers
    |--------------------------------------------------------------------------
    |
    | Add or remove request headers.
    |
    */

    'maskRequestHeaders' => function ($headers) {
        return $headers;
    },

    /*
    |--------------------------------------------------------------------------
    | Mask Request Body
    |--------------------------------------------------------------------------
    |
    | Remove any fields from body that you don't want sent to Moesif.
    |
    */

    'maskRequestBody' => function ($body) {
        if (isset($body['password'])) {
            $body['password'] = str_repeat('*', 18);
        }
        return $body;
    },

    /*
    |--------------------------------------------------------------------------
    | Mask Response Headers
    |--------------------------------------------------------------------------
    |
    | Add or remove response headers.
    |
    */

    'maskResponseHeaders' => function ($headers) {
        return $headers;
    },

    /*
    |--------------------------------------------------------------------------
    | Mask Response Body
    |--------------------------------------------------------------------------
    |
    | Remove any fields from body that you don't want sent to Moesif.
    |
    */

    'maskResponseBody' => function ($body) {
        return $body;
    },

    /*
    |--------------------------------------------------------------------------
    | Identify User ID
    |--------------------------------------------------------------------------
    |
    | Identify the user.
    |
    */

    'identifyUserId' => function ($request, $response) {
        return null;
    },

    /*
    |--------------------------------------------------------------------------
    | Identify Session ID
    |--------------------------------------------------------------------------
    |
    | Identify the session.
    |
    */

    'identifySessionId' => function ($request, $response) {
        if ($request->hasSession()) {
            return $request->session()->getId();
        } else {
            return null;
        }
    },

    /*
    |--------------------------------------------------------------------------
    | Meta Data
    |--------------------------------------------------------------------------
    |
    | Add any extra data to be sent to Moesif.
    |
    */

    'getMetaData' => function ($request, $response) {
        return [];
    },

    /*
    |--------------------------------------------------------------------------
    | Tags
    |--------------------------------------------------------------------------
    |
    | Add any tags to be sent to Moesif.
    |
    */

    'addTags' => function ($request, $response) {
        return '';
    },

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    |
    | Identify the API Version.
    |
    */

    'apiVersion' => function ($request, $response) {
        return null;
    },

    /*
    |--------------------------------------------------------------------------
    | Debug
    |--------------------------------------------------------------------------
    |
    | Set to true to see debug information.
    |
    */

    'debug' => getEnv('MOESIF_DEBUG', false),
    
);
```

Documentation for the various config values is below.

#### applicationId
Type: `String`
Required, a string that identifies your application.

#### identifyUserId
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for userId.

#### identifySessionId
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for sessionId. Moesif automatically sessionizes by processing at your data, but you can override this via identifySessionId if you're not happy with the results.

#### getMetadata
Type: `($request, $response) => Associative Array`
Optional, a function that takes a $request and $response and returns $metdata which is an associative array representation of JSON.

#### apiVersion
Type: `String`
Optional, a string to specifiy an API Version such as 1.0.1, allowing easier filters.

#### maskRequestHeaders
Type: `$headers => $headers`
Optional, a function that takes a $headers, which is an associative array, and
returns an associative array with your sensitive headers removed/masked.

#### maskRequestBody
Type: `$body => $body`
Optional, a function that takes a $body, which is an associative array representation of JSON, and
returns an associative array with any information removed.

#### maskResponseHeaders
Type: `$headers => $headers`
Optional, same as above, but for Responses.

#### maskResponseBody
Type: `$body => $body`
Optional, same as above, but for Responses.

#### skip
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and returns true if
this API call should be not be sent to Moesif.

#### debug
Type: `Boolean`
Optional, If true, will print debug messages using Illuminate\Support\Facades\Log

## Credits for Moesif Laravel SDK

- All I did was modify __[Moesif's existing Laravel package](https://github.com/Moesif/moesif-laravel)__ to be compatible with Laravel 4.2. All credit for what actually matters should be given to __[Xing Wang](https://github.com/xinghengwang)__.

## Additional Tips:

- The forked (i.e. non-blocking way) of sending data is using exec() with a cURL command. The Php exec() command can be successful but the cURL itself may have 401 errors.  So after integration, if you don't see events and data show up in your Moesif Dash. Please turn on debug option, then the cURL command itself will be logged. You can execute that cURL command and see what the issues are. The most common thing to check is if the Application ID is set correctly.

## Other integrations

To view more more documentation on integration options, please visit __[the Integration Options Documentation](https://www.moesif.com/docs/getting-started/integration-options/).__


[ico-version]: https://img.shields.io/packagist/v/moesif/moesif-laravel42.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/moesif/moesif-laravel42.svg?style=flat-square
[ico-built-for]: https://img.shields.io/badge/built%20for-laravel-blue.svg

[link-packagist]: https://packagist.org/packages/moesif/moesif-laravel42
[link-downloads]: https://packagist.org/packages/moesif/moesif-laravel42
[link-built-for]: http://laravel.com
[link-cub-php]: https://packagist.org/packages/cub/cub
[link-cub-widget-docs]: https://github.com/praetoriandigital/cub-docs
