Dynamsoft.WebTwainEnv.RegisterEvent('OnWebTwainReady', Dynamsoft_OnReady);

var dataBinding = {"data":[
        {
            "InvoiceId": "1234445",
            "Amount_Due": "867.78",
            "Account_Number": "21079",
            "Telephone": "(716) 956-2155"
        }
]};

var worksheetCanvas;
var canvas;
var context;
var image;
var ratioImage = 1;

function BindingCanvas(){
    worksheetCanvas = $('#canvas');
    if($('#images').attr('src') == undefined || $('#images').attr('src') == '') return;
    var d = new Date();
    var url = $('#images').attr('src');
    $('#images').attr('src', url + '?t=' + d.getTime());
    $('#images').on('load', function() {
        image = this;
        imageSize = parseInt($('#imagesize').val());
        ratioImage = (100/imageSize);
        canvas = worksheetCanvas.get(0);
        canvas.width = this.width/ratioImage;
        canvas.height = this.height/ratioImage;
        context = canvas.getContext("2d");
        context.drawImage(this, 0, 0, canvas.width, canvas.height);
    });
}

var ocrCtrl = function (){
    var bindingInput = function(dataObj, id){
        $('#'+ id).val(dataObj);
    };
    
    var getInvoiceData = function(){
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/getinvoicedata',
            dataType: 'json',
            type: 'POST',
            contentType: 'application/json; charset=utf-8',
            data: JSON.stringify({ contactId: 1 })
        });
    };
    
    var getInvoiceInfo = function(id, templateId){
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/getvalueinjson',
            type: 'POST',
            data: {
                physicalFileId: id,
                templateId: templateId
            }
        });
    };
    
    var getDataInPositions = function(listPositions){
        var jsonData = JSON.stringify(listPositions);
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/getdatainpositions',
            type: 'POST',
            data: { 
                data : jsonData,
                jsonFilePath: $('#json-file-path').val()
            }
        });
    };
    
    var updateInvoiceDetail = function(model){
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/UpdateInvoice',
            type: 'POST',
            data: model
        });
    };
    
    var getPhysicalFileId = function(fileName){
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/getphysicalfileid',
            type: 'POST',
            data: {physicalFileName: fileName}
        });
    };
    
    var getTemplates = function(){
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/gettemplate',
            type: 'POST',
        });
    };
    
    var createTemplate = function(model){
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/createtemplate',
            type: 'POST',
            data: model
        });
    }
    
    return {
        bindingInput: function (dataObj, id){
            return bindingInput(dataObj, id);
        },
        getInvoiceData: function(){
            return getInvoiceData();
        },
        getInvoiceInfo: function(id, templateId){
            return getInvoiceInfo(id, templateId);
        },
        getDataInPositions: function(listPositions){
            return getDataInPositions(listPositions);
        },
        updateInvoiceDetail: function(model){
            return updateInvoiceDetail(model);
        },
        getPhysicalFileId: function(fileName){
            return getPhysicalFileId(fileName);
        },
        getTemplates: function(){
            return getTemplates();
        },
        createTemplate: function(model){
            return createTemplate(model);
        },
    };
}(ocrCtrl);


$(document).on('click', '.data-binding', function (event) {
   var id = $(this).attr('data-id');
   var dataObj = $(this).text();
   
   ocrCtrl.bindingInput(dataObj, id);
});
//
//$(document).on('click', '#loadInvoice', function (event) {
//   var invoiceData = ocrCtrl.getInvoiceData();
//   $.when(invoiceData).then(function (reuslt) {
//       var obj = reuslt;
//       $('#invoiceInfoTemplate').tmpl(obj.InvoiceInfo).appendTo('#hearder');
//       $('#invoiceListTemplate').tmpl(obj).appendTo('#bodyInvoice');
//       $('#invoiceFooterTemplate').tmpl(obj.InvoiceInfo).appendTo('#footer');
//   });
//});

$(document).on('click', '.binding-data', function (event) {
    $('.binding-data').removeClass('active-binding-data')
    $(this).addClass('active-binding-data');
});

$(document).on('keydown', '.data-binding', function (event) {
    if (event.which == 13) {
        
    }
});

$(document).on('keydown', '.data-binding', function (event) {
    if (event.which == 13) {
        
    }
});

$(document).on('click', '#update-invoice-detail', function (event) {
    var model = {
        InvoiceInfoId: 1,
        VendorName: $('#vender-name').val(),
        InvoiceNumber: $('#invoice-number').val(),
        Date: $('#invoice-date').val(),
    };
    ocrCtrl.updateInvoiceDetail(model);
});

$(document).on('click', '#update-list-item', function (event) {
    var model = {
        InvoiceInfoId: 1,
        VendorName: $('#vender-name').val(),
        InvoiceNumber: $('#invoice-number').val(),
        Date: $('#invoice-date').val(),
    };
    ocrCtrl.updateInvoiceDetail(model);
});

function bindingInvoiceInfo(){
    var id = parseInt($('#physical-file-id').val());
    var templateId = parseInt($('#template-option').val());
    if(id == 0) {
        alert('You need upload file to server.')
        return;
    }
    var getData = ocrCtrl.getInvoiceInfo(id, templateId);
    $.when(getData).then(function(result, textStatus, jqXHR){
        var data = JSON.parse(result);
        $('#invoice-date').val(data.InvoiceInfo.InvoiceDate.value);
        $('#vender-number').val(data.InvoiceInfo.VendorNumber.value);
        $('#invoice-number').val(data.InvoiceInfo.InvoiceID.value);
        $('#teams').val(data.InvoiceInfo.Terms.value);
        $('#invoice-total').val(data.InvoiceInfo.Total.value);
        $('#images').removeAttr("src").attr('src', "http://localhost:8080/OcrForm/" + data.PhysicalFilePath);
        $('#json-file-path').val(data.JsonFilePath);
        BindingCanvas();
        //$('#listInvoicesTemplate').tmpl(data).appendTo('#list-invoices-data');
    });
}

function bindingTemplates(){
    var templates = ocrCtrl.getTemplates();
    Dynamsoft.Lib.detect.showMask();
    $.when(templates).then(function(result, textStatus, jqXHR){
        Dynamsoft.Lib.detect.hideMask();
        var html = '';
        var data = JSON.parse(result);
        html += '<option value="-1">--- Seleted template ---</option>';
        for(var i = 0; i < data.length; i ++){
            var item = data[i];
            html += '<option value="'+ item.ID +'">'+ item.TemplateName +'</option>'
        }
        $('#template-option').html('');
        $('#template-option').html(html);
    });
}

function createTemplate(){
    var id = parseInt($('#physical-file-id').val());
    if($('#template-name').val() == undefined || $('#template-name').val() == '' || id == 0) return;
    var model = {
        physicalFileId: id,
        templateName: $('#template-name').val()
    };
    var data = ocrCtrl.createTemplate(model);
    $.when(data).then(function(result){
        bindingTemplates();
    });
}
