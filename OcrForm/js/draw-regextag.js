var image;
var points = [];
$(function() {
    var isCtrlKeyDown = false;
    var isMouseDown = 0;
    var isDrawRectangle = false;
    var startX, startY, endX, endY;
    var worksheetCanvas = $('#canvas');
    var canvas = worksheetCanvas.get(0);
    image = document.getElementById("images");
    canvas.width = image.width;
    canvas.height = image.height;
    var context = canvas.getContext("2d");
    context.drawImage(image, 0, 0, canvas.width, canvas.height);
    var clicked = false;
    
    var maxLines = 4;
    var lineNumberCount = 0;
    
    var storedLines = [];
    
    var storedLine = {};
    var mouse = {
        x: -1,
        y: -1
    };
    
    worksheetCanvas.mousedown(function(k){
        if(isCtrlKeyDown == true){
            isDrawRectangle = false;
            drawLine(k);
        }
        else{
            isDrawRectangle = true;
            var pos = getMousePos(canvas, k);
            startX = endX = pos.x;
            startY = endY = pos.y;
            isMouseDown = 1;
            context.clearRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
        }
    });
    
    worksheetCanvas.mouseup(function(e){
        isMouseDown = 0;
    });
    
    worksheetCanvas.mousemove(function(k){
        if (isMouseDown !== 0 && isDrawRectangle) {
            var pos = getMousePos(canvas, k);
            endX = pos.x;
            endY = pos.y;
            drawRectangle();
        }
    });
    
    function drawRectangle() {
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
    
    function drawLine(e){
        lineNumberCount += 1;
        if(lineNumberCount == 6){
            context.clearRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            lineNumberCount = 1;
            storedLines = [];
            var pos = getMousePos(canvas, e);
            storedLine.startX = pos.x;
            storedLine.startY = pos.y;
            points = [];
        }
        clicked = true;
        var pos = getMousePos(canvas, e);
        mouse.x = pos.x;
        mouse.y = pos.y;

        context.moveTo(mouse.x, mouse.y);
        
        
        if (clicked) {
            storedLines.push({
                startX: storedLine.startX,
                startY: storedLine.startY,
                endX: mouse.x,
                endY: mouse.y
            });
            if(lineNumberCount < 5){
                points.push({x: mouse.x, y: mouse.y});
            }
        }
        storedLine.startX = mouse.x;
        storedLine.startY = mouse.y;
        if(storedLines.length < maxLines)
        {
            $(this).mousemove(function(k) {
                if (storedLines.length > maxLines) return;
                
                context.clearRect(0, 0, canvas.width, canvas.height);
                context.drawImage(image, 0, 0, canvas.width, canvas.height);
                context.beginPath();
                context.strokeStyle = "#FF0000";
               
                for (var i = 0; i < storedLines.length; i++) {
                    var v = storedLines[i];
                    context.moveTo(v.startX, v.startY);
                    context.lineTo(v.endX, v.endY);
                    context.stroke();
                }
                context.moveTo(mouse.x, mouse.y);
                var pos = getMousePos(canvas, k);
                context.lineTo(pos.x, pos.y);
                context.stroke();
                context.closePath();
            });
        }
    }
    
    function getMousePos(canvas, evt) {
        var rect = canvas.getBoundingClientRect();
        return {
            x: evt.clientX - rect.left,
            y: evt.clientY - rect.top
        };
    }
   
   $(document).keydown(function(event){
        if(event.ctrlKey)
            isCtrlKeyDown = true;
    });
    
    $(document).on('keyup', function (event) {
        isCtrlKeyDown = false;
    });
});