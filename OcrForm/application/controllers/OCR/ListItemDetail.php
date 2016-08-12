<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'KeyValue.php';
include_once 'OCRProcess.php';
class ItemDetail{
    public $ID;
    public $POLineNumber;
    public $ItemID;
    public $ItemDesc;
    public $Qty;
    public $Price;
    public $UOM;
    public $UOMNumber;
    public $Discount;
    public $Mutilplier;
    public $LineTotal;
}
class ListItemDetail {
    var $OCRArray = array(); // Check if the point sits exactly on one of the vertices?
    var $keyArray = array();
    function ListItemDetail($inOCRArray) {
        $OCRArray =$inOCRArray;
    }
    
    function LoadKeyArray()
    {
        $keyArray = array('');
    }
    
    function GetFirstItemByKey($key,$isNumber)
    {
        $labelItems = new OCRValue();
        $result =new KeyValue();
        foreach ($OCRArray as $itemOCR)
        {
             if(strcasecmp($key,str_replace(array('#', '.', ':'), '',$itemOCR->description))==0)
             {
                 $labelItems = $itemOCR;
                 break;
             }
        }
        
    }
}