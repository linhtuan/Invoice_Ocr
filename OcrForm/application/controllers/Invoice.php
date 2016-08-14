<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'OCR/OCRProcess.php';
include_once 'OCR/Point.php';
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
            $str = GetTextByPosition(1701,271, $OCRArray);

            $str = GetTextByRectangle(31,506,971,550,968,591,35,551,$OCRArray);

            echo "<br>Text at(1701,271): ".$str;
        }
        
        public function GetDataInPositions(){
            $array = (array) $this->input->post('Positions');
            echo json_encode($array[0]);
            echo json_encode($array[1]);
            echo json_encode($array[2]);
            echo json_encode($array[3]);
//            $listPoint = array();
//            $p1 = new Point();
//            $p1->X =133;
//            $p1->Y =1435;
//            $listPoint[]= $p1;
//            $p2 = new Point();
//            $p2->X =2495;
//            $p2->Y =1475;
//            $listPoint[]=$p2;
//            $p3 = new Point();
//            $p3->X =968;
//            $p3->Y =591;
//            $listPoint[]=$p3;
//            $p4 = new Point();
//            $p4->X =35;
//            $p4->Y =551;
//            $listPoint[]=$p4;
            
        }
}

