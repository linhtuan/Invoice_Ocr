/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var detailCtrl = function(){
    
    var getImage = function(){
        return $.ajax({
            url: '/OcrForm/index.php/InvoiceDetail/GetListImage',
            type: 'POST',
        });
    };
    
    var getInvoiceDetail = function(model){
        return $.ajax({
            url: '/OcrForm/index.php/InvoiceDetail/GetInvoicePage',
            type: 'POST',
            data: model
        });
    };
    
    return {
        getImage: function (){
            return getImage();
        },
        getInvoiceDetail: function (model){
            return getInvoiceDetail(model);
        },
    };
    
}(detailCtrl);

function getImageList(){
    var images = detailCtrl.getImage();
    $.when(images).then(function(result, textStatus, jqXHR){
        var data = JSON.parse(result);
        $('#imageTemplate').tmpl(data).appendTo('#image');
    });
}

function bindingInvoiceDetail(id){
    var model = {physicalFileId: id};
    var detail = detailCtrl.getInvoiceDetail(model);
    $.when(detail).then(function(result, textStatus, jqXHR){
        var data = JSON.parse(result);
        var firstRowListInvoice =  $.grep(data.InvoiceList, function (e) { return e.ItemID == 1; });
        var htmlTitles = '';
        for(var i = 0 ; i < firstRowListInvoice.length; i++){
            var item = firstRowListInvoice[i];
            htmlTitles += '<th>' + item.Key + '</th>'
        }
        $('#list-invoice-title').html(htmlTitles);
        var rowIndex = 0;
        var htmlListItems = '';
        for(var i = 0; i < data.InvoiceList.length; i++){
            var item = data.InvoiceList[i];
            if(item.ItemID != rowIndex){
                htmlListItems += "<tr>";
            }
            htmlListItems += '<td>'+ item.Value +'</td>';
            rowIndex = item.ItemID;
            if(item.ItemID != rowIndex){
                htmlListItems += "</tr>";
            }
        }
        $('#list-invoices-data').html(htmlListItems);
        
        
        $('#vendor-name').html(data.InvoiceDetail.VendorName);
        //$('#vendor-number').html(data.InvoiceDetail.VendorNumber.value);
        $('#other').html(data.InvoiceDetail.Other);
        $('#invoice-number').html(data.InvoiceDetail.InvoiceNumber);
        $('#invoice-date').html(data.InvoiceDetail.Date);
        $('#po-number').html(data.InvoiceDetail.PONumber);
        $('#shipping').html(data.InvoiceDetail.Shipping);
        $('#global-disc').html(data.InvoiceDetail.Discount);
        $('#teams').html(data.InvoiceDetail.Terms);
        $('#invoice-total').html(data.InvoiceDetail.Total);
        $('#tax').html(data.InvoiceDetail.Tax);
        
        $('#invoice-detail-popup').modal('toggle');
    });
}

$(document).ready(function() {
    getImageList();
});

