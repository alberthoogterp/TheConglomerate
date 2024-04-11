<?php

namespace App\CustomClasses\SudokuClasses;

class XWingFinder
{
    private static SudokuStructure $sudoku;
    private static $num;

    public static function findWings(SudokuStructure $sudokuStructure): XWing | false
    {
        self::$sudoku = $sudokuStructure;
        for (self::$num = 1; self::$num < 10; self::$num++) {
            $foundSquaresArray = self::findNumInLines();
            if ($foundSquaresArray) {
                for ($size = 4; $size > 1; $size--) {
                    $Xwing = self::recursiveWingFinder($foundSquaresArray, 0, [], [], $size); //look for xwings, swordfish or jellyfish
                    if (count($Xwing->uniquePositions) == $size && count($Xwing->wings) == $size) {
                        $affectedSquares = self::findAffectedSquares($Xwing);
                        if ($affectedSquares) {
                            $Xwing->affectedSquares = $affectedSquares;
                            return $Xwing;
                        }
                    }
                }
            }
        }
        return false;
    }

    private static function recursiveWingFinder(array $foundSquaresArray, int $currentIndex, array $wings, array $linePositions, int $size) //recursively try all combinations of wings
    {
        if ($currentIndex >= count($foundSquaresArray)) {
            return null;
        } else {
            for ($index = $currentIndex; $index < count($foundSquaresArray); $index++) {
                $newWing = $foundSquaresArray[$index];
                if (!in_array($newWing, $wings) && count($newWing) <= $size && count($newWing) > 1) {
                    $uniquePositions = self::validWing($foundSquaresArray[$index], $wings, $linePositions, $size);
                    if ($uniquePositions) {
                        $linePositions = $uniquePositions;
                        $wings[] = $newWing;
                        $result = self::recursiveWingFinder($foundSquaresArray, $index + 1, $wings, $linePositions, $size);
                        if ($result !== null) {
                            return $result;
                        }
                    }
                }
            }
        }
        return new XWing($wings, self::$num, $size, $linePositions, []);
    }

    private static function findAffectedSquares(Xwing $xwing): array //find all squares that will be changed by this x-wing
    {
        $affectedSquares = [];
        for ($sudokuNum1 = 0; $sudokuNum1 < 3; $sudokuNum1++) {
            for ($sudokuNum2 = 0; $sudokuNum2 < 3; $sudokuNum2++) {
                if (self::$sudoku->currentAreaType == AreaType::ROW) {
                    $notInWing = true;
                    foreach ($xwing->wings as $wing) {
                        if ($sudokuNum1 == $wing[0]->position->sudRow && $sudokuNum2 == $wing[0]->position->secRow) {
                            $notInWing = false;
                            break;
                        }
                    }
                    if ($notInWing) {
                        foreach ($xwing->uniquePositions as $uniquePos) {
                            $affectedSquare = self::$sudoku->sudokuBoard[$sudokuNum1][$uniquePos->sudCol][$sudokuNum2][$uniquePos->secCol];
                            if ($notInWing && is_array($affectedSquare->value) && in_array(self::$num, $affectedSquare->value)) {
                                $affectedSquares[] = $affectedSquare;
                            }
                        }
                    }
                } else if (self::$sudoku->currentAreaType == AreaType::COLUMN) {
                    $notInWing = true;
                    foreach ($xwing->wings as $wing) {
                        if ($sudokuNum1 == $wing[0]->position->sudCol && $sudokuNum2 == $wing[0]->position->secCol) {
                            $notInWing = false;
                            break;
                        }
                    }
                    if ($notInWing) {
                        foreach ($xwing->uniquePositions as $uniquePos) {
                            $affectedSquare = self::$sudoku->sudokuBoard[$uniquePos->sudRow][$sudokuNum1][$uniquePos->secRow][$sudokuNum2];
                            if ($notInWing && is_array($affectedSquare->value) && in_array(self::$num, $affectedSquare->value)) {
                                $affectedSquares[] = $affectedSquare;
                            }
                        }
                    }
                }                
            }
        }
        return $affectedSquares;
    }

    private static function findNumInLines() //loops over all lines and returns an array of squares that contain the number
    {
        $numArrays = []; //array of collumns or rows with the same nums as the originalpair
        for ($sudokuNum1 = 0; $sudokuNum1 < 3; $sudokuNum1++) {
            for ($sudokuNum2 = 0; $sudokuNum2 < 3; $sudokuNum2++) {
                if (self::$sudoku->currentAreaType == AreaType::ROW) {
                    $line = self::$sudoku->loopRow($sudokuNum1, $sudokuNum2);
                } else if (self::$sudoku->currentAreaType == AreaType::COLUMN) {
                    $line = self::$sudoku->loopCol($sudokuNum1, $sudokuNum2);
                }
                $arr = self::$sudoku->findNumInArea($line, self::$num);
                if ($arr) {
                    $numArrays[] = $arr;
                }
            }
        }
        return $numArrays;
    }

    private static function validWing($line, $wings, $linePositions, $size): array | false //checks if the new wing fits with the other wings, and returns all unique row/columns that these wings are in
    {
        foreach ($line as $square) {
            $unique = true;
            foreach ($wings as $wing) {
                foreach ($wing as $wingSquare) {
                    if (self::$sudoku->currentAreaType == AreaType::ROW) {
                        if ($square->position->sudCol == $wingSquare->position->sudCol && $square->position->secCol == $wingSquare->position->secCol) {
                            $unique = false;
                        }
                    } else if (self::$sudoku->currentAreaType == AreaType::COLUMN) {
                        if ($square->position->sudRow == $wingSquare->position->sudRow && $square->position->secRow == $wingSquare->position->secRow) {
                            $unique = false;
                        }
                    }
                }
            }
            if ($unique) {
                $linePositions[] = $square->position;
            }
        }
        if (count($linePositions) > $size) {
            return false;
        }
        return $linePositions;
    }
}
