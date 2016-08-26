<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'OCR/OCRProcess.php';
include_once 'OCR/Point.php';
include_once 'OCR/GGApi.php';
include_once 'OCR/ListItemDetail.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Invoice extends CI_Controller {
    
    public function GetInvoiceData(){
        $this->load->model('invoices_model');

        $query = $this->db->get('tbinvoiceinfo');
        $this->invoices_model->InvoiceInfo = $query->first_row();

        $id = $this->input->post('contactId');
        $array = array('InvoiceInfoID' => $this->invoices_model->InvoiceInfo->ID);
        $this->db->where($array);
        $queryInvoiceInfo = $this->db->get('tblistitem');
        $this->invoices_model->InvoiceListItem = $queryInvoiceInfo->result();
        $data = array(
            'InvoiceInfo' => $this->invoices_model->InvoiceInfo,
            'InvoiceListItem' => $this->invoices_model->InvoiceListItem
        );
        echo json_encode($data);
    }

    public function GetInvoiceInfo(){
        $query = array(
            'ID' => $this->input->post('physicalFileId')
        );
        $pdfId = $this->input->post('pdfId');
        $fileInfos;
        if($pdfId == 0){
            $fileInfos = $this->db->get_where('tbfileinfo', array('ID' => $this->input->post('physicalFileId')))->result();
        }
        else{
            $fileInfos = $this->db->get_where('tbfileinfo', array('PDFFileId' => $pdfId))->result();
        }
        
        $arrayFileInfos = array();
        $fistJson;
        $index = 1;
        foreach ($fileInfos as $row)
        {
            if($index == 1){
                $fistJson = $row->JsonFilePath;
            }
            $fileInfo = array(
                'FileInfoId' => $row->ID,
                'PathName' => $row->PathName,
                'JsonFilePath' => $row->JsonFilePath,
                'FileIndex' => $row->FileIndex,
            );
            array_push($arrayFileInfos, $fileInfo);
            $index++;
        }
        if (count($arrayFileInfos) > 0)
        {
            $s_Data = file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/OcrForm/'.$fistJson);
            $width=0;
            $height =0;
            $OCRArray = ParserJson2Object($s_Data, $width, $height);
            $anglePopular = AnglePopular($OCRArray);
            $OCRArray = MergerAllWordToLine($OCRArray,$anglePopular);
			
            $templateId = $this->input->post('templateId');
            if($templateId > 0)
            {
                $this->db->where(array('TemplateID' => $templateId));
                $templateList = $this->db->get('tbTemplateList')->first_row();
                $arrayResult = array();
                if($templateList != NULL){
                    $templateListKey = $templateList->Key;
                    $templateListCol = $templateList->ColumnNumber;
                    $cListItem = new ListItemDetail();
                    $cListItem->SetOcrArray($OCRArray);
                    $cListItem->SetAnglePopular($anglePopular);
                    $cListItem->SetWidth($width);
                    $cListItem->SetHeight($height);
                    $listRows = $cListItem->GetListItemByKey($templateListKey, $templateListCol);
                    for($i=0; $i < count($listRows);$i++)
                    {
                        $arrayItem = $listRows[$i];
                        $arrayDetil = array();
                        foreach($arrayItem as $item)
                        {
                            array_push($arrayDetil, $item);
                        }
                        array_push($arrayResult, $arrayDetil);
                    }
                }
                
                $this->db->where(array('TemplateID' => $templateId));
                $templateKey = $this->db->get('tbTemplatedetail')->result();
                $invoiceInfo = GetInvoiceInforByTemplate($OCRArray, $anglePopular, $templateKey);
                $data = array(
                    'InvoiceInfo' => $invoiceInfo,
                    'InvoiceListItem' => $arrayResult,
                    'FileInfos' => $arrayFileInfos,
                );
                echo json_encode($data);
            }else{
                
                $invoiceInfo = GetInvoiceInfor($OCRArray,$anglePopular);
                $data = array(
                    'InvoiceInfo' => $invoiceInfo,
                    'InvoiceListItem' => [],
                    'FileInfos' => $arrayFileInfos,
                );
                echo json_encode($data);
            }
        }
    }

    public function GetDataInPositions(){
        $data = json_decode(stripslashes($_POST['data']));
        $listPoint = array();
        $p1 = new Point();
        $p1->X = $data[0]->X;
        $p1->Y = $data[0]->Y;
        $listPoint[] = $p1;
        $p2 = new Point();
        $p2->X = $data[1]->X;
        $p2->Y = $data[1]->Y;
        $listPoint[] = $p2;
        $p3 = new Point();
        $p3->X = $data[2]->X;
        $p3->Y = $data[2]->Y;
        $listPoint[] = $p3;
        $p4 = new Point();
        $p4->X = $data[3]->X;
        $p4->Y = $data[3]->Y;
        $listPoint[] = $p4;
        
        $jsonFilePath = $this->input->post('jsonFilePath');
        $s_Data = file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/OcrForm/'.$jsonFilePath);
        $width=0;
        $height =0;
        $OCRArray = ParserJson2Object($s_Data,$width,$height);
        $anglePopular = AnglePopular($OCRArray);
        $str = GetTextByPolygon($listPoint,$OCRArray);
        $ocrData = GetListOCRValueByPolygon($listPoint,$OCRArray);
        
        $thisOcrData = array();
        $isActionFilter = $this->input->post('isFilter');
        if($isActionFilter == true){
            $gTitle1 = new GroupInItem();
            $gTitle1->P1 = new Point();
            $gTitle1->P1->X = $data[0]->X;
            $gTitle1->P1->Y = $data[0]->Y;
            $gTitle1->P2 = new Point();
            $gTitle1->P2->X = $data[1]->X;
            $gTitle1->P2->Y = $data[1]->Y;
            $gTitle1->P3 = new Point();
            $gTitle1->P3->X = $data[2]->X;
            $gTitle1->P3->Y = $data[2]->Y;
            $gTitle1->P4 = new Point();
            $gTitle1->P4->X = $data[3]->X;
            $gTitle1->P4->Y = $data[3]->Y;
            $listOCRValue = array();
            foreach ($ocrData as $item){
                $ocrValue = new OCRValue();
                $ocrValue->description = $item->description;
                array_push($listOCRValue, $ocrValue);
            }
            $gTitle1->listOCRValue = $listOCRValue;
            $thisOcrData = $gTitle1;
        }
        
        $result = array(
            'Text' => trim($str),
            'thisOcrData' => json_encode($thisOcrData),
        );
        echo json_encode($result);
    }
    
    public function upload_file()
    {
        $fileTempName = $_FILES['RemoteFile']['tmp_name'];	
        $fileSize = $_FILES['RemoteFile']['size'];
        $fileName = "UploadImage\\".$_FILES['RemoteFile']['name'];
        $fileName = iconv("UTF-8", "gb2312", $fileName);
        $fWriteHandle;
        if (file_exists($fileName)) {
            $fWriteHandle = fopen($fileName, 'w');
        } else {
            $fWriteHandle = fopen($fileName, 'w');
        }
        $fReadHandle = fopen($fileTempName, 'rb');
        $fileContent = fread($fReadHandle, $fileSize);
        fwrite($fWriteHandle, $fileContent);
        fclose($fWriteHandle);
        $info = new SplFileInfo($fileName);
        $fileType = $info->getExtension();
        
        if($fileType == "pdf"){
            $array = array(
                'PathName' => $fileName,
            );
            $this->db->set($array);
            $this->db->insert('tbpdffile');
            $pdfId = $this->db->insert_id();
            
            $fileBaseName = $info->getBasename('.' . $info->getExtension());
            $folderUpload = "UploadPDF\\".$fileBaseName;
            $ret = mkdir($folderUpload); 
            $string = "Magick\convert.exe -density 96 \"".$fileName."\" -resize 1700x2000 -quality 85 \"".$folderUpload."\\".$fileBaseName.".jpg\"";
            $str = exec($string);
            $files = scandir($folderUpload);
            $fileIndex = 1;
            if (is_dir($folderUpload)){
                if ($dh = opendir($folderUpload)){
                    while (($file = readdir($dh)) !== false){
                        if($file == null || $file == '' || $file == '.' || $file == '..') continue;
                        $fileNamePath = $folderUpload."\\".$file;
                        $jsonName = str_replace(".","_",$file).".json";
                        if($fileIndex == 1){
                            $ret = mkdir("JsonFile\\".$fileBaseName); 
                        }
                        $jsonPath = "JsonFile\\".$fileBaseName."\\".$jsonName;
                        $json = CallGGAPIForImage($fileNamePath);
                        file_put_contents($jsonPath, $json);
                        $array = array(
                            'PathName' => "UploadPDF/".$fileBaseName."/".$file,
                            'JsonFilePath' => "JsonFile/".$fileBaseName."/".$jsonName,
                            'FileIndex' => $fileIndex,
                            'PDFFileId' => $pdfId
                        );
                        $this->db->set($array);
                        $this->db->insert('tbfileinfo');

                        $fileIndex++;
                    }
                    closedir($dh);
                }
            }
        }
        else{
            //create file json of image
            $jsonName = str_replace(".","_",$_FILES['RemoteFile']['name']).".json";
            $jsonPath = "JsonFile\\".$jsonName;
            $json = CallGGAPIForImage($fileName);
            file_put_contents($jsonPath, $json);
            $array = array(
                'PathName' => "UploadImage/".$_FILES['RemoteFile']['name'],
                'JsonFilePath' => "JsonFile/".$jsonName
            );
            $this->db->set($array);
            $this->db->insert('tbfileinfo');
        }
        echo "";
    }
    
    public function GetPhysicalFileId()
    {
        $fileName = "UploadImage/".$this->input->post('physicalFileName');
        $info = new SplFileInfo("UploadImage\\".$this->input->post('physicalFileName'));
        $fileType = $info->getExtension();
        
        if($fileType == "pdf"){
            $name = $info->getBasename('.pdf');
            $this->db->select('*');
            $this->db->like('PathName', $name);
            $fileInfoId = $this->db->get('tbfileinfo')->first_row();
        }
        else{
            $query = array(
                'PathName' => $fileName
            );
            $this->db->select('*');
            $this->db->where($query);
            $fileInfoId = $this->db->get('tbfileinfo')->first_row();
        }
        
        echo json_encode($fileInfoId);
    }

    public function UpdateInvoice(){
        $invoiceInfoId = $this->input->post('InvoiceInfoId');
        $vendorName = $this->input->post('VendorName');
        $vendorNumber = $this->input->post('VendorNumber');
        $invoiceNumber = $this->input->post('InvoiceNumber');
        $invoiceDate = $this->input->post('InvoiceDate');
        $poNumber = $this->input->post('PONumber');
        $shipping = $this->input->post('Shipping');
        $discount = $this->input->post('Discount');
        $terms = $this->input->post('Terms');
        $total = $this->input->post('Total');
        $tax = $this->input->post('Tax');
        $fileInfoID = $this->input->post('FileInfoID');

        $data = array(
            'FileInfoID' => $fileInfoID,
            'VendorName' => $vendorName,
            'InvoiceNumber' => $invoiceNumber,
            'Date' => $invoiceDate,
            'PONumber' => $poNumber,
            'Tax' => $tax,
            'Shipping' => $shipping,
            'Discount' => $discount,
            'Total' => $total,
            'PONumber' => $poNumber,
        );
        $listInvoiceItems = json_decode(stripslashes($this->input->post('ListInvoices')));
        if($invoiceInfoId == NULL || $invoiceInfoId == '' || $invoiceInfoId == 0){
            $this->db->set($data);
            $this->db->insert('tbinvoiceinfo');
            $invoiceInfoId = $this->db->insert_id();
            if($listInvoiceItems != null && $listInvoiceItems != '')
            {
                $this->db->delete('tblistitemkeys', array('InvoiceInfoID' => $invoiceInfoId));
                $this->db->delete('tblistitem', array('InvoiceInfoID' => $invoiceInfoId));
                foreach ($listInvoiceItems as $item){
                    $this->db->set(array('InvoiceInfoID'=> $invoiceInfoId, 'ItemID' => $item->ItemId));
                    $this->db->insert('tblistitem');
                    $listItemId = $this->db->insert_id();
                    $itemKeys = array();
                    foreach ($item->ListKey as $itemKey){
                        $data = array(
                            'InvoiceInfoID' => $invoiceInfoId,
                            'ListItemId' => $listItemId,
                            'Key' => $itemKey->Key,
                            'Value' => $itemKey->Value
                        );
                        array_push($itemKeys, $data);
                    }
                    $this->db->insert_batch('tblistitemkeys', $itemKeys);
                }
                //InsertListInvoice($listInvoiceItems, $invoiceInfoId);
            }
        }
        else{
            $this->db->where('ID', $invoiceInfoId);
            $this->db->update('tbinvoiceinfo', $data);
            if($listInvoiceItems != null && $listInvoiceItems != '')
            {
                $this->db->delete('tblistitemkeys', array('InvoiceInfoID' => $invoiceInfoId));
                $this->db->delete('tblistitem', array('InvoiceInfoID' => $invoiceInfoId));
                foreach ($listInvoiceItems as $item){
                    $this->db->set(array('InvoiceInfoID'=> $invoiceInfoId, 'ItemID' => $item->ItemId));
                    $this->db->insert('tblistitem');
                    $listItemId = $this->db->insert_id();
                    $itemKeys = array();
                    foreach ($item->ListKey as $itemKey){
                        $data = array(
                            'InvoiceInfoID' => $invoiceInfoId,
                            'ListItemId' => $listItemId,
                            'Key' => $itemKey->Key,
                            'Value' => $itemKey->Value
                        );
                        array_push($itemKeys, $data);
                    }
                    $this->db->insert_batch('tblistitemkeys', $itemKeys);
                }
            }
        }
        $result = array(
            'invoiceInfoId' => $invoiceInfoId
        );
        echo json_encode($result);
    }
    
    public function InsertListInvoice($listInvoiceItems, $invoiceInfoId){
        
    }
    
    public function GetTemplate(){
        $query = $this->db->get('tbtemplate')->result();
        echo json_encode($query);
    }

    public function CreateTemplate(){
        
        //Insert Template
        $physicalFileId = $this->input->post('physicalFileId');
        $templateName = $this->input->post('templateName');
        $fileInfo = $this->db->get('tbfileinfo')->first_row();
        $arrayTemplate = array(
            'TemplateName' => $templateName,
            'ImageSampleLink' => $fileInfo->PathName
        );
        $this->db->set($arrayTemplate);
        $this->db->insert('tbtemplate');
        $templateId = $this->db->insert_id();
        
        //Insert Template Detail
        $data = array();
        $templateDetail = json_decode(stripslashes($this->input->post('templateDetail')));
        foreach ($templateDetail as $item){
            if($item->label != NULL && $item->label != '')
            {
                $dataItem = array(
                    'TemplateID' => $templateId,
                    'Keyword' => $item->label,
                    'Type' => $item->type,
                    'Vertical' => $item->vertycal,
                    'Index' => $item->index
                );
                array_push($data, $dataItem);
            }
        }
        $this->db->insert_batch('tbtemplatedetail', $data);
        
        //Insert Template List
        $templateListCol = $this->input->post('templateListCol');
        //echo $templateList;
        if($templateListCol != '' && $templateListCol != NULL && $templateListCol > 0)
        {
            $arrayTemplateList = array(
                'TemplateID' => $templateId,
                'Key' => $templateListKey,
                'ColumnNumber' => $templateListCol
            );
            $this->db->set($arrayTemplateList);
            $this->db->insert('tbTemplateList');
            $templateListId = $this->db->insert_id();
            
            $listOcrValueTitle = json_decode(stripslashes($this->input->post('ListOcrValueTitle')));
            $listOcrValueFristRow = json_decode(stripslashes($this->input->post('ListOcrValueFristRow')));
            $data_template_lists = array();
            for($i = 0; $i < count($listOcrValueTitle); $i++){
                $itemTitle = $listOcrValueTitle[i];
                $itemFirstRow = $listOcrValueFristRow[i];
                $data_template = array(
                    'TemplateID' => $templateId,
                    'TemplateListID' => $templateListId,
                    'OcrValueTitle' => json_encode($itemTitle),
                    'OcrValueFristRow' => json_encode($itemFirstRow),
                );
                array_push($data_template_lists, $data_template);
            }
            $this->db->insert_batch('tbtemplatelistkeyposition', $data_template_lists);
        }
        echo json_encode($templateId);
    }
    
    public function ListItemProcess(){
        $listOcrValueTitle = json_decode(stripslashes($this->input->post('ListOcrValueTitle')));
        $listOcrValueFristRow = json_decode(stripslashes($this->input->post('ListOcrValueFristRow')));
        $arrayGroupTitle = array();
        foreach ($listOcrValueTitle as $item){
            $gTitle = new GroupInItem();
            $gTitle->P1 = $item->P1;
            $gTitle->P2 = $item->P2;
            $gTitle->P3 = $item->P3;
            $gTitle->P4 = $item->P4;
            $gTitle->listOCRValue = $item->listOCRValue;
            array_push($arrayGroupTitle, $gTitle);
        }
        
        $arrayGroupFirst = array();
        foreach ($listOcrValueFristRow as $item){
            $gFirstRow = new GroupInItem();
            $gFirstRow->P1 = $item->P1;
            $gFirstRow->P2 = $item->P2;
            $gFirstRow->P3 = $item->P3;
            $gFirstRow->P4 = $item->P4;
            $gFirstRow->listOCRValue = $item->listOCRValue;
            array_push($arrayGroupFirst, $gFirstRow);
        }
        
        //$templateListCol = $this->input->post('templateListCol');
        $jsonFilePath = $this->input->post('jsonFilePath');
        $s_Data = file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/OcrForm/'.$jsonFilePath);
        $width=0;
        $height =0;
        $OCRArray = ParserJson2Object($s_Data,$width,$height);
        $anglePopular = AnglePopular($OCRArray);
        $OCRArray = MergerAllWordToLine($OCRArray,$anglePopular);
        $cListItem = new ListItemDetail();
        $cListItem->SetOcrArray($OCRArray);
        $cListItem->SetAnglePopular($anglePopular);
        $cListItem->SetWidth($width);
        $cListItem->SetHeight($height);
        $listRows = $cListItem->LoadListItemByTemplate($arrayGroupTitle, $arrayGroupFirst);
        $arrayResult = array();
        for($i = 0; $i < count($listRows); $i++)
        {
            $arrayItem = $listRows[$i];
            $arrayDetil = array();
            foreach($arrayItem as $item)
            {
                array_push($arrayDetil, $item);
            }
            array_push($arrayResult, $arrayDetil);
        }
        echo json_encode($arrayResult);
    }
    
    public function BindingDataByTemplateId(){
        $templateList = $this->db->get_where('tbtemplateList', 
                array('TemplateId' => $this->input->post('templateId')))->first_row();
        
        $result = array(
            'templateList' => json_encode($templateList)
        );
        return $result;
    }
    
    public function BindingInvoiceByPageIndex(){
        $filePath = $this->input->post('filePath');
        $jsonFile = $this->input->post('jsonFile');
        $s_Data = file_get_contents('http://'.$_SERVER['HTTP_HOST'].'/OcrForm/'.$jsonFile);
        $width=0;
        $height =0;
        $OCRArray = ParserJson2Object($s_Data, $width, $height);
        $anglePopular = AnglePopular($OCRArray);
        $OCRArray = MergerAllWordToLine($OCRArray,$anglePopular);

        $templateId = $this->input->post('templateId');
        if($templateId > 0)
        {
            $this->db->where(array('TemplateID' => $templateId));
            $templateList = $this->db->get('tbTemplateList')->first_row();
            $templateListKey = $templateList->Key;
            $templateListCol = $templateList->Key;
            $cListItem = new ListItemDetail();
            $cListItem->SetOcrArray($OCRArray);
            $cListItem->SetAnglePopular($anglePopular);
            $cListItem->SetWidth($width);
            $cListItem->SetHeight($height);
            $listRows = $cListItem->GetListItemByKey($templateListKey, $templateListCol);
            $arrayResult = array();
            for($i=0; $i < count($listRows);$i++)
            {
                $arrayItem = $listRows[$i];
                $arrayDetil = array();
                foreach($arrayItem as $item)
                {
                    array_push($arrayDetil, $item);
                }
                array_push($arrayResult, $arrayDetil);
            }

            $this->db->where(array('TemplateID' => $templateId));
            $templateKey = $this->db->get('tbTemplatedetail')->result();
            $invoiceInfo = GetInvoiceInforByTemplate($OCRArray, $anglePopular, $templateKey);
            $data = array(
                'InvoiceInfo' => $invoiceInfo,
                'InvoiceListItem' => $arrayResult,
                'FilePath' => $filePath,
            );
            echo json_encode($data);
        }else{
            $invoiceInfo = GetInvoiceInfor($OCRArray,$anglePopular);
            $data = array(
                'InvoiceInfo' => $invoiceInfo,
                'InvoiceListItem' => [],
                'FilePath' => $filePath,
            );
            echo json_encode($data);
        }
    }
}