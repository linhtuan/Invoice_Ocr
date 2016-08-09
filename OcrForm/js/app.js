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
            //dataType: 'json',
            type: 'POST',
            //contentType: 'application/json; charset=utf-8',
            //data: JSON.stringify({ batchId: rtb.getQueryVariable('id') })
        });
    };
    
    return {
        bindingInput: function (dataObj, id){
            return bindingInput(dataObj, id);
        },
        getInvoiceData: function(){
            return getInvoiceData();
        }
    };
}(ocrCtrl);


$(document).on('click', '.data-binding', function (event) {
   var id = $(this).attr('data-id');
   var dataObj = $(this).text();
   
   ocrCtrl.bindingInput(dataObj, id);
});
var obj;
$(document).on('click', '#loadInvoice', function (event) {
   var invoiceData = ocrCtrl.getInvoiceData();
   $.when(invoiceData).then(function (reuslt) {
       var obj = JSON.parse(reuslt);
       $('#invoiceInfoTemplate').tmpl(obj.InvoiceInfo).appendTo('#hearder');
       $('#invoiceListTemplate').tmpl(obj).appendTo('#bodyInvoice');
       $('#invoiceFooterTemplate').tmpl(obj.InvoiceInfo).appendTo('#footer');
   });
});

