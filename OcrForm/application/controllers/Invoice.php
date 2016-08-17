<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'OCR/OCRProcess.php';
include_once 'OCR/Point.php';
include_once 'OCR/GGApi.php';

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

    public function GetValueInJson(){
        $query = array(
            'ID' => $this->input->post('physicalFileId')
        );
        $this->db->select('PathName', 'Text');
        $this->db->where($query);
        $fileInfo = $this->db->get('tbfileinfo')->first_row();
        if($fileInfo != NULL){
            $s_Data = file_get_contents('http://localhost:8080/OcrForm/2.json');
            
            $width=0;
            $height =0;
            $OCRArray = ParserJson2Object($fileInfo->Text, $width, $height);
            $anglePopular = AnglePopular($OCRArray);

            //$OCRArray = MergerAllWordToLine($OCRArray,$anglePopular);

            $invoiceInfo = GetInvoiceInfor($OCRArray,$anglePopular);
            $data = array(
                'InvoiceInfo' => $invoiceInfo,
                'InvoiceListItem' => [],
                'PhysicalFilePath' => $fileInfo->PathName
            );
            echo json_encode($data);
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

        $s_Data = file_get_contents('http://localhost:8080/OcrForm/2.json');
        //   echo $s_Data;
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

        if (file_exists($fileName)) 
          $fWriteHandle = fopen($fileName, 'w');
        else
          $fWriteHandle = fopen($fileName, 'w');
        $fReadHandle = fopen($fileTempName, 'rb');
        $fileContent = fread($fReadHandle, $fileSize);
        $strFileSize = (string)intval($fileSize/1024)."KB";
        fwrite($fWriteHandle, $fileContent);
        fclose($fWriteHandle);
        $info = new SplFileInfo($fileName);
        $fileType = $info->getExtension();
        
        //$json = CallGGAPIForImage($fileName);
        $array = array(
            'PathName' => $fileName,
            'Text' => ''
        );
        $this->db->set($array);
        $this->db->insert('tbfileinfo');
        
        
        if($fileType == "pdf"){
//            $im = new Imagick($fileName);
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
        $fileName = "UploadImage\\".$this->input->post('physicalFileName');
        $query = array(
            'PathName' => $fileName
        );
        $this->db->select('ID');
        $this->db->where($query);
        $fileInfoId = $this->db->get('tbfileinfo')->first_row();
        echo json_encode($fileInfoId);
    }

    public function UpdateInvoiceDetail(){

        $invoiceInfoId = $this->input->post('InvoiceInfoId');
        $vendorName = $this->input->post('VendorName');
        $invoiceNumber = $this->input->post('InvoiceNumber');
        $date = $this->input->post('Date');
        $data = array(
            'VendorName' => $vendorName,
            'InvoiceNumber' => $invoiceNumber,
            'Date' => $date
        );
        
        $this->db->where('ID', $invoiceInfoId);
        $this->db->update('tbinvoiceinfo', $data);
        echo json_encode($data);
    }
    
    public function UpdateInvoiceListItem(){
        $invoiceInfoId = $this->input->post('InvoiceInfoId');
        $vendorName = $this->input->post('VendorName');
        $invoiceNumber = $this->input->post('InvoiceNumber');
        $date = $this->input->post('Date');
        $data = array(
            'VendorName' => $vendorName,
            'InvoiceNumber' => $invoiceNumber,
            'Date' => $date
        );

        $this->db->where('ID', $invoiceInfoId);
        $this->db->update('tblistitem', $data);
        echo json_encode('');
    }
}

