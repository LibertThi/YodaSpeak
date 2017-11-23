<?php
    session_start();
    /*require_once('config.inc.php'); 
    require_once('corenlp.php');*/
    define ('INPUT_MAX_LENGTH', 140);
    require_once('convertToYodaspeak.php');
    require_once('element.php');   
    
    // returns "empty request" code
    if (!isset($_POST['text'])){
        echo '100';
        exit;
    }
    // Sanitize input
    $textToConvert = filter_input(INPUT_POST, 'text',
                    FILTER_SANITIZE_STRING);
    $textToConvert = str_replace('"', '',  html_entity_decode($textToConvert));
    $textToConvert = trim($textToConvert);
    if (strlen($textToConvert) > INPUT_MAX_LENGTH){
            $textToConvert = mb_substr($textToConvert, 0, INPUT_MAX_LENGTH);
    }
    // if it is empty after sanitizing, returns "empty request" code
    if (empty($textToConvert)){
        echo '100';
        exit;
    }

    $_SESSION['demande'] = $textToConvert;

    // If we get a response, convert it
    if (isset($_SESSION['reponse'])){
        $json = $_SESSION['reponse'];
        // convert it to an array of elements with pos
        $elements = jsonToElements($json);
        $reponse = convert($elements); 
        // Sometimes, response isn't converted quickly enough
        // and yoda displays an empty message. This fixes it
        if (!empty($reponse)){
            session_unset();
            session_destroy();
            echo $reponse;          
        }    
        else{
            echo '200'; 
        }       
        exit;
    }
    // Returns "waiting for response" code
    else{
        echo '200';
        exit;
    }
?>

