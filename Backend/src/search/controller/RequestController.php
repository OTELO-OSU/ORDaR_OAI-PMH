<?php
namespace search\controller;
ini_set('memory_limit', '-1');


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
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
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

function ListMetadataFormats(){
      $config=self::ConfigFile();
      $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
     $request->addAttribute('verb','ListMetadataFormats');
     $ListMetadataFormat1=$sxe->addChild('ListMetadataFormats');
     $ListMetadataFormat=$ListMetadataFormat1->addChild('metadataFormat');
     $ListMetadataFormat->addChild('metadataPrefix', "oai_dc");
     $ListMetadataFormat->addChild('schema', "http://www.openarchives.org/OAI/2.0/oai_dc.xsd");
     $ListMetadataFormat->addChild('metadataNamespace', "http://www.openarchives.org/OAI/2.0/oai_dc
");


     $xml = $sxe->asXML();
     return $xml;

}

function GetRecord($identifier,$metadataPrefix){
      $config=self::ConfigFile();
      $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
     $request->addAttribute('verb','GetRecord');
     $request->addAttribute('identifier',$identifier);
     $request->addAttribute('metadataPrefix',$metadataPrefix);
     $dbdoi      = new \MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['authSource'],
            'username' => $config['username'],
            'password' => $config['password']
        ));
     $found=0;
     $db     = $dbdoi->selectDB($config['authSource']);
     $collections = $db->getCollectionNames();
     foreach ($collections as $collection) {
         $collection = $db->selectCollection($collection);
          $query  = array(
                     '_id' => $identifier,
                 '$or' => array(
  array("INTRO.ACCESS_RIGHT" => "Closed"),
  array("INTRO.ACCESS_RIGHT" => "Open"),
   array("INTRO.ACCESS_RIGHT" => "Embargoed")));;
     $cursor = $collection->find($query);
     foreach ($cursor as $key => $value) {
     }
      if ($cursor->count() == 1) {
          $found=1;
          $getrecord=$sxe->addChild('GetRecord');
          $record=$getrecord->addChild('record');
          $header=$record->addChild('header');
          $identifier=$header->addChild('identifier',$identifier);
          $datestamp=$header->addChild('datestamp',$value['INTRO']['CREATION_DATE']);
          $Setspec=$header->addChild('setSpec',"??");
          $metadata=$record->addChild('metadata');
          $oai_dc=$metadata->addChild('oai_dc:oai_dc:dc');
          $oai_dc->addAttribute('xmlns:xmlns:dc', 'http://purl.org/dc/elements/1.1/');
          $oai_dc->addAttribute('xmlns:xmlns:oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
          $oai_dc->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
          $oai_dc->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
          $dc_identifier=$oai_dc->addChild('dc:dc:identifier',$identifier);
          $dc_title=$oai_dc->addChild('dc:dc:title',$value['INTRO']['TITLE']);
          foreach ($value['INTRO']['FILE_CREATOR'] as $key => $author) {
                $oai_dc->addChild('dc:dc:creator', $author['DISPLAY_NAME']);
            }
          $dc_date=$oai_dc->addChild('dc:dc:date',$value['INTRO']['CREATION_DATE']);
          $dc_description=$oai_dc->addChild('dc:dc:description',$value['INTRO']['DATA_DESCRIPTION']);
          $dc_language=$oai_dc->addChild('dc:dc:language',$value['INTRO']['LANGUAGE']);
          $dc_publisher=$oai_dc->addChild('dc:dc:dc_publisher',$value['INTRO']['PUBLISHER']);
          foreach ($value['INTRO']['SCIENTIFIC_FIELD'] as $key => $SCIENTIFIC_FIELD) {
                $oai_dc->addChild('dc:dc:subject', $SCIENTIFIC_FIELD['NAME']);
            }
            /* foreach ($value['INTRO']['INSTITUTION'] as $key => $INSTITUTIONS) {
                $oai_dc->addChild('dc:dc:institution', $INSTITUTIONS['NAME']);
            }*/
          //$dc_license=$oai_dc->addChild('dc:dc:dc_license',$value['INTRO']['LICENSE']);
          $dc_accessright=$oai_dc->addChild('dc:dc:dc_rights',$value['INTRO']['ACCESS_RIGHT']);

     }
   
     }
     if ($found==0) {
          $identify=$sxe->addChild('error',' "'.$identifier.'" is unknown or illegal in this repository');
          $identify->addAttribute('code', 'iDoesNotExist');
     }
      

  

     $xml = $sxe->asXML();
     return $xml;

}

