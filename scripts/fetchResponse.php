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
    $textToConvert = str_replace('"', '',  html_entity_decode($textToConvert));
    $textToConvert = trim($textToConvert);
    if (strlen($textToConvert) > INPUT_MAX_LENGTH){
            $textToConvert = mb_substr($textToConvert, 0, INPUT_MAX_LENGTH);
    }
    
    if (empty($textToConvert)){
        echo '100';
        exit;
    }

    $_SESSION['demande'] = $textToConvert;

    if (isset($_SESSION['reponse'])){
        $json = $_SESSION['reponse'];
        
      //  echo 'reponse : ' . $json . '<br>';
        // convert it to an array of elements with pos
        $elements = jsonToElements($json);
       /* echo 'elements : ';
        print_r($elements);
        echo '<br>';*/
        $reponse = convert($elements);
        
        if (!empty($reponse)){
            session_unset();
            session_destroy();
            echo $reponse;          
            exit;
        }
        else{
            echo '200'; 
        }       
        exit;
    }
    else{
        echo '200';
        exit;
    }
?>

