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

}


 // class OCRProcess extends CI_Controller{
       
   function CallGGAPI($pathFile)
    {
        $type = 'TEXT_DETECTION';
        $data = file_get_contents($pathFile);
         $base64 = base64_encode($data);
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
                
              //  echo $returnArray[0]->description;
	return  $returnArray;
    }
    
     function checkStringIsNumber($new_str)
    {
      
        if(is_numeric($new_str) ) //Check string is Int
        {
           return TRUE;
        }
        else
        {
            if(is_numeric($new_str) && strpos($new_str, ".") !== false) //Check string is double
            {
                return TRUE;
            }
        }
        
        return FALSE;
    }

   function Check2BillIsLine($b1,$b2,$vertycal,$anglePopular)
    {      
         if ($b1->X1 > $b2->X1)
            {
                return FALSE;
            }
            if($vertycal)
            {
                 if(abs($b1->Y4 - $b2->Y4)<abs(($b1->Y1-$b1->Y3)))//Case vetycal
                    {
                  //      return TRUE;
                    }
            }
            else //Horyzoltal
            {
                
            }
            
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
             
            if ($dt1 < abs($b1->Y4 - $b1->Y1) && $dt2 < abs($b1->Y4 - $b1->Y1))
            {
                echo "    ";
            echo $dt1;
            echo "    ";
            echo $dt2;
                return TRUE;
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
                     break;
                 }
            }
            echo $index;
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
                          $dis = str_replace(array(' ', '$', '-'), ' ', $dis);
                          $dis = str_replace(',', '.', $dis);
                         if(checkStringIsNumber($dis))
                         {
                             
                             if(Check2BillIsLine($itemFound,$itemOCR,TRUE,$anglePopular))
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
    }
    
    function GetBillIDByKey($OCRArray,$key)
    {
        $labelItems = array();
        $billID ="";
        foreach ($OCRArray as $itemOCR)
        {
             if(strcasecmp($key,$itemOCR->description)==0)
             {
                 $labelItems[] = $itemOCR;
             }
        }
        
        if (count($labelItems) == 0)
                    return $billID;
        
         for ($n = 0; n < count($labelItems); $n++)
            {
                $labelItem = $labelItems[n];
                for ($i = 0; $i < count($OCRArray); $i++)
                {
                     $item = $OCRArray[i];
                      if(strcasecmp($labelItem->description,$itemOCR->description)==0)
                        {
                          continue;
                        }
                }
            }
    }
    function GetInvoiceID($OCRArray,$groupKeysBillID)
    {
        foreach ($groupKeysBillID as $key)
        {
            
        }
    }
    function GetInvoiceInfor($OCRArray)
    {
        $invoiceInfo = new InvoiceInfo();
        
        //Calculate $anglePopular
        $anglePopular = AnglePopular($OCRArray);
         echo $anglePopular;
         echo "  aaa";
       // $invoiceID = new KeyValue();
        $invoiceIDKey = array('invoice id','bill id', 'invoice number', 'invoice no');
        $invoiceDateKey=array('invoice date','bill date', 'date');
        $POKey = array('invoice id','bill id', 'invoice number', 'invoice no');
        $subTotalKey =array('sub-total','SubTotal');
        $totalKey=array('Total','Invoice Total');
       // $taxKey=array
        
        //GetInvoidID
     //   $invoiceID = GetInvoiceInfoByKey($OCRArray,$invoiceIDKey);
     //  $invoiceInfo->InvoiceID = $invoiceID;
        
        //Get POKey 
       // $PO = GetInvoiceInfoByKey($OCRArray,$POKey);
       // $invoiceInfo->PONumber = $PO;
        
        //Get Subtotal
        //$subtotal = GetInvoiceInfoByKey($OCRArray, $subTotalKey,$anglePopular);
        //$invoiceInfo->SubTotal = $subtotal;
        //echo $subtotal->value;
        //Gettotal
       // $Total = GetInvoiceInfoByKey($OCRArray, $totalKey,$anglePopular);
      //  $invoiceInfo->Total = $Total;
        return $invoiceInfo;
    }
//}

