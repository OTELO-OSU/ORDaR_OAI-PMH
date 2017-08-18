<?php
namespace search\controller;
ini_set('memory_limit', '-1');


class RequestController
{
    function identify()
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xmlns:xmlns:mml', 'http://www.w3.org/1998/Math/MathML');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', 'Identify');
        $identify = $sxe->addChild('Identify');
        $identify->addChild('repositoryName', $config['REPOSITORY_NAME']);
        $identify->addChild('baseURL', $config['BaseUrl']);
        $identify->addChild('protocolVersion', $config['ProtocolVersion']);
        $identify->addChild('adminEmail', $config['adminEmail']);
        $values = self::requestToAPI(0, "0000-01-01", "9999-12-31", "0", 'asc', 1, null);
        $identify->addChild('earliestDatestamp', $values['hits']['hits'][0]['CREATION_DATE']);
        $identify->addChild('deletedRecord', $config['deletedRecord']);
        $identify->addChild('granularity', $config['granularity']);
        $xml = $sxe->asXML();
        return $xml;
        
    }
    
    function ListMetadataFormats()
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', 'ListMetadataFormats');
        $ListMetadataFormat1 = $sxe->addChild('ListMetadataFormats');
        $ListMetadataFormat  = $ListMetadataFormat1->addChild('metadataFormat');
        $ListMetadataFormat->addChild('metadataPrefix', "oai_dc");
        $ListMetadataFormat->addChild('schema', "http://www.openarchives.org/OAI/2.0/oai_dc.xsd");
        $ListMetadataFormat->addChild('metadataNamespace', "http://www.openarchives.org/OAI/2.0/oai_dc
");
        
        
        $xml = $sxe->asXML();
        return $xml;
        
    }
    
    function GetRecord($identifier, $metadataPrefix)
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', 'GetRecord');
        $request->addAttribute('identifier', $identifier);
        $request->addAttribute('metadataPrefix', $metadataPrefix);
        $dbdoi       = new \MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['authSource'],
            'username' => $config['username'],
            'password' => $config['password']
        ));
        $found       = 0;
        $db          = $dbdoi->selectDB($config['authSource']);
        $collections = $db->getCollectionNames();
        foreach ($collections as $collection) {
            $collection = $db->selectCollection($collection);
            $query      = array(
                '_id' => $identifier,
                '$or' => array(
                    array(
                        "INTRO.ACCESS_RIGHT" => "Closed"
                    ),
                    array(
                        "INTRO.ACCESS_RIGHT" => "Open"
                    ),
                    array(
                        "INTRO.ACCESS_RIGHT" => "Embargoed"
                    )
                )
            );
            ;
            $cursor = $collection->find($query);
            foreach ($cursor as $key => $value) {
            }
            if ($cursor->count() == 1) {
                $found      = 1;
                $getrecord  = $sxe->addChild('GetRecord');
                $record     = $getrecord->addChild('record');
                $header     = $record->addChild('header');
                $identifier = $header->addChild('identifier', $identifier);
                $datestamp  = $header->addChild('datestamp', $value['INTRO']['CREATION_DATE']);
                $Setspec    = $header->addChild('setSpec', "??");
                $metadata   = $record->addChild('metadata');
                $oai_dc     = $metadata->addChild('oai_dc:oai_dc:dc');
                $oai_dc->addAttribute('xmlns:xmlns:dc', 'http://purl.org/dc/elements/1.1/');
                $oai_dc->addAttribute('xmlns:xmlns:oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
                $oai_dc->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
                $oai_dc->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
                $dc_identifier = $oai_dc->addChild('dc:dc:identifier', $identifier);
                $dc_title      = $oai_dc->addChild('dc:dc:title', $value['INTRO']['TITLE']);
                foreach ($value['INTRO']['FILE_CREATOR'] as $key => $author) {
                    $oai_dc->addChild('dc:dc:creator', $author['DISPLAY_NAME']);
                }
                $dc_date        = $oai_dc->addChild('dc:dc:date', $value['INTRO']['CREATION_DATE']);
                $dc_description = $oai_dc->addChild('dc:dc:description', $value['INTRO']['DATA_DESCRIPTION']);
                $dc_language    = $oai_dc->addChild('dc:dc:language', $value['INTRO']['LANGUAGE']);
                $dc_publisher   = $oai_dc->addChild('dc:dc:dc_publisher', $value['INTRO']['PUBLISHER']);
                foreach ($value['INTRO']['SCIENTIFIC_FIELD'] as $key => $SCIENTIFIC_FIELD) {
                    $oai_dc->addChild('dc:dc:subject', $SCIENTIFIC_FIELD['NAME']);
                }
                $dc_accessright = $oai_dc->addChild('dc:dc:dc_rights', $value['INTRO']['ACCESS_RIGHT']);
                
            }
            
        }
        if ($found == 0) {
            $identify = $sxe->addChild('error', ' "' . $identifier . '" is unknown or illegal in this repository');
            $identify->addAttribute('code', 'iDoesNotExist');
        }
        
        
        
        
        $xml = $sxe->asXML();
        return $xml;
        
    }
    private function Curlrequest($url, $curlopt)
    {
        $ch      = curl_init();
        $curlopt = array(
            CURLOPT_URL => $url
        ) + $curlopt;
        curl_setopt_array($ch, $curlopt);
        $rawData = curl_exec($ch);
        curl_close($ch);
        return $rawData;
    }
    
    function requestToAPI($page, $from, $until, $page, $order, $size, $set)
    {
        $config = self::ConfigFile();
        $bdd    = strtolower($config['authSource']);
        if (!empty($set)) {
            $set = "%20AND%20_type:" . $set;
        } else {
            $set = "";
        }
        $postcontent                = '{  
            "sort": { "INTRO.CREATION_DATE": { "order": "' . $order . '" }} 
            }

       ';
        $url                        = 'http://localhost/' . $bdd . '/_search?q=*AND%20INTRO.CREATION_DATE:[' . $from . '%20TO%20' . $until . ']%20AND%20NOT%20INTRO.ACCESS_RIGHT:Unpublished%20AND%20NOT%20INTRO.ACCESS_RIGHT:Draft' . $set . '&size=' . $size . '&from=' . $page;
        $curlopt                    = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT => 9200,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postcontent
        );
        $response                   = self::Curlrequest($url, $curlopt);
        $response                   = json_decode($response, TRUE);
        $responses["hits"]["total"] = $response["hits"]["total"];
        foreach ($response["hits"]["hits"] as $key => $value) {
            $responses["hits"]["hits"][$key]           = $value["_source"]["INTRO"];
            $responses["hits"]["hits"][$key]["_index"] = $value["_index"];
            $responses["hits"]["hits"][$key]["_id"]    = $value["_id"];
            $responses["hits"]["hits"][$key]["_type"]  = $value["_type"];
        }
        ;
        return $responses;
    }
    
    
    
    function ListIdentifiers($metadataPrefix, $from, $until, $set, $encodedresumptionToken)
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', 'ListIdentifiers');
        $request->addAttribute('metadataPrefix', $metadataPrefix);
        $array  = array();
        $Token  = "";
        $cursor = 0;
        if (!empty($encodedresumptionToken)) {
            $resumptionToken = openssl_decrypt($encodedresumptionToken, "AES-128-CBC", $config['TokenGenerationKey']);
            $array           = explode("AND", $resumptionToken);
            $result          = array();
            foreach ($array as $key => $value) {
                $values             = explode("!", $value);
                $result[$values[0]] = $values[1];
            }
            $metadataPrefix = $result['metadataPrefix'];
            $from           = $result['from'];
            $until          = $result['until'];
            $cursor         = $result['cursor'];
            $set            = $result['set'];
            $date           = new \DateTime();
            $currentime     = $date->getTimestamp();
            if (empty($cursor) || empty($metadataPrefix) OR ($currentime > $result['time'])) {
                $xml = self::badToken('ListRecords', $encodedresumptionToken);
                return $xml;
            }
        }
        if (!empty($metadataPrefix)) {
            $Token .= 'metadataPrefix!' . $metadataPrefix;
        }
        if (!empty($from)) {
            $Token .= 'ANDfrom!' . $from;
        }
        if (!empty($until)) {
            $Token .= 'ANDuntil!' . $until;
        }
        if (!empty($set)) {
            $Token .= 'ANDset!' . $set;
        }
        if (empty($from)) {
            $from = "0001-01-01";
        }
        if (empty($until)) {
            $until = "9999-12-31";
        }
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $from)) {
            $xml = self::badArgumentDate("from");
            return $xml;
        }
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $until)) {
            $xml = self::badArgumentDate("until");
            return $xml;
        }
        $values = self::requestToAPI(0, $from, $until, $cursor, 'desc', 10, $set);
        $cursor = $cursor + 10;
        $Token .= 'ANDcursor!' . $cursor;
        $date = new \DateTime();
        $date->modify('+5 minutes');
        $Token .= 'ANDtime!' . $date->getTimestamp();
        
        $getrecord       = $sxe->addChild('ListIdentifiers');
                if ($values['hits']['total'] > $cursor) {

        $resumptionToken = $sxe->addChild('resumptionToken', urlencode(openssl_encrypt($Token, "AES-128-CBC", $config['TokenGenerationKey'])));
        $resumptionToken->addAttribute('completeListSize', $values['hits']['total']);
   }
        if ($values['hits']['total'] == 0) {
            $xml = self::NoResult('ListIdentifiers', $metadataPrefix, $until, $from, $set);
            return $xml;
        }
        foreach ($values['hits']['hits'] as $key => $value) {
            $header     = $getrecord->addChild('header');
            $identifier = $header->addChild('identifier', $value['_id']);
            $datestamp  = $header->addChild('datestamp', $value['CREATION_DATE']);
            $Setspec    = $header->addChild('setSpec', $value['_type']);
            
        }
        
        $xml = $sxe->asXML();
        return $xml;
        
    }
    
    
    function ListRecords($metadataPrefix, $from, $until, $set, $encodedresumptionToken)
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', 'ListRecords');
        $request->addAttribute('metadataPrefix', $metadataPrefix);
        $dbdoi  = new \MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['authSource'],
            'username' => $config['username'],
            'password' => $config['password']
        ));
        $Token  = "";
        $cursor = 0;
        if (!empty($encodedresumptionToken)) {
            $resumptionToken = openssl_decrypt($encodedresumptionToken, "AES-128-CBC", $config['TokenGenerationKey']);
            $array           = explode("AND", $resumptionToken);
            $result          = array();
            foreach ($array as $key => $value) {
                $values             = explode("!", $value);
                $result[$values[0]] = $values[1];
            }
            $metadataPrefix = $result['metadataPrefix'];
            $from           = $result['from'];
            $until          = $result['until'];
            $cursor         = $result['cursor'];
            $set            = $result['set'];
            $date           = new \DateTime();
            $currentime     = $date->getTimestamp();
            if (empty($cursor) || empty($metadataPrefix) OR ($currentime > $result['time'])) {
                $xml = self::badToken('ListRecords', $encodedresumptionToken);
                return $xml;
            }
        }
        if (!empty($metadataPrefix)) {
            $Token .= 'metadataPrefix!' . $metadataPrefix;
        }
        if (!empty($from)) {
            $Token .= 'ANDfrom!' . $from;
        }
        if (!empty($until)) {
            $Token .= 'ANDuntil!' . $until;
        }
        if (!empty($set)) {
            $Token .= 'ANDset!' . $set;
        }
        if (empty($from)) {
            $from = "0001-01-01";
        }
        if (empty($until)) {
            $until = "9999-12-31";
        }
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $from)) {
            $xml = self::badArgumentDate("from");
            return $xml;
        }
        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $until)) {
            $xml = self::badArgumentDate("until");
            return $xml;
        }
        
        
        $values = self::requestToAPI(0, $from, $until, $cursor, 'desc', 10, $set);
        $cursor = $cursor + 10;
        $Token .= 'ANDcursor!' . $cursor;
        $date = new \DateTime();
        $date->modify('+5 minutes');
        $Token .= 'ANDtime!' . $date->getTimestamp();
        
        $getrecord = $sxe->addChild('ListRecords');
        foreach ($values['hits']['hits'] as $key => $value) {
            $record     = $getrecord->addChild('record');
            $header     = $record->addChild('header');
            $identifier = $header->addChild('identifier', $value['_id']);
            $datestamp  = $header->addChild('datestamp', $value['CREATION_DATE']);
            $Setspec    = $header->addChild('setSpec', "??");
            $metadata   = $record->addChild('metadata');
            $oai_dc     = $metadata->addChild('oai_dc:oai_dc:dc');
            $oai_dc->addAttribute('xmlns:xmlns:dc', 'http://purl.org/dc/elements/1.1/');
            $oai_dc->addAttribute('xmlns:xmlns:oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
            $oai_dc->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
            $oai_dc->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
            $dc_identifier = $oai_dc->addChild('dc:dc:identifier', $identifier);
            $dc_title      = $oai_dc->addChild('dc:dc:title', $value['TITLE']);
            foreach ($value['FILE_CREATOR'] as $key => $author) {
                $oai_dc->addChild('dc:dc:creator', $author['DISPLAY_NAME']);
            }
            $dc_date        = $oai_dc->addChild('dc:dc:date', $value['CREATION_DATE']);
            $dc_description = $oai_dc->addChild('dc:dc:description', $value['DATA_DESCRIPTION']);
            $dc_language    = $oai_dc->addChild('dc:dc:language', $value['LANGUAGE']);
            $dc_publisher   = $oai_dc->addChild('dc:dc:dc_publisher', $value['PUBLISHER']);
            foreach ($value['SCIENTIFIC_FIELD'] as $key => $SCIENTIFIC_FIELD) {
                $oai_dc->addChild('dc:dc:subject', $SCIENTIFIC_FIELD['NAME']);
            }
            $dc_accessright = $oai_dc->addChild('dc:dc:dc_rights', $value['ACCESS_RIGHT']);
            
        }
        if ($values['hits']['total'] > $cursor) {
            $resumptionToken = $sxe->addChild('resumptionToken', urlencode(openssl_encrypt($Token, "AES-128-CBC", $config['TokenGenerationKey'])));
            $resumptionToken->addAttribute('completeListSize', $values['hits']['total']);
        }
        if ($values['hits']['total'] == 0) {
            $xml = self::NoResult('ListIdentifiers', $metadataPrefix, $until, $from, $set);
            return $xml;
        }
        
        $xml = $sxe->asXML();
        return $xml;
        
    }
    
    
    
    function ListSets()
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', 'ListSets');
        $Listsets    = $sxe->addChild('ListSets');
        $dbdoi       = new \MongoClient("mongodb://" . $config['host'] . ':' . $config['port'], array(
            'authSource' => $config['authSource'],
            'username' => $config['username'],
            'password' => $config['password']
        ));
        $db          = $dbdoi->selectDB($config['authSource']);
        $collections = $db->getCollectionNames();
        foreach ($collections as $key => $value) {
            $sets = $Listsets->addChild('set');
            $sets->addChild('setSpec', $value);
            $sets->addChild('setName', $value);
            
        }
        
        $xml = $sxe->asXML();
        return $xml;
        
    }
    
    function NoResult($verb, $metadataPrefix, $until, $from, $set)
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', $verb);
        $request->addAttribute('metadataPrefix', $metadataPrefix);
        if (!empty($until)) {
            $request->addAttribute('until', $until);
        }
        if (!empty($from)) {
            $request->addAttribute('from', $from);
        }
        if (!empty($set)) {
            $request->addAttribute('set', $set);
        }
        $identify = $sxe->addChild('error', 'The combination of the values of the from, until, set, and metadataPrefix arguments results in an empty list.');
        $identify->addAttribute('code', 'noRecordsMatch');
        $xml = $sxe->asXML();
        return $xml;
    }
    
    
    function badArgumentDate($arg)
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb');
        $identify = $sxe->addChild('error', "'" . $arg . "'" . ' is not a valid date.');
        $identify->addAttribute('code', 'badArgument');
        $xml = $sxe->asXML();
        return $xml;
    }
    
    
    function badToken($verb, $token)
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', $verb);
        $request->addAttribute('resumptionToken', $token);
        $identify = $sxe->addChild('error', 'The value of the resumptionToken argument is invalid or expired');
        $identify->addAttribute('code', 'badResumptionToken');
        $xml = $sxe->asXML();
        return $xml;
    }
    
    
    
    function badArgument($verb)
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', $verb);
        $identify = $sxe->addChild('error', 'The request includes illegal arguments, is missing required arguments, includes a repeated argument, or values for arguments have an illegal syntax.');
        $identify->addAttribute('code', 'badArgument');
        $xml = $sxe->asXML();
        return $xml;
    }
    
    function cannotDisseminateFormat($verb)
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri     = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $request->addAttribute('verb', $verb);
        $identify = $sxe->addChild('error', 'This format is unknown.');
        $identify->addAttribute('code', 'cannotDisseminateFormat');
        $xml = $sxe->asXML();
        return $xml;
    }
    
    function IllegalVerb()
    {
        $config = self::ConfigFile();
        $sxe    = new \SimpleXMLElement("<OAI-PMH/>");
        $sxe->addAttribute('xmlns', 'http://www.openarchives.org/OAI/2.0/');
        $sxe->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $sxe->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd');
        $sxe->addChild('responseDate', date("Y-m-d\TH:i:s\Z"));
        $uri      = explode('?', $_SERVER['REQUEST_URI'], 2);
        $request  = $sxe->addChild('request', $config['BaseUrl'] . $uri[0]);
        $identify = $sxe->addChild('error', 'Illegal verb');
        $identify->addAttribute('code', 'badVerb');
        $xml = $sxe->asXML();
        return $xml;
    }
    
    function ConfigFile()
    {
        $config = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/../config.ini');
        return $config;
    }
}
?>