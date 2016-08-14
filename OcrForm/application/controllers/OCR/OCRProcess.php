<!--
	Copyright 2015, Google, Inc.
 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
-->
<?php

include_once 'creds.php'; // Get $api_key
include_once 'OCRValue.php';
include_once 'KeyValue.php';
include_once 'Utils.php';
//include_once '../../models/OCRModel.php';
 $cvurl = 'https://vision.googleapis.com/v1/images:annotate?key=' . $api_key;

class InvoiceInfo
{
    public  $VendorName;
    public  $VendorNumber;
    public  $InvoiceID;
    public  $InvoiceDate;
    public  $PONumber;
    public  $Total;
    public  $SubTotal;
    public  $TotalTax;
    public  $Shipping;
    public  $Terms;

}

//$OCRListArray = array();
 // class OCRProcess extends CI_Controller{
       
   function CallGGAPI($pathFile,$pageNum)
    {
        $type = 'TEXT_DETECTION';
        //Check if file is pdf page
        if($pageNum>=0)
        {
            $base64 = Pdf2Image($pathFile, $pageNum);
        }
        else {
             $data = file_get_contents($pathFile);
             $base64 = base64_encode($data);    
        }
             
            //Create this JSON
         $request_json = '{
			  	"requests": [
					{
					  "image": {
					    "content":"' . $base64 . '"
					  },
					  "features": [
					      {
					      	"type": "' . $type . '",
							"maxResults": 200
					      }
					  ]
					}
				]
			}';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $cvurl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
            $json_response = curl_exec($curl);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($status != 200) {
                die("Error: call to URL $cvurl failed with status $status, response $json_response, curl_error " . curl_error($curl) . ', curl_errno ' . curl_errno($curl));
            }
          return $json_response;
    }
    
     function ParserJson2Object($json_response)
    {
          $a_Data =  json_decode($json_response);
         // echo $json_response;
         
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
			
			$returnArray[] = $tempObj;
			
		}
                
            
	return  $returnArray;
    }
    
    function MergerAllWordToLine($OCRArray,$anglePopular)
    {
        for ($i = 1; $i < count($OCRArray) - 1; $i++)
            {
                $thress = ($OCRArray[$i]->X2 - $OCRArray[$i]->X1) / strlen($OCRArray[$i]->description);
              
                if (abs($OCRArray[$i]->X2 - $OCRArray[$i + 1]->X1) < (3 * $thress))
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
   function GetValueOfPriceByKey($key,$OCRArray,$anglePopular)
    {
         $index=0;
            for ($i = 0; $i < count($OCRArray) ; $i ++)
            {
                $itemOCR = $OCRArray[$i];
                 if(strcasecmp($key,$itemOCR->description)==0)
                 {
                     $index=$i;
                     // echo "Thien Anh" .$itemOCR->description;
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
                              //   echo $dis;
                             //    echo '<br>';
                                 return $itemOCR;
                             }
                         }
                    }
                  
                    
                }
            }
            
            return NULL;
    }
    function GetInvoiceInfoByKey($OCRArray,$invoiceIDKey,$anglePopular)
    {
             $result = new KeyValue(); 
        
        for($j=0; $j<count($invoiceIDKey);$j++)
        {
            $key = $invoiceIDKey[$j];
            
            $value = GetValueOfPriceByKey($key, $OCRArray,$anglePopular);
            if($value!=NULL)
            {
                $result->label = $key;
                $result->value = $value->description;
             //   echo $value->description;
                return $result;
            }
        }
        return $result;
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
                        return $value;
                        //return word.Replace(key, "");
                    }
                }
            }

            return "";
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
        $totalKey=array('Tota','Total','Invoice Total');
        $taxKey=array('Tax','GTS','HST');
       // $taxKey=array
        
        //GetInvoidID
     //   $invoiceID = GetInvoiceInfoByKey($OCRArray,$invoiceIDKey);
     //  $invoiceInfo->InvoiceID = $invoiceID;
        
        //Get POKey 
       // $PO = GetInvoiceInfoByKey($OCRArray,$POKey);
       // $invoiceInfo->PONumber = $PO;
        
  
        $CustomerNumKey =array('Customer No.','Customer','Customer ID');
        $CustomerNum = GetInvoiceIDOrDate($OCRArray,$CustomerNumKey,FALSE);
        $invoiceInfo->VendorNumber = $CustomerNum;
        echo '<br>Vendor Number : ';
        echo $CustomerNum->value;
         
       
         
         
        $invoiceIDKey =array('Invoice','invoice id','bill id', 'invoice number', 'invoice no');
        $InvoiceID = GetInvoiceIDOrDate($OCRArray,$invoiceIDKey,FALSE);
        $invoiceInfo->InvoiceID = $InvoiceID;
        echo '<br>Invoice ID : ';
        echo $InvoiceID->value;
         
         
        $InvoiceDateKey =array('Date','Invoice Date','Order Date', 'Payment date', 'Billing Date');
        $InvoiceDate = GetInvoiceIDOrDate($OCRArray,$InvoiceDateKey,TRUE);
        $invoiceInfo->InvoiceDate = $InvoiceDate;
        echo '<br>Invoice Date : ';
        echo $InvoiceDate->value;
         
         
         $groupTermsKey =array('Terms');
         $Terms = GetInvoiceIDOrDate($OCRArray,$groupTermsKey,FALSE);
         $invoiceInfo->Terms = $Terms;
         echo '<br>Terms ID : ';
         echo $Terms->value;
         
         
               //Get Subtotal
        $subtotal = GetInvoiceInfoByKey($OCRArray, $subTotalKey,$anglePopular);
        $invoiceInfo->SubTotal = $subtotal;
        echo '<br>Subtotal : ';
        echo $subtotal->value;
        
        $Tax = GetInvoiceInfoByKey($OCRArray, $taxKey,$anglePopular); 
        $invoiceInfo->TotalTax = $Tax;
         echo '<br>Tax : ';
         echo $Tax->value;
          //Gettotal
        $Total = GetInvoiceInfoByKey($OCRArray, $totalKey,$anglePopular);
        $invoiceInfo->Total = $Total;
        echo '<br>Total : ';
        echo $Total->value;
         
      
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
               || CheckPointInRectangle($item->X2,$item->Y2,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1
               || CheckPointInRectangle($item->X3,$item->Y3,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1
               || CheckPointInRectangle($item->X4,$item->Y4,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)>-1 )
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
            if(CheckPointInPolygon($item->X1,$item->Y1,$listPoint)>-1
               || CheckPointInPolygon($item->X2,$item->Y2,$listPoint)>-1
               || CheckPointInPolygon($item->X3,$item->Y3,$listPoint)>-1
               || CheckPointInPolygon($item->X4,$item->Y4,$listPoint)>-1 )
            {
                //return $item->description;
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

