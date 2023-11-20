<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/homepage.css">
    <title>The choice is yours</title>
</head>
<body>
    <div style="display:flex; justify-content: center; align-items:center; text-align:center; height:100vh; width:100vw">
        <div style="height:50%; width:90%">
            <ul id="gallery" class="gallery">
                <li><a href="{{route('jsonbuilder')}}">jsonbuilder</a></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>
        </div>
    </div>
</body>
</html>

<script>
    function getRandomColour() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    let gallery = document.getElementById("gallery");
    gallery.style.gridTemplateColumns = `repeat(${gallery.childElementCount}, 30%)`
    for(child of gallery.children){
        child.style.background = getRandomColour();
    }

    const slider = document.querySelector('.gallery');
    let isDown = false;
    let startX;
    let scrollLeft;

    slider.addEventListener('mousedown', e => {
        isDown = true;
        slider.classList.add('active');
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    });
    slider.addEventListener('mouseleave', _ => {
        isDown = false;
        slider.classList.remove('active');
    });
    slider.addEventListener('mouseup', _ => {
        isDown = false;
        slider.classList.remove('active');
    });
    slider.addEventListener('mousemove', e => {
    if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const SCROLL_SPEED = 3;
        const walk = (x - startX) * SCROLL_SPEED;
        slider.scrollLeft = scrollLeft - walk;
    });
</script>