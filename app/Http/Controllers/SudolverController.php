<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomClasses\SudokuModus;
use App\CustomClasses\SudokuType;

class SudolverController extends Controller
{
    public function pageload(){
        $typeArray = $this->enumToArray(SudokuType::class);
        $modusArray = $this->enumToArray(SudokuModus::class);
        $sudokuArray = session()->get("SudokuArray") ?? [];
        return view("sudolverPage",["typeArray"=>$typeArray, "modusArray"=>$modusArray, "sudokuArray"=>$sudokuArray]);
    }

    public function solve(Request $request){
        $sudokuArray = $request->get("input");
        $error = "";
        
        if($sudokuArray){
            $filledInputsAmmount = $this->validSudokuInput($sudokuArray);
            if(!$filledInputsAmmount){
                $error = "This sudoku is invalid. Make sure you fill in atleast 17 numbers, with no duplicates in rows columns and squares.";
            }
            else{
                $solution = $this->sudokuSolver($sudokuArray, $filledInputsAmmount[0], $filledInputsAmmount[1]);
                if(!$solution){
                    $error = "This sudoku is unsolvable.";
                }
                else{
                    $sudokuArray = $solution;
                    $error = "Succes! :)";
                }
            }
        }
        else{
            $error = "Something went wrong.";
        }
        return redirect()->Route("sudolver")->withErrors(["solveError"=>$error])->with(["SudokuArray"=>$sudokuArray]);
    }

    private function enumToArray($enumClass): array {
        return array_map(
            function($case) { return $case->value; },
            $enumClass::cases()
        );
    }
    
    //checks if the prefilled sudoku is solvable
    private function validSudokuInput(array $sudArray) : array | false{
        $preFilledAmmount = 0;
        $total = 0;
        for($i = 0; $i < count($sudArray); $i++){
            for($j = 0; $j < count($sudArray[$i]); $j++){
                $total++;
                if ($sudArray[$i][$j] != "-"){
                    $preFilledAmmount++;
                    if($this->sectorHasDuplicate($sudArray[$i], $j, $sudArray[$i][$j]) || $this->RowHasDuplicate($sudArray, $i, $j, $sudArray[$i][$j]) || $this->colHasDuplicate($sudArray, $i, $j, $sudArray[$i][$j])){
                        return false;
                    }
                }
            }
        }
        if($preFilledAmmount < 17){//any sudoku with less than 17 characters is unsolvable
            return false;
        }
        return [$preFilledAmmount, $total];
    }

    private function sectorHasDuplicate(array $sector, $numPos, int $num): bool{
        for($i = 0; $i < count($sector); $i++){//check if a number appears multiple times in the same sector
            if($i != $numPos){
                if($num == $sector[$i]){
                    return true;
                }
            }
        }
        return false;
    }

    private function colHasDuplicate(array $sudArray, int $sector, int $numPos, int $num){
        $createIndex = function($number){//get the right indexes that need to be looped over based on coordinates of the currently evaluated number
            if(in_array($number, [0,3,6])){
                $start = 0;
                $stop = 7;
            }
            else if(in_array($number, [1,4,7])){
                $start = 1;
                $stop = 8;
            }
            else{
                $start = 2;
                $stop = 9;
            }
            return [$start,$stop];
        };

        $sectorIndices = $createIndex($sector);
        $sectorEnd = $sectorIndices[1];
        $numposIndices = $createIndex($numPos);
        $numPosEnd = $numposIndices[1];
       
        for($sectorIndex = $sectorIndices[0]; $sectorIndex < $sectorEnd; $sectorIndex += 3){
           for($numPosIndex = $numposIndices[0]; $numPosIndex < $numPosEnd; $numPosIndex += 3){
                $comparedNum = $sudArray[$sectorIndex][$numPosIndex];
                if($sectorIndex == $sector && $numPosIndex == $numPos){
                    continue;
                }
                if($comparedNum == $num){
                    return true;
                }
           }
        }
        return false;
    }

    private function RowHasDuplicate(array $sudArray, int $sector, int $numPos, int $num){
        $createIndex = function($number){//get the right indexes that need to be looped over based on coordinates of the currently evaluated number
            if($number < 3){
                $start = 0;
                $stop = 3; 
            }
            else if($number > 5){
                $start = 6;
                $stop = 9;
            }
            else{
                $start = 3;
                $stop = 6;
            }  
            return [$start, $stop];
        };

        $sectorIndices = $createIndex($sector);
        $sectorEnd = $sectorIndices[1];
        $numPosIndices = $createIndex($numPos);
        $numPosEnd = $numPosIndices[1];

        for($sectorIndex = $sectorIndices[0]; $sectorIndex < $sectorEnd; $sectorIndex++){
            for($numPosIndex = $numPosIndices[0]; $numPosIndex < $numPosEnd; $numPosIndex++){
                $comparedNum = $sudArray[$sectorIndex][$numPosIndex];
                if($sectorIndex == $sector && $numPosIndex == $numPos){
                    continue;
                }
                if($comparedNum == $num){
                    return true;
                }
            }
        } 
        return false;
    }

    private function sudokuSolver(array $sudArray, int $filledAmmount, $totalAmmount) : array | false{
        $solution = $sudArray;
        $filledInputs = $filledAmmount;
        $nosolution = false;
        while($filledInputs != $totalAmmount && !$nosolution){
            $nosolution = true;
            for($i = 0; $i < count($solution); $i++){
                for($j = 0; $j < count($solution[$i]); $j++){
                    if($solution[$i][$j] == "-"){
                        $multipleOptions = false;
                        $secpos = null;
                        $numpos = null;
                        $input = null;
                        for($num = 1; $num <= 9; $num++){
                            if(!$this->sectorHasDuplicate($solution[$i], $j, $num) && !$this->RowHasDuplicate($solution, $i, $j, $num) && !$this->colHasDuplicate($solution, $i, $j, $num)){
                                if($secpos === null){
                                    $secpos = $i;
                                    $numpos = $j;
                                    $input = $num;
                                }
                                else{
                                    $multipleOptions = true;
                                    break;
                                }
                            }
                        }
                        if(!$multipleOptions){
                            $nosolution = false;
                            $solution[$secpos][$numpos] = (string)$input; 
                            $filledInputs++;
                        }
                    }
                }
            }
        }
        if($nosolution){
            return false;
        }
        else{
            return $solution;
        }
    }
}
?>