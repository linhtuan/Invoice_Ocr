<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InvoiceDetail
 *
 * @author linh tuan
 */
class InvoiceDetail extends CI_Controller {
    
    public function index()
    {
        $this->load->helper('url');
        $this->load->view('detail');
    }
    
    public function GetListImage(){
        $ImageFiles = $this->db->get("tbfileinfo")->result();
        echo json_encode($ImageFiles);
    }
    
    public function GetInvoicePage(){
        $physicalFileId = $this->input->post('physicalFileId');
        $fileInfos = $this->db->get_where('tbfileinfo', array('ID' => $this->input->post('physicalFileId')))->result();
        $arrayFileInfos = array();
        $fistId;
        $index = 1;
        foreach ($fileInfos as $row)
        {
            if($index == 1){
                $fistId = $row->ID;
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
            $invoiceDetail = $this->db->get_where('tbinvoiceinfo', array('FileInfoID' => $fistId))->first_row();
            
            $this->db->select('*');
            $this->db->from('tblistitem');
            $this->db->join('tblistitemkeys', 'tbinvoiceinfo.ID = tblistitemkeys.ListItemId');
            $this->db->where('tblistitem.FileInfoID', $fistId);
            $invoiceList = $this->db->get()->result();
            
            $result = array(
                'InvoiceDetail' => $invoiceDetail,
                'InvoiceList' => $invoiceList
            );
            echo json_encode($result);
        }
    }
    
}
