<?php

namespace App\CustomClasses\SudokuClasses;

class SudokuStructure
{
    private SudokuType $type;
    private SudokuModus $modus;
    public array $sudokuBoard;
    private array $moves;
    private int $preFilledAmmount;
    private int $filledAmmount;
    private int $totalBoardSize;
    private bool $solvable;
    private int $cycles;
    private int $strategyIndex;
    private bool $currentStrategySucces;
    public AreaType $currentAreaType;

    public function __construct(SudokuType $type, SudokuModus $modus)
    {
        $this->type = $type;
        $this->modus = $modus;
    }

    public function getType(): SudokuType
    {
        return $this->type;
    }

    public function getModus(): SudokuModus
    {
        return $this->modus;
    }

    private function initializeBoard($sudoku)
    { //turn the sudokuarray into our custom objects
        $this->sudokuBoard = $sudoku;
        for ($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++) {
            for ($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++) {
                for ($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++) {
                    for ($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++) {
                        $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol] = new SudokuSquare($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol], $sudokuRow, $sudokuCol, $sectorRow, $sectorCol);
                    }
                }
            }
        }
    }

    private function createReturnArray(): array
    {
        $solution = $this->sudokuBoard;
        for ($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++) {
            for ($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++) {
                for ($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++) {
                    for ($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++) {
                        if (is_array($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol]->value)) {
                            $solution[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol] = "-";
                        } else {
                            $solution[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol] = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol]->value;
                        }
                    }
                }
            }
        }
        return $solution;
    }

    private function validSudokuInput(): bool
    { //checks whether the sudoku input is valid
        $this->preFilledAmmount = 0;
        $this->totalBoardSize = 0;
        for ($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++) {
            for ($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++) {
                if ($this->hasDuplicate($this->loopSector($sudokuRow, $sudokuCol))) {
                    return false;
                }
                for ($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++) {
                    if ($this->hasDuplicate($this->loopRow($sudokuRow, $sectorRow))) {
                        return false;
                    }
                    for ($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++) {
                        if ($this->hasDuplicate($this->loopCol($sudokuCol, $sectorCol))) {
                            return false;
                        }
                        $num = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
                        $this->totalBoardSize++;
                        if ($num->value == "-") {
                            $num->value = [1, 2, 3, 4, 5, 6, 7, 8, 9];
                        } else {
                            $this->preFilledAmmount++;
                        }
                    }
                }
            }
        }
        if ($this->preFilledAmmount < 17) {
            return false;
        }
        return true;
    }

    public function loopSector($sudokuRow, $sudokuCol): array
    { //returns a list of references to values in a sector of the board
        $area = [];
        for ($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++) {
            for ($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++) {
                $area[] = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
            }
        }
        return $area;
    }

    public function loopRow($sudokuRow, $sectorRow)
    { //returns a list of references to values in a row of the board
        $area = [];
        for ($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++) {
            for ($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++) {
                $area[] = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
            }
        }
        return $area;
    }

    public function loopCol($sudokuCol, $sectorCol)
    { //returns a list of references to values in a collumn of the board
        $area = [];
        for ($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++) {
            for ($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++) {
                $area[] = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
            }
        }
        return $area;
    }

    private function loopStrategies($area)
    { //loops over strategies, goes back to the first each time a strategies has succes, a puzzle is unsolvable if none of the strategies return succesfully
        $strategies = ['findSingles', 'findHiddenSingles', 'findPairs', 'findPointingPairs', 'findXWing', 'findYWing'];
        if ($this->strategyIndex >= count($strategies)) {
            $this->solvable = false;
            return;
        }
        $strat = $strategies[$this->strategyIndex];
        if ($this->$strat($area)) {
            $this->currentStrategySucces = true;
            $this->strategyIndex = 0;
        }
    }

    private function hasDuplicate(array $area): bool
    { //checks if there are multiple of the same number in the area
        for ($num = 1; $num < 10; $num++) {
            $found = false;
            foreach ($area as $square) {
                if ($square->value != "-" && $square->value == $num) {
                    if ($found) {
                        return true;
                    } else {
                        $found = true;
                    }
                }
            }
        }
        return false;
    }

    private function removeOptionFromArea(array $area, int $val, array $exclusions = []): bool
    { //removes num from the options in the area
        $succes = false;
        foreach ($area as $square) {
            if (is_array($square->value)) {
                foreach ($exclusions as $exc) {
                    if ($square->position === $exc->position) {
                        continue 2;
                    }
                }
                $index = array_search($val, $square->value);
                if ($index !== false) {
                    unset($square->value[$index]);
                    $square->value = array_values($square->value);
                    $succes = true;
                }
            }
        }
        return $succes;
    }

    private function removeOptionFromAll(SudokuSquare $square)
    { //removes the square's value from all squares that share the same sector, row or collumn
        $sector = $this->loopSector($square->position->sudRow, $square->position->sudCol);
        $row = $this->loopRow($square->position->sudRow, $square->position->secRow);
        $col = $this->loopCol($square->position->sudCol, $square->position->secCol);
        $succes = false;
        if ($this->removeOptionFromArea($sector, $square->value)) {
            $succes = true;
        }
        if ($this->removeOptionFromArea($row, $square->value)) {
            $succes = true;
        }
        if ($this->removeOptionFromArea($col, $square->value)) {
            $succes = true;
        }
        return $succes;
    }

    private function findSingles(array $area): bool
    { //if a spot has only one option left we fill it in
        foreach ($area as $square) {
            if (is_array($square->value) && count($square->value) == 1) {
                $this->filledAmmount++;
                $square->value = $square->value[0];
                $this->addMove("Naked Single", [$square->value], [$square]);
                $this->removeOptionFromAll($square);
                return true;
            }
        }
        return false;
    }

    public function findNumInArea($area, $num): array
    { //return the squares that contain a given number in an area
        $foundArray = [];
        foreach ($area as $square) {
            if (is_array($square->value) && in_array($num, $square->value)) {
                $foundArray[] = $square;
            }
        }
        return $foundArray;
    }

    private function findHiddenSingles(array $area): bool
    { //counts how often a number is found in an area
        for ($num = 1; $num < 10; $num++) {
            $foundArray = $this->findNumInArea($area, $num);

            if (count($foundArray) == 1) { //hidden single can be filled in
                $square = $foundArray[0];
                $this->filledAmmount++;
                $square->value = $num;
                $this->addMove("Hidden Single", [$square->value], [$square]);
                $this->removeOptionFromAll($square);
                return true;
            }
        }
        return false;
    }

    function findPointingPairs(array $area): bool
    {
        for ($num = 1; $num < 10; $num++) {
            $foundArray = $this->findNumInArea($area, $num);

            if (count($foundArray) == 2 || count($foundArray) == 3) { //looking for pointing pairs in a sector
                $sharedPosition = $this->comparePositions($foundArray);
                if ($sharedPosition) {
                    if ($this->currentAreaType === AreaType::SECTOR) { //if the pointing pair is found in a sector, remove the pair from all other sectors in that row or column
                        if (in_array(AreaType::ROW, $sharedPosition->types) || in_array(AreaType::COLUMN, $sharedPosition->types)) {
                            if (in_array(AreaType::ROW, $sharedPosition->types)) {
                                $removeArea = $this->loopRow($sharedPosition->position->sudRow, $sharedPosition->position->secRow);
                            } else {
                                $removeArea = $this->loopCol($sharedPosition->position->sudCol, $sharedPosition->position->secCol);
                            }

                            if ($this->removeOptionFromArea($removeArea, $num, $foundArray)) {
                                $this->addMove("Pointing Pair", [$num], $foundArray);
                                return true;
                            }
                        }
                    } else { //if the pointing pair is found in a sector and its the only places for that number in that row or column, remove the num from other squares in the sector
                        if (in_array(AreaType::SECTOR, $sharedPosition->types)) {
                            $sector = $this->loopSector($sharedPosition->position->sudRow, $sharedPosition->position->sudCol);
                            if ($this->removeOptionFromArea($sector, $num, $foundArray)) {
                                $this->addMove("Box Line Reduction", [$num], $foundArray);
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    function findAllCombinations($numbers, $combinationSize): false | array
    { //returns all combinations of a given size with given numbers 
        $totalNumbers = count($numbers);
        if ($totalNumbers < 2) {
            return false; // Not enough elements for combinations
        }
        if ($totalNumbers < $combinationSize) {
            return false; // Skip if not enough elements
        }
        $combinations = $this->generateCombinations($numbers, $combinationSize);
        return $combinations;
    }

    function generateCombinations($numbers, $combinationSize): array
    { //recursive function for finding combinations
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

    private function findPairs($area): bool
    { //finds different sizes of pairs of numbers either naked or hidden
        $numbers = [];
        foreach ($area as $square) {
            if (is_array($square->value) && !in_array($this->currentAreaType, $square->areaExlusionForPairFinding)) {
                $numbers[] = $square->value;
            }
        }
        $mergedNumbers = call_user_func_array("array_merge", $numbers);
        $uniqueNumbers = array_unique($mergedNumbers);
        sort($uniqueNumbers, SORT_REGULAR);
        for ($pairSize = 2; $pairSize < 5; $pairSize++) {
            $type = "";
            switch ($pairSize) {
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
            if ($pairs) {
                foreach ($pairs as $pair) {
                    $validSquares = [];
                    foreach ($area as $square) {
                        if (is_array($square->value)) {
                            $sameValues = array_intersect($pair, $square->value);
                            if (count($sameValues) >= 1) {
                                $validSquares[] = $square;
                            }
                        }
                    }
                    $validCount = count($validSquares);
                    if ($validCount > 1) {
                        $nakedPair = [];
                        foreach ($validSquares as $square) { //finds the squares that have the exact same numbers as the pair without any other numbers
                            $diff = array_diff($square->value, $pair);
                            if (count($diff) == 0) {
                                $nakedPair[] = $square;
                            }
                        }
                        if (count($nakedPair) == $pairSize) { //is naked
                            $removed = false;
                            foreach ($pair as $num) {
                                if ($this->removeOptionFromArea($area, $num, $nakedPair)) {
                                    $removed = true;
                                }
                            }
                            if ($removed) {
                                foreach ($nakedPair as $np) {
                                    $np->areaExlusionForPairFinding[] = $this->currentAreaType;
                                }
                                $this->addMove("Naked {$type}", $pair, $nakedPair);
                                return true;
                            }
                        } else if ($validCount == $pairSize) { //is hidden
                            $NumbersWithoutPair = array_diff([1, 2, 3, 4, 5, 6, 7, 8, 9], $pair);
                            $removed = false;
                            foreach ($NumbersWithoutPair as $num) {
                                if ($this->removeOptionFromArea($validSquares, $num)) {
                                    $removed = true;
                                }
                            }
                            if ($removed) {
                                foreach ($validSquares as $vs) {
                                    $vs->areaExlusionForPairFinding[] = $this->currentAreaType;
                                }
                                $this->addMove("Hidden {$type}", $pair, $validSquares);
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    private function findXWing(array $area): bool
    {
        if ($this->currentAreaType == AreaType::SECTOR) {
            return false;
        }
        $Xwing = XWingFinder::findWings($this);
        if ($Xwing) {
            $this->removeOptionFromArea($Xwing->affectedSquares, $Xwing->num);
            $this->addMove($Xwing->type, [$Xwing->num], $Xwing->affectedSquares);
            return true;
        }
        return false;
    }

    private function findYWing(array $area): bool
    { //finds 3 squares with 3 unique numbers, each containing a unique pair of 2 numbers
        foreach ($area as $square) {
            if (is_array($square->value) && count($square->value) == 2) {
                $hinges = HingeFinder::FindHinges($square, $this);
                if ($hinges) {
                    $removed = false;
                    foreach ($hinges as $hinge) {
                        foreach ($hinge->affectedSquares as $afs) {
                            $index = array_search($hinge->z, $afs->value);
                            if ($index !== false) {
                                $removed = true;
                                unset($afs->value[$index]);
                                $afs->value = array_values($afs->value);
                            }
                        }
                        if ($removed) {
                            $this->addMove("Y-Wing", [$hinge->z], $hinge->affectedSquares);
                        }
                    }
                    if ($removed) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function addMove(string $type, array $values, array $squares)
    {
        $positionString = "";
        foreach ($squares as $square) {
            $str = "[" . $square->position->sudRow . "][" . $square->position->sudCol . "][" . $square->position->secRow . "][" . $square->position->secCol . "] ";
            $positionString .= $str;
        }

        $this->moves[] = $type . " [" . implode(',', $values) . "] in " . $this->currentAreaType->value . " at " . $positionString;
    }

    private function comparePositions($squareArray): null|SharedPosition
    {
        $sudRow = null;
        $secRow = null;
        $sudCol = null;
        $secCol = null;
        $sameRow = true;
        $sameCol = true;
        $sameSec = true;
        $sharedPosition = null;

        foreach ($squareArray as $square) {
            if ($sudRow === null) {
                $sudRow = $square->position->sudRow;
                $secRow = $square->position->secRow;
            } else if ($square->position->sudRow !== $sudRow || $square->position->secRow !== $secRow) {
                $sameRow = false;
            }
            if ($sudCol === null) {
                $sudCol = $square->position->sudCol;
                $secCol = $square->position->secCol;
            } else if ($square->position->sudCol !== $sudCol || $square->position->secCol !== $secCol) {
                $sameCol = false;
            }
            if ($square->position->sudRow !== $sudRow || $square->position->sudCol !== $sudCol) {
                $sameSec = false;
            }
        }
        $sameAreas = null;
        if ($sameSec) {
            $sameAreas[] = AreaType::SECTOR;
        }
        if ($sameRow) {
            $sameAreas[] = AreaType::ROW;
        } else if ($sameCol) {
            $sameAreas[] = AreaType::COLUMN;
        }
        if ($sameAreas != null) {
            $sharedPosition = new SharedPosition($sameAreas, new SudokuPosition($sudRow, $sudCol, $secRow, $secCol));
        }
        return $sharedPosition;
    }

    public function solve(array $sudoku): array | false
    {
        $this->initializeBoard($sudoku);
        if (!$this->validSudokuInput()) {
            return false;
        }
        $this->strategyIndex = 0;
        $this->filledAmmount = $this->preFilledAmmount;
        $this->cycles = 0;
        $this->moves = [];
        $this->solvable = true;
        while ($this->filledAmmount !== $this->totalBoardSize && $this->solvable) {
            $this->currentStrategySucces = false;
            for ($sudokuRow = 0; $sudokuRow < count($this->sudokuBoard); $sudokuRow++) {
                for ($sudokuCol = 0; $sudokuCol < count($this->sudokuBoard[$sudokuRow]); $sudokuCol++) {
                    if ($this->cycles > 0) {
                        //all sector searches
                        $this->currentAreaType = AreaType::SECTOR;
                        $sector = $this->loopSector($sudokuRow, $sudokuCol);
                        $this->loopStrategies($sector);
                    }
                    for ($sectorRow = 0; $sectorRow < count($this->sudokuBoard[$sudokuRow][$sudokuCol]); $sectorRow++) {
                        if ($this->cycles > 0) {
                            //all row searches
                            $this->currentAreaType = AreaType::ROW;
                            $row = $this->loopRow($sudokuRow, $sectorRow);
                            $this->loopStrategies($row);
                        }
                        for ($sectorCol = 0; $sectorCol < count($this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow]); $sectorCol++) {
                            if ($this->cycles > 0) {
                                //all col searches
                                $this->currentAreaType = AreaType::COLUMN;
                                $col = $this->loopCol($sudokuCol, $sectorCol);
                                $this->loopStrategies($col);
                            }
                            if ($this->cycles == 0) { //removes the prefilled numbers from the surrounding options
                                $square = $this->sudokuBoard[$sudokuRow][$sudokuCol][$sectorRow][$sectorCol];
                                if (!is_array($square->value)) {
                                    $this->removeOptionFromAll($square);
                                }
                            }
                        }
                    }
                }
            }
            if (!$this->currentStrategySucces && $this->cycles > 0) {
                $this->strategyIndex++;
            }
            $this->cycles++;
        }
        if (!$this->solvable) {
            return [false, $this->createReturnArray(), $this->moves, $this->cycles];
        } else {
            return [true, $this->createReturnArray(), $this->moves, $this->cycles];
        }
    }
}
