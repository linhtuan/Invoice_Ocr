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

            foreach ($OCRArray as $item)
            {
           //     echo "<br> ". $item->description;
            }
              $this->load->helper('url');
              $this->load->view('main');


              //
	}
}
