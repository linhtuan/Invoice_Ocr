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
        $('#invoice-detail-popup').modal('show');
    });
}

$(document).ready(function() {
    getImageList();
});

