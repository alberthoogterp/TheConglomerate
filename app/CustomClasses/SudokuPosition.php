<?php
namespace App\CustomClasses;
class SudokuPosition{
    
    public function __construct(
        public readonly int $sudRow,
        public readonly int $sudCol,
        public readonly int $secRow,
        public readonly int $secCol,
    ){}
}