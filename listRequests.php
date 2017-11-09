<?php
    // access UID
    define('UID', 'a1b2c3d4');
    
    if (!isset($_GET['uid'])){
        header("HTTP/1.1 403 Forbidden" );
        exit;
    }
    $uid = filter_input(INPUT_GET,'uid',FILTER_SANITIZE_STRING);
    if ($uid != UID) {
        header("HTTP/1.1 403 Forbidden" );
        exit;
    }
    $sessions_path = session_save_path();
    $requests = array();
    // get all sess_id
    $dir = scandir($sessions_path);
    foreach ($dir as $entry){
        if (!is_dir($sessions_path . '/' . $entry)){
            $requests[$entry] = false;
        }
    }
    if (empty($requests)){
        exit;
    }
    // get content for each sess_id
    foreach ($requests as $sess_id => $sess_content){
        $file_content = file_get_contents($sessions_path . '/' . $sess_id);    
        $pattern = '/demande\|s:\d+:"(.*?)".*/';
        $request = preg_match($pattern, $file_content, $matches);
        if (!empty($matches)){
            $requests[$sess_id] = $matches[1];
        }
    }
    $json = urlencode(json_encode($requests));
    echo $json;
?>