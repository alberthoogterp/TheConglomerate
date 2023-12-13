<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>visualizer</title>
</head>
<body>
    <form action="{{route('loadaudio')}}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="audiofile" accept="audio/*">
        <input type="submit" value="submit">
    </form>
    @if (session()->get("audioType") && session()->get("audioPath"))
    <h3>{{session()->get("audioType")}}</h3>
    <h3>{{session()->get("audioPath")}}</h3>
        @if(str_contains(session()->get("audioType"), "audio"))
            <audio controls src="{{asset('audio/'.Session::get('audioPath'))}}"></audio>
        @else
            <h3>Not a suitable File</h3>
        
        @endif
    @else
        <h3>No file</h3>
    @endif
</body>
</html>