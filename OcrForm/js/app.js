var dataBinding = {"data":[
        {
            "InvoiceId": "1234445",
            "Amount_Due": "867.78",
            "Account_Number": "21079",
            "Telephone": "(716) 956-2155"
        }
]};

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
    
    var getInvoiceInfo = function(){
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/getvalueinjson',
            type: 'POST',
        });
    };
    
    var getDataInPositions = function(listPositions){
        var jsonData = JSON.stringify(listPositions);
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/getdatainpositions',
            type: 'POST',
            data: { 
                data : jsonData 
            }
        });
    };
    
    var updateInvoiceDetail = function(model){
        var jsonData = JSON.stringify(model);
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/updateinvoicedetail',
            type: 'POST',
            data: { 
                data : jsonData 
            }
        });
    };
    
    var updateInvoiceListItems = function(model){
        var jsonData = JSON.stringify(model);
        return $.ajax({
            url: 'http://localhost:8080/OcrForm/index.php/invoice/updateinvoicelistitem',
            type: 'POST',
            data: { 
                data : jsonData 
            }
        });
    }
    
    return {
        bindingInput: function (dataObj, id){
            return bindingInput(dataObj, id);
        },
        getInvoiceData: function(){
            return getInvoiceData();
        },
        getInvoiceInfo: function(){
            return getInvoiceInfo();
        },
        getDataInPositions: function(listPositions){
            return getDataInPositions(listPositions);
        },
        updateInvoiceDetail: function(model){
            return updateInvoiceDetail(model);
        },
        updateInvoiceListItems: function(model){
            return updateInvoiceListItems(model);
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
    
});

$(document).on('click', '#update-list-item', function (event) {
    
});

function bindingInvoiceInfo(){
    var getData = ocrCtrl.getInvoiceInfo();
    $.when(getData).then(function(result, textStatus, jqXHR){
        var data = JSON.parse(result);
        $('#invoice-date').val(data.InvoiceDate.value);
        $('#vender-number').val(data.VendorNumber.value);
        $('#invoice-number').val(data.InvoiceID.value);
        $('#teams').val(data.Terms.value);
        $('#invoice-total').val(data.Total.value);
    });
}

