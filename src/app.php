<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\SessionServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
// Nombre de la session a manejar
$app->register(new SessionServiceProvider(), array(
	'session.storage.options' => array(
		'name' => '_ACCEDA'
	)
));

// Servicio de email
$app->register(new SwiftmailerServiceProvider(), array(
	'host' => 'mail.acceda.mx',
	'port' => 26,
	'username' => 'no-reply@acceda.mx',
	'password' => '4eNo_[8.gRA6',
	'encryption' => null,
	'auth_mode' => null //'plain', 'login'
));

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});

return $app;
