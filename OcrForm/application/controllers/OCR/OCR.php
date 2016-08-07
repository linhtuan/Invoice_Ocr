<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OCR
 *
 * @author thienanh
 */

include_once 'creds.php'; // Get $bucket
use google\appengine\api\cloud_storage\CloudStorageTools;

$options = ['gs_bucket_name' => $bucket];
$upload_url = CloudStorageTools::createUploadUrl('/process.php', $options);


