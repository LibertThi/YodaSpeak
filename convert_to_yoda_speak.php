<?php 
    function convert($elements){
        $result = '';
        
        // separate sentences to treat them one by one
        $sentences = separateSentences($elements);

        foreach ($sentences as $sentence){     
            if ($result != ''){
                $result .= ' ';
            }
                  
            // use the appropriate method to treat the sentence
            // depending of its type
            $sentenceType = getSentenceType($sentence);

            switch ($sentenceType){
                // Composed
                case 'Composed':
                case 'Passive':
                    $result .= convertComposed($sentence);
                    break;
                // Imperative
                case 'Imperative':
                    $result .= convertImperative($sentence);
                    break;
                // 'Standard'
                case 'SVO':
                    $result .= convertSVO($sentence);
                    break;
                // What might not be defined, thus making it not applicable
                default:
                    $result .= convertSVO($sentence);
                    break;
            }       
        }
        
        return $result;
    }
    
    // Get all the elements and separate sentences
    // returns an array of arrays of elements
    function separateSentences($elements){
        $sentences = array(); 
        foreach ($elements as $element){
            $sentenceIndex = $element->getSentenceIndex();            
            $sentences[$sentenceIndex][] = $element;
        }
        return $sentences;
    }
    
    function getSentenceType($elements){
        $csFound = false;
        $nbVerbs = 0;
        $imperativeFound = false;
        $isPassive = false;
        
        // loop into the sentence to determinate it's type
        foreach ($elements as $element){
            $pos = $element->getPos();
            
            // spot conjonction to look for composed sentence
            if ($pos == 'CS' or $pos == 'CC'){
                $csFound = true;
            }
            // count verbs in each side of the conjonction
            elseif (($pos == 'V') and ($csFound xor ($nbVerbs == 0))){
                $nbVerbs ++;
            }
            // check if there's an imperative verb
            elseif ($pos == 'VIMP') {
                $imperativeFound = true;
            }  
            elseif ($pos == 'PROREL' and $nbVerbs == 0){
                $isPassive = true;
            }
        }
        
        // if a conjonction (qui, que dont, etc) is found
        // and each side has a verb, then it's a composed sentence
        if ($csFound and $nbVerbs >= 2){
            return 'Composed';
        }
        elseif ($imperativeFound){
            return 'Imperative';
        }
        elseif ($isPassive){
            return 'Passive';
        }
        else{
            return 'SVO';
        }              
    }
     
    // Subject + Verb + Object
    function convertSVO($elements){      
        if ($elements == null){
            return '';
        }      
        
        //------------------------------------------
        // Sort elements into 2 arrays
        //------------------------------------------
        $start = array();
        $end = array();
        $verbFound = false;
        $vinfAlone = null;
        
        for ($i = 0; $i < count($elements); $i++){
            $element = $elements[$i];
            $pos = $element->getPos();
            
            // Put what is before the verb in the "end" array
            if (!$verbFound and $pos != 'PUNC'){             
               $end[] = $element;
            }
            
            // Split at the first verb encountered
            if ($pos == 'V' and !$verbFound){
                $verbFound = true;
            }
            // if an infinitive verb is found
            elseif ($pos == 'VINF'){               
                // check if the previous word is a pronoun
                $prevPos = $elements[$i - 1]->getPos();
                // move the VINF to the start with it's pronoun if found
                if ($prevPos == 'CLS' or $prevPos == 'CLO' or $prevPos == 'P'){
                    $start[] = $element;
                }
                // keep it for the end array otherwise
                else{
                    $vinfAlone = $element;                
                }
                
            }
            // Put what is after the verb in the "start" array
            elseif($pos != 'PUNC' and $verbFound){
                $start[] = $element;
            }          
        }
        
        //------------------------------------     
        // Reform sentence
        //------------------------------------      
        $sentence = '';
        
        // if there is no object, either place the infinitive verb before
        // or don't change anything
        if ($start == null){
            if ($vinfAlone != null){
                $start[] = $vinfAlone;
            }
            else{
                $start = $end;
                $end = null; 
            }         
        }
        elseif ($vinfAlone != null){
            array_unshift($end, $vinfAlone);
        }
       
        // Add the start array
        $sentence .= stringFromElements($start, true);
        
        // Add the end if it exists
        if ($end != null){
            $sentence .= ', ';              
            $sentence .= stringFromElements($end, false);
        }
        
        // Put the end punctuation
        putCorrectPunct($sentence, $elements);
        
        return $sentence;
    }
    
    function convertImperative($elements){
        if ($elements == null){
            return '';
        }
        
        $start = array();
        $imperativeVerb = '';
        
        //---------------------------------
        // Sort elements
        //---------------------------------
        foreach ($elements as $element){
            $pos = $element->getPos();
            $word = $element->getWord();
            
            if ($pos == 'VIMP' and $imperativeVerb == ''){
                $imperativeVerb = $word;
            }
            elseif ($pos != 'PUNC') {
                $start[] = $element;
            }
        }

        //---------------------------------
        // Reform sentence
        //---------------------------------
        $sentence = '';      
        
        // Put the start
        $sentence .= stringFromElements($start, true);       
        // Separate the start and the imperative with a comma 
        $sentence .= ', ';         
        // Put the imperative
        $sentence .= mb_strtolower($imperativeVerb) . " ";         
        // Put the end punctuation
        putCorrectPunct($sentence, $elements);
        
        return $sentence;
    }
    
    // Put the correct punct. at the end
    function putCorrectPunct(&$sentence, $elements){      
        $sentence = rtrim($sentence);
        
        $lastPunc = $elements[count($elements)-1];   
        if ($lastPunc->getPos() == 'PUNC'){
            // put space before if it's not a dot
            if ($lastPunc->getWord() != '.'){  
                $sentence .= ' ';
            }   
            $sentence .= $lastPunc->getWord();
        }
        // Put a '.' if there is no punct in the original sentence
        else{
            $sentence .= '.';
        }
    }
    
    function convertComposed($elements){
        if ($elements == null){
            return '';
        }
        
        // We need to split the composed sentence into 2 differents
        // and treat them separetely
        $firstSentence = array();
        $secondSentence = array();        
        $splitter = null;
        $possibleSplitters = array('CS', 'CC', 'PROREL');
        
        foreach ($elements as $element){
            $pos = $element->getPos();
            // store what is before the splitter
            if ($splitter == null and !in_array($pos, $possibleSplitters)){
                $firstSentence[] = $element;
            }
            // define the splitter
            elseif ($splitter == null and in_array($pos, $possibleSplitters)){
                $splitter = $element->getWord();
            }
            // store what is after the splitter
            else{
                $secondSentence[] = $element;
            }         
        }    
        
        var_dump($firstSentence);
        var_dump($secondSentence);
        
        // Treat each sentence
        $fullSentence = '';             
        // remove punct at the end of the first sentence  
        $modifiedFirstSentence = rtrim(convert($firstSentence), '.!?');       
        // remove uppercase at the beginning of the second
        $modifiedSecondSentence = lcfirst(convert($secondSentence));
        
        // Format the splitter   
        // Check if first letter of second sentence is a vowel
        // then, change "que" into "qu'" and ommit the space after
        $firstLetterFirstWord = substr(
                explode(' ',trim($modifiedSecondSentence))[0], 0, 1);
        $vowels = '/^a|e|i|o|u|y$/';
        if (preg_match($vowels, $firstLetterFirstWord) and $splitter == 'que'){
            $splitter = ' qu\'';
        }
        // put spaces before and after
        else{
            $splitter = " $splitter ";
        }
        
        // Reform the full sentence
        $fullSentence =  $modifiedFirstSentence . $splitter
                . $modifiedSecondSentence;      
        return $fullSentence;
    }
    
    // create an "ucfirst" function that works with accents
    function mb_ucfirst($str, $encoding = "UTF-8", $lower_str_end = false) {
        $first_letter = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding);
        $str_end = "";
        if ($lower_str_end) {
          $str_end = mb_strtolower(mb_substr($str, 1, mb_strlen($str, $encoding), $encoding), $encoding);
        }
        else {
          $str_end = mb_substr($str, 1, mb_strlen($str, $encoding), $encoding);
        }
        $str = $first_letter . $str_end;
        return $str;
    }   
    
    function stringFromElements($elements, $isStart){
        $string = '';
        $noSpaceChars = "/^(\.|\-|,|')$/";
             
        foreach ($elements as $element){           
            $word = $element->getWord();
            
            // First word with uppercase if specified
            if ($string == '' and $isStart){
                $string .= mb_ucfirst($word, 'UTF-8'); 
            }        
            else{
                // Remove last space if the word begins with "-"
                $charToCheck = mb_substr($word, 0, 1);
                
                if (preg_match("/^-$/", $charToCheck)){
                    $string = rtrim($string);
                }
                
                // Add word with lowercase if it isn't a named person
                if ($element->getPos() == 'NPP'){
                    $string .= $word;
                }
                else{
                    $string .= mb_strtolower($word);
                }  
            }         
            // Put a space after if needed
            // We need to look for dots, comma, dash and apostrophe           
            $charToCheck = mb_substr($word, strlen($word) - 1);
            if (!preg_match($noSpaceChars, $charToCheck)){
                $string .= ' ';
            }
        }
        // remove useless last space
        $string = rtrim($string);
        return $string;
    }
?>
