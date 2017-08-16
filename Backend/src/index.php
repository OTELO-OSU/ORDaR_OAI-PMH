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
		
		$legitarg=['verb','identifier','metadataPrefix'];
		foreach($allGetVars as $key => $param){
			if (!in_array($key, $legitarg)){
	    	$xml= $request->BadArgument();
	    	print $xml;
	    	return $response->WithHeader("Content-type:","text/xml");					
			}
		}
			
			
	    if ($allGetVars['verb']=='Identify') {
	    	$xml= $request->identify();
	    	print $xml;
	    	return $response->WithHeader("Content-type:","text/xml");
	    }
	    elseif ($allGetVars['verb']=='ListMetadataFormats') {
	    	$xml= $request->ListMetadataFormats();
	    	print $xml;
	    	return $response->WithHeader("Content-type:","text/xml");
	    }
	    elseif ($allGetVars['verb']=='ListSets') {
	    	$xml= $request->ListSets();
	    	print $xml;
	    	return $response->WithHeader("Content-type:","text/xml");
	    }
	    elseif ($allGetVars['verb']=='GetRecord') {
	    	if (empty($allGetVars['identifier']) OR empty($allGetVars['metadataPrefix'])) {
	    		$xml= $request->BadArgument();
		    	
	    	}
	    	else{
	    		$identifier=$allGetVars['identifier'];
	    		$xml= $request->GetRecord($identifier,$allGetVars['metadataPrefix']);
	    		
	    	}
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

