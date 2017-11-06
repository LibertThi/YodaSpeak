<?php       
    class corenlp {
    
        private $SERVER_URL;
        private $PROPERTIES;

        public function __construct($serverUrl, $properties) {
            $this->SERVER_URL = $serverUrl;
            $this->PROPERTIES = $properties;
        }
        
        public function testConnection(){
            $json = $this->postRequest("Oui");
            if ($json != null) {
                return true;
            }
            else{
                return false;
            }
        }
        
        // Basic POST Request using cURL
        public function postRequest($text){
            // initiate curl
            $ch = curl_init();
            $url = $this->SERVER_URL . $this->PROPERTIES;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $text);
            curl_setopt($ch, CURLOPT_POST, 1);

            $headers = array();
            $headers[] = "Content-Type: application/x-www-form-urlencoded; charset=utf-8";
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // Execute the curl command
            try{
            $result = curl_exec($ch);
            }
            catch(CURLException $e){
                $result = 'Curl timeout';
            }
            
            // Returns the error if encountered
            if (curl_errno($ch)) {
                $result = null;         
            }
            // Close curl
            curl_close ($ch);

            return $result;         
        }
        
        public function jsonToElements($json){
            // decode the json first
            $json = html_entity_decode($json, ENT_QUOTES, "UTF-8");

            // parse json in a "raw" array
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
    }
?>