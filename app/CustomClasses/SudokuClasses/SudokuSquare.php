<?php
namespace App\CustomClasses\SudokuClasses;

//use app\CustomClasses\SudokuPosition;

Class SudokuSquare{
    public int|array|string $value;
    public SudokuPosition $position;
    public array $areaExlusionForPairFinding;
    public function __construct($val, $sudR, $sudC, $secR, $secC){
        $this->value = $val;
        $this->position = new SudokuPosition($sudR, $sudC, $secR, $secC);
        $this->areaExlusionForPairFinding = [];
    }
}