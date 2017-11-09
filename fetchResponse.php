<?php
    session_start();
    require_once('config.inc.php'); 
    require_once('corenlp.php');
    require_once('convert_to_yoda_speak.php');
    require_once('element.php');   
    
    if (!isset($_GET['text'])){
        echo '100';
        exit;
    }
    
    $textToConvert = filter_input(INPUT_GET, 'text',
                    FILTER_SANITIZE_STRING);
    $textToConvert = trim($textToConvert);
    if (strlen($textToConvert) > INPUT_MAX_LENGTH){
            $textToConvert = mb_substr($textToConvert,0,INPUT_MAX_LENGTH);
    }
    
    if (empty($textToConvert)){
        echo '100';
        exit;
    }

    if (!empty($textToConvert) and !isset($_SESSION['demande'])){
        $_SESSION['demande'] = $textToConvert;
    }

    if (isset($_SESSION['reponse'])){
        $reponse = $textToConvert;
        $json = $_SESSION['reponse'];
        // convert it to an array of elements with pos
        $elements = jsonToElements($json);
        $reponse = convert($elements);
        echo $reponse;
        session_unset();
        exit;
    }
    else{
        echo '200';
        exit;
    }
?>

