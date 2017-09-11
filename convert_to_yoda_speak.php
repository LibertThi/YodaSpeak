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
                case 'C':
                    $result .= convertComposed($sentence);
                    break;
                // Imperative
                case 'I':
                    $result .= convertImperative($sentence);
                    break;
                // 'Standard'
                case 'S':
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
    
    // C : Composed
    // I : Imperative
    // S : Standard
    // NA : Not applicable
    function getSentenceType($elements){
        $typeDetermined = false;   
        
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
            return 'C';
        }
        elseif ($imperativeFound){
            return 'I';
        }
        else{
            return 'S';
        }              
    }
     
    // Subject + Verb + Object
    function convertSVO($elements){      
        if ($elements == null){
            return '';
        }
        
        $start = array();
        $end = array();
        $verbFound = false;
        $infVerbWithoutPronoun = null;
        
        // Sort all elements of the sentence to the right array
        foreach ($elements as $element){
            $pos = $element->getPos();
            
            // Put what is before the verb in the "end" array
            if (!$verbFound and $pos != 'PUNC'){             
               $end[] = $element;
            }
            
            // Split at the first verb encountered
            if ($pos == 'V'){
                $verbFound = true;
            }
            // if an infinitive verb is found
            elseif ($pos == 'VINF'){               
                // We move put it at the start with it's pronoun
                $index = $element->getIndex();
                $prevPos = $elements[$index - 1]->getPos();
                if ($prevPos == 'CLS' or $prevPos == 'CLO'){
                    $start[] = $element;
                }
                // or we put it at the beginning of the "end" array
                // AFTER we check that the "start" array is empty
                // because in sentence "J'aime manger.", we don't want
                // it in the "end" array.
                else{
                    $infVerbWithoutPronoun = $element;                
                }
                
            }
            // put the rest in the "start" array
            elseif($pos != 'PUNC' and $verbFound ){
                $start[] = $element;
            }
            
        }
        var_dump($start);
        var_dump($end);
                
        // Reform sentence
        $sentence = '';
        
        // if there is no object, either place the infinitive verb before
        // or don't change anything
        if ($start == null){
            if ($infVerbWithoutPronoun != null){
                $start[] = $infVerbWithoutPronoun;
            }
            else{
                $start = $end;
                $end = null; 
            }         
        }
        elseif ($infVerbWithoutPronoun != null){
            array_unshift($end, $infVerbWithoutPronoun);
        }
       
        foreach ($start as $element){
            if ($sentence != ''){
                // Put spaces between words
                $sentence .= ' ';
                $sentence .= $element->getWord();
            }
            // First word
            else{
                // Change first letter to uppercase
                // Supports accents
                $sentence .= mb_substr(
                        mb_strtoupper($element->getWord()),0,1,'UTF-8')
                    . mb_substr($element->getWord(), 1);
            }          
        }
        // Separate the start and the end with a comma
        if ($end != null){
            $sentence .= ','; 
              
            // Put the subject and verb at the end
            foreach ($end as $element){
                if ($sentence != ''){
                    $sentence .= ' ';
                }
                $sentence .= mb_strtolower($element->getWord());
            }
        }
        // Put the correct punct. at the end
        if ($elements[count($elements)-1]->getPos() == 'PUNC'){
            $sentence .= $elements[count($elements)-1]->getWord();
        }
        // put a '.' if there is no punct in the original sentence
        else{
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
        
        // Sort elements
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

        // Reform sentence
        $sentence = '';
             
        // Put the start
        foreach ($start as $element){
            if ($sentence != ''){
                // Put spaces between words
                $sentence .= ' ';
                $sentence .= $element->getWord();
            }
            // First word
            else{
                // Change first letter to uppercase
                // Supports accents
                $sentence .= mb_substr(
                        mb_strtoupper($element->getWord()),0,1,'UTF-8')
                    . mb_substr($element->getWord(), 1);
            }          
        }
        // Separate the start and the imperative with a comma   
        $sentence .= ', '; 
        
        // Put the imperative
        $sentence .= mb_strtolower($imperativeVerb);
            
        // Put the correct punct. at the end
        if ($elements[count($elements)-1]->getPos() == 'PUNC'){
            $sentence .= $elements[count($elements)-1]->getWord();
        }
        // put a '.' if there is no punct in the original sentence
        else{
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
        
        $splitterFound = false;
        
        foreach ($elements as $element){
            $pos = $element->getPos();
            if (!$splitterFound){
                $firstSentence[] = $element;
            }
            else{
                $secondSentence[] = $element;
            }
            
            if ($pos == 'CS'){
                $splitterFound = true;
            }
        }
        
        $fullSentence = '';
        
        $fullSentence .= ConvertSVO($firstSentence);
        $fullSentence .= ConvertSVO($secondSentence);
        
        return $fullSentence;
    }
?>
