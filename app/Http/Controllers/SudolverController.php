<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\CustomClasses\SudokuModus;
use App\CustomClasses\SudokuType;
use App\CustomClasses\SudokuStructure;

class SudolverController extends Controller
{
    public function pageload(){
        $typeArray = $this->enumToArray(SudokuType::class);
        $modusArray = $this->enumToArray(SudokuModus::class);
        $sudokuArray = session()->get("SudokuArray") ?? [];
        return view("sudolverPage",["typeArray"=>$typeArray, "modusArray"=>$modusArray, "sudokuArray"=>$sudokuArray]);
    }

    // public function solve(Request $request){
    //     $sudokuArray = $request->get("input");
    //     $error = "";
    //     if($sudokuArray){
    //         $filledInputsAmmount = $this->validSudokuInput($sudokuArray);
    //         if(!$filledInputsAmmount){
    //             $error = "This sudoku is invalid. Make sure you fill in atleast 17 numbers, with no duplicates in rows columns and squares.";
    //         }
    //         else{
    //             $solution = $this->sudokuSolver($sudokuArray, $filledInputsAmmount[0], $filledInputsAmmount[1]);
    //             if(!$solution[0]){
    //                 $error = "This sudoku is unsolvable.";
    //             }
    //             else{
    //                 $error = "Succes! :)";
    //             }
    //             $sudokuArray = $solution[1];
    //         }
    //     }
    //     else{
    //         $error = "Something went wrong.";
    //     }
    //     return redirect()->Route("sudolver")->withErrors(["solveError"=>$error])->with(["SudokuArray"=>$sudokuArray]);
    // }

