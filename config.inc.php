<?php
    // set to true to display some debugging elements
    define('DEBUG', false);
    // Maximum length of input
    define('INPUT_MAX_LENGTH', 140);
    // Base URL of the server (with port)
    define('SERVER_URL', 'http://192.168.154.130:9000/');
    // Properties for a POST request returning part-of-speech in French
    define('PROPERTIES','?properties='
            . '%7B%22annotators%22%3A%20%22tokenize%2Cssplit%2Cparse%2Cpos%22'
            . '%7D&pipelineLanguage=fr');
?>