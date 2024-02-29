<?php

namespace App\CustomClasses;

class SudokuStructure{
    private SudokuType $type;
    private SudokuModus $modus;
    private array $sudokuBoard;
    private array $moves;
    private int $preFilledAmmount;
    private int $filledAmmount;
    private int $totalBoardSize;
    private bool $solvable;
    private int $cycles;
    private int $strategyIndex;
    private bool $currentStrategySucces;
    private AreaType $currentAreaType;
    
    public function __construct(SudokuType $type, SudokuModus $modus) {
        $this->type = $type;
        $this->modus = $modus;
    }
    
    public function getType():SudokuType{
        return $this->type;
    }
    
    public function getModus():SudokuModus{
        return $this->modus;
    } 

    private function initializeBoard($sudoku){//turn the sudokuarray into our custom objects
        $this->sudokuBoard = $sudoku;
        for($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++){
            for($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++){
                for($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++){
                    for($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                        $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol] = new SudokuSquare($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol], $sudokuRow, $sudokuCol, $sectorRow, $sectorCol);
                    }
                }
            }
        }
    }

    private function createReturnArray(): array{
        $solution = $this->sudokuBoard;
        for($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++){
            for($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++){
                for($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++){
                    for($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                        if(is_array($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol]->value)){
                            $solution[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol] = "-";
                        }
                        else{
                            $solution[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol] = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol]->value;
                        }
                    }
                }
            }
        }
        return $solution;
    }

    private function validSudokuInput() : bool{//checks whether the sudoku input is valid
        $this->preFilledAmmount = 0;
        $this->totalBoardSize = 0;
        for($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++){
            for($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++){
                if($this->hasDuplicate($this->loopSector($sudokuRow, $sudokuCol))){
                    return false;
                }
                for($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++){
                    if($this->hasDuplicate($this->loopRow($sudokuRow, $sectorRow))){
                        return false;
                    }
                    for($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                        if($this->hasDuplicate($this->loopCol($sudokuCol, $sectorCol))){
                            return false;
                        }
                        $num = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
                        $this->totalBoardSize++;
                        if ($num->value == "-"){
                            $num->value = [1,2,3,4,5,6,7,8,9];
                        }
                        else{
                            $this->preFilledAmmount++;
                        }
                    }
                }
            }
        }
        if($this->preFilledAmmount < 17){
            return false;
        }
        return true;
    }

    private function loopSector($sudokuRow, $sudokuCol) : array{//returns a list of references to values in a sector of the board
        $area = [];
        for($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++){
            for($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                $area[] = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
            }
        }
        return $area;
    }

    private function loopRow($sudokuRow, $sectorRow){//returns a list of references to values in a row of the board
        $area = [];
        for($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++){
            for($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                $area[] = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
            }
        }
        return $area;
    }

    private function loopCol($sudokuCol, $sectorCol){//returns a list of references to values in a collumn of the board
        $area = [];
        for($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++){
            for($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++){
                $area[] = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
            }
        }
        return $area;
    }

    private function loopStrategies($area){//goes over all strategies in a given area
        $strategies = ['findSingles', 'findHiddenSinglesAndPointingPairs','findPairs'];
        if($this->strategyIndex >= count($strategies)){
            $this->solvable = false;
            return;
        }
        else if($this->strategyIndex < 0){
            $this->strategyIndex = 0;
        }
        $strat = $strategies[$this->strategyIndex];
        if($this->$strat($area)){
            $this->currentStrategySucces = true;
            $this->strategyIndex = 0;
        }
    }

    private function hasDuplicate(array $area): bool{//checks if there are multiple of the same number in the area
        for($num = 1; $num < 10; $num++){
            $found = false;
            foreach($area as $square){
                if($square->value != "-" && $square->value == $num){
                    if($found){
                        return true;
                    }
                    else{
                        $found = true;
                    }
                }
            }
        }
        return false;
    }

    private function removeOptionFromArea(array $area, int $val, array $exclusions = []){//removes num from the options in the area
        $succes = false;
        foreach($area as $square){
            if(is_array($square->value)){
                foreach($exclusions as $exc){
                    if($square->position === $exc->position){
                        continue 2;
                    }
                }
                $index = array_search($val, $square->value);
                if($index !== false){
                    unset($square->value[$index]);
                    $square->value = array_values($square->value);
                    $succes = true;
                }
            }
        }
        return $succes;
    }

    private function removeOptionFromAll(SudokuSquare $square){//removes the square's value from all squares that share the same sector, row or collumn
        $sector = $this->loopSector($square->position->sudRow, $square->position->sudCol);
        $row = $this->loopRow($square->position->sudRow, $square->position->secRow);
        $col = $this->loopCol($square->position->sudCol, $square->position->secCol);
        $this->removeOptionFromArea($sector, $square->value);
        $this->removeOptionFromArea($row, $square->value);
        $this->removeOptionFromArea($col, $square->value);
    }

    private function findSingles(array $area){//if a spot has only one option left we fill it in
        foreach($area as $square){
            if(is_array($square->value) && count($square->value) == 1){
                $this->filledAmmount++;
                $square->value = $square->value[0];
                $this->moves[] = "Naked Single (".$square->value.") in ". $this->currentAreaType->value ." at "."[".$square->position->sudRow."][".$square->position->sudCol."][".$square->position->secRow."][".$square->position->secCol."]";
                $this->removeOptionFromAll($square);
                return true;
            }
        }
        return false;
    }

    private function findNumInArea($area, $num): array{
        $foundArray = [];
        foreach($area as $square){
            if(is_array($square->value) && in_array($num, $square->value)){
                $foundArray[] = $square;
            }
        }
        return $foundArray;
    }

    private function findHiddenSinglesAndPointingPairs(array $area){//counts how often a number is found in an area
        for($num = 1; $num < 10; $num++){
            $foundArray = $this->findNumInArea($area, $num);
            
            if(count($foundArray) == 1){//hidden single can be filled in
                $square = $foundArray[0];
                $this->filledAmmount++;
                $square->value = $num;
                $this->moves[] = "Hidden Single (".$square->value.") in ". $this->currentAreaType->value ." at "."[".$square->position->sudRow."][".$square->position->sudCol."][".$square->position->secRow."][".$square->position->secCol."]";
                $this->removeOptionFromAll($square);
                return true;
            }
            else if(count($foundArray) == 2 || count($foundArray) == 3){//looking for pointing pairs in a sector
                $sharedPosition = $this->comparePositions($foundArray);
                if($sharedPosition){
                    if($this->currentAreaType === AreaType::SECTOR){//if the pointing pair is found in a sector, remove the pair from all other sectors in that row or column
                        if(in_array(AreaType::ROW, $sharedPosition->types)){
                            $row = $this->loopRow($sharedPosition->position->sudRow, $sharedPosition->position->secRow);
                            if($this->removeOptionFromArea($row, $num, $foundArray)){
                                $this->moves[] = "Pointing Pair (".$num.") in Row [".$row[0]->position->sudRow."][".$row[0]->position->sudCol."][".$row[0]->position->secRow."][".$row[0]->position->secCol."]";
                                return true;
                            }
                        }
                        else if(in_array(AreaType::COLUMN, $sharedPosition->types)){
                            $col = $this->loopCol($sharedPosition->position->sudCol, $sharedPosition->position->secCol);
                            if($this->removeOptionFromArea($col, $num, $foundArray)){
                                $this->moves[] = "Pointing Pair (".$num.") in Column [".$col[0]->position->sudRow."][".$col[0]->position->sudCol."][".$col[0]->position->secRow."][".$col[0]->position->secCol."]";
                                return true;
                            }
                        }
                    }
                    else if(count($foundArray) == 2){//possible x-wing
                        $this->findXWing($foundArray, $num);
                    }
                }

            }
        }
        return false;
    }

    function findAllCombinations($numbers, $combinationSize) {        
        $totalNumbers = count($numbers);
        if ($totalNumbers < 2) {
            return false; // Not enough elements for combinations
        }
        // Generate combinations for pairs (2), triples (3), and quadruples (4)
        if ($totalNumbers < $combinationSize){
            return false; // Skip if not enough elements
        } 
        $combinations = $this->generateCombinations($numbers, $combinationSize);
        return $combinations;
    }
    
    function generateCombinations($numbers, $combinationSize) {
        $combinations = [];
        if ($combinationSize == 0) {
            return [[]];
        }
    
        for ($i = 0; $i <= count($numbers) - $combinationSize; $i++) {
            // Generate combinations of the remaining elements
            $tailCombinations = $this->generateCombinations(array_slice($numbers, $i + 1), $combinationSize - 1);
            foreach ($tailCombinations as $combination) {
                array_unshift($combination, $numbers[$i]);
                $combinations[] = $combination;
            }
        }
    
        return $combinations;
    }

    private function findPairs($area){
        $numbers = [];
        foreach($area as $square){
            if(is_array($square->value) && !in_array($this->currentAreaType, $square->areaExlusionForPairFinding)){
                $numbers[] = $square->value;
            }
        }
        $mergedNumbers = call_user_func_array("array_merge", $numbers);
        $uniqueNumbers = array_unique($mergedNumbers);
        sort($uniqueNumbers,SORT_REGULAR);
        for($pairSize = 2; $pairSize < 5; $pairSize++){
            $type = "";
            switch($pairSize){
                case 2:
                    $type = "Pair";
                    break;
                case 3:
                    $type = "Triple";
                    break;
                case 4:
                    $type = "Quad";
                    break;
            }
            $pairs = $this->findAllCombinations($uniqueNumbers, $pairSize);
            if($pairs){
                foreach($pairs as $pair){
                    $validSquares = [];
                    foreach($area as $square){
                        if(is_array($square->value)){
                            $sameValues = array_intersect($pair, $square->value);
                            if(count($sameValues) >= 1){
                                $validSquares[] = $square;
                            }
                        }
                    }
                    $validCount = count($validSquares);
                    if($validCount > 1){
                        $nakedPair = [];
                        foreach($validSquares as $square){
                            $diff = array_diff($square->value, $pair);
                            if(count($diff) == 0){//could be hidden
                                $nakedPair[] = $square;
                            }
                        }
                        if(count($nakedPair) == $pairSize){//is naked
                            foreach($pair as $num){
                                $this->removeOptionFromArea($area, $num, $nakedPair);
                            }
                            foreach($nakedPair as $np){
                                $np->areaExlusionForPairFinding[] = $this->currentAreaType;
                            }
                            $this->addMove("Naked {$type}", $pair, $nakedPair);
                            return true;
                        }
                        else if($validCount == 2){//is hidden
                            $NumbersWithoutPair = array_diff([1,2,3,4,5,6,7,8,9], $pair);
                            foreach($NumbersWithoutPair as $num){
                                $this->removeOptionFromArea($validSquares, $num);
                            }
                            foreach($validSquares as $vs){
                                $vs->areaExlusionForPairFinding[] =$this->currentAreaType;
                            }
                            $this->addMove("Hidden {$type}", $pair, $validSquares);
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    private function findXWing(array $pair, $num){
        if($this->currentAreaType == AreaType::ROW){
            for($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++){
                $validSquares = [];
                for($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][0]); $sectorRow++){
                    if($sudokuRow != $pair[0]->position->sudRow || $sectorRow != $pair[0]->position->secRow){
                        $rowArea = $this->loopRow($sudokuRow, $sectorRow);
                        $foundSquares = $this->findNumInArea($rowArea, $num);
                        if(count($foundSquares) == 2){
                            $valid = true;
                            for($i = 0; $i < count($foundSquares); $i++){
                                if($foundSquares[$i]->position->sudCol != $pair[$i]->position->sudCol || $foundSquares[$i]->position->secCol != $pair[$i]->position->secCol){
                                    $valid = false;
                                }
                            }
                            if($valid){
                                if($validSquares){
                                    return false;
                                }
                                $validSquares = $foundSquares;
                            }
                        }
                    }
                }
            }
            if($validSquares){
                for($i = 0; $i < count($pair); $i++){
                    $col = $this->loopCol($pair[$i]->position->sudCol, $pair[$i]->position->secCol);
                    $pair[$i]->areaExlusionForPairFinding[] = AreaType::XWING;
                    $this->removeOptionFromArea($col, $num, [$pair[$i], $validSquares[$i]]);  
                }
                $this->addMove("X-Wing", $num, [$pair[0], $pair[1], $validSquares[0], $validSquares[1]]);
                return true;
            }
        }
        else if($this->currentAreaType == AreaType::COLUMN){
            for($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[0]); $sudokuCol++){
                $validSquares = [];
                for($sectorCol = 0; $sectorCol < count($this->sudokuBoard[0][$sudokuCol][0]); $sectorCol++){
                    if($sudokuCol != $pair[0]->position->sudCol || $sectorCol != $pair[0]->position->secCol){
                        $colArea = $this->loopCol($sudokuCol, $sectorCol);
                        $foundSquares = $this->findNumInArea($colArea, $num);
                        if(count($foundSquares) == 2){
                            $valid = true;
                            for($i = 0; $i < count($foundSquares); $i++){
                                if($foundSquares[$i]->position->sudRow != $pair[$i]->position->sudRow || $foundSquares[$i]->position->secRow != $pair[$i]->position->secRow){
                                    $valid = false;
                                }
                            }
                            if($valid){
                                if($validSquares){
                                    return false;
                                }
                                $validSquares = $foundSquares;
                            }
                        }
                    }
                }
            }
            if($validSquares){
                for($i = 0; $i < count($pair); $i++){
                    $row = $this->loopRow($pair[$i]->position->sudRow, $pair[$i]->position->secRow);
                    $pair[$i]->areaExlusionForPairFinding[] = AreaType::XWING;
                    $this->removeOptionFromArea($row, $num, [$pair[$i], $validSquares[$i]]);  
                }
                $this->addMove("X-Wing", $num, [$pair[0], $pair[1], $validSquares[0], $validSquares[1]]);
                return true;
            }
        }
        return false;
    }

    private function findYWing(array $pair, array $uniqueNumbers){
        
    }

    private function addMove(string $type, array $values, array $squares){
        $positionString = "";
        foreach($squares as $square){
            $str = "[".$square->position->sudRow."][".$square->position->sudCol."][".$square->position->secRow."][".$square->position->secCol."] ";
            $positionString .= $str;
        }

        $this->moves[] = $type." [".implode(',',$values)."] in ".$this->currentAreaType->value." at ".$positionString;
    }

    private function comparePositions($squareArray) : null|SharedPosition{
        $sudRow = null;
        $secRow = null;
        $sudCol = null;
        $secCol = null;
        $sameRow = true;
        $sameCol = true;
        $sameSec = true;
        $sharedPosition = null;

        foreach($squareArray as $square){
            if($sudRow === null){
                $sudRow = $square->position->sudRow;
                $secRow = $square->position->secRow;
            }
            else if($square->position->sudRow !== $sudRow || $square->position->secRow !== $secRow){
                $sameRow = false;
            }
            if($sudCol === null){
                $sudCol = $square->position->sudCol;
                $secCol = $square->position->secCol;
            }
            else if($square->position->sudCol !== $sudCol || $square->position->secCol !== $secCol){
                $sameCol = false;
            }
            if($square->position->sudRow !== $sudRow || $square->position->sudCol !== $sudCol){
                $sameSec = false;
            }
        }
        $sameAreas = null;
        if($sameSec){
            $sameAreas[] = AreaType::SECTOR;
        }
        if($sameRow){
            $sameAreas[] = AreaType::ROW;
        }
        else if($sameCol){
            $sameAreas[] = AreaType::COLUMN;
        }
        if($sameAreas != null){
            $sharedPosition = new SharedPosition($sameAreas, new SudokuPosition($sudRow, $sudCol, $secRow, $secCol));
        }
        return $sharedPosition;
    }

    public function solve(array $sudoku): array | false{
        $this->initializeBoard($sudoku);
        if(!$this->validSudokuInput()){
            return false;
        }
        $this->strategyIndex = 0;
        $this->filledAmmount = $this->preFilledAmmount;
        $this->cycles = 0;
        $this->moves = [];
        $this->solvable = true;
        while($this->filledAmmount !== $this->totalBoardSize && $this->solvable){
            $this->currentStrategySucces = false;
            for($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++){
                for($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++){
                    if($this->cycles > 0){
                        //alle sector searches
                        $this->currentAreaType = AreaType::SECTOR;
                        $sector = $this->loopSector($sudokuRow, $sudokuCol);
                        $this->loopStrategies($sector);  
                    }
                    for($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++){
                        if($this->cycles > 0){
                            //alle row searches
                            $this->currentAreaType = AreaType::ROW;
                            $row = $this->loopRow($sudokuRow, $sectorRow);
                            $this->loopStrategies($row);
                        }
                        for($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++){
                            if($this->cycles > 0){
                                //alle col searches
                                $this->currentAreaType = AreaType::COLUMN;
                                $col = $this->loopCol($sudokuCol, $sectorCol);
                                $this->loopStrategies($col);
                            }
                            if($this->cycles == 0){//removes the prefilled numbers from the surrounding options
                                $square = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
                                if(!is_array($square->value)){
                                    $this->removeOptionFromAll($square);
                                }
                            }
                        }
                    }
                }
            }
            if(!$this->currentStrategySucces && $this->cycles > 0){
                $this->strategyIndex++;
            }
            $this->cycles++;
        }
        if(!$this->solvable){
            echo "fail<br>";
            $this->tempdumpy();
            return [false, $this->createReturnArray()];
        }
        else{
            echo "succes<br>";
            $this->tempdumpy();
            return [true, $this->createReturnArray()];
        }
    }

    private function tempdumpy(){
        for($a=0; $a<3; $a++){
            for($b=0;$b<3;$b++){
                print_r($this->sudokuBoard[2][0][$a][$b]->value);
                echo "<br>";
            }
        }
        echo "<br>";
        echo "Moves:<br>";
        foreach($this->moves as $move){
            echo $move."<br>";
        }
        echo "<br>";
        for($i = 0; $i<3; $i++){
            for($j=0; $j <3; $j++){
                for($k=0; $k< 3; $k++){
                    for($l=0; $l<3; $l++){
                        if(!is_array($this->sudokuBoard[$i][$k][$j][$l]->value)){
                            echo $this->sudokuBoard[$i][$k][$j][$l]->value;
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
        dd($this->sudokuBoard);
        die();
    }
}