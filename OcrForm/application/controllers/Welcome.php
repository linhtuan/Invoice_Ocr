<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include_once 'OCR/OCRProcess.php';
include_once 'OCR/ListItemDetail.php';
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
            $width=0;
            $height =0;
            $OCRArray = ParserJson2Object($s_Data,$width,$height);
            $anglePopular = AnglePopular($OCRArray);

         //   $OCRArray = MergerAllWordToLine($OCRArray,$anglePopular);

         /*
              $invoiceInfo = GetInvoiceInfor($OCRArray,$anglePopular);
              $total = $invoiceInfo->Total;
              $str = GetTextByPosition(1701,271,$OCRArray);
              */ 
           //   $str = GetTextByRectangle(133,1435,2495,1475,968,591,35,551,$OCRArray);
             
            //  echo "<br>Text at(1701,271): ".$str;
              
            $cListItem = new ListItemDetail($OCRArray,$anglePopular);
           //  $str = $cListItem->GetFirstItemByKey('Number',true);
            $listItem = $cListItem->GetListItemByKey('LShipped');
              
            foreach($listItem as $item)
            {
                $OCRItemList=$item->ListOCRValue;
                $s="";
                foreach ($OCRItemList as $OCRitem)
                {
                    $s = $s ." ".$OCRitem->description;
                }
                echo "<br>Item :".$s ;
            }
            $this->load->helper('url');
            $this->load->view('main');
               
               
                //
	}
}
