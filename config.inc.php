<?php
    // set to true to display some debugging elements
    define('DEBUG', false);
    // Maximum length of input
    define('INPUT_MAX_LENGTH', 140);
    // Base URL of the server (with port)
    define('SERVER_URL', 'http://corenlp.s2.rpn.ch:9000/'); // Beaglebone
    // Properties for a POST request returning part-of-speech in French
    define('PROPERTIES','?properties='
            . '%7B%22annotators%22%3A%20%22pos%22'
            . '%7D&pipelineLanguage=fr');
?>