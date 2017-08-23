<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \search\controller\RequestController as RequestApi;

require '../vendor/autoload.php';

$c   = new \Slim\Container();
$app = new \Slim\App($c);

$app->get('/oai', function($request, $response, $args)
{
    $allGetVars = $request->getQueryParams();
    $request    = new RequestApi();
    
    if ($allGetVars['verb'] == 'Identify') {
        $legitarg = array(
            'verb'
        );
        $xml      = $request->identify();
        
    } elseif ($allGetVars['verb'] == 'ListMetadataFormats') {
        $legitarg = array(
            'verb',
            'identifier'
        );
        $xml      = $request->ListMetadataFormats();
    } elseif ($allGetVars['verb'] == 'ListSets') {
        $legitarg = array(
            'verb',
            'resumptionToken'
        );
        $xml      = $request->ListSets();
    } elseif ($allGetVars['verb'] == 'ListIdentifiers') {
        $legitarg = array(
            'verb',
            'metadataPrefix',
            'from',
            'until',
            'set',
            'resumptionToken'
        );
        if (empty($allGetVars['metadataPrefix'])) {
            if (empty($allGetVars['resumptionToken'])) {
                $xml = $request->BadArgument($allGetVars['verb']);
                
            } else {
                $xml = $request->ListIdentifiers(null, null, null, null, $allGetVars['resumptionToken']);
            }
            
        } else {
            $xml = $request->ListIdentifiers($allGetVars['metadataPrefix'], @$allGetVars['from'], @$allGetVars['until'], @$allGetVars['set'], @$allGetVars['resumptionToken']);
        }
    } elseif ($allGetVars['verb'] == 'ListRecords') {
        $legitarg = array(
            'verb',
            'metadataPrefix',
            'from',
            'until',
            'set',
            'resumptionToken'
        );
        if (empty($allGetVars['metadataPrefix'])) {
            if (empty($allGetVars['resumptionToken'])) {
                $xml = $request->BadArgument($allGetVars['verb']);
                
            } else {
                $xml = $request->ListRecords(null, null, null, null, $allGetVars['resumptionToken']);
            }
        } else {
            $xml = $request->ListRecords($allGetVars['metadataPrefix'], @$allGetVars['from'], @$allGetVars['until'], @$allGetVars['set'], @$allGetVars['resumptionToken']);
        }
    } elseif ($allGetVars['verb'] == 'GetRecord') {
    	$allGetVars['identifier']=str_replace('info:doi:', '', $allGetVars['identifier']);
        $legitarg = array(
            'verb',
            'metadataPrefix',
            'identifier'
        );
        if (empty($allGetVars['identifier']) OR empty($allGetVars['metadataPrefix'])) {
            $xml = $request->BadArgument($allGetVars['verb']);
            
        } else {
            $identifier = $allGetVars['identifier'];
            $xml        = $request->GetRecord($identifier, $allGetVars['metadataPrefix']);
            
        }
    } else {
        $xml = $request->IllegalVerb();
        print $xml;
        return $response->WithHeader("Content-type:", "text/xml");
        
    }
    $badarg = 0;
    foreach ($allGetVars as $key => $param) {
        if (!in_array($key, $legitarg)) {
            $xml = $request->BadArgument($allGetVars['verb']);
            print $xml;
            return $response->WithHeader("Content-type:", "text/xml");
        } else {
            $badarg = 1;
        }
    }
    if (isset($allGetVars['metadataPrefix'])) {
        $supportedformat = array(
            'oai_dc'
        );
        
        if (!in_array($allGetVars['metadataPrefix'], $supportedformat)) {
            $xml = $request->cannotDisseminateFormat($allGetVars['verb']);
            print $xml;
            return $response->WithHeader("Content-type:", "text/xml");
        } else {
            $badarg = 1;
        }
    }
    
    
    if ($badarg == 1) {
        print $xml;
        return $response->WithHeader("Content-type:", "text/xml");
        
    }
    
    
});

$app->run();