function ListIdentifiers($metadataPrefix){
      $config=self::ConfigFile();
      $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
     $request->addAttribute('verb','ListIdentifiers');
     $request->addAttribute('metadataPrefix',$metadataPrefix);
      $dbdoi      = new \MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['authSource'],
            'username' => $config['username'],
            'password' => $config['password']
        ));
     $array=array();
     $db     = $dbdoi->selectDB($config['authSource']);
     $collections = $db->getCollectionNames();
     foreach ($collections as $collection) {
         $collection = $db->selectCollection($collection);
         $query=array('$or' => array(
  array("INTRO.ACCESS_RIGHT" => "Closed"),
  array("INTRO.ACCESS_RIGHT" => "Open"),
   array("INTRO.ACCESS_RIGHT" => "Embargoed")));
     $cursor = $collection->find($query);
     $cursor->sort(array('INTRO.CREATION_DATE' => -1));
     foreach ($cursor as $key => $value) {
          $array[]=$value;
     }
     }
        $getrecord=$sxe->addChild('ListIdentifiers');
     foreach ($array as $key => $value) {
          $header=$getrecord->addChild('header');
          $identifier=$header->addChild('identifier',$value['_id']);
          $datestamp=$header->addChild('datestamp',$value['INTRO']['CREATION_DATE']);
          $Setspec=$header->addChild('setSpec',"??");
         
     }

     $xml = $sxe->asXML();
    return $xml;

}


