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
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Convertisseur Yoda Speak">
    <meta name="keywords" content="HTML,CSS,XML,JavaScript">
    <meta name="author" content="Thibault Libert">
    <title>Yoda Speak</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous"/>
    <link rel="stylesheet" href="styles/style.css"/>
</head>
<body>
<div id="background-opacity"></div>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xs-11 col-sm-10 col-md-8 col-lg-8">
            <h1>Yoda Speak</h1>
            <div class="row justify-content-center">
            <form class="col-xs-10 col-sm-8 col-md-8 col-lg-8" action="index.php" method="post">
                <p>
                    <label for="idTextToConvert">Vous dites: (<?php
                        echo MAX_LENGTH;?> caractères maximum)
                    </label>
                </p>
                <p>
                    <textarea id="idTextToConvert" name="v_TextToConvert"
                             rows="4"
                             maxlength="<?php echo MAX_LENGTH;?>"
                             placeholder="Ecrire ici ta phrase à modifier, tu dois."
                             ><?php
                        if (isset($textToConvert)){
                            echo $textToConvert;
                        }
                    ?></textarea></p>
                <button type="submit" name="v_Convert">Convertir</button>
            </form>
            </div>
           
            <?php       
            if (!empty($textToConvert)){
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
                echo
                    '<div id="bubble">
                    <span id="text">' . convert($elements) . '</span>
                    <span id="arrow_border"></span>
                    <span id="arrow_inner"></span>
                    </div>';
            }
            echo '<img class="col-5" id="yoda" src="images/dank-yoda.png"/>';
            ?>
        </div>
    </div>
    <footer>
        <p>Copyright - <?php echo date("Y") ?></p>
    </footer>
</div>
</body>
</html>