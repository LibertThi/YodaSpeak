<?php
class element {   
    // Attributes
    private $WORD;
    private $INDEX;
    private $POS;
    private $NUM_SENTENCE;   
    // Constructor
    public function __construct($index, $numSentence, $word, $pos) {
        $this->INDEX = $index;
        $this->NUM_SENTENCE = $numSentence;
        $this->WORD = $word;
        $this->POS = $pos;
        
    }   
    // Accessors
    function getIndex(){
        return $this->INDEX;
    }   
    function getWord(){
        return $this->WORD;
    }   
    function getSentenceIndex(){
        return $this->NUM_SENTENCE;
    }  
    function getPOS(){
        return $this->POS;
    }
}
?>
