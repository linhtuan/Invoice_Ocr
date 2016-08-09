<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class Invoice extends CI_Controller {
        public function GetInvoiceData(){
            $query = $this->db->get('tbinvoiceinfo');
            $data = array();
            foreach ($query->result() as $row)
            {
                $data = $row;
            }
            echo json_encode($data);
        }
}