function ListRecords($metadataPrefix,$from,$until,$set){
      $config=self::ConfigFile();
      $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
     $request->addAttribute('verb','ListRecords');
     $request->addAttribute('metadataPrefix',$metadataPrefix);
      $dbdoi      = new \MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['authSource'],
            'username' => $config['username'],
            'password' => $config['password']
        ));
     $array=array();
     if (empty($from)) {
          $from="0001-01-01";
     }
      if (empty($until)) {
          $until="9999-12-31";
     }
     if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$from)) {
             $xml=self::badArgumentDate("from"); 
             return $xml;
     } 
     if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$until)) {
             $xml =self::badArgumentDate("until"); 
             return $xml;
     } 
     $db     = $dbdoi->selectDB($config['authSource']);
     $collections = $db->getCollectionNames();
     foreach ($collections as $collection) {
         $collection = $db->selectCollection($collection);
         $query=array('$and'=>array(
          array('$or' => array(
                 array("INTRO.ACCESS_RIGHT" => "Closed"),
                 array("INTRO.ACCESS_RIGHT" => "Open"),
                 array("INTRO.ACCESS_RIGHT" => "Embargoed"))),

          array('INTRO.CREATION_DATE' => array( '$gt' => $from, '$lt' => $until ))
          ));
     $cursor = $collection->find($query);
     $cursor->sort(array('INTRO.CREATION_DATE' => -1));
     foreach ($cursor as $key => $value) {
          $array[]=$value;
     }
     }
     $getrecord=$sxe->addChild('ListRecords');
     foreach ($array as $key => $value) {
        $record=$getrecord->addChild('record');
          $header=$record->addChild('header');
          $identifier=$header->addChild('identifier',$value['_id']);
          $datestamp=$header->addChild('datestamp',$value['INTRO']['CREATION_DATE']);
          $Setspec=$header->addChild('setSpec',"??");
          $metadata=$record->addChild('metadata');
          $oai_dc=$metadata->addChild('oai_dc:oai_dc:dc');
          $oai_dc->addAttribute('xmlns:xmlns:dc', 'http://purl.org/dc/elements/1.1/');
          $oai_dc->addAttribute('xmlns:xmlns:oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
          $oai_dc->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
          $oai_dc->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
          $dc_identifier=$oai_dc->addChild('dc:dc:identifier',$identifier);
          $dc_title=$oai_dc->addChild('dc:dc:title',$value['INTRO']['TITLE']);
          foreach ($value['INTRO']['FILE_CREATOR'] as $key => $author) {
                $oai_dc->addChild('dc:dc:creator', $author['DISPLAY_NAME']);
            }
          $dc_date=$oai_dc->addChild('dc:dc:date',$value['INTRO']['CREATION_DATE']);
          $dc_description=$oai_dc->addChild('dc:dc:description',$value['INTRO']['DATA_DESCRIPTION']);
          $dc_language=$oai_dc->addChild('dc:dc:language',$value['INTRO']['LANGUAGE']);
          $dc_publisher=$oai_dc->addChild('dc:dc:dc_publisher',$value['INTRO']['PUBLISHER']);
          foreach ($value['INTRO']['SCIENTIFIC_FIELD'] as $key => $SCIENTIFIC_FIELD) {
                $oai_dc->addChild('dc:dc:subject', $SCIENTIFIC_FIELD['NAME']);
            }
            /* foreach ($value['INTRO']['INSTITUTION'] as $key => $INSTITUTIONS) {
                $oai_dc->addChild('dc:dc:institution', $INSTITUTIONS['NAME']);
            }*/
          //$dc_license=$oai_dc->addChild('dc:dc:dc_license',$value['INTRO']['LICENSE']);
          $dc_accessright=$oai_dc->addChild('dc:dc:dc_rights',$value['INTRO']['ACCESS_RIGHT']);
         
     }

     $xml = $sxe->asXML();
    return $xml;

}



function ListSets(){
      $config=self::ConfigFile();
      $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
     $request->addAttribute('verb','ListSets');
     $Listsets=$sxe->addChild('ListSets');

    
     $xml = $sxe->asXML();
    return $xml;

}


function badArgumentDate($arg){
      $config=self::ConfigFile();
      $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
     $request->addAttribute('verb');
     $identify=$sxe->addChild('error',"'".$arg."'".' is not a valid date.');
     $identify->addAttribute('code', 'badArgument');
     $xml = $sxe->asXML();
     return $xml;
}


function badArgument($verb){
	 $config=self::ConfigFile();
	 $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
     $request->addAttribute('verb',$verb);
     $identify=$sxe->addChild('error','The request includes illegal arguments, is missing required arguments, includes a repeated argument, or values for arguments have an illegal syntax.');
     $identify->addAttribute('code', 'badArgument');
     $xml = $sxe->asXML();
     return $xml;
}

function cannotDisseminateFormat($verb){
      $config=self::ConfigFile();
      $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
     $request->addAttribute('verb',$verb);
     $identify=$sxe->addChild('error','This format is unknown.');
     $identify->addAttribute('code', 'cannotDisseminateFormat');
     $xml = $sxe->asXML();
     return $xml;
}

function IllegalVerb(){
      $config=self::ConfigFile();
      $sxe = new \SimpleXMLElement("<OAI-PMH/>");
     $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
     $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
     $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
     $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
     $uri=explode('?', $_SERVER['REQUEST_URI'], 2);
     $request = $sxe->addChild('request', $config['BaseUrl'].$uri[0]);
     $identify=$sxe->addChild('error','Illegal verb');
     $identify->addAttribute('code', 'badVerb');
     $xml = $sxe->asXML();
     return $xml;
}

function ConfigFile(){
            $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
            return $config;
    } 
}
?>
