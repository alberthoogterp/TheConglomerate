<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/css/jsonbuilderpage.css">
    <title>Json-builder</title>
</head>
<body onload="filltextbox()">
    <h2>Load existing json file</h2>
    <form id="jsonForm" action="{{route('loadjson')}}" method="post" enctype="multipart/form-data">
        @csrf
        <input name="jsonfile" type="file">
        <input type="submit" value="load">
    </form>
    <form  action="{{route('savejson')}}" method="POST">
        @csrf
        <textarea name="jsontextbox" id="jsontextbox" rows="30" readonly></textarea>
    </form>
    <button onclick="updateJson()">Update</button>
    <input form="jsonForm" type="submit" value="Save">

    @if (isset($jsonasoc))
    <div id="jsonEditableFields">
        @foreach ($jsonasoc as $key => $value)
            @if (is_array($value))
                <div class="jsoninputsection" id="jsoninputsection_{{$key}}" data-type="{{isset($value[0]) ? 'array' : 'object'}}">
                    <div class="inputsection" id="inputsection_{{$key}}">
                        <button class='dropdownbutton' id='button_{{$key}}' onclick="changeDropdownbuttonState('{{$key}}')"></button>
                        <input class="jsoninput" type='text' value='{{$key}}' size='{{strlen($key)}}'>
                        <button class="addbutton" data-key="{{$key}}"></button><br>
                    </div>
                    <div class="section" id="section_{{$key}}">
                        @include('partials.jsonsubobject',['jsondata'=>$value])
                    </div>
                </div>
            @else
                <div class="jsoninputsection" id="jsoninputsection_{{$key}}" data-type="value">
                    <div class="inputsection" id="inputsection_{{$key}}">
                        <input class="jsoninput" type='text' style="margin-left:2em"  value='{{$key}}' size='{{strlen($key)}}'>:
                        <input class="jsoninput" type='text' value='{{$value}}' size='{{strlen($value)}}'>
                        <button class="addbutton" data-key="{{$key}}"></button><br>
                    </div>
                </div>
            @endif
        @endforeach  
        <div tabindex='-1' id='buttonmenu' class='buttonmenu' data-key="">
            <button id="addBeforeButton" class='buttonmenuButton'>Add before</button> 
            <button id="addIntoButton" class="buttonmenuButton">Add into</button>
            <button id="addAfterButton" class='buttonmenuButton'>Add after</button> 
            <button id="removeButton" class='buttonmenuButton'>Remove</button>
            <div id="addoptionmenu" class="buttonmenu" data-position="">
                <button id="newobjectbutton" class='buttonmenuButton'>New object</button>
                <button id="newvaluebutton" class='buttonmenuButton'>New value</button>
                <button id="newarraybutton" class='buttonmenuButton'>New array</button>
            </div>
        </div>
    </div>
    @endif
    
    <script>
    function filltextbox(){
        if(jsonasoc != null){
            document.getElementById("jsontextbox").value = JSON.stringify(jsonasoc, null, 4);
        }
    }

    function updateJson(){
        
    }

    //changes dropdown arrow direction when clicked
    function changeDropdownbuttonState(key){
        let section = document.getElementById('section_' + key);
        let button = document.getElementById('button_' + key);
        let displaystyle = window.getComputedStyle(section).display; //get the style thats actually applied to the element and not just the inline style
        button.classList.toggle("toggled");
        if (displaystyle === 'none') {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    }

    var newElementId = 0;
    //creates new elements of appropriate type
    function addJsonElement(type, event){        
        let key = event.target.parentElement.parentElement.dataset.key;
        let jsoninputsection = document.getElementById("jsoninputsection_"+key);
        let position = event.target.parentElement.dataset.position;
        let newdiv = document.createElement("div");
        newdiv.setAttribute("class", "jsoninputsection");
        newdiv.setAttribute("id", "jsoninputsection_"+newElementId);
        let inputsection = document.createElement("div");
        inputsection.setAttribute("class", "inputsection");

        if(type == "object" || "array"){
            let dropdownbutton = document.createElement("button");
            dropdownbutton.setAttribute("class", "dropdownbutton");
            dropdownbutton.setAttribute("id", "button_"+newElementId);
            dropdownbutton.setAttribute("onclick", `changeDropdownbuttonState('${newElementId}')`);
            
            let jsoninput = document.createElement("input");
            jsoninput.setAttribute("class", "jsoninput");
            jsoninput.setAttribute("type", "text");
            jsoninput.setAttribute("value", "newinput");
            jsoninput.setAttribute("size", "8");

            let addbutton = document.createElement("button");
            addbutton.setAttribute("class", "addbutton");
            addbutton.setAttribute("data-key", newElementId);
            let br = document.createElement("br");
            
            inputsection.appendChild(dropdownbutton);
            inputsection.appendChild(jsoninput);
            inputsection.appendChild(addbutton);
            inputsection.appendChild(br);
            
            let section = document.createElement("div");
            section.setAttribute("class", "section");
            section.setAttribute("id","section_"+newElementId);
            
            newdiv.appendChild(inputsection);
            newdiv.appendChild(section);
        }
        else if(type == "value"){
            let jsonkeyinput = document.createElement("input");
            jsonkeyinput.setAttribute("class", "jsoninput");
            jsonkeyinput.setAttribute("type", "text");
            jsonkeyinput.setAttribute("style", "margin-left:2em");
            jsonkeyinput.setAttribute("value", "newkey");
            jsonkeyinput.setAttribute("size", "8");
            let doublepoint = document.createTextNode(":");
            
            let jsonvalueinput = document.createElement("input");
            jsonvalueinput.setAttribute("class", "jsoninput");
            jsonvalueinput.setAttribute("type", "text");
            jsonvalueinput.setAttribute("value", "newvalue");
            jsonvalueinput.setAttribute("size", "8");
            
            let addbutton = document.createElement("button");
            addbutton.setAttribute("class", "addbutton");
            addbutton.setAttribute("data-key", newElementId);
            let br = document.createElement("br");
           
            inputsection.appendChild(jsonkeyinput);
            inputsection.appendChild(doublepoint);
            inputsection.appendChild(jsonvalueinput);
            inputsection.appendChild(addbutton);
            inputsection.appendChild(br);

            newdiv.appendChild(inputsection);
        }
        newElementId++;
        
        if(position == "before"){
            jsoninputsection.parentElement.insertBefore(newdiv, jsoninputsection);
        }
        else if(position == "into"){
            let sectionname = "section_"+key;
            console.log(sectionname);
            console.log(jsoninputsection.children);
            jsoninputsection.children[sectionname].appendChild(newdiv);
        }
        else if(position == "after"){
            jsoninputsection.parentElement.insertBefore(newdiv, jsoninputsection.nextSibling);
        }
    }

    //shows a submenu when a options button is clicked
    function optionbuttonClick(event){
        if (event.target.tagName === 'BUTTON' && event.target.className === "addbutton") {
            let buttonmenu = document.getElementById('buttonmenu');
            buttonmenu.style.display = "flex";
            let buttonBound = event.target.getBoundingClientRect();
            buttonmenu.dataset.key = event.target.dataset.key;
            let scrollX = window.scrollX;
            let scrollY = window.scrollY;
            buttonmenu.style.left = buttonBound.left + buttonBound.width + scrollX + "px";
            buttonmenu.style.top = buttonBound.top + buttonBound.height + scrollY + "px";
            buttonmenu.focus();
        }
    }

    //shows another submenu when you have chosen to add before or after an element
    function addButtonClicked(position, event){
        let addoptionmenu = document.getElementById("addoptionmenu");
        addoptionmenu.style.display = "flex"
        addoptionmenu.dataset.position = position;
    }

    function removeButtonClicked(event){
        console.log("remove");
    }

    //hides the submenus when they lose focus
    function removeButtonMenu(event){
        if(event.target.className === "buttonmenu"){
            if(!event.relatedTarget || event.relatedTarget.className !== "buttonmenuButton"){
                event.target.style.display = "none";
                event.target.children.addoptionmenu.style.display = "none";
            }
            else{
                document.getElementById("buttonmenu").focus(); 
            }
        }
    }
    </script>
    @if(!isset($jsonasoc)) 
        <script> jsonasoc = null</script>
    @else
        <script> 
            jsonasoc = JSON.parse('{!!json_encode($jsonasoc)!!}');
            document.getElementById('jsonEditableFields').addEventListener('click', optionbuttonClick);//add click event to all buttons in jsoneditablefields div
            document.getElementById('buttonmenu').addEventListener("focusout", removeButtonMenu);
            document.getElementById('addBeforeButton').addEventListener("click", addButtonClicked.bind(null, "before"));
            document.getElementById('addIntoButton').addEventListener("click", addButtonClicked.bind(null, "into"));
            document.getElementById('addAfterButton').addEventListener("click", addButtonClicked.bind(null, "after"));
            document.getElementById('removeButton').addEventListener("click", removeButtonClicked);
            document.getElementById('newobjectbutton').addEventListener("click", addJsonElement.bind(null, "object"));
            document.getElementById('newvaluebutton').addEventListener("click", addJsonElement.bind(null, "value"));
            document.getElementById('newarraybutton').addEventListener("click", addJsonElement.bind(null, "array"));
        </script> 
    @endif
</body>
</html>