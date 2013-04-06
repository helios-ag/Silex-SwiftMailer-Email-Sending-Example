<?php

use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;

$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider());

$app->register(new TwigServiceProvider(), array(
    'twig.path' => array(__DIR__.'/templates'),
    'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));

$app->register(new SwiftmailerServiceProvider());

$app['swiftmailer.options'] = array(
    'host'       => 'smtp.gmail.com',
    'port'       => 465,
    'username'   => 'silex.swiftmailer@gmail.com',
    'password'   => 'simplepassword',
    'encryption' => 'ssl',
    'auth_mode'  => 'login'
);

return $app;