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
var listFileInfos = [];
var possitionListInvoice;
$('#imagesize').val(40);

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
    
    var getInvoiceInfo = function(id, templateId, pdfId){
        return $.ajax({
            url: '/OcrForm/index.php/invoice/GetInvoiceInfo',
            type: 'POST',
            data: {
                physicalFileId: id,
                templateId: templateId,
                pdfId: pdfId
            }
        });
    };
    
    var getDataInPositions = function(listPositions, isActionFilter){
        var jsonData = JSON.stringify(listPositions);
        return $.ajax({
            url: '/OcrForm/index.php/invoice/getdatainpositions',
            type: 'POST',
            data: { 
                data : jsonData,
                jsonFilePath: $('#json-file-path').val(),
                isFilter: isActionFilter
            }
        });
    };
    
    var updateInvoiceDetail = function(model){
        return $.ajax({
            url: '/OcrForm/index.php/invoice/UpdateInvoice',
            type: 'POST',
            data: model
        });
    };
    
    var getPhysicalFileId = function(fileName){
        return $.ajax({
            url: '/OcrForm/index.php/invoice/getphysicalfileid',
            type: 'POST',
            data: {physicalFileName: fileName}
        });
    };
    
    var getTemplates = function(){
        return $.ajax({
            url: '/OcrForm/index.php/invoice/gettemplate',
            type: 'POST',
        });
    };
    
    var createTemplate = function(model){
        return $.ajax({
            url: '/OcrForm/index.php/invoice/createtemplate',
            type: 'POST',
            data: model
        });
    };
    
    var listInvoiceProcess = function(model){
        return $.ajax({
            url: '/OcrForm/index.php/invoice/ListItemProcess',
            type: 'POST',
            data: model
        });
    };
    
    var bindingDataByTemplateId = function(id){
         return $.ajax({
            url: '/OcrForm/index.php/invoice/BindingDataByTemplateId',
            type: 'POST',
            data: {templateId: id}
        });
    };
    
    var getInvoiceByPageIndex = function(model){
         return $.ajax({
            url: '/OcrForm/index.php/invoice/BindingInvoiceByPageIndex',
            type: 'POST',
            data: model
        });
    };
    
    return {
        bindingInput: function (dataObj, id){
            return bindingInput(dataObj, id);
        },
        getInvoiceInfo: function(id, templateId, pdfId){
            return getInvoiceInfo(id, templateId, pdfId);
        },
        getDataInPositions: function(listPositions, isActionFilter){
            return getDataInPositions(listPositions, isActionFilter);
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
        listInvoiceProcess: function(model){
            return listInvoiceProcess(model);
        },
        bindingDataByTemplateId: function(id){
            return bindingDataByTemplateId(id);
        },
        getInvoiceByPageIndex: function(model){
            return getInvoiceByPageIndex(model);
        }
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

$(document).on('keydown', '#column-number', function (event) {
    if (event.which == 13) {
        var colNumber = $(this).val();
        var htmlTitle = '';
        var htmlListItems = '';
        htmlListItems += "<tr>"
        for(var i = 0; i < colNumber; i ++){
            htmlTitle += '<td><div class="wrapper"><div class="wysiwyg"><textarea data-position="" class="title-header binding-data" style="background-color: #87CEEB;"></textarea></div></div></td>';
            htmlListItems += '<td><div class="wrapper"><div class="wysiwyg"><textarea data-position="" class="first-row binding-data" style="resize:both;overflow:auto;"></textarea></div></div></td>';
        }
        htmlListItems += "</tr>"
        $('#list-invoice-title').html('');    
        $('#list-invoice-title').html(htmlTitle);
        $('#list-invoices-data').html('');
        $('#list-invoices-data').html(htmlListItems);
    }
});

$(document).on('click', '#update-invoice-detail', function (event) {
    var model = {
        InvoiceInfoId: $('#invoice-info-id').val(),
        VendorName: $('#vendor-name').val(),
        VendorNumber: $('#vendor-number').val(),
        InvoiceNumber: $('#invoice-number').val(),
        InvoiceDate: $('#invoice-date').val(),
        PONumber: $('#po-number').val(),
        Shipping: $('#shipping').val(),
        Discount: $('#global-disc').val(),
        Terms: $('#teams').val(),
        Total: $('#invoice-total').val(),
        Tax: $('#tax-1').val(),
        FileInfoID: $('#physical-file-id').val(),
        ListInvoices: null
    };
    var data = ocrCtrl.updateInvoiceDetail(model);
    $.when(data).then(function(result){
        var dataResult = JSON.parse(result);
        $('#invoice-info-id').val(dataResult.invoiceInfoId)
    });
});

$(document).on('click', '#update-list-item', function (event) {
    var listInvoice = getListInvoiceItem();
    var model = {
        InvoiceInfoId: $('#invoice-info-id').val(),
        VendorName: $('#vendor-name').val(),
        VendorNumber: $('#vendor-number').val(),
        InvoiceNumber: $('#invoice-number').val(),
        InvoiceDate: $('#invoice-date').val(),
        PONumber: $('#po-number').val(),
        Shipping: $('#shipping').val(),
        Discount: $('#global-disc').val(),
        Terms: $('#teams').val(),
        Total: $('#invoice-total').val(),
        Tax: $('#tax-1').val(),
        FileInfoID: $('#physical-file-id').val(),
        ListInvoices: JSON.stringify(listInvoice)
    };
    var data = ocrCtrl.updateInvoiceDetail(model);
    $.when(data).then(function(result){
        var dataResult = JSON.parse(result);
        $('#invoice-info-id').val(dataResult.invoiceInfoId)
    });
});

$(document).on('click', '#process-list-item', function (event) {
    if(possitionListInvoice == undefined || possitionListInvoice == null || possitionListInvoice.length == 0) return;
    var titles = $('#list-invoice-title .binding-data');
    var ListOcrValueTitle = [];
    var ListOcrValueFristRow = [];
    for(var i = 0; i < titles.length; i ++){
        var thisTitle = $(titles[i]);
        var ocrValueTitle = JSON.parse($(thisTitle).attr('data-ocr'));
        ListOcrValueTitle.push(ocrValueTitle);
        
        var thisFirstRow = $('#list-invoices-data .first-row').eq(i);
        var ocrValueFristRow = JSON.parse($(thisFirstRow).attr('data-ocr'));
        ListOcrValueFristRow.push(ocrValueFristRow)
    }
    var model = {
        templateListCol: $('#column-number').val(),
        jsonFilePath: $('#json-file-path').val(),
        ListOcrValueTitle: JSON.stringify(ListOcrValueTitle),
        ListOcrValueFristRow: JSON.stringify(ListOcrValueFristRow),
    };
    
    var data = ocrCtrl.listInvoiceProcess(model);
    $.when(data).then(function(result){
        BindingListInvoiceItems(JSON.parse(result));
    });
});

$(document).on('change', '#template-option', function (event) {
    if(templateIdIsActive != parseInt($(this).val())) return;

});

$("#page-size").change(function() {
    $('#physical-file-id').val($('#page-size option:selected').val());
    $('#invoice-info-id').val(0);
    $('#json-file-path').val($('#page-size option:selected').attr('data-json-path'));
    var model = {
        filePath: $('#page-size option:selected').attr('data-file-path'),
        jsonFile: $('#page-size option:selected').attr('data-json-path'),
        templateId: parseInt($('#template-option').val())
    };
    var dataBinding = ocrCtrl.getInvoiceByPageIndex(model);
    $.when(dataBinding).then(function(result, textStatus, jqXHR){
        var data = JSON.parse(result);
        BindingDataInvoiceJson(data);
        $('#images').removeAttr("src").attr('src', "/OcrForm/" + data.FilePath);
        if(data.InvoiceListItem != null && data.InvoiceListItem.length > 0){
            BindingListInvoiceItems(data);
        }
        BindingCanvas();
    });
});

function bindingInvoiceInfo(){
    $('#list-invoice-title').html('');    
    $('#list-invoices-data').html('');
    $('.binding-data').val('');
    var id = parseInt($('#physical-file-id').val());
    var pdfId = parseInt($('#pdf-file-id').val());
    var templateId = parseInt($('#template-option').val());
    if(id == 0) {
        alert('You need upload file to server.')
        return;
    }
    var getData = ocrCtrl.getInvoiceInfo(id, templateId, pdfId);
    $.when(getData).then(function(result, textStatus, jqXHR){
        var data = JSON.parse(result);
        BindingDataInvoiceJson(data);
        $('#images').removeAttr("src").attr('src', "/OcrForm/" + data.FileInfos[0].PathName);
        $('#json-file-path').val(data.FileInfos[0].JsonFilePath);
        listFileInfos = data.FileInfos;
        if(listFileInfos.length > 1){
            $('#page-index').show();
            $('#page-index').removeClass('hidden');
            html = '';
            for(var i = 0; i < listFileInfos.length; i++){
                var item = listFileInfos[i];
                html += '<option data-file-path="' + item.PathName + '" data-json-path="'+ item.JsonFilePath +'" value="' + (i+1) + '">Page ' + (i+1) + '</option>'
            }
            $('#page-size').html('');
            $('#page-size').html(html);
        }
        if(data.InvoiceListItem != null && data.InvoiceListItem.length > 0){
            BindingListInvoiceItems(data);
        }
        BindingCanvas();
    });
}

function BindingDataInvoiceJson(data){
    invoiceDetail = data.InvoiceInfo;
    $('#vendor-name').val(data.InvoiceInfo.VendorName.value);
    $('#vendor-number').val(data.InvoiceInfo.VendorNumber.value);
    $('#invoice-number').val(data.InvoiceInfo.InvoiceID.value);
    $('#invoice-date').val(data.InvoiceInfo.InvoiceDate.value);
    $('#po-number').val(data.InvoiceInfo.PONumber.value);
    $('#shipping').val(data.InvoiceInfo.Shipping.value);
    $('#global-disc').val(data.InvoiceInfo.Discount.value);
    $('#teams').val(data.InvoiceInfo.Terms.value);
    $('#invoice-total').val(data.InvoiceInfo.Total.value);
    $('#tax-1').val(data.InvoiceInfo.TotalTax.value);

    $('#vendor-name-text').val(data.InvoiceInfo.VendorName.label);
    $('#vendor-number-text').val(data.InvoiceInfo.VendorNumber.label);
    $('#invoice-number-text').val(data.InvoiceInfo.InvoiceID.label);
    $('#invoice-date-text').val(data.InvoiceInfo.InvoiceDate.label);
    $('#po-number-text').val(data.InvoiceInfo.PONumber.label);
    $('#shipping-text').val(data.InvoiceInfo.Shipping.label);
    $('#global-disc-text').val(data.InvoiceInfo.Discount.label);
    $('#teams-text').val(data.InvoiceInfo.Terms.label);
    $('#invoice-total-text').val(data.InvoiceInfo.Total.label);
    $('#tax-1-text').val(data.InvoiceInfo.TotalTax.label);
}

function BindingListInvoiceItems(data){
    var array = data.InvoiceListItem;
    listInvoiceItem = array;
    var title = array[0];
    if($('.title-header').length <= 0|| $('.title-header').eq(0).val() == undefined || $('.title-header').eq(0).val() == '' ){
        var htmlTitle = '';
        var htmlListItems = '';
        htmlListItems += '<tr>';
        var firstRow = array[1];
        for(var i = 0; i < title.length; i ++){
            var titleText = title[i].trim();
            var firstRowText = firstRow[i];
            htmlTitle += "<td><div class='wrapper'><div class='wysiwyg'><textarea data-ocr='' data-position='' class='title-header binding-data' style='background-color: #87CEEB;'>" + titleText + "</textarea></div></div></td>";
            htmlListItems += "<td><div class='wrapper'><div class='wysiwyg'><textarea data-ocr='' data-position='' class='first-row binding-data' style='resize:both;overflow:auto;'>" + firstRowText + "</textarea></div></div></td>";
        }
        htmlListItems += '</tr>';
        $('#list-invoice-title').html('');    
        $('#list-invoice-title').html(htmlTitle);
        $('#list-invoices-data').html('');
        $('#list-invoices-data').html(htmlListItems);
        
        var titleData = data.TitleData;
        var firstRowDatas = data.FirstRowData;
        for(var i = 0; i < title.length; i ++){
            var titleOcr = titleData[i];
            var titleJson = JSON.parse(titleOcr);
            var positionTitle = [];
            positionTitle.push(titleJson.P1);
            positionTitle.push(titleJson.P2);
            positionTitle.push(titleJson.P3);
            positionTitle.push(titleJson.P4);
            
            var firstRowOcr = firstRowDatas[i];
            var firstJson = JSON.parse(firstRowOcr);
            var positionFisrt = [];
            positionFisrt.push(firstJson.P1);
            positionFisrt.push(firstJson.P2);
            positionFisrt.push(firstJson.P3);
            positionFisrt.push(firstJson.P4);
            
            $('.title-header').eq(i).attr('data-ocr', JSON.stringify(titleJson));
            $('.title-header').eq(i).attr('data-position', JSON.stringify(positionTitle));
            
            $('.first-row').eq(i).attr('data-ocr', JSON.stringify(firstJson));
            $('.first-row').eq(i).attr('data-position', JSON.stringify(positionFisrt));
        }
    }
    var htmlListItems = '';
    for(var i = 1; i < array.length; i++){
        var item = array[i];
        htmlListItems += "<tr>";
        for(var j = 0; j < item.length; j++){
            var data = item[j].trim();
            var id = title[j].trim().replace(/ /g, '_');
            id = id.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "_");
            if(i == 1){
                var firstCtrl = $('#list-invoices-data .first-row').eq(j);
                $(firstCtrl).attr('id', id +'-'+ i +'-'+ j);
                htmlListItems += '<td><div class="wrapper"><div class="wysiwyg">';
                htmlListItems += $($($('#list-invoices-data .first-row').eq(j)).parent()).html();
                htmlListItems += '</div></div></td>';
            }else{
                htmlListItems += '<td><div class="wrapper"><div class="wysiwyg"><textarea data-position="" binding-data" value="'
                        + data +'" id="'+ id +'-'+ i +'-'+ j +'">' + data + '</textarea></div></div></td>';
            }
        }
        htmlListItems += "</tr>";
    }
    $('#list-invoices-data').html('');
    $('#list-invoices-data').append(htmlListItems);
}

function getListInvoiceItem(){
    var title = listInvoiceItem[0];
    var dataResult = [];
    for(var i = 1; i < listInvoiceItem.length; i++){
        var item = listInvoiceItem[i];
        var dataIndex = {ItemId: i, ListKey:[]}
        for(var j = 0; j < item.length; j++){
            var id = title[j].trim().replace(/ /g, '_');
            id = id.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "_");
            var dataCtrl = $("#" + id + "-" + i + "-" + j ).val();
            dataIndex.ListKey.push({Key: title[j], Value: dataCtrl});
        }
        dataResult.push(dataIndex);
    }
    return dataResult;
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
    templateDetails.push({label: invoiceDetail.VendorName.label, 
        type: invoiceDetail.VendorName.type,
        vertycal: invoiceDetail.VendorName.vertycal, 
        index: invoiceDetail.VendorName.index});
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
    templateDetails.push({label: invoiceDetail.PONumber.label, 
        type: invoiceDetail.PONumber.type,
        vertycal: invoiceDetail.PONumber.vertycal, 
        index: invoiceDetail.PONumber.index});
    templateDetails.push({label: invoiceDetail.Shipping.label, 
        type: invoiceDetail.Shipping.type,
        vertycal: invoiceDetail.Shipping.vertycal, 
        index: invoiceDetail.Shipping.index});
    templateDetails.push({label: invoiceDetail.Discount.label, 
        type: invoiceDetail.Discount.type,
        vertycal: invoiceDetail.Discount.vertycal, 
        index: invoiceDetail.Discount.index});
    templateDetails.push({label: invoiceDetail.Terms.label, 
        type: invoiceDetail.Terms.type,
        vertycal: invoiceDetail.Terms.vertycal, 
        index: invoiceDetail.Terms.index});
    templateDetails.push({label: invoiceDetail.Total.label, 
        type: invoiceDetail.Total.type,
        vertycal: invoiceDetail.Total.vertycal, 
        index: invoiceDetail.Total.index});
    templateDetails.push({label: invoiceDetail.TotalTax.label, 
        type: invoiceDetail.TotalTax.type,
        vertycal: invoiceDetail.TotalTax.vertycal, 
        index: invoiceDetail.TotalTax.index});
    
    var colNumber = $('#column-number').val();
    var ListOcrValueTitle = [];
    var ListOcrValueFristRow = [];
    if(colNumber > 0 && colNumber != '' && colNumber != undefined){
        var titles = $('#list-invoice-title .binding-data');
        for(var i = 0; i < titles.length; i ++){
            var thisTitle = $(titles[i]);
            var ocrValueTitle = JSON.parse($(thisTitle).attr('data-ocr'));
            ListOcrValueTitle.push(ocrValueTitle);

            var thisFirstRow = $('#list-invoices-data .first-row').eq(i);
            var ocrValueFristRow = JSON.parse($(thisFirstRow).attr('data-ocr'));
            ListOcrValueFristRow.push(ocrValueFristRow)
        }
    }
    else{
        colNumber = 0;
    }
    
    var model = {
        physicalFileId: id,
        templateName: $('#template-name').val(),
        templateDetail: JSON.stringify(templateDetails),
        templateListCol: colNumber,
        ListOcrValueTitle: JSON.stringify(ListOcrValueTitle),
        ListOcrValueFristRow: JSON.stringify(ListOcrValueFristRow),
    };
    
    var data = ocrCtrl.createTemplate(model);
    $.when(data).then(function(result){
        $('#createTemplate').modal('toggle');
        bindingTemplates(result);
    });
}
