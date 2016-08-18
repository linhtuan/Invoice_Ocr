<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to Invoice Auto mapping</title>
        
        <link rel="stylesheet" type = "text/css"  href="http://localhost:8080/OcrForm/css/ds.demo.css">
	<link rel = "stylesheet" type = "text/css" href = "http://localhost:8080/OcrForm/css/bootstrap.min.css">
        <link rel="stylesheet" type = "text/css"  href="http://localhost:8080/OcrForm/css/app.css">
</head>
<body  class="ds-demo-main">
<div id="container" class="navbar navbar-default navbar-static-top">
    <div class="row">
            <div class="col-sm-12">
                <h1 class="page-header">Forms</h1>
            </div>
    </div>
    <div class="row">
        <div class="ds-demo-container ds-demo-padding-top-large ds-demo-center">
                <select size="1" id="source" style="position: relative; width: 220px;"></select>
            <div id="divLeft" class="ds-demo-left ds-demo-inline-block ds-demo-border-light-grey" style="width: 508px; height: 650px;">
                <div id="dwtcontrolContainer"></div>
            </div>
            <div class="ds-demo-left ds-demo-bright-grey ds-demo-inline-block ds-demo-padding-large ds-demo-center ds-demo-border-light-grey" style="width: 470px; height: 650px;">
                <input type="button" value="Scan" onclick="AcquireImage();" class="ds-demo-btn ds-demo-width-92 ds-demo-margin-left-large" />
                <input type="button" value="Open a local file" onclick="LoadImages();" class="ds-demo-btn ds-demo-width-168 ds-demo-margin-left-large" />
                <div class="ds-demo-margin" id="div-extra-fields" style="display:none; max-height:140px;">
                    <div class="div-fields-item ds-demo-margin-bottom">
                        <input type="text" class="ds-demo-txt ds-demo-margin-zero" style="width: 127px;" placeholder="Field Name" /> : 
                        <input type="text" class="ds-demo-txt ds-demo-width-168 ds-demo-margin-zero" style="margin-left:10px!important;" placeholder="Field Value" />
                    </div>
                </div>
                <div class="ds-demo-margin">
                    <input type="radio" value="jpg" name="ImageType" id="imgTypejpeg" class="ds-demo-radio" checked="checked" />
                    <label style="position:relative;top:6px;">JPEG</label>
                    <input type="radio" value="pdf" name="ImageType" id="imgTypepdf" class="ds-demo-radio ds-demo-margin-left" />
                    <label style="position:relative;top:6px;">PDF</label><br />	<br />
                    <input type="checkbox" value="quiet" class="ds-demo-check" id="quietScan" /><label style="position:relative;top:6px;">Quiet</label>
                    <span id='quietTip' class="ds-demo-cursor-help ds-demo-badge ds-demo-green ds-demo-teal">?</span> 
                    <input type="checkbox" value="directly" class="ds-demo-check" id="uploadDirectly" /><label style="position:relative;top:6px;">Direct</label>
                    <span id='directTip' class="ds-demo-cursor-help ds-demo-badge ds-demo-green ds-demo-teal">?</span><br />
                    <div id='quietTip-div' style='display:none; z-index:100; position:absolute; width:300px;' class="ds-demo-box-shadow ds-demo-light-grey ds-demo-radius-small">Upload without showing progress bar.</div>
                    <div id='directTip-div' style='display:none; z-index:100; position:absolute; width:300px;' class="ds-demo-box-shadow ds-demo-light-grey ds-demo-radius-small">Upload any file without loading it.</div>
                    <input type="button" value="Upload" onclick="UploadImage();" class="ds-demo-blue ds-demo-btn-large ds-demo-border-0 ds-demo-margin" />			
                </div>			
                <hr>	
            </div>
        </div>
    </div>
            </div>
<div class="row">
        <div class="col-sm-6">
            <div class="col-sm-6">
                
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Customer templates</label>
                    <select class="form-control">
                        <option>--- Seleted template ---</option>
                        <option>1</option>
                        <option>2</option>
                        <option>3</option>
                        <option>4</option>
                        <option>5</option>
                    </select>
                </div>
            </div>

        </div>
        <div class="col-sm-6">
            <div class="col-sm-3">
                <div class="form-group">
                    <label>Resize Image</label>
                    <select class="form-control" id="imagesize">
                        <option value="100">100%</option>
                        <option value="60">60%</option>
                        <option value="50">50%</option>
                        <option value="40">40%</option>
                        <option value="30">30%</option>
                        <option value="20">20%</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label>Action</label>
                    <button type="button" class="form-control btn btn-primary" onclick="bindingInvoiceInfo();" id="loadInvoice">Process</button>
                </div>
            </div>
        </div>
    </div>
