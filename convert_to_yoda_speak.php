<?php 
    function convert($elements){
        $result = '';
        
        // separate sentences to treat them one by one
        $sentences = separateSentences($elements);

        foreach ($sentences as $sentence){     
            if ($result != ''){
                $result .= ' ';
            }
                       
            // is there an imperative verb ?
            
            // is it a composed sentence ?
            
            // else, it is a standard sentence
            
            $result .= convertSVO($sentence);
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
    function getSentenceType($elements){
        $typeDetermined = false;   
        
        
        // loop once to check if it is composed
        $csFound = false;
        $nbVerbs = 0;
        foreach ($elements as $element){
            $pos = $element->getPos();
            if ($pos == 'CS'){
                $csFound = true;
            }
            elseif ($pos == 'V' and ($csFound xor $nbVerbs == 0)){
                $nbVerbs ++;
            }
        }
        
        if ($csFound and $nbVerbs > 2){
            
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
                if ($prevPos == 'CLS' || $prevPos == 'CLO'){
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
            elseif($pos != 'PUNC' && $verbFound ){
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
    
    // Subordinate clause, separated by "que, qui, dont"
    function convertSubordinate($elements){
    
    }
?>
