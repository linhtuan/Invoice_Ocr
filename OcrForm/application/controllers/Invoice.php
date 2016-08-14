<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'OCR/OCRProcess.php';
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
            $s_Data = file_get_contents('http://localhost:8080/OcrForm/2.json');

            //   echo $s_Data;
            $OCRArray = ParserJson2Object($s_Data);
            $anglePopular = AnglePopular($OCRArray);

            $OCRArray = MergerAllWordToLine($OCRArray,$anglePopular);


            $invoiceInfo = GetInvoiceInfor($OCRArray,$anglePopular);
            $total = $invoiceInfo->Total;
            $str = GetTextByPosition(1701,271,$OCRArray);

            $str = GetTextByRectangle(31,506,971,550,968,591,35,551,$OCRArray);

            echo "<br>Text at(1701,271): ".$str;
        }
}

