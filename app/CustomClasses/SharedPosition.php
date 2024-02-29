<?php
namespace App\CustomClasses;
use Exception;

class SharedPosition{
    public readonly array $types;
    public readonly SudokuPosition $position;
    public function __construct(array $areaTypes, SudokuPosition $position){
        foreach($areaTypes as $type){
            if(!$type instanceof AreaType){
                throw new Exception("areaTypes must be of type AreaType");
            }
        }
        $this->types = $areaTypes;
        $this->position = $position;
    }
}