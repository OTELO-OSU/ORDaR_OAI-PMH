<?php
namespace search\controller;

class RequestController
{
function identify(){
	 $config=self::ConfigFile();
	 $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xmlns:xmlns:mml', 'http://www.w3.org/1998/Math/MathML');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $request = $sxe->addChild('request', 'DOI');
     $request->addAttribute('verb','Identify');
     $identify=$sxe->addChild('Identify');
     $identify->addChild('repositoryName', $config['REPOSITORY_NAME']);
     $identify->addChild('baseURL', $config['BaseUrl']);
     $identify->addChild('protocolVersion', $config['ProtocolVersion']);
     $identify->addChild('adminEmail', $config['adminEmail']);
     $identify->addChild('earliestDatestamp', "??");
     $identify->addChild('deletedRecord', $config['deletedRecord']);
     $identify->addChild('granularity', $config['granularity']);
     $xml = $sxe->asXML();
     return $xml;

}


function Fail(){
	 $config=self::ConfigFile();
	 $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $request = $sxe->addChild('request', $config['BaseUrl']);
     $request->addAttribute('verb','Identify');
     $identify=$sxe->addChild('error','The request includes illegal arguments, is missing required arguments, includes a repeated argument, or values for arguments have an illegal syntax.');
     $identify->addAttribute('code', 'badArgument');
     $xml = $sxe->asXML();
     return $xml;
}

function ConfigFile(){
            $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
            return $config;
    } 
}
?>
