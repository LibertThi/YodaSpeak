<?php    
    // Base URL of the server
    define('SERVER_URL', 'http://192.168.154.129/');
    // Properties for a POST request returning part-of-speech in French
    define('PROPERTIES','?properties='
            . '%7B%22annotators%22%3A%20%22tokenize%2Cssplit%2Cparse%2Cpos%22'
            . '%7D&pipelineLanguage=fr');
    
    // Basic POST Request using cURL
    function postRequest($text){
        // initiate curl
        $ch = curl_init();
        $url = SERVER_URL . PROPERTIES;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $text);
        curl_setopt($ch, CURLOPT_POST, 1);

        $headers = array();
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute the curl command
        $result = curl_exec($ch);

        // Returns the error if encountered
        if (curl_errno($ch)) {
                $result = 'Error:' . curl_error($ch);
        }
        // Close curl
        curl_close ($ch);

        return $result;
    }
    
    
    function jsonToElements($json){
        // Decode json in a "raw" array
        $jsonToArray = json_decode($json, true);
        $elements = array();

        if (!isset($jsonToArray['sentences'])){
            return null;
        }
        
        foreach ($jsonToArray['sentences'] as $sentence){
           $numSentence = $sentence['index'];
            foreach ($sentence['tokens'] as $tokens){
                $element = new element($tokens['index']-1,
                                    $numSentence,
                                    $tokens['word'],
                                    $tokens['pos']);
                $elements[] = $element;
            }
        }
        return $elements;
    }	
?>