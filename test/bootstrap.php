<?php

if (file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    $loader = require_once $file;
    $loader->add('Andreiashu\Silex\Provider', __DIR__);
    $loader->add('Andreiashu\Silex\Provider', '/../src');
} else {
    throw new RuntimeException('Install dependencies to run test suite.');
}

