<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/css/sudolverpage.css">
    <title>Sudolver</title>
</head>
<body onLoad="initPage()">
    <div id="main">
        <div id="sudokuContainer">
            <div id="sudokuOptions">
                <h1>Sudolver</h1>
                <select name="sudokuModus" id="sudokuModus">
                    @foreach ($modusArray as $modus)
                        <option value={{$modus}}>{{$modus}}</option>
                    @endforeach
                </select>
                
                <select name="sudokuType" id="sudokuType">
                    @foreach ($typeArray as $type)
                        <option value={{$type}}>{{$type}}</option>
                    @endforeach
                </select>
            </div>

            <form id="canvasForm" name="sudokuCanvasForm" action="{{route('solve')}}" method="POST">
                @csrf
                <div id="sudokuCanvas" class="sudokuCanvas">
                </div>
            </form>
            <div id="canvasButtons">
                <input id="canvasSubmit" type="submit" form="canvasForm">
                <button onclick="reset()">Reset</button>
            </div>
            
            <div id="errors">{{$errors->first() ?? ""}}</div>

            <template id="sudokuSector">
                <div class="sudokuSector">
                    <input type="text" maxlength="1" onclick="emptyValue(this)" onblur="validateInput(this)">
                    <input type="text" maxlength="1" onclick="emptyValue(this)" onblur="validateInput(this)">
                    <input type="text" maxlength="1" onclick="emptyValue(this)" onblur="validateInput(this)">
                    <input type="text" maxlength="1" onclick="emptyValue(this)" onblur="validateInput(this)">
                    <input type="text" maxlength="1" onclick="emptyValue(this)" onblur="validateInput(this)">
                    <input type="text" maxlength="1" onclick="emptyValue(this)" onblur="validateInput(this)">
                    <input type="text" maxlength="1" onclick="emptyValue(this)" onblur="validateInput(this)">
                    <input type="text" maxlength="1" onclick="emptyValue(this)" onblur="validateInput(this)">
                    <input type="text" maxlength="1" onclick="emptyValue(this)" onblur="validateInput(this)">
                </div>
            </template>

            <script>
                const enumTypeArray =  {!! json_encode($typeArray) !!};
                const enumModusArray = {!! json_encode($modusArray) !!};
                
                function initPage(){
                    let modus = getSudokuModus();
                    let type = getSudokuType();
                    let sudokuArray = {!! json_encode($sudokuArray) !!};
                    createCanvas(type, modus, sudokuArray);
                }

                function getSudokuModus(){
                    let elem = document.getElementById("sudokuModus");
                    return elem.value;
                }

                function getSudokuType(){
                    let elem = document.getElementById("sudokuType");
                    return elem.value;
                }

                function createCanvas(type, modus, sudokuArray){
                    const canvas = document.getElementById("sudokuCanvas"); 
                    const sectorTemplate = document.getElementById("sudokuSector");
                    if(modus == "Solver"){
                        const submitButton = document.getElementById("canvasSubmit");
                        submitButton.setAttribute("value", "Solve");
                        if(type == "Standard"){
                            var sudokuSectorAmmount = 9
                        }
                        for(i = 0; i < sudokuSectorAmmount; i++){
                            const sectorTemplateClone = document.importNode(sectorTemplate.content, true);
                            const inputArray = sectorTemplateClone.querySelector(".sudokuSector").children;
                            for(j = 0; j < inputArray.length; j++){
                                inputArray[j].setAttribute("name", "input["+i+"]["+j+"]");
                                if(sudokuArray.length === 0){
                                    value = "-";
                                }
                                else{
                                    value = sudokuArray[i][j];
                                }
                                inputArray[j].setAttribute("value", value);
                                canvas.appendChild(sectorTemplateClone);
                            }
                        }
                    } 
                    else{
                    }   
                }

                function validateInput(input){
                    regex = new RegExp("[1-9\-]{1}");
                        
                    if(!regex.test(input.value)){
                        input.value = "-";
                    }
                }

                function emptyValue(input){
                    if(input.value === "-"){
                        input.value = "";
                    }
                }

                function reset(){
                    const canvas = document.getElementById("sudokuCanvas"); 
                    const errorDiv = document.getElementById("errors");
                    errorDiv.textContent = "";
                    for(const input of canvas.children){
                        for(let value of input.children){
                            value.value = "-";
                        }
                    }
                }
                
                document.getElementById("sudokuModus").addEventListener("change", getSudokuModus);
                document.getElementById("sudokuType").addEventListener("change", getSudokuType);
            </script>
        </div>
    </div>
</body>
</html>