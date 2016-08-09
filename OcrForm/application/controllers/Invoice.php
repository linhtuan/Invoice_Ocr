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
            
            $queryInvoiceInfo = $this->db->get('tblistitem');
            $this->invoices_model->InvoiceListItem = $queryInvoiceInfo->result();
            
            echo json_encode($this->invoices_model);
        }
}

