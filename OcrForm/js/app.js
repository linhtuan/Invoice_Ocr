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
var listInvoiceItem = [];
var invoiceDetail;
var templateIdIsActive = -1;

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
    
    var getInvoiceInfo = function(id, templateId){
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/GetInvoiceInfo',
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
        VendorName: $('#vendor-name').val(),
        InvoiceNumber: $('#invoice-number').val(),
        Date: $('#invoice-date').val(),
    };
    ocrCtrl.updateInvoiceDetail(model);
});

$(document).on('click', '#update-list-item', function (event) {
    var model = {
        InvoiceInfoId: 1,
        VendorName: $('#vendor-name').val(),
        InvoiceNumber: $('#invoice-number').val(),
        Date: $('#invoice-date').val(),
    };
    ocrCtrl.updateInvoiceDetail(model);
});

$(document).on('click', '#process-list-item', function (event) {
    var itemId = $('#item-id').val();
    if(itemId == undefined || itemId == null == itemId == '') return;
    
    
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
        invoiceDetail = data.InvoiceInfo;
        $('#invoice-date').val(data.InvoiceInfo.InvoiceDate.value);
        $('#vendor-number').val(data.InvoiceInfo.VendorNumber.value);
        $('#invoice-number').val(data.InvoiceInfo.InvoiceID.value);
        $('#teams').val(data.InvoiceInfo.Terms.value);
        $('#invoice-total').val(data.InvoiceInfo.Total.value);
        
        $('#invoice-date-text').val(data.InvoiceInfo.InvoiceDate.label);
        $('#vendor-number-text').val(data.InvoiceInfo.VendorNumber.label);
        $('#invoice-number-text').val(data.InvoiceInfo.InvoiceID.label);
        $('#teams-text').val(data.InvoiceInfo.Terms.label);
        $('#invoice-total-text').val(data.InvoiceInfo.Total.label);
        
        $('#images').removeAttr("src").attr('src', "http://localhost:8080/OcrForm/" + data.PhysicalFilePath);
        $('#json-file-path').val(data.JsonFilePath);
        if(data.InvoiceListItem != null && data.InvoiceListItem.length > 0){
            BindingListInvoiceItems(data.InvoiceListItem);
        }
        BindingCanvas();
        //$('#listInvoicesTemplate').tmpl(data).appendTo('#list-invoices-data');
    });
}

function BindingListInvoiceItems(array){
    listInvoiceItem = array;
    var title = array[0];
    var htmlTitle = '';
    for(var i = 0; i < title.length; i ++){
        var item = title[i];
        var id = item.replace(/ /g, '_');
        id = item.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "_");
        htmlTitle += '<th><input class="form-control binding-data" style="background-color: #87CEEB" value="'+ item +'" id="'+ id +'"></th>'
    }
    $('#list-invoice-title').html('');    
    $('#list-invoice-title').html(htmlTitle);
    
    var htmlListItems = '';
    for(var i = 1; i < array.length; i++){
        var item = array[i];
        for(var j = 0; j < item.length; j++){
            var data = item[j];
            var id = title[j].replace(/ /g, '_');
            id = id.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "_");
            htmlListItems += '<td><input class="form-control binding-data" value="'+ data +'" id="'+ id +'"></td>';
        }
    }
    $('#list-invoices-data').html('');
    $('#list-invoices-data').html(htmlListItems);
}

function bindingTemplates(id){
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
        if(id == undefined || id == null || id == -1) return;
        
        $('#template-option').val(parseInt(id));
    });
}

function createTemplate(){
    if(invoiceDetail == undefined) return;
    var id = parseInt($('#physical-file-id').val());
    if($('#template-name').val() == undefined || $('#template-name').val() == '' || id == 0) return;
    
    var templateDetails = [];
    templateDetails.push({label: invoiceDetail.InvoiceDate.label, 
        type: invoiceDetail.InvoiceDate.type,
        vertycal: invoiceDetail.InvoiceDate.vertycal, 
        index: invoiceDetail.InvoiceDate.index});
    templateDetails.push({label: invoiceDetail.VendorNumber.label, 
        type: invoiceDetail.VendorNumber.type,
        vertycal: invoiceDetail.VendorNumber.vertycal, 
        index: invoiceDetail.VendorNumber.index});
    templateDetails.push({label: invoiceDetail.InvoiceID.label, 
        type: invoiceDetail.InvoiceID.type,
        vertycal: invoiceDetail.InvoiceID.vertycal, 
        index: invoiceDetail.InvoiceID.index});
    templateDetails.push({label: invoiceDetail.Terms.label, 
        type: invoiceDetail.Terms.type,
        vertycal: invoiceDetail.Terms.vertycal, 
        index: invoiceDetail.Terms.index});
    templateDetails.push({label: invoiceDetail.Total.label, 
        type: invoiceDetail.Total.type,
        vertycal: invoiceDetail.Total.vertycal, 
        index: invoiceDetail.Total.index});
    var model = {
        physicalFileId: id,
        templateName: $('#template-name').val(),
        templateDetail: JSON.stringify(templateDetails),
        templateListKey: $('#item-id').val(),
        templateListCol: $('#column-number').val()
    };
    
    var data = ocrCtrl.createTemplate(model);
    $.when(data).then(function(result){
        bindingTemplates(result);
    });
}
