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
    <div class="row">
        <div class="col-sm-12">
            <h1 class="page-header">Forms</h1>
        </div>
    </div>
</div>
    
<div id="createTemplate" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Create Template</h4>
            </div>
            <div class="modal-body from">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Vendor Name: </label>
                    </div>
                    <div class="form-group">
                        <label>Vendor Number: </label>
                    </div>
                    <div class="form-group">
                        <label>Invoice Number: </label>
                    </div>
                    <div class="form-group">
                        <label>Invoice Date: </label>
                    </div>
                    <div class="form-group">
                        <label>PO Number: </label>
                    </div>
                    <div class="form-group">
                        <label>Tax: </label>
                    </div>
                    
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Other: </label>
                    </div>
                    <div class="form-group">
                        <label>Shipping: </label>
                    </div>
                    <div class="form-group">
                        <label>Invoice Total: </label>
                    </div>
                    <div class="form-group">
                        <label>Teams: </label>
                    </div>
                    <div class="form-group">
                        <label>Global Disc: </label>
                    </div>
                </div>
                <div class="col-sm-12">
                    
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" onclick="createTemplate()">Save</button>
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