<?php
namespace app\CustomClasses\SudokuClasses;

class XWing{
    public array $wings;
    public int $num;
    readonly int $size;
    readonly string $type;
    public array $uniquePositions;
    public array $affectedSquares;

    public function __construct(array $wings, int $num, int $size, array $uniquePositions, array $affectedSquares){
        $this->wings = $wings;
        $this->num = $num;
        $this->size = $size;
        $this->uniquePositions = $uniquePositions;
        $this->affectedSquares = $affectedSquares;

        switch($size){
            case 2:
                $this->type = "Xwing";
                break;
            case 3:
                $this->type = "Swordfish";
                break;
            case 4:
                $this->type = "Jellyfish";
                break;
        }
    }
}