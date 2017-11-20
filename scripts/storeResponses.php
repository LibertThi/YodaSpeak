<?php
    // access UID
    define('UID', '0082f2af-a4fe-43b2-8b44-2a87765aac72');

    if (!isset($_GET['uid']) or !isset($_GET['json'])){
        header("HTTP/1.1 403 Forbidden" );
        exit;
    }
    $uid = filter_input(INPUT_GET,'uid',FILTER_SANITIZE_STRING);
    if ($uid != UID) {
        header("HTTP/1.1 403 Forbidden");
        exit;
    }
    $sessions_path = session_save_path();
    
    $json_url = $_GET['json'];
    $json = html_entity_decode($json_url, ENT_QUOTES, "UTF-8");
    // recreate an array to store responses
    $responses = json_decode($json, true);
    foreach($responses as $sess_id => $sess_resp){
        // format content to put in session file
        $content = 'reponse|s:' . strlen($sess_resp) . ':"' . $sess_resp . '";';
        // append response to the associated session file
        file_put_contents($sessions_path . '/' . $sess_id, $content, FILE_APPEND);
    }
?>