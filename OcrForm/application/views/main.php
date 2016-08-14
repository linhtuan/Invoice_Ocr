<?php
defined('BASEPATH') OR exit('No direct script access allowed');

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to Invoice Auto mapping</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }
        
	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	#body {
		margin: 0 15px 0 15px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}
	</style>
        
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
				<input type="radio" value="jpg" name="ImageType" class="ds-demo-radio" checked="checked" />
				<label style="position:relative;top:6px;">JPEG</label>
				<input type="radio" value="tif" name="ImageType" class="ds-demo-radio ds-demo-margin-left" />
				<label style="position:relative;top:6px;">TIFF</label>
				<input type="radio" value="pdf" name="ImageType" class="ds-demo-radio ds-demo-margin-left" />
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
    </div>
    
    <div class="row">
        <div class="col-sm-6">
            <div class="col-sm-6">
                
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Customer templates</label>
                    <select class="form-control">
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

                </div>
            </div>
            
            <div class="col-sm-3">
                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="loadInvoice">Load Invoice</button>
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
                            <div class="col-sm-6"><h4>Invoice header detail</h4></div>
                            <div class="col-sm-6  text-right">
                                <button type="button" class="btn btn-primary" id="clear-active">Clear Active</button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6 from">
                                <div class="form-group">
                                    <label>Vender Name</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>Vender Number</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>Invoice Number</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>Invoice Date</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>PO Number</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>Tax 1</label>
                                    <input class="form-control binding-data">
                                </div>
                            </div>
                            <div class="col-sm-6 from">
                                <div class="form-group">
                                    <label>Other</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>Shipping</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>Invoice Total</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>Teams</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>Global Disc %</label>
                                    <input class="form-control binding-data">
                                </div>
                                <div class="form-group">
                                    <label>Global Disc %</label>
                                    <input class="form-control binding-data">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-heading"><h4>List Item</h4></div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-6 from">
                                <div class="form-group">
                                    <label>Item Id</label>
                                    <input class="form-control binding-data">
                                </div>
                            </div>
                            <div class="col-sm-6 from">
                                <div class="form-group">
                                    <label>Item description</label>
                                    <input class="form-control binding-data">
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
                                    <tbody>
                                        <tr>
                                            <td><input class="form-control bingind-data"></td>
                                            <td><input class="form-control bingind-data"></td>
                                            <td><input class="form-control bingind-data"></td>
                                            <td><input class="form-control bingind-data"></td>
                                            <td><input class="form-control bingind-data"></td>
                                            <td><input class="form-control bingind-data"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 panel" >
            <div class="col-sm-12 panel panel-default">
                <div class="col-sm-12">
                    <div class="col-sm-12" style="min-height: 50px">
                    </div>
                </div>
                <div class="row" id="hearder">
                    
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="col-sm-9"></div>
                        <div class="col-sm-3">
                            <h3 class="">Invoice</h3>
                        </div>
                    </div>
                </div>
                <div class="row" id="bodyInvoice">
                    
                </div>
                <div class="row" id="footer">
                    
                </div>
            </div>
            <div class="col-sm-12" id="invoiceImage">
                <img id="images" src="http://localhost:8080/OcrForm/image/2-2.jpg" class="hidden">
                <canvas id="canvas" style="border: 1px solid black; cursor: pointer;"></canvas>
            </div>
        </div>
        <div class="col-sm-12 text-center">
            <button type="button" class="btn btn-primary" id="update">Save Invoice</button>
        </div>
    </div>
    
</div>

<script id="invoiceInfoTemplate" type="text/x-jquery-tmpl">
    <div class="col-sm-4">
        <div class="col-sm-12">
        ${VendorName}
        </div>
        <div class="col-sm-12">
            ${VendorAddress}
        </div> 
    </div>
    <div class="col-sm-8">
        <div class="col-sm-12">
            <div class="row panel panel-default">
                 <div class="col-sm-12">
                    <div class="col-sm-6">Invoice No:</div>
                    <div class="col-sm-6 text-right">${InvoiceNumber}</div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-6">Amount Due:</div>
                    <div class="col-sm-6 text-right">${InvoiceNumber}</div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-7">Account number:</div>
                    <div class="col-sm-5 text-right">21079</div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-6">Telephone:</div>
                    <div class="col-sm-6 text-right">(716) 956-2155</div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-6">Page 1 of 1</div>
                </div>
            </div>
        </div>
    </div>
</script>

<script id="invoiceListTemplate" type="text/x-jquery-tmpl">
<div class="row">
    <div class="col-sm-12">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>QTY</th>
                    <th>UM</th>
                    <th>Discount</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                {{each InvoiceListItem}}
                <tr>
                    <td>${$value.Description}</td>
                    <td>${$value.QTY}</td>
                    <td>${$value.UM}</td>
                    <td>${$value.Discount}</td>
                    <td>${$value.Total}</td>
                </tr>
                {{/each}}
            </tbody>
        </table>   
    </div>
</div>
</script>

<script id="invoiceFooterTemplate" type="text/x-jquery-tmpl">
<div class="col-sm-4">
    <div class="col-sm-12">TOTAL</div>
    <div class="col-sm-12">FREIGHT IN</div>
    <div class="col-sm-12">TAXED</div>
    <div class="col-sm-12">INVOICE TOTAL</div>
</div>
<div class="col-sm-4">
    <div class="col-sm-12 text-right">${Total}</div>
    <div class="col-sm-12 text-right">2</div>
    <div class="col-sm-12 text-right">${Tax}</div>
    <div class="col-sm-12 text-right">1</div>
</div>
</script>

<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/jquery-2.1.4.min.js"></script>
<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/jquery.tmpl.js"></script>
<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/bootstrap.min.js"></script>
<script src="http://localhost:8080/OcrForm/Resources/dynamsoft.webtwain.config.js"></script>
<script src="http://localhost:8080/OcrForm/Resources/dynamsoft.webtwain.initiate.js"></script>
<script src="http://localhost:8080/OcrForm/Resources/addon/dynamsoft.webtwain.addon.pdf.js"></script>

<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/app.js"></script>
<script type = 'text/javascript' src = "http://localhost:8080/OcrForm/js/draw-regextag.js"></script>
</body>
</html>