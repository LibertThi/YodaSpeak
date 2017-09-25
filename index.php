<?php    
    // set to false to disable the display of some debug elements
    define('DEBUG', false);
    define('MAX_LENGTH', 140);
    
    // Load functions
    include('element.php');
    include('connect_api.php');
    include('convert_to_yoda_speak.php');
      
    // Get user input if it exists
    if (isset($_POST['v_TextToConvert'])){
        $textToConvert = filter_input(INPUT_POST, 'v_TextToConvert',
                FILTER_SANITIZE_STRING);
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="description" content="Convertisseur Yoda Speak">
    <meta name="keywords" content="HTML,CSS,XML,JavaScript">
    <meta name="author" content="Thibault Libert">
    <title>Yoda Speak</title>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css"
  rel="stylesheet">
    <style>
        #yoda{
            width: 200px;
            position: relative;
            z-index: 1;
            margin-top: 20px;
            border-radius: 5%;
            -webkit-box-shadow: 0px 0px 1px 0px rgba(0,0,0,0.75);
            -moz-box-shadow: 0px 0px 1px 0px rgba(0,0,0,0.75);
            box-shadow: 0px 0px 1px 0px rgba(0,0,0,0.75);
        }
        textarea{
            resize: none;
        }
        #bubble{
            margin-top: 20px;
            position: relative;
            background-color:white;
            text-align:center;
            width:200px;
            padding: 1em;
            border:2px solid #555555;
            border-top-right-radius:80px 40px;
            border-top-left-radius:80px 40px;
            border-bottom-right-radius:80px 40px;
            border-bottom-left-radius:80px 40px;
            /* implémentation Firefox */
            -moz-border-radius-topright:80px 40px;
            -moz-border-radius-topleft:80px 40px;
            -moz-border-radius-bottomright:80px 40px;
            -moz-border-radius-bottomleft:80px 40px;
            /* implémentation Chrome, Safari, ... */
            -webkit-border-top-right-radius:80px 40px;
            -webkit-border-top-left-radius:80px 40px;
            -webkit-border-bottom-right-radius:80px 40px;
            -webkit-border-bottom-left-radius:80px 40px;
        }
        #arrow_border{
            width:0;
            height:0;
            line-height:0;
            border-bottom:30px solid transparent;
            border-right:30px solid transparent;
            border-left:30px solid black; /* couleur de la bordure de la bulle */
            position:absolute;
            bottom:-30px;
            right:17px;
            z-index: 2;
        }
        #arrow_inner{
            width:0;
            height:0;
            line-height:0;
            border-bottom:30px solid transparent;
            border-right:30px solid transparent;
            border-left:30px solid white; /* couleur du fond de la bulle */
            position:absolute;
            bottom:-25px;
            right:15px;
            z-index: 2;
        }
    </style>
</head>
<body>
    <br/>
    <div class="container">
    <form action="index.php" method="post">
        <p><label for="idTextToConvert">Vous dites:</label>
        <?php echo MAX_LENGTH;?> charactères maximum.</p>
        <p><textarea id="idTextToConvert" name="v_TextToConvert"
                     rows="4" cols="50"
                     maxlength="<?php echo MAX_LENGTH;?>"
                     placeholder="Ecrire ici ta phrase à modifier, tu dois."
                     ><?php
                if (isset($textToConvert)){
                    echo $textToConvert;
                }
            ?></textarea></p>
        <button type="submit" name="v_Convert">Convertir</button>
    </form>
    <?php       
    if (isset($textToConvert)){
        // retrieves json from corenlp server
        $json = postRequest($textToConvert);

        // convert it to an array of elements with pos
        $elements = jsonToElements($json);

        if (DEBUG){
            // print json
            echo '<h2>JSON:</h2><p>' . $json . '</p>';
            // print annotated sentence
            echo '<h2>Phrase annotée:</h2><p>';
            foreach ($elements as $element){
                echo $element->getWord() . '('. $element->getPOS(). ') ';
            }
            echo '</p>';
       }

        // Display the converted sentence
        //echo '<h2>Yoda dit:</h2><p>';       
        echo '<div id="bubble">
            <span id="text">' . convert($elements) . '</span>
            <span id="arrow_border"></span>
            <span id="arrow_inner"></span>
            </div>';
        echo '<img id="yoda" src="images/dank-yoda.png"/>';
        echo '</p>';
    }
    ?>
    </div>
</body>
</html>