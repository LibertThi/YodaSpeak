<?php
    define('UID', 'a1b2c3d4');
    define('PROPERTIES','?properties='
            . '%7B%22annotators%22%3A%20%22pos%22'
            . '%7D&pipelineLanguage=fr');
    require_once('corenlp.php');

    $json_encoded = file_get_contents('http://localhost/LibertT/YodaSpeak/listRequests.php?uid=' . UID);
    $json = html_entity_decode(urldecode($json_encoded), ENT_QUOTES, "UTF-8");
    $requests = json_decode($json, true);
    var_dump($requests);
    if (!isset($requests)){
        exit;
    }
    
    foreach ($requests as $sess_id => $sess_request){       
        $corenlp = new corenlp('http://192.168.154.130:9000/', PROPERTIES);
        // connect to server and convert
        if ($corenlp->testConnection()){
            // retrieves json from corenlp server
            $json_response = $corenlp->postRequest($sess_request);
        }
        // print "error" if server is unreachable
        else{
            $error = 'Une perturbation dans la Force, '
                    . 'à me connecter m\'empêche. Réessayer plus tard, tu dois.';
        }
        
        $responses[$sess_id] = $json_response;
        var_dump($responses);
        // Send response to the web server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost/LibertT/YodaSpeak/storeResponses.php?uid="
                . UID . "&json=" . urlencode(json_encode($responses)));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
    }
?>