<div id="body" class="row"> 
    <div class="col-sm-6">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-sm-6"><h5>Invoice header detail</h5></div>
                        <div class="col-sm-6  text-right">
                            <button type="button" class="btn btn-primary" id="update-invoice-detail">Save Invoice Detail</button>
                            <button type="button" class="btn btn-primary clear-active">Clear Active</button>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6 from">
                            <div class="form-group">
                                <label>Vender Name</label>
                                <input class="form-control binding-data" id="vender-name">
                            </div>
                            <div class="form-group">
                                <label>Vender Number</label>
                                <input class="form-control binding-data"  id="vender-number">
                            </div>
                            <div class="form-group">
                                <label>Invoice Number</label>
                                <input class="form-control binding-data" id="invoice-number">
                            </div>
                            <div class="form-group">
                                <label>Invoice Date</label>
                                <input class="form-control binding-data" id="invoice-date">
                            </div>
                            <div class="form-group">
                                <label>PO Number</label>
                                <input class="form-control binding-data" id="po-number">
                            </div>
                            <div class="form-group">
                                <label>Tax 1</label>
                                <input class="form-control binding-data" id="tax-1">
                            </div>
                        </div>
                        <div class="col-sm-6 from">
                            <div class="form-group">
                                <label>Other</label>
                                <input class="form-control binding-data" id="other">
                            </div>
                            <div class="form-group">
                                <label>Shipping</label>
                                <input class="form-control binding-data" id="shipping">
                            </div>
                            <div class="form-group">
                                <label>Invoice Total</label>
                                <input class="form-control binding-data" id="invoice-total">
                            </div>
                            <div class="form-group">
                                <label>Teams</label>
                                <input class="form-control binding-data" id="teams">
                            </div>
                            <div class="form-group">
                                <label>Global Disc %</label>
                                <input class="form-control binding-data" id="global-disc-1">
                            </div>
                            <div class="form-group">
                                <label>Global Disc %</label>
                                <input class="form-control binding-data" id="global-disc-2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    
                    <div class="row">
                        <div class="col-sm-6"><h5>List Item</h5></div>
                        <div class="col-sm-6  text-right">
                            <button type="button" class="btn btn-primary" id="update-list-item">Save List Item</button>
                            <button type="button" class="btn btn-primary clear-active">Clear Active</button>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-6 from">
                            <div class="form-group">
                                <label>Item Id</label>
                                <input class="form-control binding-data" id="item-id">
                            </div>
                        </div>
                        <div class="col-sm-6 from">
                            <div class="form-group">
                                <label>Item description</label>
                                <input class="form-control binding-data" id="item-description">
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-sm-12 table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>UOM</th>
                                        <th>Discount</th>
                                        <th>Multiplier</th>
                                        <th>Line Total</th>
                                    </tr>
                                </thead>
                                <tbody id="list-invoices-data">
<!--                                    <tr>
                                        <td><input class="form-control bingind-data" id="qty"></td>
                                        <td><input class="form-control bingind-data" id="price"></td>
                                        <td><input class="form-control bingind-data" id="uom"></td>
                                        <td><input class="form-control bingind-data" id="discount"></td>
                                        <td><input class="form-control bingind-data" id="multiplier"></td>
                                        <td><input class="form-control bingind-data" id="line-total"></td>
                                    </tr>-->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 panel" >
        <div class="col-sm-12" id="invoiceImage">
            <img id="images" src="" class="hidden">
            <canvas id="canvas" style="border: 1px solid black; cursor: pointer;"></canvas>
        </div>
    </div>
<!--    <div class="col-sm-12 text-center">
        <button type="button" class="btn btn-primary" id="update">Save Invoice</button>
    </div>-->

<input type="hidden" id="physical-file-id" value="0"/>
<input type="hidden" id="json-file-path" value=""/>
</div>

<script id="listInvoicesTemplate" type="text/x-jquery-tmpl">
    {{each InvoiceListItem}}
    <tr class="list-invoices" data-id="${$value.ID}">
        <td><input class="form-control bingind-data" value="${$value.QTY}" id="qty-${$value.ID}"></td>

        <td><input class="form-control bingind-data" value="${$value.UM}" id="uom-${$value.ID}"></td>
        <td><input class="form-control bingind-data" value="${$value.Discount}" id="discount-${$value.ID}"></td>
        <td><input class="form-control bingind-data" value="${$value}" id="multiplier-${$value.ID}"></td>
        <td><input class="form-control bingind-data" value="${$value.Total}" id="line-total-${$value.ID}"></td>
    </tr>
    {{/each}}
</script>

<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/jquery-2.1.4.min.js"></script>
<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/jquery.tmpl.js"></script>
<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/bootstrap.min.js"></script>
<script src="http://localhost:8080/OcrForm/Resources/dynamsoft.webtwain.config.js"></script>
<script src="http://localhost:8080/OcrForm/Resources/dynamsoft.webtwain.initiate.js"></script>
<script src="http://localhost:8080/OcrForm/Resources/addon/dynamsoft.webtwain.addon.pdf.js"></script>

<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/app.js"></script>
<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/draw-rectangle.js"></script>
<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/scanTwainImage.js"></script>
</body>
</html>