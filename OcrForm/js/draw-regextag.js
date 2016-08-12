var image;
var points = [];
$(function() {

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
    }
    
    worksheetCanvas.click(function(e) {
        lineNumberCount += 1;

        if(lineNumberCount == 6){
            context.clearRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            return;
        }else if (lineNumberCount == 7){
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
    });
    
    function getMousePos(canvas, evt) {
        var rect = canvas.getBoundingClientRect();
        return {
            x: evt.clientX - rect.left,
            y: evt.clientY - rect.top
        };
    }
});