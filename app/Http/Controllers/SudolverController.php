<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomClasses\SudokuModus;
use App\CustomClasses\SudokuType;

class SudolverController extends Controller
{
    private function enumToArray($enumClass) {
        return array_map(
            function($case) { return $case->value; },
            $enumClass::cases()
        );
    }

    //checks if the prefilled sudoku is solvable
    private function sudokuInputValidation(array $sudArray) : boolean{
        $preFilledAmmount = 0;
        for($i = 0; $i < count($sudArray); $i++){
            for($j = 0; $j < count($sudArray[$i]); $j++){
                if ($sudArray[$i][$j] != "-"){
                    $preFilledAmmount++;
                    $duplicate = false;
                    foreach($sudArray[$i] as $num){//check if a number appears multiple times in the same sector
                        if($num == $sudArray[$i][$j]){
                            if($duplicate == false){
                                $duplicate = true;
                            }
                            else{
                                return false;
                            }
                        }
                    }
                    $duplicate = false;
                    for($k = 0; $k < count($sudArray); $k++){//check if a number appears multiple times in the same row
                        
                    }

                    $duplicate = false;
                    for($l = 0; $l < count($sudArray); $k++){//check if a number appears multiple times in the same collumn

                    }
                }
            }
        }
    }

    private function sudokuSolver(array $sudArray) : array | false{
        $solution = [];
        while($solution == [] && $solution != false){
            for($i = 0; $i < count($sudArray); $i++){
                
            }
        }
        return $solution;
    }
    
    public function pageload(){
        $typeArray = $this->enumToArray(SudokuType::class);
        $modusArray = $this->enumToArray(SudokuModus::class);
        return view("sudolverPage",["typeArray"=>$typeArray, "modusArray"=>$modusArray]);
    }

    public function solve(Request $request){
        $sudokuArray = $request->get("input");
        $error = "";
        if($sudokuArray){
            if($this->sudokuInputValidation($sudokuArray)){
                $this->sudokuSolver($sudokuArray);
            }
            else{
                $error = "This sudoku is invalid. Make sure you fill in atleast 17 numbers, with no duplicates in rows columns and squares.";
            }
        }
        else{
            $error = "Something went wrong.";
        }
        return redirect()->back()->withErrors(["solveError"=>$error]);
    }
}
?>