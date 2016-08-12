var canvas, context, startX, endX, startY, endY, image;
var mouseIsDown = 0;
function init() {
    canvas = document.getElementById("canvas");
    image = document.getElementById("images");
    context = canvas.getContext("2d");
    context.drawImage(image, 0, 0, canvas.width, canvas.height);
    canvas.addEventListener("mousedown", mouseDown, false);
    canvas.addEventListener("mousemove", mouseXY, false);
    canvas.addEventListener("mouseup", mouseUp, false);
}

function mouseUp(eve) {
    if (mouseIsDown !== 0) {
        mouseIsDown = 0;
        var pos = getMousePos(canvas, eve);
        endX = pos.x;
        endY = pos.y;
        drawSquare(); //update on mouse-up
    }
}

function mouseDown(eve) {
    mouseIsDown = 1;
    var pos = getMousePos(canvas, eve);
    startX = endX = pos.x;
    startY = endY = pos.y;
    drawSquare(); //update
}

function mouseXY(eve) {

    if (mouseIsDown !== 0) {
        var pos = getMousePos(canvas, eve);
        endX = pos.x;
        endY = pos.y;

        drawSquare();
    }
}

function drawSquare() {
    // creating a square
    var w = endX - startX;
    var h = endY - startY;
    var offsetX = (w < 0) ? w : 0;
    var offsetY = (h < 0) ? h : 0;
    var width = Math.abs(w);
    var height = Math.abs(h);

    context.clearRect(0, 0, canvas.width, canvas.height);
    context.drawImage(image, 0, 0, canvas.width, canvas.height);
    context.lineWidth = 1.5;
    context.strokeStyle = '#FF0000';
    context.strokeRect(startX + offsetX, startY + offsetY, width, height);
}

function getMousePos(canvas, evt) {
    var rect = canvas.getBoundingClientRect();
    return {
        x: evt.clientX - rect.left,
        y: evt.clientY - rect.top
    };
}