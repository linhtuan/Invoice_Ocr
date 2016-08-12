<?php

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
}

