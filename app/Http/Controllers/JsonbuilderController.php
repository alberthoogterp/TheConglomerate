<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JsonbuilderController extends Controller
{
    public function pageload(){
        return view("jsonbuilderpage");
    }

    public function loadJson(Request $request){
        $jsonFile = $request->jsonfile;
        if($jsonFile){
            $jsonAsoc = json_decode(file_get_contents($jsonFile),true);
            return view("jsonbuilderpage",["jsonasoc"=>$jsonAsoc]);
        }
        return back();
    }

    public function saveJson(Request $request){
        dd($request->all());
    }
}
