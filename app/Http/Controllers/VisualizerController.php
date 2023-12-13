<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class VisualizerController extends Controller
{
    function pageload(Request $request){
        return view("visualizerpage");
    }

    function loadAudio(Request $request){
        if($request->file("audiofile")){
            $audioFile = $request->file("audiofile");
            $filename = $audioFile->getClientOriginalName();
            $audioType = $audioFile->getMimeType();
            $audioFile->move(public_path("audio"), $filename);
            Session::put("audioType", $audioType);
            Session::put("audioPath", $filename);
            return redirect()->route("visualizer");
        }
        back();
    }
}
