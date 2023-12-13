<?php

use App\Http\Controllers\JsonbuilderController;
use App\Http\Controllers\sudolverController;
use App\Http\Controllers\visualizerController;
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
Route::post("/jsonBuilder/update", [JsonbuilderController::class, "updateJson"])->name("updatejson");

Route::get("/visualizer", [visualizerController::class, "pageload"])->name("visualizer");
Route::post("/visualizer/upload", [visualizerController::class, "loadAudio"])->name("loadaudio");

Route::get("/sudolver", [sudolverController::class, "pageload"])->name("sudolver");
Route::post("/sudolver/solve", [sudolverController::class, "solve"])->name("solve");