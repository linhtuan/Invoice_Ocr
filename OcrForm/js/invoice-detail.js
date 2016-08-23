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
        console.log(data);
        var firstRowListInvoice =  $.grep(data.InvoiceList, function (e) { return e.ItemID === 1; });
        var title = [];
        for(var i = 0 ; i < firstRowListInvoice.length; i++){
            var item = firstRowListInvoice[i];
            title.push(item.Key);
        }
        var rowIndex = 0;
        var htmlListItems = '';
        for(var i = 0; i < data.InvoiceList.length; i++){
            var item = data.InvoiceList[i];
            if(item.ItemID != rowIndex){
                htmlListItems += "<tr>";
            }
            htmlListItems += '<td>'+ item.Value +'</td>';
            if(item.ItemID != rowIndex){
                htmlListItems += "</tr>";
            }
            rowIndex = item.ItemID;
        }
    });
}

$(document).ready(function() {
    getImageList();
});

