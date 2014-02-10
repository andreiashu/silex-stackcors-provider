# Silex Cors Service Provider

Silex service provider enabling cross-origin resource sharing for your
Silex application. It's based on the [Stack/Cors Library](https://github.com/asm89/stack-cors)
in order to do request/response handling.

Master [![Build Status](https://api.travis-ci.org/andreiashu/silex-stackcors-provider.png?branch=master)](https://travis-ci.org/andreiashu/silex-stackcors-provider)

## Installation

Require `andreiashu/silex-stackcors-provider` using composer.

## Usage
For more options see the [Stack/Cors Library readme](https://github.com/asm89/stack-cors)

```php
<?php

$app = new Silex\Application();
$cors_options = array(
    // allow all headers
    'allowedHeaders' => array('*'),
    // allow requests from localhost only. Use '*' to allow any origins
    'allowedOrigins' => array('localhost'),
    // optional: use a specific response class when the request is not allowed
    // should be a subclass of \Symfony\Component\HttpFoundation\Response
    // example
    'denied_reponse_class' => '\Andreiashu\Silex\Provider\CorsServiceDeniedResponse'
);
$app->register(new Andreiashu\Silex\Provider\CorsServiceProvider($cors_options));

// in a REST API you can add an ->after() hook to check if there are any CORS
// errors and render your response according to your API error standards
$app->after(function (Request $request, Response $response) use ($app) {
    if (is_a($response, '\Andreiashu\Silex\Provider\CorsServiceDeniedResponse')) {
        // alter the response object as needed
    }
});
```
