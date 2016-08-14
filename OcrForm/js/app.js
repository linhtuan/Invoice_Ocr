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
    
    return {
        bindingInput: function (dataObj, id){
            return bindingInput(dataObj, id);
        },
        getInvoiceData: function(){
            return getInvoiceData();
        },
        getDataInPositions: function(listPositions){
            return getDataInPositions(listPositions);
        }
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

