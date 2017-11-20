<?php
    define('UID', '0082f2af-a4fe-43b2-8b44-2a87765aac72');
    define('PROPERTIES','?properties='
            . '%7B%22annotators%22%3A%20%22pos%22'
            . '%7D&pipelineLanguage=fr');
    // local testing
    //
    define('CORENLP_URL', 'http://192.168.154.130:9000/');   
    define('SCRIPT_DIR_URL', 'http://localhost/LibertT/YodaSpeak/scripts/');
    //
    
    // remote  
    /*
    define('CORENLP_URL', 'http://localhost:9000/');   
    define('SCRIPT_DIR_URL', 'http://yoda.pdf.s2dev.ch/scripts/');
    */
    require_once('corenlp.php');
    
    $json_encoded = file_get_contents(SCRIPT_DIR_URL . '/listRequests.php?uid=' . UID);
    $json = html_entity_decode(urldecode($json_encoded), ENT_QUOTES, "UTF-8");
    $requests = json_decode($json, true);
    var_dump($requests);
    if (!isset($requests)){
        exit;
    }
    foreach ($requests as $sess_id => $sess_request){       
        $corenlp = new corenlp(CORENLP_URL, PROPERTIES);
        // connect to server and convert
        if ($corenlp->testConnection()){
            // retrieves json from corenlp server
            $json_response = $corenlp->postRequest($sess_request);
            $responses[$sess_id] = $json_response;
            var_dump($responses);
            // Send response to the web server
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, SCRIPT_DIR_URL . '/storeResponses.php?uid='
                    . UID . "&json=" . urlencode(json_encode($responses)));
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
        }  
        else{
            print('CoreNLP unreachable');
        }
    } 
?>
