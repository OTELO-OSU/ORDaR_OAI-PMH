<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \search\controller\RequestController as RequestApi;

require '../vendor/autoload.php';

$c = new \Slim\Container();
$app = new \Slim\App($c);

$app->get('/oai', function ($request, $response,$args) {
		$allGetVars = $request->getQueryParams();
		$request= new RequestApi();
		$legitarg=['verb'];
		foreach($allGetVars as $key => $param){
			foreach ($legitarg as  $value) {
				if ($key!=$value) {
	    	$xml= $request->BadArgument();
	    	print $xml;
	    	return $response->WithHeader("Content-type:","text/xml");				}
			}

		}
	    if ($allGetVars['verb']=='identify') {
	    	$xml= $request->identify();
	    	print $xml;
	    	return $response->WithHeader("Content-type:","text/xml");
	    }
	    else{
			$xml= $request->IllegalVerb();
	    	print $xml;
	    	return $response->WithHeader("Content-type:","text/xml");
	    }

      
});

$app->run();

