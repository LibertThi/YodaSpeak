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
        
        // loop into the sentence to determinate it's type
        foreach ($elements as $element){
            $pos = $element->getPos();
            
            // spot conjonction to look for composed sentence
            if ($pos == 'CS'){
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
        }
        
        // if a conjonction (qui, que dont, etc) is found
        // and each side has a verb, then it's a composed sentence
        if ($csFound and $nbVerbs >= 2){
            return 'Composed';
        }
        elseif ($imperativeFound){
            return 'Imperative';
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
        $noSpaceChars = "\.|\-|,|'";
        
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
        
        // Put the correct punct. at the end
        $lastPunc = $elements[count($elements)-1];   
        if ($lastPunc->getPos() == 'PUNC'){
            // remove space before if it's a dot
            if ($lastPunc->getWord() == '.'){
                $sentence = rtrim($sentence);
            } 
            $sentence .= $lastPunc->getWord();
        }
        // put a '.' if there is no punct in the original sentence
        else{
            $sentence = rtrim($sentence);
            $sentence .= '.';
        }
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
            
         // Put the correct punct. at the end
        $lastPunc = $elements[count($elements)-1];   
        if ($lastPunc->getPos() == 'PUNC'){
            // remove space before if it's a dot
            if ($lastPunc->getWord() == '.'){
                $sentence = rtrim($sentence);
            } 
            $sentence .= $lastPunc->getWord();
        }
        // Put a '.' if there is no punct in the original sentence
        else{
            $sentence = rtrim($sentence);
            $sentence .= '.';
        }
        return $sentence;
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
        
        foreach ($elements as $element){
            $pos = $element->getPos();
            if ($splitter == null and $pos != 'CS'){
                $firstSentence[] = $element;
            }
            elseif ($pos == 'CS'){
                $splitter = $element->getWord();
            }
            else{
                $secondSentence[] = $element;
            }         
        }    
        //---------------------------
        // Treat each sentence
        //---------------------------
        $fullSentence = '';
        // remove punct at the end of the first sentence  
        $modifiedFirstSentence = rtrim(ConvertSVO($firstSentence), '.!?');
        
        // remove uppercase at the beginning of the second
        $modifiedSecondSentence = ConvertSVO($secondSentence);
        $modifiedSecondSentence = mb_substr(mb_strtolower($modifiedSecondSentence)
                , 0, 1, 'UTF-8') . mb_substr($modifiedSecondSentence, 1);
        
        $fullSentence =  $modifiedFirstSentence . ' ' . $splitter
                . ' ' . $modifiedSecondSentence;      
        return $fullSentence;
    }
    
    
    function stringFromElements($elements, $isStart){
        $string = '';
        $noSpaceChars = "\.|\-|,|'";
             
        foreach ($elements as $element){           
            $word = $element->getWord();
            
            // First word with uppercase if specified
            if ($string == '' and $isStart){
                // Change first letter to uppercase
                // Supports accents
                $string .= mb_substr(mb_strtoupper($word),0,1,'UTF-8')
                    . mb_substr($word, 1);         
            }        
            else{
                // Remove last space if the word begins with "-"
                $charToCheck = mb_substr($word, 0, 1);
                mb_ereg_search_init($charToCheck, '-');
                if (mb_ereg_search()){
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
            $charToCheck = mb_substr($word, strlen($word)-1);
            mb_ereg_search_init($charToCheck, $noSpaceChars);
            if (!mb_ereg_search()){
                $string .= ' ';
            }
        }
        // remove useless last space
        $string = rtrim($string);
        return $string;
    }
?>
