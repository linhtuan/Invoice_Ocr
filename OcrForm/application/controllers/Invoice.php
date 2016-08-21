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
    
//    public function __constructor(){
//        
//        parent::__construct();
//        $this->output->enable_profiler(TRUE);
//    }
    
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
        
        $fileInfo = $this->db->get_where('tbfileinfo', array('ID' => $this->input->post('physicalFileId')))->row();
        if (isset($fileInfo))
        {
            $s_Data = file_get_contents('http://localhost:8080/OcrForm/'.$fileInfo->JsonFilePath);
            $width=0;
            $height =0;
            $OCRArray = ParserJson2Object($s_Data, $width, $height);
            $anglePopular = AnglePopular($OCRArray);
            
            $templateId = $this->input->post('templateId');
            if($templateId > 0)
            {
                $listItemDetail = array("Item Number", "Description", "Ordered", "B/O", "Shipped", "Unit Price", "Ext. Price");
                $listItemDetail1 = array("SUR1900", "SURREY GRAY SPELL", "30", "0", "30", "$11.17", "$335.10");
                $listItems = array();
                array_push($listItems, $listItemDetail);
                array_push($listItems, $listItemDetail1);
                
                $this->db->where(array('TemplateID' => $templateId));
                $templateKey = $this->db->get('tbkeyword')->result();
                $invoiceInfo = GetInvoiceInforByTemplate($OCRArray, $anglePopular, $templateKey);
                $data = array(
                    'InvoiceInfo' => $invoiceInfo,
                    'InvoiceListItem' => $listItems,
                    'PhysicalFilePath' => $fileInfo->PathName,
                    'JsonFilePath' => $fileInfo->JsonFilePath
                );
                echo json_encode($data);
            }else{
                //$OCRArray = MergerAllWordToLine($OCRArray,$anglePopular);
                $invoiceInfo = GetInvoiceInfor($OCRArray,$anglePopular);
                $data = array(
                    'InvoiceInfo' => $invoiceInfo,
                    'InvoiceListItem' => [],
                    'PhysicalFilePath' => $fileInfo->PathName,
                    'JsonFilePath' => $fileInfo->JsonFilePath
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
        $s_Data = file_get_contents('http://localhost:8080/OcrForm/'.$jsonFilePath);
        $width=0;
        $height =0;
        $OCRArray = ParserJson2Object($s_Data,$width,$height);
        $anglePopular = AnglePopular($OCRArray);

        //$OCRArray = MergerAllWordToLine($OCRArray,$anglePopular);
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
        $strFileSize = (string)intval($fileSize/1024)."KB";
        fwrite($fWriteHandle, $fileContent);
        fclose($fWriteHandle);
        $info = new SplFileInfo($fileName);
        $fileType = $info->getExtension();
        
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
        
        if($fileType == "pdf"){
            //$im = new \Imagick($fileName);
//            $noOfPagesInPDF = $im->getNumberImages();
//            for ($i = 0; $i < $noOfPagesInPDF; $i++) { 
// 
//              $url = $fileName.'['.$i.']'; 
// 
//              $image = new Imagick($url);
// 
//              $image->setImageFormat("jpg"); 
// 
//              $image->writeImage("UploadImage/".__DIR__."/".($i+1).'-'.rand().'.jpg'); 
//          }
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
        $invoiceNumber = $this->input->post('InvoiceNumber');
        $date = $this->input->post('Date');
        $data = array(
            'VendorName' => $vendorName,
            'InvoiceNumber' => $invoiceNumber,
            'Date' => $date
        );
        if($invoiceInfoId == NULL || $invoiceInfoId == ''){
            $this->db->set($data);
            $this->db->insert('tbtemplate');
            $invoiceInfoId = $this->db->insert_id();
        }
        else{
            $this->db->where('ID', $invoiceInfoId);
            $this->db->update('tbinvoiceinfo', $data);
        }
        $result = array(
            'invoiceInfoId' => $invoiceInfoId
        );
        echo json_encode($result);
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
        $cListItem = new ListItemDetail();
        $cListItem->SetOcrArray($OCRArray);
        $cListItem->SetAnglePopular($anglePopular);
        $cListItem->SetWidth($width);
        $cListItem->SetHeight($height);
        $listRows = $cListItem->GetListItemByKey('Unit Price',6);
        $arrayResult = array();
        for($i=1; $i<count($listRows);$i++)
        {
            $arrayItem = $listRows[$i];
            //Write gias tri
//            $arrayDetil = 
//            foreach($arrayItem as $item)
//            {
//                echo $item. "--";
//            }
//            echo '<br>';
        }
    }
}