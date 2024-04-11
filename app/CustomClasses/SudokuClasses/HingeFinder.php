<?php
namespace App\CustomClasses\SudokuClasses;

class HingeFinder{
    private static array $hinges;
    private static SudokuSquare $pivot;
    private static array $areas;
    private static int $x;
    private static int $y;
    private static int|null $z;
    private static SudokuStructure $sudoku;

    public static function FindHinges(SudokuSquare $sudokuPivot, SudokuStructure $sudokuStructure){
        self::$pivot = $sudokuPivot;
        self::$sudoku = $sudokuStructure;
        self::$hinges = [];
        $pivotSector = self::$sudoku->loopSector(self::$pivot->position->sudRow, self::$pivot->position->sudCol);
        $pivotCol = self::$sudoku->loopCol(self::$pivot->position->sudCol, self::$pivot->position->secCol);
        $pivotRow = self::$sudoku->loopRow(self::$pivot->position->sudRow, self::$pivot->position->secRow);
        self::$areas = [$pivotSector, $pivotCol, $pivotRow];
        self::$x = self::$pivot->value[0];
        self::$y = self::$pivot->value[1];
        self::$z = null;

        foreach(self::$areas as $area){
            $compSquaresArray = self::findHingeSquares($area);
            foreach($compSquaresArray as $compSquares){
                $intersectSector1 = self::$sudoku->loopSector($compSquares[0]->position->sudRow, $compSquares[1]->position->sudCol);
                $intersectSector2 = self::$sudoku->loopSector($compSquares[1]->position->sudRow, $compSquares[0]->position->sudCol);
                $intersectSectorArray = [$intersectSector1, $intersectSector2];
                $affectedSquares = self::findAffectedSquares($intersectSectorArray, $compSquares[0], $compSquares[1]);
                if($affectedSquares){
                    $hinge = new SudokuHinge(self::$pivot, [$compSquares[0], $compSquares[1]], $affectedSquares, self::$x, self::$y, self::$z);
                    self::$hinges[] = $hinge;
                }
            }
        }
        return self::$hinges;
    }

    private static function findHingeSquares(array $area1){
        $compSquaresArray = [];
        foreach($area1 as $areaSquare){
            if(self::isValidFirstHingeSquare($areaSquare)){
                $compSquare1 = $areaSquare;
                $diff = array_diff($areaSquare->value, self::$pivot->value);
                if($diff){
                    self::$z = array_values($diff)[0];//we want to get the number that isnt already in our first square
                    foreach(self::$areas as $area2){
                        if($area2 != $area1){
                            foreach($area2 as $areaSquare2){
                                if(self::isValidSecondHingeSquare($compSquare1, $areaSquare2)){
                                    $compSquare2 = $areaSquare2;
                                    $compSquaresArray[] = [$compSquare1, $compSquare2];
                                }
                            }
                        }
                    }
                }
            }
        }
        return $compSquaresArray;
    }
    
    private static function isValidFirstHingeSquare(SudokuSquare $areaSquare){
        if($areaSquare->value != self::$pivot->value && is_array($areaSquare->value) && count($areaSquare->value) == 2 && in_array(self::$x, $areaSquare->value)){
            return true;
        }
        return false;
    }

    private static function isValidSecondHingeSquare(SudokuSquare $compSquare1, SudokuSquare $areaSquare){
       
        if(is_array($areaSquare->value)
        && $areaSquare->value != self::$pivot->value && count($areaSquare->value) == 2 && in_array(self::$y, $areaSquare->value) && in_array(self::$z, $areaSquare->value)
        && ($compSquare1->position->sudRow != $areaSquare->position->sudRow || $compSquare1->position->sudCol != $areaSquare->position->sudCol) 
        && ($compSquare1->position->sudRow != $areaSquare->position->sudRow || $compSquare1->position->secRow != $areaSquare->position->secRow) 
        && ($compSquare1->position->sudCol != $areaSquare->position->sudCol || $compSquare1->position->secCol != $areaSquare->position->secCol)){//both comparesquares can't be on the same line or sector
            return true;
        }
        return false;
    }

    private static function findAffectedSquares(array $intersectSectorArray, SudokuSquare $compSquare1, SudokuSquare $compSquare2){
        $affectedSquares = [];
        foreach($intersectSectorArray as $sector){
            foreach($sector as $affectedSquare){
                if($affectedSquare != self::$pivot && $affectedSquare != $compSquare1 && $affectedSquare != $compSquare2){//it isnt the hinge squares
                    if(is_array($affectedSquare->value) && in_array(self::$z, $affectedSquare->value)){
                       if($compSquare1->position->sudRow == $compSquare2->position->sudRow){//the composite squares are on the same row
                            if($affectedSquare->position->sudCol == $compSquare1->position->sudCol && $affectedSquare->position->secRow == $compSquare2->position->secRow){
                                $affectedSquares[] = $affectedSquare;
                            }
                            else if($affectedSquare->position->sudCol == $compSquare2->position->sudCol && $affectedSquare->position->secRow == $compSquare1->position->secRow){
                                $affectedSquares[] = $affectedSquare;
                            }
                       }
                       else if($compSquare1->position->sudCol == $compSquare2->position->sudCol){//the composite squares are on the same column
                            if($affectedSquare->position->sudRow == $compSquare1->position->sudRow && $affectedSquare->position->secCol == $compSquare2->position->secCol){
                                $affectedSquares[] = $affectedSquare;

                            }
                            else if($affectedSquare->position->sudRow == $compSquare2->position->sudRow && $affectedSquare->position->secCol == $compSquare1->position->secCol){
                                $affectedSquares[] = $affectedSquare;
                            }
                       }
                       else{
                            if($affectedSquare->position->secRow == $compSquare1->position->secRow && $affectedSquare->position->secCol == $compSquare2->position->secCol){
                                $affectedSquares[] = $affectedSquare;
                            }
                            else if($affectedSquare->position->secRow == $compSquare2->position->secRow && $affectedSquare->position->secCol == $compSquare1->position->secCol){
                                $affectedSquares[] = $affectedSquare;
                            }
                       }
                    }
                }
            }
        }
        return $affectedSquares;
    }
}