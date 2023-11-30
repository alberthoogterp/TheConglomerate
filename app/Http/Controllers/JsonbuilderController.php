<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class JsonbuilderController extends Controller
{
    public function pageload(){
        return view("jsonbuilderpage");
    }

    public function loadJson(Request $request){
        $jsonFile = $request->jsonfile;
        if($jsonFile){
            $jsonarray = json_decode(file_get_contents($jsonFile));
            return view("jsonbuilderpage",["jsonarray"=>$jsonarray]);
        }
        return back();
    }

    public function saveJson(Request $request){
        $newJson = json_encode(json_decode($request->jsontextbox), JSON_PRETTY_PRINT);
        $filename = "New_json_file.json";
        $newFile = fopen($filename,'w') or die("Unable to write file");
        fwrite($newFile,$newJson);
        fclose($newFile);
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename='.basename($filename));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        header("Content-Type: application/json");
        readfile($filename);
    }
    
    public function updateJson(Request $request){
        if($request->updatedjson){
            return view("jsonbuilderpage",["jsonarray"=>json_decode($request->updatedjson)]);
        }
        return back();
    }
}
