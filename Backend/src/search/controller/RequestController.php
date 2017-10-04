<?php
namespace search\controller;
ini_set('memory_limit', '-1');
date_default_timezone_set('Europe/Paris');


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
        $identify->addChild('earliestDatestamp', $values['hits']['hits'][0]['PUBLICATION_DATE']);
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
        $ListMetadataFormat_oaidc  = $ListMetadataFormat1->addChild('metadataFormat');
        $ListMetadataFormat_oaidc->addChild('metadataPrefix', "oai_dc");
        $ListMetadataFormat_oaidc->addChild('schema', "http://www.openarchives.org/OAI/2.0/oai_dc.xsd");
        $ListMetadataFormat_oaidc->addChild('metadataNamespace', "http://www.openarchives.org/OAI/2.0/oai_dc
");
        $ListMetadataFormat_oai_datacite  = $ListMetadataFormat1->addChild('metadataFormat');
        $ListMetadataFormat_oai_datacite->addChild('metadataPrefix', "oai_datacite");
        $ListMetadataFormat_oai_datacite->addChild('schema', "http://schema.datacite.org/oai/oai-1.0/ oai_datacite.xsd");
        $ListMetadataFormat_oai_datacite->addChild('metadataNamespace', "http://schema.datacite.org/oai/oai-1.0/
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
        $record=self::GetDocumentfromAPI($identifier);
        if ($record['_source']['INTRO']['ACCESS_RIGHT']=="Open" OR $record['_source']['INTRO']['ACCESS_RIGHT']=="Closed" OR $record['_source']['INTRO']['ACCESS_RIGHT']=="Embargoed" ) {
                $getrecord  = $sxe->addChild('GetRecord');
                $recordxml     = $getrecord->addChild('record');
                $header     = $recordxml->addChild('header');
                $identifier = $header->addChild('identifier', "info:doi:".$identifier);
                $datestamp  = $header->addChild('datestamp', $record['_source']['INTRO']['PUBLICATION_DATE']);
               foreach ($record['_source']['INTRO']['SCIENTIFIC_FIELD'] as $key => $value) {
                    $Setspec    = $header->addChild('setSpec',  str_replace(' ', '_', $value['NAME']));
               }                
               if (!empty($config['SpecialSet'])) {
                    $sets=explode(",", $config['SpecialSet']);
                    foreach ($sets as $key => $value) {
                           $header->addChild('setSpec', $value);
                       }
                  }
                  if ($metadataPrefix=='oai_dc') {
                       
               $metadata   = $recordxml->addChild('metadata');
                $oai_dc     = $metadata->addChild('oai_dc:oai_dc:dc');
                $oai_dc->addAttribute('xmlns:xmlns:dc', 'http://purl.org/dc/elements/1.1/');
                $oai_dc->addAttribute('xmlns:xmlns:oai_dc', 'http://www.openarchives.org/OAI/2.0/oai_dc/');
                $oai_dc->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
                $oai_dc->addAttribute('xsi:xsi:schemaLocation', 'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd');
                $dc_identifier = $oai_dc->addChild('dc:dc:identifier', $identifier);
                $dc_title      = $oai_dc->addChild('dc:dc:title', $record['_source']['INTRO']['TITLE']);
                foreach ($record['_source']['INTRO']['FILE_CREATOR'] as $key => $author) {
                    $oai_dc->addChild('dc:dc:creator', $author['DISPLAY_NAME']);
                }
                $dc_date        = $oai_dc->addChild('dc:dc:date', $record['_source']['INTRO']['PUBLICATION_DATE']);
                $dc_description = $oai_dc->addChild('dc:dc:description', $record['_source']['INTRO']['DATA_DESCRIPTION']);
                $dc_language    = $oai_dc->addChild('dc:dc:language', $record['_source']['INTRO']['LANGUAGE']);
                $dc_publisher   = $oai_dc->addChild('dc:dc:publisher', $record['_source']['INTRO']['PUBLISHER']);
               $oai_dc->addChild('dc:dc:type',"info:eu-repo/semantics/other");
                foreach ($record['_source']['INTRO']['SCIENTIFIC_FIELD'] as $key => $SCIENTIFIC_FIELD) {
                    $oai_dc->addChild('dc:dc:subject', $SCIENTIFIC_FIELD['NAME']);
                }
               foreach ($record['_source']['DATA']['FILES'] as $key => $files) {
                $oai_dc->addChild('dc:dc:format',$files['FILETYPE']);
                }
                $dc_accessright = $oai_dc->addChild('dc:dc:rights', "info:eu-repo/semantics/".strtolower($record['_source']['INTRO']['ACCESS_RIGHT'])."Access");
                $dc_license = $oai_dc->addChild('dc:dc:rights',$record['_source']['INTRO']['LICENSE'] );
                  }
                  elseif ($metadataPrefix=='oai_datacite'){
                     $metadata   = $recordxml->addChild('metadata');
                     $oai_dc     = $metadata->addChild('oai_datacite:oai_datacite');
                     $oai_dc->addAttribute('xmlns:xmlns', 'http://schema.datacite.org/oai/oai-1.0/');
                     $oai_dc->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
                     $oai_dc->addAttribute('xsi:xsi:schemaLocation', 'http://schema.datacite.org/oai/oai-1.0/ oai_datacite.xsd');
                     $dc_identifier = $oai_dc->addChild('identifier', $identifier);
                     $dc_title      = $oai_dc->addChild('title', $record['_source']['INTRO']['TITLE']);
                     foreach ($record['_source']['INTRO']['FILE_CREATOR'] as $key => $author) {
                         $oai_dc->addChild('creator', $author['DISPLAY_NAME']);
                     }
                     $dc_date        = $oai_dc->addChild('date', $record['_source']['INTRO']['PUBLICATION_DATE']);
                     $dc_description = $oai_dc->addChild('description', $record['_source']['INTRO']['DATA_DESCRIPTION']);
                     $dc_language    = $oai_dc->addChild('language', $record['_source']['INTRO']['LANGUAGE']);
                     $dc_publisher   = $oai_dc->addChild('publisher', $record['_source']['INTRO']['PUBLISHER']);
                    $oai_dc->addChild('type',"info:eu-repo/semantics/other");
                     foreach ($record['_source']['INTRO']['SCIENTIFIC_FIELD'] as $key => $SCIENTIFIC_FIELD) {
                         $oai_dc->addChild('subject', $SCIENTIFIC_FIELD['NAME']);
                     }
                    foreach ($record['_source']['DATA']['FILES'] as $key => $files) {
                     $oai_dc->addChild('format',$files['FILETYPE']);
                     }
                     $dc_accessright = $oai_dc->addChild('rights', "info:eu-repo/semantics/".strtolower($record['_source']['INTRO']['ACCESS_RIGHT'])."Access");
                     $dc_license = $oai_dc->addChild('rights',$record['_source']['INTRO']['LICENSE'] );

                  }
        }
        else{
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
            $set = "%20AND%20INTRO.SCIENTIFIC_FIELD.NAME:" . $set;
        } else {
            $set = "";
        }
        $postcontent                = '{  
            "sort": { "INTRO.PUBLICATION_DATE": { "order": "' . $order . '" }} 
            }

       ';
        $url                        = 'http://'.$config['APIHost'].'/' . $bdd . '/_search?q=*AND%20INTRO.PUBLICATION_DATE:[' . $from . '%20TO%20' . $until . ']%20AND%20NOT%20INTRO.ACCESS_RIGHT:Unpublished%20AND%20NOT%20INTRO.ACCESS_RIGHT:Draft' . $set . '&size=' . $size . '&from=' . $page;
        $curlopt                    = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT => $config['APIPort'],
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
            $responses["hits"]["hits"][$key]['DATA']          = $value["_source"]["DATA"];
            $responses["hits"]["hits"][$key]["_index"] = $value["_index"];
            $responses["hits"]["hits"][$key]["_id"]    = $value["_id"];
            $responses["hits"]["hits"][$key]["_type"]  = $value["_type"];
        }
        ;
        return $responses;
    }

     function requestSetToAPI()
    {
        $config = self::ConfigFile();
        $bdd    = strtolower($config['authSource']);
        $postcontent                = '{  
            "sort": { "INTRO.PUBLICATION_DATE": { "order": "' . $order . '" }} 
            }

       ';
        $url                        = 'http://'.$config['APIHost'].'/' . $bdd . '/_search?q=*';
        $postcontent = '{  
            "sort": { "INTRO.METADATA_DATE": { "order": "desc" }} , 
            "_source": { 
            "excludes": [ "DATA" ] 
             }, 
            "aggs" : {   
                "scientific_field" : {   
                    "terms" : {   
                      "field" : "INTRO.SCIENTIFIC_FIELD.NAME"  
                    }  
                } 
            }  
        }';
        $curlopt                    = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT => $config['APIPort'],
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postcontent
        );
        $response                   = self::Curlrequest($url, $curlopt);
        $response                   = json_decode($response, TRUE);
        $response                   = $response['aggregations']['scientific_field']['buckets'];
       
        ;
        return $response;
    }

      function GetDocumentfromAPI($id)
    {
        $config = self::ConfigFile();
        $bdd    = strtolower($config['authSource']);
        
        $url      = 'http://'.$config['APIHost'].'/' . $bdd . '/_all/' . urlencode($id);

        $curlopt                    = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PORT => $config['APIPort'],
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
          );
        $response                   = self::Curlrequest($url, $curlopt);
        $response                   = json_decode($response, TRUE);
        return $response;
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
            $resumptionToken = openssl_decrypt($encodedresumptionToken, "AES-128-ECB", $config['TokenGenerationKey']);
            $array           = explode("AND", $resumptionToken);
            $result          = array();
            foreach ($array as $key => $value) {
                $values             = explode("!", $value);
                $result[$values[0]] = $values[1];
            }
            $metadataPrefix = $result['metadataPrefix'];
            $from           = @$result['from'];
            $until          = @$result['until'];
            $cursor         = @$result['cursor'];
            $set            = @$result['set'];
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

        $resumptionToken = $sxe->addChild('resumptionToken', urlencode(openssl_encrypt($Token, "AES-128-ECB", $config['TokenGenerationKey'])));
        $resumptionToken->addAttribute('completeListSize', $values['hits']['total']);
   }
        if ($values['hits']['total'] == 0) {
            $xml = self::NoResult('ListIdentifiers', $metadataPrefix, $until, $from, $set);
            return $xml;
        }
        foreach ($values['hits']['hits'] as $key => $value) {
            $header     = $getrecord->addChild('header');
            $identifier = $header->addChild('identifier', "info:doi:".$value['_id']);
            $datestamp  = $header->addChild('datestamp', $value['PUBLICATION_DATE']);
            foreach ($value['SCIENTIFIC_FIELD'] as $key => $value) {
               $Setspec    = $header->addChild('setSpec', str_replace(' ', '_', $value['NAME']));
            }
            if (!empty($config['SpecialSet'])) {
                    $sets=explode(",", $config['SpecialSet']);
                    foreach ($sets as $key => $value) {
                           $header->addChild('setSpec', $value);
                       }
                  }

            
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
        $Token  = "";
        $cursor = 0;
        if (!empty($encodedresumptionToken)) {
            $resumptionToken = openssl_decrypt($encodedresumptionToken, "AES-128-ECB", $config['TokenGenerationKey']);
            $array           = explode("AND", $resumptionToken);
            $result          = array();
            foreach ($array as $key => $value) {
                $values             = explode("!", $value);
                $result[$values[0]] = $values[1];
            }
            $metadataPrefix = $result['metadataPrefix'];
            $from           = @$result['from'];
            $until          = @$result['until'];
            $cursor         = @$result['cursor'];
            $set            = @$result['set'];
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
            $identifier = $header->addChild('identifier', "info:doi:".$value['_id']);
            $datestamp  = $header->addChild('datestamp', $value['PUBLICATION_DATE']);
            foreach ($value['SCIENTIFIC_FIELD'] as $key => $SCIENTIFIC_FIELD) {
               $Setspec    = $header->addChild('setSpec', str_replace(' ', '_', $SCIENTIFIC_FIELD['NAME']));
            }      
          if ($metadataPrefix=='oai_dc') {
  
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
            $dc_date        = $oai_dc->addChild('dc:dc:date', $value['PUBLICATION_DATE']);
            $dc_description = $oai_dc->addChild('dc:dc:description', $value['DATA_DESCRIPTION']);
            $dc_language    = $oai_dc->addChild('dc:dc:language', $value['LANGUAGE']);
            $dc_publisher   = $oai_dc->addChild('dc:dc:dc_publisher', $value['PUBLISHER']);
            foreach ($value['SCIENTIFIC_FIELD'] as $key => $SCIENTIFIC_FIELD) {
                $oai_dc->addChild('dc:dc:subject',$SCIENTIFIC_FIELD['NAME']);
            }
            foreach ($value['DATA']['FILES'] as $key => $files) {
                $oai_dc->addChild('dc:dc:format',$files['FILETYPE']);
                 if (!empty($config['SpecialSet'])) {
                    $sets=explode(",", $config['SpecialSet']);
                    foreach ($sets as $key => $set) {
                           $header->addChild('setSpec', $set);
                       }
                  }


          $oai_dc->addChild('dc:dc:type',"info:eu-repo/semantics/other");
            
            $dc_accessright = $oai_dc->addChild('dc:dc:rights',"info:eu-repo/semantics/".strtolower($value['ACCESS_RIGHT'])."Access");   
            $dc_license = $oai_dc->addChild('dc:dc:rights',$value['LICENSE'] );
            }
          }
          elseif ($metadataPrefix=='oai_datacite') {
                 $metadata   = $record->addChild('metadata');
                 $oai_dc     = $metadata->addChild('oai_datacite:oai_datacite');
                 $oai_dc->addAttribute('xmlns:xmlns', 'http://schema.datacite.org/oai/oai-1.0/');
                 $oai_dc->addAttribute('xmlns:xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
                 $oai_dc->addAttribute('xsi:xsi:schemaLocation', 'http://schema.datacite.org/oai/oai-1.0/ oai_datacite.xsd');
                 $dc_identifier = $oai_dc->addChild('identifier', $identifier);
                 $dc_title      = $oai_dc->addChild('title', $value['TITLE']);
                 foreach ($value['FILE_CREATOR'] as $key => $author) {
                     $oai_dc->addChild('creator', $author['DISPLAY_NAME']);
                 }
                 $dc_date        = $oai_dc->addChild('date', $value['PUBLICATION_DATE']);
                 $dc_description = $oai_dc->addChild('description', $value['DATA_DESCRIPTION']);
                 $dc_language    = $oai_dc->addChild('language', $value['LANGUAGE']);
                 $dc_publisher   = $oai_dc->addChild('publisher', $value['PUBLISHER']);
                 foreach ($value['SCIENTIFIC_FIELD'] as $key => $SCIENTIFIC_FIELD) {
                     $oai_dc->addChild('subject',$SCIENTIFIC_FIELD['NAME']);
                 }
                 foreach ($value['DATA']['FILES'] as $key => $files) {
                     $oai_dc->addChild('format',$files['FILETYPE']);
                 }
               $oai_dc->addChild('type',"info:eu-repo/semantics/other");
                if (!empty($config['SpecialSet'])) {
                    $sets=explode(",", $config['SpecialSet']);
                    foreach ($sets as $key => $set) {
                           $header->addChild('setSpec', $set);
                       }
                  }


          $oai_dc->addChild('type',"info:eu-repo/semantics/other");
            
            $dc_accessright = $oai_dc->addChild('rights',"info:eu-repo/semantics/".strtolower($value['ACCESS_RIGHT'])."Access");   
            $dc_license = $oai_dc->addChild('rights',$value['LICENSE'] );


          }
           
       
        }
        if ($values['hits']['total'] > $cursor) {
            $resumptionToken = $sxe->addChild('resumptionToken', urlencode(openssl_encrypt($Token, "AES-128-ECB", $config['TokenGenerationKey'])));
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
        $values = self::requestSetToAPI();
        foreach ($values as $key => $value) {
            $sets = $Listsets->addChild('set');
            $sets->addChild('setSpec', str_replace(' ', '_', $value['key']));
            $sets->addChild('setName', $value['key']);
            
        }
        if (!empty($config['SpecialSet'])) {
          $sets=explode(",", $config['SpecialSet']);
             foreach ($sets as $key => $value) {
                  $sets = $Listsets->addChild('set');
            $sets->addChild('setSpec', $value);
            $sets->addChild('setName', $value);
             }
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