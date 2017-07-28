<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \search\controller\RequestController as RequestApi;

require '../vendor/autoload.php';

$c = new \Slim\Container();
$app = new \Slim\App($c);

$app->get('/', function ($request, $response, $args) {
      
});

$app->run();

