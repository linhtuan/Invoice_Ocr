<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'OCR/OCRProcess.php';
class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
             $s_Data = file_get_contents('http://localhost:8080/OcrForm/data.json');
           
             //   echo $s_Data;
              $OCRArray = ParserJson2Object($s_Data);
              $invoiceInfo = GetInvoiceInfor($OCRArray);
              $total = $invoiceInfo->Total;
             
		$this->load->helper('url');
		$this->load->view('main');
               
               
                //
	}
}
