var image;
var points = [];
var arrayPosition = [];
$(function() {
    var isCtrlKeyDown = false;
    var isMouseDown = 0;
    var isDrawRectangle = false;
    var startX, startY, endX, endY;
    var imageSize = 100;
    var clicked = false;
    var maxLines = 4;
    var lineNumberCount = 0;
    var storedLines = [];
    var storedLine = {};
    var mouse = {x: -1,y: -1};
    var ratioImage = 1;
    var worksheetCanvas;
    var canvas;
    var context;
    
    
    function BindingCanvas(){
        imageSize = parseInt($('#imagesize').val());
        ratioImage = (100/imageSize);
        worksheetCanvas = $('#canvas');
        canvas = worksheetCanvas.get(0);
        image = document.getElementById("images");
        canvas.width = image.width/ratioImage;
        canvas.height = image.height/ratioImage;
        context = canvas.getContext("2d");
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
    }
    BindingCanvas();
    worksheetCanvas.mousedown(function(k){
        if(!$('.binding-data').hasClass('active-binding-data')) return;
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
        if(!$('.binding-data').hasClass('active-binding-data')) return;
        if(isDrawRectangle){
            var x1,y1,x2,y2,x3,y3,x4,y4;
            x1 = x4 = startX * ratioImage;
            x2 = x3 = endX * ratioImage;
            y1 = y2 = startY * ratioImage;
            y3 = y4 = endY * ratioImage;
            if(x1 == x2)return;
            arrayPosition = [];
            arrayPosition.push({X: parseInt(x1), Y: parseInt(y1)});
            arrayPosition.push({X: parseInt(x2), Y: parseInt(y2)});
            arrayPosition.push({X: parseInt(x3), Y: parseInt(y4)});
            arrayPosition.push({X: parseInt(x4), Y: parseInt(y4)});
            bindingDataInput(arrayPosition);
        }else{
            if(lineNumberCount == 5){
                bindingDataInput(arrayPosition);
            }
        }
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
        if (lineNumberCount == 0){
            arrayPosition = [];
        }
        lineNumberCount += 1;
        if(lineNumberCount == 6){
            context.clearRect(0, 0, canvas.width, canvas.height);
            context.drawImage(image, 0, 0, canvas.width, canvas.height);
            lineNumberCount = 1;
            storedLines = [];
            var pos = getMousePos(canvas, e);
            storedLine.startX = pos.x;
            storedLine.startY = pos.y;
            arrayPosition = [];
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
                arrayPosition.push({X: parseInt(mouse.x  * ratioImage), Y: parseInt(mouse.y * ratioImage)});
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
    
    function bindingDataInput(arrayPosition){
        var getData = ocrCtrl.getDataInPositions(arrayPosition);
        $.when(getData).then(function(result, textStatus, jqXHR){
            $('.active-binding-data').val(result);
        });
    }
   
   $(document).keydown(function(event){
        if(event.ctrlKey)
            isCtrlKeyDown = true;
    });
    
    $(document).on('keyup', function (event) {
        isCtrlKeyDown = false;
    });
    
    $(document).on('click', '#clear-active', function (event) {
        $('.binding-data').val('');
        $('.binding-data').removeClass('active-binding-data');
        context.clearRect(0, 0, canvas.width, canvas.height);
        context.drawImage(image, 0, 0, canvas.width, canvas.height);
    });
    
    $(document).on('click', '#imagesize', function (event) {
        context.clearRect(0, 0, 0, 0);
        BindingCanvas();
    });
});