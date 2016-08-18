<?php


include_once 'OCRValue.php';
include_once 'KeyValue.php';
include_once 'Utils.php';
//include_once '../../models/OCRModel.php';

class InvoiceInfo
{
    public  $VendorName; //000
    public  $VendorNumber; //100
    public  $InvoiceID; //200
    public  $InvoiceDate; //300
    public  $PONumber;//400
    public  $Terms;//500
    public  $SubTotal;  //600
    public  $TotalTax; //700
    public  $Shipping; //800
     public $Discount; //900
    public  $Total;//1000
   

}
class TemplateKeyword{
    public $ID;
    public $Keyword;
    public $Type;
    public $Vertycal;
    public $Index;
}
//$OCRListArray = array();
 // class OCRProcess extends CI_Controller{
   
    
     function ParserJson2Object($json_response,&$width,&$height)
    {
          $a_Data =  json_decode($json_response);
         // echo $json_response;
         $minX=5000;
         $minY=5000;
         $maxX = 0;
         $maxY= 0;
         
	  $data = reset($a_Data->responses);
	  $annotationData = $data->textAnnotations;
	  $returnArray = array();
		foreach( $annotationData as $object ) 
		{
			$tempObj = new OCRValue();
			$tempObj->description = $object->description;
			
			$tempObj->X1 = $object->boundingPoly->vertices[0]->x;
			$tempObj->Y1 = $object->boundingPoly->vertices[0]->y;
			
			$tempObj->X2 = $object->boundingPoly->vertices[1]->x;
			$tempObj->Y2 = $object->boundingPoly->vertices[1]->y;
			
			$tempObj->X3 = $object->boundingPoly->vertices[2]->x;
			$tempObj->Y3 = $object->boundingPoly->vertices[2]->y;
			
			$tempObj->X4 = $object->boundingPoly->vertices[3]->x;
			$tempObj->Y4 = $object->boundingPoly->vertices[3]->y;
			
                        $minX = min($minX, $tempObj->X1);
                        $maxX = max($maxX, $tempObj->X2);
                        
                        $minY =  min($minY, $tempObj->Y1);
                        $maxY =  max($maxY, $tempObj->Y4);
                        
			$returnArray[] = $tempObj;
			
		}
                
          $width = $maxX;
          $height =$maxY;
        
	return  $returnArray;
    }
    
    function MergerAllWordToLine($OCRArray,$anglePopular)
    {
        for ($i = 1; $i < count($OCRArray) - 1; $i++)
            {
                $thress1 = ($OCRArray[$i]->X2 - $OCRArray[$i]->X1) / strlen($OCRArray[$i]->description);
                $thress2 = ($OCRArray[$i+1]->X2 - $OCRArray[$i+1]->X1) / strlen($OCRArray[$i+1]->description);
                $thress = min($thress1,$thress2);
                if (abs($OCRArray[$i]->X2 - $OCRArray[$i + 1]->X1) < (1.5 * $thress))
                {
                     if (Check2BillIsLine($OCRArray[$i], $OCRArray[$i + 1],TRUE,$anglePopular,TRUE ))
                     {
                            $OCRArray[$i]->description = $OCRArray[$i]->description ." ". $OCRArray[$i + 1]->description;
                            
                            $OCRArray[$i]->X2 = $OCRArray[$i+1]->X2;
                            $OCRArray[$i]->Y2 = $OCRArray[$i+1]->Y2;
                            $OCRArray[$i]->X3 = $OCRArray[$i+1]->X3;
                            $OCRArray[$i]->Y3 = $OCRArray[$i+1]->Y3;
                            array_splice($OCRArray,$i+1,1);
                           
                            $i--;
                     }
                
                }
            }
         
            return $OCRArray;
    }
    
    function checkStringIsNumber($new_str)
    {
        if(is_numeric($new_str) ) //Check string is Int
        {
           return TRUE;
        }
        else
        {
            if(is_numeric($new_str) && strpos($new_str, ".") != false) //Check string is double
            {
                return TRUE;
            }
        }
        
        return FALSE;
    }

   function Check2BillIsLine($b1,$b2,$vertycal,$anglePopular,$isMerger)
    {      
        
            //////////////////////////////////////////////////////////////////
            $angle1 = GetAngleOfLine($b1->X4, $b1->Y4, $b1->X3, $b1->Y3);
            $p1_4 = new Point();
            $p1_4->X = $b1->X4;
            $p1_4->Y = $b1->Y4;
            $p1_3 = new Point();
            $p1_3->X = $b1->X3;
            $p1_3->Y = $b1->Y3;
            $newP1_3 = Rotate($p1_4, $p1_3, $angle1,$anglePopular);
            
            
            $angle2 = GetAngleOfLine($b2->X4, $b2->Y4, $b2->X3, $b2->Y3);
            $p2_4 = new Point();
            $p2_4->X = $b2->X4;
            $p2_4->Y = $b2->Y4;
            $p2_3 = new Point();
            $p2_3->X = $b2->X3;
            $p2_3->Y = $b2->Y3;
            $newP2_3 = Rotate($p2_4, $p2_3, $angle2,$anglePopular);
            
             //Create line1 -----------------------------
            //Line1

            $A1 = $p1_4->Y - $newP1_3->Y;
            $B1 = $newP1_3->X - $p1_4->X;
            $C1 = -$A1 * $newP1_3->X - $B1 * $newP1_3->Y;
            ///////////////////////////////////////////////////////////////
            $dt1 = DistanceFromPoint2Line($A1, $B1, $C1, $p2_4);
            $dt2 = DistanceFromPoint2Line($A1, $B1, $C1, $newP2_3);
             
            if($isMerger)
            {
                if($dt1<15 && $dt2<15)
                {
                    if ($dt1 < abs($b1->Y4 - $b1->Y1) && $dt2 < abs($b1->Y4 - $b1->Y1))
                    {    
                        return TRUE;
                    }
                }
            }
            else
            {  
             //   echo "<br> dt1: ".$dt1 ." dt2: ".$dt2 . " value:".$b2->description;
                if ($dt1 < abs($b1->Y4 - $b1->Y1) && $dt2 < abs($b1->Y4 - $b1->Y1))
                {    
                    return TRUE;
                }
            }
            return FALSE;
    }
    
   function GetValueOfPriceByKeyOfTemplate($key,$OCRArray,$anglePopular,$vertycal)
   {
       $result = new KeyValue(); 
       $index=0;
            for ($i = 0; $i < count($OCRArray) ; $i ++)
            {
                $itemOCR = $OCRArray[$i];
                 if(strcasecmp($key,$itemOCR->description)==0)
                 {
                     $index=$i;
                     break;
                 }
            }
            
            if($index>0)
            {
                $itemFound = $OCRArray[$index];
                for ($i = 0; $i < count($OCRArray) ; $i ++)
                {
                    if($i!=$index)
                    {
                          $itemOCR = $OCRArray[$i];
                          //Mapping $itemFound keyword with value
                          $dis = $itemOCR->description;
                           //Replase some special charactor
                          $dis = str_replace(array(' ', '$', '-'), '', $dis);
                          $dis = str_replace(',', '', $dis);
                         if(checkStringIsNumber($dis))
                         {
                             if(!$vertycal)
                             {
                                 if(Check2BillIsLine($itemFound,$itemOCR,TRUE,$anglePopular,FALSE))
                                {
                                        $result->value = $itemOCR->description;
                                        $point = new Point();
                                        $point->X = $itemOCR->X1;
                                        $point->Y = $itemOCR->Y1;
                                        $result->point= $point;
                                        $result->label = $itemFound->description;
                                        $result->vertycal = FALSE;
                                    return $result;
                                }
                             }
                             else
                             {
                                 if (($itemOCR->Y1 - $itemFound->Y4) < (10 * ($itemOCR->Y4 - $itemOCR->Y1)) && ($itemOCR->Y1 - $itemFound->Y1) > 0)//Vertycal
                                {

                                    if ((abs($itemOCR->X2 - $itemFound->X2) < 7) || (abs($itemOCR->X1 - $itemFound->X1) < 7) || (abs(($itemOCR->X1 + $itemOCR->X2) / 2 - ($itemFound->X1 + $itemFound->X2) / 2) < 30))
                                    {
                                        $result->value = $itemOCR->description;
                                        $point = new Point();
                                        $point->X = $itemOCR->X1;
                                        $point->Y = $itemOCR->Y1;
                                        $result->point= $point;
                                        $result->label = $itemFound->description;
                                        $result->vertycal = TRUE;
                                        return $result;
                                    }
                                }
                             }
                           
                         }
                    }
                  
                    
                }
            }
            
            return NULL;
   }
   function GetValueOfPriceByKey($key,$OCRArray,$anglePopular)
    {
        $result = new KeyValue(); 
         $index=0;
            for ($i = 0; $i < count($OCRArray) ; $i ++)
            {
                $itemOCR = $OCRArray[$i];
                 if(strcasecmp($key,$itemOCR->description)==0)
                 {
                     $index=$i;
                     break;
                 }
            }
           
            if($index>0)
            {
                $itemFound = $OCRArray[$index];
                for ($i = 0; $i < count($OCRArray) ; $i ++)
                {
                    if($i!=$index)
                    {
                          $itemOCR = $OCRArray[$i];
                          //Mapping $itemFound keyword with value
                          $dis = $itemOCR->description;
                           //Replase some special charactor
                          $dis = str_replace(array(' ', '$', '-'), '', $dis);
                          $dis = str_replace(',', '', $dis);
                         if(checkStringIsNumber($dis))
                         {
                           if(Check2BillIsLine($itemFound,$itemOCR,TRUE,$anglePopular,FALSE))
                             {
                                        $result->value = $itemOCR->description;
                                        $point = new Point();
                                        $point->X = $itemOCR->X1;
                                        $point->Y = $itemOCR->Y1;
                                        $result->point= $point;
                                        $result->label = $itemFound->description;
                                        $result->vertycal = FALSE;
                                    return $result;
                             }
                         }
                    }
                  
                    
                }
            }
            
            return NULL;
    }
    function GetInvoiceInfoByKey($OCRArray,$invoiceIDKey,$anglePopular)
    {
          
        
        for($j=0; $j<count($invoiceIDKey);$j++)
        {
            $key = $invoiceIDKey[$j];
            
            $result = GetValueOfPriceByKey($key, $OCRArray,$anglePopular);
            if($result!=NULL)
            {
               
                return $result;
            }
        }
        return NULL;
    } 
    function GetInvoiceIDorDateByKeyInItem($OCRArray,$key,$isDate)
    {
          $result =new KeyValue();
           for($i=1; $i<count($OCRArray);$i++)
           {
                $itemOCR = $OCRArray[$i];
          
                $description = $itemOCR->description;
                $index = strpos(strtoupper($description),strtoupper($key));
                if ($index ===FALSE)
                {
                    continue;
                }
                else
                {
                    
                    if (ValidateBillOrDate($description,$isDate))
                    {
                         
                        $value = substr($description,$index + strlen($key));
                        $result->value = $value;
                       
                        $point = new Point();
                         $point->X = $itemOCR->X1;
                         $point->Y = $itemOCR->Y1;
                         $result->point= $point;
                         
                        return $result;
                        //return word.Replace(key, "");
                    }
                }
            }

            return "";
        }

    function GetInvoiceIDOrDateByKeyTemPlate($OCRArray,$key,$isDate,$vertycal)
    {
        $labelItems = array();
        $result =new KeyValue();
        foreach ($OCRArray as $itemOCR)
        {
             if(strcasecmp($key,str_replace(array('#', '.', ':'), '',$itemOCR->description))==0)
             {
                 $labelItems[] = $itemOCR;
             }
        }
        
        if (count($labelItems) == 0)
                    return $result;
         for ($n = 0; $n < count($labelItems); $n++)
            {
                $labelItem = $labelItems[$n];
                for ($i = 1; $i < count($OCRArray); $i++)
                {
                     $item = $OCRArray[$i];
                      if(strcasecmp($labelItem->description,$itemOCR->description)==0)
                        {
                          continue;
                        }
                        if (ValidateBillOrDate($item->description,$isDate)==FALSE) continue;
                       
                        //horizontal
                        if(!$vertycal)
                        {
                            if (abs($labelItem->Y4 - $item->Y4) < 5) //horizontal
                                    {
                                        if (($item->X2 - $labelItem->X1) > 0)
                                        {
                                             $result->value = $item->description;
                                            $point = new Point();
                                            $point->X = $item->X1;
                                            $point->Y = $item->Y1;
                                            $result->point= $point;
                                            $result->label = $labelItem->description;
                                            $result->vertycal =FALSE;
                                            return $result;
                                        }

                                    }                                 
                        }
                        else {  //Vertycal
                              if (($item->Y1 - $labelItem->Y4) < (10 * ($item->Y4 - $item->Y1)) && ($item->Y1 - $labelItem->Y1) > 0)//Vertycal
                                {

                                    if ((abs($item->X2 - $labelItem->X2) < 7) || (abs($item->X1 - $labelItem->X1) < 7) || (abs(($item->X1 + $item->X2) / 2 - ($labelItem->X1 + $labelItem->X2) / 2) < 30))
                                    {
                                         $result->value = $item->description;
                                        $point = new Point();
                                        $point->X = $item->X1;
                                        $point->Y = $item->Y1;
                                        $result->point= $point;
                                        $result->label = $labelItem->description;
                                         $result->vertycal =TRUE;
                                        return $result;
                                    }
                                }
                        }
                }
            }
            
            
        //If is horizoltal
          if($vertycal==FALSE)
          {
               $ret = GetInvoiceIDorDateByKeyInItem($OCRArray,$key,$isDate);
           
                if (!empty($ret->value))
                {
                  $result = $ret;
                  $result->label=$key;
                  return $result;
                }
          }
          
          return $result;
    }
    function GetInvoiceIDorDateByKey($OCRArray,$key,$isDate)
    {
        $labelItems = array();
        $result =new KeyValue();
        foreach ($OCRArray as $itemOCR)
        {
             if(strcasecmp($key,str_replace(array('#', '.', ':'), '',$itemOCR->description))==0)
             {
                 $labelItems[] = $itemOCR;
             }
        }
        
        if (count($labelItems) == 0)
                    return $result;
        
         for ($n = 0; $n < count($labelItems); $n++)
            {
                $labelItem = $labelItems[$n];
                for ($i = 1; $i < count($OCRArray); $i++)
                {
                     $item = $OCRArray[$i];
                      if(strcasecmp($labelItem->description,$itemOCR->description)==0)
                        {
                          continue;
                        }
                        if (ValidateBillOrDate($item->description,$isDate)==FALSE) continue;
                        
                        if (abs($labelItem->Y4 - $item->Y4) < 5 &&
                        (($item->X1 - $labelItem->X2) < ($item->X2 - $item->X1 + $labelItem->X2 - $labelItem->X2) 
                         && ($item->X2 - $labelItem->X1) > 0)) //horizontal
                        {
                             {
                                $result->value = $item->description;
                                $point = new Point();
                                $point->X = $item->X1;
                                $point->Y = $item->Y1;
                                $result->point= $point;
                                return $result;
                            }
                        }
                        else
                        {
                            if (($item->Y1 - $labelItem->Y4) < (5 * ($item->Y4 - $item->Y1)) && ($item->Y1 - $labelItem->Y1) > 0)//Vertycal
                            {

                                if ((abs($item->X1 - $labelItem->X1) < 7) || (abs(($item->X1 + $item->X2) / 2 - ($labelItem->X1 + $labelItem->X2) / 2) < 30))
                                {
                                     $result->value = $item->description;
                                    $point = new Point();
                                    $point->X = $item->X1;
                                    $point->Y = $item->Y1;
                                    $result->point= $point;
                                    return $result;
                                }
                                else
                                {
                                    if (abs($labelItem->Y4 - $item->Y4) < 5) //horizontal
                                    {
                                        if (($item->X2 - $labelItem->X1) > 0)
                                        {
                                             $result->value = $item->description;
                                            $point = new Point();
                                            $point->X = $item->X1;
                                            $point->Y = $item->Y1;
                                            $result->point= $point;
                                            return $result;
                                        }

                                    }
                                }
                            }
                            else
                            {
                                if (abs($labelItem->Y4 - $item->Y4) < 5) //horizontal
                                {
                                    if (($item->X2 - $labelItem->X1) > 0)
                                    {
                                         $result->value = $item->description;
                                        $point = new Point();
                                        $point->X = $item->X1;
                                        $point->Y = $item->Y1;
                                        $result->point= $point;
                                        return $result;
                                    }

                                }
                            }
                        }
                        
                }
            }
             return $result;
    }
    function GetInvoiceIDOrDate($OCRArray,$groupKeysBillID,$isDate)
    {
         $result = new KeyValue();
        foreach ($groupKeysBillID as $key)
        {
         
             $ret = GetInvoiceIDorDateByKey( $OCRArray,$key,$isDate);
             if(empty($ret->value)==FALSE)
             {
                 $result = $ret;
                 $result->label=$key;
               
                 return $result;
             }
        }
        //If not have $result, check it in item
        foreach ($groupKeysBillID as $key)
        {               
          $ret = GetInvoiceIDorDateByKeyInItem($OCRArray,$key,$isDate);
           
          if (!empty($ret->value))
          {
            $result = $ret;
            $result->label=$key;
            return $result;
          }
        }
           
        return $result;
    }
    function GetInvoiceInfor($OCRArray,$anglePopular)
    {
        $invoiceInfo = new InvoiceInfo();
        
        //Calculate $anglePopular
      
         
       // $invoiceID = new KeyValue();
      
        $POKey = array('invoice id','bill id', 'invoice number', 'invoice no');
        $subTotalKey =array('sub-total','SubTotal','SubTota');
        $totalKey=array('Tota','Total','Invoice Total','TOTAL AMOUNT DUE');
        $taxKey=array('Tax','GTS','HST');
       // $taxKey=array
        
      
        //Get POKey 
       // $PO = GetInvoiceInfoByKey($OCRArray,$POKey);
       // $invoiceInfo->PONumber = $PO;
        
  
        $CustomerNumKey =array('Customer No','Customer','Customer ID');
        $CustomerNum = GetInvoiceIDOrDate($OCRArray,$CustomerNumKey,FALSE);
        $invoiceInfo->VendorNumber = $CustomerNum;
   //     echo '<br>Customer Number : ';
    //    echo $CustomerNum->value;
            
       
        $invoiceIDKey =array('invoice','invoice id','bill id', 'Invoice Number', 'invoice no');
        $InvoiceID = GetInvoiceIDOrDate($OCRArray,$invoiceIDKey,FALSE);
        $invoiceInfo->InvoiceID = $InvoiceID;
    //    echo '<br>Invoice ID : ';
    //    echo $InvoiceID->value;
         
         
        $InvoiceDateKey =array('Date','Invoice Date','Order Date', 'Payment date', 'Billing Date');
        $InvoiceDate = GetInvoiceIDOrDate($OCRArray,$InvoiceDateKey,TRUE);
        $invoiceInfo->InvoiceDate = $InvoiceDate;
   //     echo '<br>Invoice Date : ';
    //    echo $InvoiceDate->value;
         
         
         $groupTermsKey =array('Terms');
         $Terms = GetInvoiceIDOrDate($OCRArray,$groupTermsKey,FALSE);
         $invoiceInfo->Terms = $Terms;
  //       echo '<br>Terms ID : ';
   //      echo $Terms->value;
         
         
               //Get Subtotal
        $subtotal = GetInvoiceInfoByKey($OCRArray, $subTotalKey,$anglePopular);
        $invoiceInfo->SubTotal = $subtotal;
    //    echo '<br>Subtotal : ';
    //    echo $subtotal->value;
        
        $Tax = GetInvoiceInfoByKey($OCRArray, $taxKey,$anglePopular); 
        $invoiceInfo->TotalTax = $Tax;
   //      echo '<br>Tax : ';
    //     echo $Tax->value;
          //Gettotal
        $Total = GetInvoiceInfoByKey($OCRArray, $totalKey,$anglePopular);
        $invoiceInfo->Total = $Total;
   //     echo '<br>Total : ';
    //    echo $Total->value;
 
        return $invoiceInfo;
    }
    function GetInvoiceInforByTemplate($OCRArray,$anglePopular,$ListKeyWordTemplate)
    {       
        $invoiceInfo = new InvoiceInfo();
        foreach ($ListKeyWordTemplate as $KeyWordTemplate)
        {
            $keyword = $KeyWordTemplate->Keyword;
            $Type =$KeyWordTemplate->Type;
            $Vertycal = $KeyWordTemplate->Vertycal;
            $index = $KeyWordTemplate->Index;
            $result = new  KeyValue();
           
            if($Type==0)
            {
               $result =   GetInvoiceIDOrDateByKeyTemPlate($OCRArray,$keyword,FALSE,$Vertycal);
            }
            else
            {
                if($Type==2)
                {
                    $result =   GetInvoiceIDOrDateByKeyTemPlate($OCRArray,$keyword,TRUE,$Vertycal);
                }
                else
                {
                    $result =   GetValueOfPriceByKeyOfTemplate($keyword,$OCRArray,$anglePopular,$Vertycal);
                }
            }
                
            switch ($index) {
                case 0:
                    {
                        $invoiceInfo->VendorName =$result;
                    }
                     break;
                 case 100:
                    {
                        $invoiceInfo->VendorNumber =$result;
                    }
                     break;
                case 200:
                    {
                        $invoiceInfo->InvoiceID =$result;
                    }
                     break;
                case 300:
                     {
                         $invoiceInfo->InvoiceDate =$result;
                     }
                    break;
                case 400:
                     {
                         $invoiceInfo->PONumber =$result;
                     }
                    break;
                case 500:
                     {
                         $invoiceInfo->Terms =$result;
                     }
                    break;
                case 600:
                     {
                         $invoiceInfo->SubTotal =$result;
                     }
                    break;
                case 700:
                     {
                         $invoiceInfo->TotalTax =$result;
                     }
                    break;
                case 800:
                     {
                         $invoiceInfo->Shipping =$result;
                     }
                    break;
                case 900:
                     {
                         $invoiceInfo->Discount =$result;
                     }
                    break;
                case 1000:
                     {
                         $invoiceInfo->Total =$result;
                     }
                    break;
                 default:
                    break;
            }
            
          //  echo "<br>".$result->label.": ".$result->value;
        }
        return $invoiceInfo;
    }
    
    //////////////////////////////get text for user  manual edit////////////////////////////////
    function GetTextByPosition($posX,$posY,$OCRListArray)
    {
        //foreach ($OCRListArray as $item)
        for($i=1; $i<count($OCRListArray); $i++)
        {
            $item = $OCRListArray[$i];
            if(CheckPointInRectangle($posX,$posY,$item->X1,$item->Y1,$item->X2,$item->Y2,$item->X3,$item->Y3,$item->X4,$item->Y4)>-1)
            {
                return $item->description;
            }
        }
        
        return "";
    }
    
    function GetTextByRectangle($X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4,$OCRListArray)
    {
        $text="";
       for($i=1; $i<count($OCRListArray); $i++)
        {
            $item = $OCRListArray[$i];
            if(CheckPointInRectangle($item->X1,$item->Y1,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1
               && CheckPointInRectangle($item->X2,$item->Y2,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1
               && CheckPointInRectangle($item->X3,$item->Y3,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1
               && CheckPointInRectangle($item->X4,$item->Y4,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1 )
            {
                //return $item->description;
                $text = $text. " ". $item->description;
            }
        }
        
        return $text;
    }
    function GetTextByPolygon($listPoint,$OCRListArray)
    {
       $text="";
       for($i=1; $i<count($OCRListArray); $i++)
        {
            $item = $OCRListArray[$i];
            //$a =CheckPointInPolygon($item->X1,$item->Y1,$listPoint);
            //echo "<br> ghfhfhf: ".$a;
            if(CheckPointInPolygon($item->X1,$item->Y1,$listPoint)>-1
               && CheckPointInPolygon($item->X2,$item->Y2,$listPoint)>-1
               && CheckPointInPolygon($item->X3,$item->Y3,$listPoint)>-1
               && CheckPointInPolygon($item->X4,$item->Y4,$listPoint)>-1 )
            {
               
                $text = $text. " ". $item->description;
            }
        }
        
        return $text;
    }
    function GetListOCRValueInRectange($X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4,$OCRListArray)
    {
         $listOCRValue =array();
       for($i=1; $i<count($OCRListArray); $i++)
        {
            $item = $OCRListArray[$i];
            if(CheckPointInRectangle($item->X1,$item->Y1,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1
               || CheckPointInRectangle($item->X2,$item->Y2,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1
               || CheckPointInRectangle($item->X3,$item->Y3,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1
               || CheckPointInRectangle($item->X4,$item->Y4,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1 )
            {
                //return $item->description;
               $listOCRValue[]= $item;
            }
        }
          return $listOCRValue;
    }


    