<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomClasses\SudokuClasses\SudokuModus;
use App\CustomClasses\SudokuClasses\SudokuType;
use App\CustomClasses\SudokuClasses\SudokuStructure;

class SudolverController extends Controller
{
    public function pageload(){
        $typeArray = $this->enumToArray(SudokuType::class);
        $modusArray = $this->enumToArray(SudokuModus::class);
        $sudokuArray = session()->get("SudokuArray") ?? [];
        $moveList = session()->get("moves") ?? [];
        return view("sudolverPage",["typeArray"=>$typeArray, "modusArray"=>$modusArray, "sudokuArray"=>$sudokuArray, "moves"=>$moveList]);
    }

    public function solve(Request $request){
        $type = SudokuType::from($request->get("sudokuType"));
        $modus = SudokuModus::from($request->get("sudokuModus"));
        $sudokuSolver = new SudokuStructure($type, $modus);
        $sudokuArray = $request->get("input");
        $error = "";
        if($sudokuArray){
            $startTime = microtime(true);
            $solution = $sudokuSolver->solve($sudokuArray);
            $solveTime = round(microtime(true) - $startTime, 4);
            
            if($solution == false){
                $error = "This sudoku is invalid. Make sure you fill in atleast 17 numbers, with no duplicates in rows columns and squares.";
            }
            else{
                if($solution[0] == false){
                    $error = "This sudoku is unsolvable.";
                }
                else{
                    $error = "Succesfully solved in ".$solution[3]." cycles in ".$solveTime."seconds.";
                }
                $sudokuArray = $solution[1];
                $moves = $solution[2];
            }
        }
        else{
            $error = "Something went wrong.";
        }
        return redirect()->Route("sudolver")->withErrors(["solveError"=>$error])->with(["SudokuArray"=>$sudokuArray, "moves"=>$moves]);
    }

    private function enumToArray($enumClass): array {
        return array_map(
            function($case) { return $case->value; },
            $enumClass::cases()
        );
    }
}