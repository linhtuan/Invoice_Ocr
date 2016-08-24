<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to Invoice Auto mapping</title>
        
        <link rel="stylesheet" type = "text/css"  href="/OcrForm/css/ds.demo.css">
	<link rel = "stylesheet" type = "text/css" href = "/OcrForm/css/bootstrap.min.css">
        <link rel="stylesheet" type = "text/css"  href="/OcrForm/css/app.css">
</head>
<body  class="ds-demo-main">
<div id="container" class="navbar navbar-default navbar-static-top">
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav in" id="side-menu">
                    <li>
                        <a href="/OcrForm/" class="active" style="display: inline-block"><i class="fa fa-dashboard fa-fw"></i> Invoice</a>
                        <a href="/OcrForm/index.php/InvoiceDetail" class="active" style="display: inline-block"><i class="fa fa-dashboard fa-fw"></i> Invoice Detail</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="row">
        <div class="col-sm-12" id="image">
            
        </div>
    </div>
</div>
    
<script id="imageTemplate" type="text/x-jquery-tmpl">
    <div class="col-sm-2" style="margin-top: 20px">
        <img data-id="${ID}" onclick="bindingInvoiceDetail(${ID})" src="/OcrForm/${PathName}" style="width: 100%;">
    </div>
</script>

<script id="invoice-list" type="text/x-jquery-tmpl">
    
</script>
    
<div id="invoice-detail-popup" class="modal fade" role="dialog" style="width: 1500px">
    <div class="modal-dialog" style="width: 800px">
      <!-- Modal content-->
      <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Template</h4>
            </div>
            <div class="modal-body from col-sm-6">
                <div class="form-group">
                    <label>Vendor Name: </label><label id="vendor-name"></label>
                </div>
                <div class="form-group">
                    <label>Invoice Number: </label><label id="invoice-number"></label>
                </div>
                <div class="form-group">
                    <label>PO Number: </label><label id="po-number"></label>
                </div>
                <div class="form-group">
                    <label>Other: </label><label id="other"></label>
                </div>
                <div class="form-group">
                    <label>Invoice Total: </label><label id="invoice-total"></label>
                </div>
                <div class="form-group">
                    <label>Global Disc: </label><label id="global-disc"></label>
                </div>
            </div>
            <div class="modal-body from col-sm-6">
                <div class="form-group">
                   <label>Vendor Number: </label><label id="vendor-number"></label>
                </div>
                <div class="form-group">
                    <label>Invoice Date: </label><label id="invoice-date"></label>
                </div>
                <div class="form-group">
                    <label>Tax: </label><label id="tax"></label>
                </div>
                <div class="form-group">
                    <label>Shipping: </label><label id="shipping"></label>
                </div>
                <div class="form-group">
                    <label>Teams: </label><label id="teams"></label>
                </div>
            </div>
            <div class="modal-body from col-sm-12">
                <div class="form-group">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                           <table class="table table-striped table-bordered table-hover" id="list-invoice">
                               <thead>
                                    <tr id="list-invoice-title">
                                    </tr>
                               </thead>
                               <tbody id="list-invoices-data">
                               </tbody>
                           </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
      </div>
    </div>
</div>

<script type = 'text/javascript' src = "/OcrForm/js/jquery-2.1.4.min.js"></script>
<script type = 'text/javascript' src = "/OcrForm/js/jquery.tmpl.js"></script>
<script type = 'text/javascript' src = "/OcrForm/js/bootstrap.min.js"></script>
<script type = 'text/javascript' src = "/OcrForm/js/invoice-detail.js"></script>

</body>
</html>