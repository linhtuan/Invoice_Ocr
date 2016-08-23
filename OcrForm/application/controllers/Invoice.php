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
        
        $fileInfos = $this->db->get_where('tbfileinfo', array('ID' => $this->input->post('physicalFileId')))->result();
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
                $templateListKey = $templateList->Key;
                $templateListCol = $templateList->Key;
                $cListItem = new ListItemDetail();
                $cListItem->SetOcrArray($OCRArray);
                $cListItem->SetAnglePopular($anglePopular);
                $cListItem->SetWidth($width);
                $cListItem->SetHeight($height);
                $listRows = $cListItem->GetListItemByKey($templateListKey, $templateListCol);
                $arrayResult = array();
                for($i=1; $i < count($listRows);$i++)
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
        echo trim($str);;
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
            $fileBaseName = $info->getBasename('.' . $info->getExtension());
            $folderUpload = "UploadPDF\\".$fileBaseName;
            $ret = mkdir($folderUpload); 
            $string = "Magick\convert.exe -density 300 \"".$fileName."\" \"".$folderUpload."\"".$fileBaseName.".jpg\"";
            $str = exec($string);
            $files = scandir($folderUpload);
            $fileIndex = 1;
            foreach ($files as $value) {
                $fileNamePath = $folderUpload."\\".$value;
                $jsonName = str_replace(".","_",$value).".json";
                $ret = mkdir("JsonFile\\".$fileBaseName); 
                $jsonPath = "JsonFile\\".$fileBaseName."\\".$jsonName;
                $json = CallGGAPIForImage($fileNamePath);
                file_put_contents($jsonPath, $json);
                $array = array(
                    'PathName' => "UploadPDF/".$fileBaseName."/".$_FILES['RemoteFile']['name'],
                    'JsonFilePath' => "JsonFile/".$fileBaseName."/".$jsonName,
                    'FileIndex' => $fileIndex
                );
                $this->db->set($array);
                $this->db->insert('tbfileinfo');
                
                $fileIndex++;
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
        $query = array(
            'PathName' => $fileName
        );
        $this->db->select('ID');
        $this->db->where($query);
        $fileInfoId = $this->db->get('tbfileinfo')->first_row();
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

        $data = array(
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
        $listInvoiceItems = json_decode(stripslashes($this->input->post('listInvoiceItems')));
        if($invoiceInfoId == NULL || $invoiceInfoId == ''){
            $this->db->set($data);
            $this->db->insert('tbtemplate');
            $invoiceInfoId = $this->db->insert_id();
            InsertListInvoice($listInvoiceItems, $invoiceInfoId);
        }
        else{
            $this->db->where('ID', $invoiceInfoId);
            $this->db->update('tbinvoiceinfo', $data);
            InsertListInvoice($listInvoiceItems, $invoiceInfoId);
        }
        $result = array(
            'invoiceInfoId' => $invoiceInfoId
        );
        echo json_encode($result);
    }
    
    public function InsertListInvoice($listInvoiceItems, $invoiceInfoId){
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
        $templateListKey = $this->input->post('templateListKey');
        $templateListCol = $this->input->post('templateListCol');
        //echo $templateList;
        if($templateListKey != '' && $templateListKey != NULL)
        {
            $arrayTemplateList = array(
                'TemplateID' => $templateId,
                'Key' => $templateListKey,
                'ColumnNumber' => $templateListCol
            );
            $this->db->set($arrayTemplateList);
            $this->db->insert('tbTemplateList');
        }
        
        echo json_encode($templateId);
    }
    
    public function ListItemProcess(){
        $possitionListInvoice = json_decode(stripslashes($this->input->post('possitionListInvoice')));
        $templateListCol = $this->input->post('templateListCol');
        
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
        $listRows = $cListItem->GetListItemByPosition($possitionListInvoice, $templateListCol);
        $arrayResult = array();
        for($i = 1; $i < count($listRows); $i++)
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
            for($i=1; $i < count($listRows);$i++)
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