<?php

use App\Http\Controllers\JsonbuilderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route("homepage");
});
Route::get("/home", function(){
    return view("homepage");
})->name("homepage");

Route::get("/jsonBuilder", [JsonbuilderController::class, "pageload"])->name("jsonbuilder");
Route::post("/jsonBuilder/load", [JsonbuilderController::class, "loadJson"])->name("loadjson");
Route::post("/jsonBuilder/save", [JsonbuilderController::class, "saveJson"])->name("savejson");
