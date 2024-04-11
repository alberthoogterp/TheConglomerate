<?php
namespace App\CustomClasses\SudokuClasses;

class SudokuHinge{
    public SudokuSquare $pivot;
    public array $complementaryPair;
    public array $affectedSquares;

    public $x;
    public $y;
    public $z;
    public function __construct($pivot, $complementaryPair, $affectedSquares, $x, $y, $z){
        $this->pivot = $pivot;
        $this->complementaryPair = $complementaryPair;
        $this->affectedSquares = $affectedSquares;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }
}