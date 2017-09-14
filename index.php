<?php    
    // Load functions
    include('element.php');
    include('connect_api.php');
    include('convert_to_yoda_speak.php');
    
    // Get user input if it exists
    if (isset($_POST['v_TextToConvert'])){
        $textToConvert = $_POST['v_TextToConvert'];
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="DCTERMS.creator" content="Thibault Libert"/>
        <title>Yoda Speak</title>
        <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css"
      rel="stylesheet">
    </head>
    <body>
        <br/>
        <div class="container">
        <form action="index.php" method="post">
            <p><label for="idTextToConvert">Texte à convertir :</label></p>
            <p><textarea id="idTextToConvert" name="v_TextToConvert"
                         rows="4" cols="50"
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
            echo '<h2>JSON:</h2><p>' . $json . '</p>';
            // convert it to an array of elements with pos
            $elements = jsonToElements($json);
            
            // print annotated sentence
            echo '<h2>Phrase annotée:</h2><p>';
            foreach ($elements as $element){
                echo $element->getWord() . '('. $element->getPOS(). ') ';
            }
            echo '</p>';
            
            // Display the converted sentence
            echo '<h2>Phrase modifiée:</h2><p>';
            echo convert($elements);
            echo '</p>';
        }
        ?>
        </div>
    </body>
</html>