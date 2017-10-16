<?php    
    // set to false to disable the display of some debug elements
    define('DEBUG', true);
    // Maximum length of input
    define('INPUT_MAX_LENGTH', 140);
    // Base URL of the server
    define('SERVER_URL', 'http://192.168.154.130:9000/');
    // Properties for a POST request returning part-of-speech in French
    define('PROPERTIES','?properties='
            . '%7B%22annotators%22%3A%20%22tokenize%2Cssplit%2Cparse%2Cpos%22'
            . '%7D&pipelineLanguage=fr');
    
    // Load functions
    include('element.php');
    include('corenlp.php');
    include('convert_to_yoda_speak.php');
      
    // Get user input if it exists
    if (isset($_POST['v_TextToConvert'])){
        $textToConvert = filter_input(INPUT_POST, 'v_TextToConvert',
                FILTER_SANITIZE_STRING);
        $textToConvert = trim($textToConvert);
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
        <!-- Header -->
        <header class="row justify-content-center">
            <h1 class="col-12">Yoda Speak</h1>
        </header>
        <!-- Form row -->
        <section class="row"> 
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <p>
                    Tempore quo primis auspiciis in mundanum fulgorem surgeret victura dum erunt homines Roma, ut augeretur sublimibus incrementis, foedere pacis aeternae Virtus convenit atque Fortuna plerumque dissidentes, quarum si altera defuisset, ad perfectam non venerat summitatem.
                </p>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <form action="index.php" method="post">
                    <label>Votre texte (maximum 140 caractères)</label>
                    <textarea id="idTextToConvert" name="v_TextToConvert"
                             rows="4"
                             maxlength="<?php echo INPUT_MAX_LENGTH;?>"
                             placeholder="Ecrire ici ta phrase à modifier, tu dois."
                             ><?php
                        if (isset($textToConvert)){
                            echo $textToConvert;
                        }
                    ?></textarea>
                    <button type="submit" name="v_Convert">Convertir</button>
                </form>
            </div>
        </section>        
        <?php       
        if (!empty($textToConvert)){
            $sentence = $textToConvert;
            
            // create a new connection on corenlp
            $corenlp = new corenlp(SERVER_URL, PROPERTIES);   
            // connect to server and convert
            if ($corenlp->testConnection()){
                // retrieves json from corenlp server
                $json = $corenlp->postRequest($textToConvert);
                // convert it to an array of elements with pos
                $elements = $corenlp->jsonToElements($json);
                $sentence = convert($elements);
            }
            // print "error" if server is unreachable
            else{
                $sentence = 'Une perturbation dans la Force, '
                        . 'à me connecter m\'empêche.';
            }
            // display infos when in debug mode
            if (DEBUG){
                echo '<section class="row" style="margin-top:10px;"><div class="col-12">';
                echo '<h2 style="color:red;">DEBUG</h2>';
                // print json
                echo '<h2>JSON:</h2><p>' . $json . '</p>';
                // print annotated sentence
                echo '<h2>Phrase annotée:</h2><p>';
                foreach ($elements as $element){
                    echo $element->getWord() . '('. $element->getPOS(). ') ';
                }
                echo '</p>';
                echo '</div></section>';
            }
            
            // random number to display a random img of yoda
            $numImg = rand(1, 4);          
            echo
            '<div class="row justify-content-center">
                <div class="col-xs-12 col-sm-8 col-md-6 col-lg-4" id="bubble">
                <span id="text">' . $sentence . '</span>
                <span id="arrow_border"></span>
                <span id="arrow_inner"></span>
            </div>                 
            </div>
            <div class="row justify-content-center">
                <div class="col-xs-8 col-sm-8 col-md-6 col-lg-4">
                    <img id="yoda" src="images/yoda-0'. $numImg . '.png" alt="Yoda"/>
                </div>
            </div>';           
        }   
        ?>               
        <!-- Footer -->
        <footer class="row">
            <div class="col-12">
                Copyright © <?php echo date("Y") ?> | Thibault Libert
            </div>    
        </footer>
    </div>
</body>
</html>