<?php

namespace App\CustomClasses;

use App\CustomClasses\SudokuModus;
use App\CustomClasses\SudokuType;

class SudokuStructure{
    private SudokuType $type;
    private SudokuModus $modus;
    private \SplFixedArray $content;
    
    public function __construct(SudokuType $type, SudokuModus $modus) {
        $this->type = $type;
        $this->modus = $modus;
        $this->initialize();
    }
    
    public function getType():SudokuType{
        return $this->type;
    }
    
    public function getModus():SudokuModus{
        return $this->modus;
    } 
    
    public function getContent():array{
        return $this->content->toArray();
    }
    
    private function initialize(){
        if($this->type === SudokuType::Standard){
            $this->content = new \SplFixedArray(9);
            for($i=0; $i<count($this->content); $i++){
                $this->content[$i] = new \SplFixedArray(9);
            }
        }
    }
}
?>