    public function solve(Request $request){
        $type = SudokuType::from($request->get("sudokuType"));
        $modus = SudokuModus::from($request->get("sudokuModus"));
        $sudokuSolver = new SudokuStructure($type, $modus);
        $sudokuArray = $request->get("input");
        $error = "";
        if($sudokuArray){
            $solution = $sudokuSolver->solve($sudokuArray);
            
            if($solution == false){
                $error = "This sudoku is invalid. Make sure you fill in atleast 17 numbers, with no duplicates in rows columns and squares.";
            }
            else{
                if($solution[0] == false){
                    $error = "This sudoku is unsolvable.";
                }
                else{
                    $error = "succes! :)";
                }
                $sudokuArray = $solution[1];
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
        for($sudokuRow = 0; $sudokuRow < count($sudArray); $sudokuRow++){
            for($sudokuCol = 0; $sudokuCol < count($sudArray[$sudokuRow]); $sudokuCol++){
                for($sectorRow = 0; $sectorRow < count($sudArray[$sudokuRow][$sudokuCol]); $sectorRow++){
                    for($sectorCol = 0; $sectorCol < count($sudArray[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                        $total++;
                        $num = $sudArray[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
                        if ($num !== "-"){
                            $preFilledAmmount++;
                            if(count($this->sectorHasDuplicate($sudArray[$sudokuRow][$sudokuCol], $sectorRow, $sectorCol, [$num])) === 0 || count($this->RowHasDuplicate($sudArray, $sudokuRow, $sectorRow, $sudokuCol, $sectorCol, [$num])) === 0 || count($this->colHasDuplicate($sudArray, $sudokuCol, $sectorCol, $sudokuRow, $sectorRow, [$num])) === 0){
                                return false;
                            }
                        }
                    }
                }
            }
        }
        if($preFilledAmmount < 17){//any sudoku with less than 17 characters is always unsolvable
            return false;
        }
        return [$preFilledAmmount, $total];
    }

    private function sectorHasDuplicate(array $sector, int $numRow, int $numCol, array $numOptions): array{
        $newOptions = [];
        foreach($numOptions as $num){//check which numbers already are in the sector
            $duplicate = false;
            for($row = 0; $row < count($sector); $row++){
                for($col = 0; $col < count($sector[$row]); $col++){
                    if($row !== $numRow || $col !== $numCol){
                        if($sector[$row][$col] == $num){
                            $duplicate = true;
                            break 2;
                        }
                    }
                }
            }
            if(!$duplicate){
                array_push($newOptions, $num);
            }
        }
        return $newOptions;
    }

    private function RowHasDuplicate(array $sudArray, int $sudokuRow, int $sectorRow, int $numSudokuCol, int $numSectorCol, array $numOptions){
        $newOptions = [];
        foreach($numOptions as $num){//check which numbers already are in the row
            $duplicate = false;
            for($sudokuCol = 0; $sudokuCol < count($sudArray[$sudokuRow]); $sudokuCol++){
                for($sectorCol = 0; $sectorCol < count($sudArray[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                    $comparedNum = $sudArray[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
                    if($sudokuCol !== $numSudokuCol || $sectorCol !== $numSectorCol){
                        if($comparedNum == $num){
                            $duplicate = true;
                            break 2;
                        }
                    }
                }
            }
            if(!$duplicate){
                array_push($newOptions, $num);
            }
        }
        return $newOptions;
    }

    private function ColHasDuplicate(array $sudArray, int $sudokuCol, int $sectorCol, int $numSudokuRow, int $numSectorRow, array $numOptions){
        $newOptions = [];
        foreach($numOptions as $num){//check check which numbers already are in the collumn
            $duplicate = false;
            for($sudokuRow = 0; $sudokuRow < count($sudArray); $sudokuRow++){
                for($sectorRow = 0; $sectorRow < count($sudArray[$sudokuRow][$sudokuCol]); $sectorRow++){
                    $comparedNum = $sudArray[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
                    if($sudokuRow !== $numSudokuRow || $sectorRow !== $numSectorRow){
                        if($comparedNum == $num){
                            $duplicate = true;
                            break 2;
                        }
                    }
                }
            }
            if(!$duplicate){
                array_push($newOptions, $num);
            }
        }
        return $newOptions;
    }

    private function FindPairs(array &$sudArray, int $sudokuRow, int $sudokuCol, $cycles){//checks if there are options within a sector that all allign on the same axis so we can remove those options from other sectors on that axis
        for($num = 1; $num < 10; $num++){
        if($cycles == 1 && $num == 4 && $sudokuRow == 1 && $sudokuCol == 2){
            //$this->tempdumpy($sudArray);
            $hier = true;
        }
            $FoundCoordinates = [];
            for($sectorRow = 0; $sectorRow < count($sudArray[$sudokuRow][$sudokuCol]); $sectorRow++){
                for($sectorCol = 0; $sectorCol < count($sudArray[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                    $options = &$sudArray[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
                    if(is_array($options) && count($options) > 1 && in_array($num, $options)){
                        array_push($FoundCoordinates, [$sectorRow, $sectorCol]);
                    }
                }
            }
            if(count($FoundCoordinates) <= 3 && count($FoundCoordinates) > 1){
                $row = null;
                $col = null;
                $sameRow = true;
                $sameCol = true;
                foreach($FoundCoordinates as $coord){
                    if($row === null){
                        $row = $coord[0];
                    }
                    else{
                        if($coord[0] != $row){
                            $sameRow = false;
                        }
                    }
                    if($col === null){
                        $col = $coord[1];
                    }
                    else{
                        if($coord[1] != $col){
                            $sameCol = false;
                        }
                    }
                }
                if($sameRow){
                    for($i = 0; $i < count($sudArray[$sudokuRow]); $i++){
                        for($j = 0; $j < count($sudArray[$sudokuRow][$i][$row]); $j++){
                            if($i !== $sudokuCol){
                                $options = &$sudArray[$sudokuRow][$i][$row][$j];
                                if(is_array($options) && count($options) > 1){
                                    $index = array_search($num, $options);
                                    if($index != null){
                                        unset($options[$index]);
                                        $options = array_values($options);
                                        echo "<script>console.log('".$sudokuRow . $sudokuCol . $row . $col . " ". $num . " moveprediction row " . $cycles . "' );</script>";
                                    }
                                }
                            }
                        }
                    }
                }
                else if($sameCol){
                    for($i = 0; $i < count($sudArray); $i++){
                        for($j = 0; $j < count($sudArray[$i][$sudokuCol]); $j++){
                            if($i !== $sudokuRow){
                                $options = &$sudArray[$i][$sudokuCol][$j][$col];
                                if(is_array($options) && count($options) > 1){
                                    $index = array_search($num, $options);
                                    if($index != null){
                                        unset($options[$index]);
                                        $options = array_values($options);
                                        echo "<script>console.log('".$sudokuRow . $sudokuCol . $row . $col . " ". $num . " moveprediction col " . $cycles . "' );</script>";
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    private function findSingles(array &$sudArray, int $indexA, int $indexB, string $searchType, int &$filledInputs, $cycles) {
        $found = false;
        for ($num = 1; $num < 10; $num++) {
            $sudokuRow = null;
            $sudokuCol = null;
            $sectorRow = null;
            $sectorCol = null;
            $countA = null;
            $countB = null;
            $savedIndexA = null;
            $savedIndexB = null;
            $found = false;
            $duplicate = false;
            
            if($searchType === "col"){
                $sudokuCol = $indexA;
                $sectorCol = $indexB;
                $countA = count($sudArray);
            }
            else if($searchType === "row"){
                $sudokuRow = $indexA;
                $sectorRow = $indexB;
                $countA = count($sudArray[$sudokuRow]);
            }
            else{
                $sudokuRow = $indexA;
                $sudokuCol = $indexB;
                $countA = count($sudArray[$sudokuRow][$sudokuCol]);
            }
            
            for ($i = 0; $i < $countA; $i++) {
                $savedIndexA = $i;
                if($searchType === "col"){
                    $countB = count($sudArray[$savedIndexA][$sudokuCol]);
                }
                else if($searchType === "row"){
                    $countB = count($sudArray[$sudokuRow][$savedIndexA][$sectorRow]);
                }
                else{
                    $countB = count($sudArray[$sudokuRow][$sudokuCol][$savedIndexA]);
                }
                for ($j = 0; $j < $countB; $j++) {
                    $savedIndexB = $j;
                    if($searchType === "col"){
                        $options = $sudArray[$savedIndexA][$sudokuCol][$savedIndexB][$sectorCol];
                    }
                    else if($searchType === "row"){
                        
                        $options = $sudArray[$sudokuRow][$savedIndexA][$sectorRow][$savedIndexB];
                    }
                    else{
                        $options = $sudArray[$sudokuRow][$sudokuCol][$savedIndexA][$savedIndexB];
                    }
                    if (is_array($options) && in_array($num, $options)) {
                        if(!$found){
                            $found = true;
                            if($searchType === "col"){
                                $sudokuRow = $savedIndexA;
                                $sectorRow = $savedIndexB;
                            }
                            else if($searchType === "row"){
                                $sudokuCol = $savedIndexA;
                                $sectorCol = $savedIndexB;
                            }
                            else{
                                $sectorRow = $savedIndexA;
                                $sectorCol = $savedIndexB;
                            }
                        }
                        else{
                            $duplicate = true;
                            break 2;
                        }
                    }
                }
            }
            if (!$duplicate && $found && $sudokuRow !== null && $sudokuCol !== null && $sectorRow !== null && $sectorCol !== null) {
                $sudArray[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol] = $num;
                $filledInputs++;
                echo "<script>console.log('".$sudokuRow . $sudokuCol . $sectorRow . $sectorCol . " ". $num . $searchType . " " . $cycles . "' );</script>";
                $this->removeOptionFromSurrounding($sudArray, $sudokuRow, $sudokuCol, $sectorRow, $sectorCol, $num);
                $found = true;
            }
        }
        return $found;
    }

    private function removeOptionFromSurrounding(array &$sudokuArray, int $sudokuRow, int $sudokuCol, int $sectorRow, int $sectorCol, int $num){
        for($sudRow = 0; $sudRow < count($sudokuArray); $sudRow++){
            for($sudCol = 0; $sudCol < count($sudokuArray[$sudRow]); $sudCol++){
                for($secRow = 0; $secRow < count($sudokuArray[$sudRow][$sudCol]); $secRow++){
                    for($secCol = 0; $secCol < count($sudokuArray[$sudRow][$sudCol][$secRow]); $secCol++){
                        $options = &$sudokuArray[$sudRow][$sudCol][$secRow][$secCol];
                        if(is_array($options) && count($options) > 1){
                            if(($sudRow == $sudokuRow && $sudCol == $sudokuCol) || ($sudRow == $sudokuRow && $secRow == $sectorRow) || ($sudCol == $sudokuCol && $secCol == $sectorCol)){
                                $index = array_search($num, $options);
                                if($index !== false){
                                    unset($options[$index]);
                                    $options = array_values($options);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private function sudokuSolver(array $sudArray, int $filledAmmount, $totalAmmount) : array | false{
        $solution = $sudArray;
        $filledInputs = $filledAmmount;
        $nosolution = false;
        $cycles = 0;
        while($filledInputs !== $totalAmmount && !$nosolution && $cycles < 100){
            $nosolution = true;
            for($sudokuRow = 0; $sudokuRow < count($solution); $sudokuRow++){
                for($sudokuCol = 0; $sudokuCol < count($solution[$sudokuRow]); $sudokuCol++){
                    for($sectorRow = 0; $sectorRow < count($solution[$sudokuRow][$sudokuCol]); $sectorRow++){
                        for($sectorCol = 0; $sectorCol < count($solution[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                            $numOptions = &$solution[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
                            if($numOptions == "-"){//an unfilled spot means all options are technically possible
                                $numOptions = [1,2,3,4,5,6,7,8,9];
                            }
                            if(is_array($numOptions)){//if its an array that means there are still multiple options possible and requires further looping to find the correct number.
                                    if($cycles < 1){
                                        $sectorOptions = $this->sectorHasDuplicate($solution[$sudokuRow][$sudokuCol], $sectorRow, $sectorCol, $numOptions);
                                        $rowOptions = $this->RowHasDuplicate($solution, $sudokuRow, $sectorRow, $sudokuCol, $sectorCol, $numOptions);
                                        $colOptions = $this->colHasDuplicate($solution, $sudokuCol, $sectorCol, $sudokuRow, $sectorRow, $numOptions);
                                        $newNumOptions = array_intersect($sectorOptions, $rowOptions, $colOptions);//only keep the options that are possible in all funcions.
                                        $newNumOptions = array_values($newNumOptions);//array_intersect creates an associate array, but we just want an indexed array.
                                        $numOptions = $newNumOptions;
                                        $nosolution = false;
                                    }
                                    if(count($numOptions) === 1){//if only one options remains we can change it to an integer, indicating that its the final value.
                                        $numOptions = $numOptions[0];
                                        $this->removeOptionFromSurrounding($solution, $sudokuRow, $sudokuCol, $sectorRow, $sectorCol, $numOptions);
                                        
                                        echo "<script>console.log('" . $sudokuRow . $sudokuCol . $sectorRow . $sectorCol . " ". $numOptions . " " . $cycles . "' );</script>";
                                        $filledInputs++;
                                        $nosolution = false;
                                    }
                            }
                            if($cycles > 0){
                                $this->findSingles($solution, $sudokuCol, $sectorCol, "col", $filledInputs, $cycles);
                            }
                        }
                        if($cycles > 0){
                            $this->findSingles($solution, $sudokuRow, $sectorRow, "row", $filledInputs, $cycles);
                        }
                    }
                    if($cycles > 0){
                        $this->findSingles($solution, $sudokuRow, $sudokuCol, "sector", $filledInputs, $cycles);
                        $this->FindPairs($solution, $sudokuRow, $sudokuCol, $cycles);
                        // if($cycles == 1 && $sudokuRow == 0 && $sudokuCol == 0){
                        //     $this->tempdumpy($solution);
                        // }
                    }
                }
            }
            $cycles++;
        }
        if($nosolution){
            echo "fail";
            $this->tempdumpy($solution);
            return [false, $solution];
        }
        else{
            echo "succes";
            $this->tempdumpy($solution);
            return [true, $solution];
        }
    }

    private function tempdumpy($solution){
        for($a=0; $a<3; $a++){
            for($b=0;$b<3;$b++){
                print_r($solution[0][1][$a][$b]);
                echo "<br>";
            }
        }
        for($i = 0; $i<3; $i++){
            for($j=0; $j <3; $j++){
                for($k=0; $k< 3; $k++){
                    for($l=0; $l<3; $l++){
                        if(!is_array($solution[$i][$k][$j][$l])){
                            echo $solution[$i][$k][$j][$l];
                        }
                        else{
                            echo "-";
                        }
                    }
                    echo "  ";
                }
                echo "<br>";
            }
            echo "<br>";
        }
        dd($solution);
        die();
    }
}