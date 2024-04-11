<?php

namespace App\CustomClasses\SudokuClasses;
enum AreaType:string{
    case SECTOR = "sector";
    case ROW = "row";
    case COLUMN = "column";
    case XWING = "xwing";
    case YWING = "ywing";
}