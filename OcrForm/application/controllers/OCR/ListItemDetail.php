<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'KeyValue.php';
include_once 'OCRProcess.php';
class GroupInItem{
    public $listOCRValue;
    public $P1;
    public $P2;
    public $P3;
    public $P4;
}
class RowItem{
    public $listGroupInItem;
}
class ColItem{
    public $key;
    public $isNumber;
}
class ItemTemplate{
    public $listColItem;
    
}
class ItemDetail{
    public $ID;
    public $POLineNumber;
    public $ItemID;
    public $ItemDesc;
    public $Qty;
    public $Price;
    public $UOM;
    public $UOMNumber;
    public $Discount;
    public $Mutilplier;
    public $LineTotal;
    public $TopLeft;
    public $TopRight;
    public $BottomLeft;
    public $BottomRight;
    public $FullStr;
    public $Key;
    public $KeyIndex;
    public $ListOCRValue;
}
class ListItemDetail {
    public static $OCRArray = array(); // Check if the point sits exactly on one of the vertices?
    public static $OriOCRArray = array();
    public $keyArray = array();
    public static $anglePopular;
    public static $width=0;
    public static $height=0;
    
    function SetOcrArray($inOcrArray)
    {
        self::$OCRArray = $inOcrArray;
    }
     function SetOriOcrArray($inOcrArray)
    {
        self::$OriOCRArray = $inOcrArray;
    }
    function SetAnglePopular($inAnglePopular)
    {
        self::$anglePopular = $inAnglePopular;
    }
    function SetWidth($inWidth)
    {
        self::$width = $inWidth;
    }
    function SetHeight($inHeight)
    {
        self::$height = $inHeight;
    }
    function LoadKeyArray()
    {
        $keyArray = array('');
    }
    
    function CheckVertycalPosition($OCRItem1,$OCRItem2)
    {
        if($OCRItem2->Y1 < $OCRItem1->Y4) return FALSE;
        $center1 = ($OCRItem1->X1+$OCRItem1->X2)/2;
        $center2 = ($OCRItem2->X1+$OCRItem2->X2)/2;
       
        if( (Check2LineOverlap($OCRItem1->X1,$OCRItem1->X2,$OCRItem2->X1,$OCRItem2->X2)==TRUE) || abs($OCRItem1->X1 - $OCRItem2->X1)<10 || abs($center1-$center2)<10 || abs($OCRItem1->X2 - $OCRItem2->X2)<10 )
        {
           
            return TRUE;
        }
        if(($OCRItem1->X1 > $OCRItem2->X1) && ($OCRItem1->X1 < $OCRItem2->X2))
        { 
           
            return TRUE;
        }
        return FALSE;
    }
    
    function GetFirstItemByKey($key,$isNumber)
    {
        
        $labelItems = new OCRValue();
        $result =new KeyValue();
        $index =0;
        for($i =1; $i< count(self::$OCRArray);$i++)
       {
            $itemOCR =self::$OCRArray[$i];
            $perc=0;
            //if(strcasecmp($key,str_replace(array('#', '.', ':'), '',$itemOCR->description))==0)
            similar_text(strtolower($key),strtolower(str_replace(array('#', '.', ':'), '',$itemOCR->description)),$perc);
            if($perc>85)
           //if(strcasecmp($key,str_replace(array('#', '.', ':'), '',$itemOCR->description))==0)
             {
                 $labelItems = $itemOCR;
                
                 $index=$i;
                 break;
             }
        }
     
  // echo "Nguyen Thien: ".$labelItems->description." done";
      
       
        //Get next item position in vetycal
        $firstItem = new OCRValue();
        $indexFisrt = 0;
        for($i =1; $i< count(self::$OCRArray);$i++)
        {
            if($i!==$index)
            {
                $itemOCR =self::$OCRArray[$i];
                if($this->CheckVertycalPosition($labelItems,$itemOCR)==TRUE)
                {
                   $firstItem = $itemOCR;
                  
                   $indexFisrt=$i;
                   break;
                }
            }
        }
           
           
        $nextItem = new OCRValue();
        $indexNext = 0;
        for($i =1; $i< count(self::$OCRArray);$i++)
        {
            if($i!==$indexFisrt)
            {
                $itemOCR =self::$OCRArray[$i];
                if($this->CheckVertycalPosition($firstItem,$itemOCR)==TRUE)
                {
                   $nextItem = $itemOCR;
                   $indexNext=$i;
                  
                   break;
                }
            }
        }
     
        $p1 = new Point();
        $p1->X = $firstItem->X1;
        $p1->Y = $firstItem->Y1+5;
       
        $newPoint12 = GetNewPoint($p1, self::$anglePopular,self::$width);
        $p1 = $newPoint12[0];
        $p2 = $newPoint12[1];
     
        $p4 = new Point();
        $p4->X = $firstItem->X1;
        $p4->Y = $nextItem->Y1-20;
       
       // echo self::$anglePopular;
        
        $newPoint34 = GetNewPoint($p4, self::$anglePopular,self::$width);
        $p4 = $newPoint34[0];
        $p3 = $newPoint34[1];
       
        $str = GetTextByRectangle($p1->X,$p1->Y,$p2->X,$p2->Y,$p3->X,$p3->Y,$p4->X,$p4->Y,self::$OCRArray);
        $firstItemDetail = new ItemDetail();
        $firstItemDetail->TopLeft=$p1;
        $firstItemDetail->TopRight=$p2;
        $firstItemDetail->BottomLeft=$p4;
        $firstItemDetail->BottomRight=$p3;
        $firstItemDetail->FullStr= $str;
        $firstItemDetail->Key = $firstItem;
        $firstItemDetail->KeyIndex =$indexFisrt;
        $listOCRValue = GetListOCRValueInRectange($p1->X,$p1->Y,$p2->X,$p2->Y,$p3->X,$p3->Y,$p4->X,$p4->Y,self::$OCRArray);   
        $firstItemDetail->ListOCRValue =  $this->ReSortOCRValueInItem($listOCRValue);
       
	 
        return $firstItemDetail;
    }
     function GetFirstItemByPosition($OCRValue,$isNumber)
    {
        $labelItems = new OCRValue();
        $labelItems =$OCRValue;
        $result =new KeyValue();
        $index =0;
     
        $index = array_search($labelItems,self::$OCRArray);
        
        //Get next item position in vetycal
        $firstItem = new OCRValue();
        $indexFisrt = 0;
        for($i =1; $i< count(self::$OCRArray);$i++)
        {
            if($i!==$index)
            {
                $itemOCR =self::$OCRArray[$i];
                if($this->CheckVertycalPosition($labelItems,$itemOCR)==TRUE)
                {
                   $firstItem = $itemOCR;
                  
                   $indexFisrt=$i;
                   break;
                }
            }
        }
        
        $nextItem = new OCRValue();
        $indexNext = 0;
        for($i =1; $i< count(self::$OCRArray);$i++)
        {
            if($i!==$indexFisrt)
            {
                $itemOCR =self::$OCRArray[$i];
                if($this->CheckVertycalPosition($firstItem,$itemOCR)==TRUE)
                {
                   $nextItem = $itemOCR;
                   $indexNext=$i;
                   
                   break;
                }
            }
        }
        
        
        $p1 = new Point();
        $p1->X = $firstItem->X1;
        $p1->Y = $firstItem->Y1-5;
       
        $newPoint12 = GetNewPoint($p1, self::$anglePopular,self::$width);
        $p1 = $newPoint12[0];
        $p2 = $newPoint12[1];
     
        $p4 = new Point();
        $p4->X = $firstItem->X1;
        $p4->Y = $nextItem->Y1-20;
       
        $newPoint34 = GetNewPoint($p4, self::$anglePopular,self::$width);
        $p4 = $newPoint34[0];
         $p3 = $newPoint34[1];
      
        $listPoint = array();
        $listPoint[] = $p1;
        $listPoint[] = $p2;
        $listPoint[] = $p3;
        $listPoint[] = $p4;
         
        $str = GetTextByPolygon($listPoint,self::$OCRArray);
     //   $str = GetTextByRectangle($p1->X,$p1->Y,$p2->X,$p2->Y,$p3->X,$p3->Y,$p4->X,$p4->Y,self::$OCRArray);
        $firstItemDetail = new ItemDetail();
        $firstItemDetail->TopLeft=$p1;
        $firstItemDetail->TopRight=$p2;
        $firstItemDetail->BottomLeft=$p4;
        $firstItemDetail->BottomRight=$p3;
        $firstItemDetail->FullStr= $str;
        $firstItemDetail->Key = $firstItem;
        $firstItemDetail->KeyIndex =$indexFisrt;
         $listOCRValue = GetListOCRValueByPolygon($listPoint, self::$OCRArray);
       // $listOCRValue = GetListOCRValueInRectange($p1->X,$p1->Y,$p2->X,$p2->Y,$p3->X,$p3->Y,$p4->X,$p4->Y,self::$OCRArray);   
        $firstItemDetail->ListOCRValue =  $this->ReSortOCRValueInItem($listOCRValue);
       //  echo $str;
        return $firstItemDetail;
    }
   function GetListItem($firstItemDetail)
    {
        $bNotStopCheck=TRUE;
        $listItem = array();
        $firstItem = $firstItemDetail->Key;
        $listItem[]=$firstItemDetail;
        $currentIndex=0;
        $indexFisrt = $firstItemDetail->KeyIndex;
      //   echo "<br>Item ". $firstItemDetail->FullStr;
       //  return $listItem;
        while ($bNotStopCheck)
        {
            $currentItem = $listItem[$currentIndex]->Key;
            
            $nextItem = new OCRValue();
            $indexNext = 0;
            $indexTmp =$listItem[$currentIndex]->KeyIndex;
            $bNotStopCheck=FALSE;
            for($i =1; $i< count(self::$OCRArray);$i++)
            {
                if($i!==$indexTmp)
                {
                    $itemOCR =self::$OCRArray[$i];
                    if($this->CheckVertycalPosition($currentItem,$itemOCR)==TRUE)
                    {
                       $nextItem = $itemOCR;
                       $indexNext=$i;
                       $bNotStopCheck=TRUE;
                       break;
                    }
                }
            }
            
             if(($nextItem->Y4 - $currentItem->Y1)>200)
                {
                    // echo "<br> Item:...".$nextItem->Y4 ."    ".$currentItem->Y1;
                    $bNotStopCheck = FALSE;
                }
            //Find bottom position of nextItem
            if($bNotStopCheck)
            {
              $p1 = new Point();
              $p1->X = $nextItem->X1;
              $p1->Y = $nextItem->Y1-5;

              $newPoint12 = GetNewPoint($p1, self::$anglePopular,self::$width);
              $p1 = $newPoint12[0];
              $p2 = $newPoint12[1];
              
              $nextItemTmp = new OCRValue();
              $indexNextTmp = 0;
              
                for($i =1; $i< count(self::$OCRArray);$i++)
                {
                    if($i!==$indexFisrt)
                    {
                        $itemOCR =self::$OCRArray[$i];
                        if($this->CheckVertycalPosition($nextItem,$itemOCR)==TRUE)
                        {
                           $nextItemTmp = $itemOCR;
                           $indexNextTmp= $i;
                          
                           break;
                        }
                    }
                }
                 $p4 = new Point();
                 $p3 = new Point();
                if($nextItemTmp->description!==NULL && ($nextItemTmp->Y4 - $nextItem->Y1)<100)
                {
                    
                    $p4->X = $nextItem->X1;
                    $p4->Y = $nextItemTmp->Y1-10;
                    
                }
                else
                {
                    $p4->X = $nextItem->X1;
                    $p4->Y = $nextItem->Y4 + 2*($nextItem->Y4 -$nextItem->Y1);
                }
                 $newPoint34 = GetNewPoint($p4, self::$anglePopular,self::$width);
                 $p4 = $newPoint34[0];
                 $p3 = $newPoint34[1];
             
                $str = GetTextByRectangle($p1->X,$p1->Y,$p2->X,$p2->Y,$p3->X,$p3->Y,$p4->X,$p4->Y,self::$OCRArray);
                $nextItemDetail = new ItemDetail();
                $nextItemDetail->TopLeft=$p1;
                $nextItemDetail->TopRight=$p2;
                $nextItemDetail->BottomLeft=$p4;
                $nextItemDetail->BottomRight=$p3;
                 $listOCRValue = GetListOCRValueInRectange($p1->X,$p1->Y,$p2->X,$p2->Y,$p3->X,$p3->Y,$p4->X,$p4->Y,self::$OCRArray);
       
                $nextItemDetail->ListOCRValue =  $this->ReSortOCRValueInItem($listOCRValue);
     
                $nextItemDetail->KeyIndex =$indexFisrt;
                $nextItemDetail->Key = $nextItem;
                $nextItemDetail->FullStr =$str;
                $listItem[] = $nextItemDetail;
                
            }
            $currentIndex++;
        }
        
        return $listItem;
    }
    function GetTitleByPosition($listPolygon,$firstItemDetail )
    {
        $TitleItem = new ItemDetail();
        $p1 = new Point();
        $p1->X = $listPolygon[0]->X;
        $p1->Y = $listPolygon[0]->Y-5;
       
      //  echo "p1:".$p1->X.":".$p1->Y."p4:".$listPolygon[3]->X.":".$firstItemDetail->TopLeft->Y;
        
        $newPoint12 = GetNewPoint($p1, self::$anglePopular,self::$width);
        $p1 = $newPoint12[0];
        $p2 = $newPoint12[1];
     
        $p4 = new Point();
        $p4->X =  $listPolygon[3]->X;
        $p4->Y = $firstItemDetail->TopLeft->Y-10;
      
        $newPoint34 = GetNewPoint($p4, self::$anglePopular,self::$width);
        $p4 = $newPoint34[0];
        $p3 = $newPoint34[1];
       
      //   echo "<br>p1:".$p1->X.":".$p1->Y."p2:".$p2->X.":".$p2->Y."p3:".$p3->X.":".$p3->Y."p4:".$p4->X.":".$p4->Y;
         
        $listPoint = array();
        $listPoint[] = $p1;
        $listPoint[] = $p2;
        $listPoint[] = $p3;
        $listPoint[] = $p4;
       
        $str = GetTextByPolygon($listPoint,self::$OCRArray);
      
        $TitleItem->TopLeft=$p1;
        $TitleItem->TopRight=$p2;
        $TitleItem->BottomLeft=$p4;
        $TitleItem->BottomRight=$p3;
      
        $TitleItem->FullStr= $str;
        $listOCRValue = GetListOCRValueByPolygon($listPoint, self::$OCRArray);
        //$listOCRValue = GetListOCRValueInRectange($p1->X,$p1->Y,$p2->X,$p2->Y,$p3->X,$p3->Y,$p4->X,$p4->Y,self::$OCRArray);   
        $TitleItem->ListOCRValue =  $this->ReSortOCRValueInItem($listOCRValue);
        // echo "Thien Anh".$str;
       return $TitleItem;
    }
   function GetListItemByKey($key,$colNumber)
   {
       $listItem = array();        
       $firstItem = $this->GetFirstItemByKey($key,true);
     
       $listItem = $this->GetListItem($firstItem);
       
       $listRows = array();
        foreach($listItem as $item)
            {
                $OCRItemList=$item->ListOCRValue;
                $rows = self::ClusterringListItem($OCRItemList);
                $listRows[] = $rows;
             
            }
       $result = self::MappinglistGroup2List($listRows) ;
       
      return  $result;
   }
   
    function GetListItemByPosition($listPolygon,$colNumber)
   {
       $listKey = GetListOCRValueByPolygon($listPolygon,self::$OCRArray);
       $OCRKey = new OCRValue();
       
      
       if(count($listKey)>0)
       {
           $OCRKey = new OCRValue();
           $OCRKey->X1 = $listPolygon[0]->X;
           $OCRKey->Y1 = $listPolygon[0]->Y;
           $OCRKey->X2= $listPolygon[1]->X;
           $OCRKey->Y2 = $listPolygon[1]->Y;
           $OCRKey->X3 = $listPolygon[2]->X;
           $OCRKey->Y3 = $listPolygon[2]->Y;
           $OCRKey->X4 = $listPolygon[3]->X;
           $OCRKey->Y4 = $listPolygon[3]->Y;
           $str="";
           foreach ($listKey as $key)
           {
               $str = $str." ".$key->description; 
           }
           $OCRKey->description = $str;
       }
       $listItem = array();        
       $firstItem = $this->GetFirstItemByPosition($OCRKey,true);
      
       $titleItem = $this->GetTitleByPosition($listPolygon,$firstItem);
       $listItem = $this->GetListItem($firstItem);
       
       $listRows = array();
       //Label
    //   if($titleItem)
       {
        $OCRTitlelList=$titleItem->ListOCRValue;
        $rowsTitle = self::ClusterringListItem($OCRTitlelList);
        $listRows[] =$rowsTitle;
      
       }
       //Label
        foreach($listItem as $item)
            {
                $OCRItemList=$item->ListOCRValue;
                $rows = self::ClusterringListItem($OCRItemList);
                $listRows[] = $rows;
            }
       $result = self::MappinglistGroup2List($listRows) ;
       
      return  $result;
   }
   function CheckDistanFromItem2Group($item,$group)
   {
           if(Check2LineOverlap($group->P1->X,$group->P2->X,$item->X1,$item->X2)==TRUE)
                   return TRUE;
           $space = ($item->X2 - $item->X1 )/strlen($item->description);
          // echo "Space  :".$space;
            if(abs($group->P2->X - $item->X1) < 2*$space)
            {
                    return TRUE;
            }
            

            return false;
   }
   
   function ListPositionOfStartCol($listItem,$AnglePopular)
   {
       $startPoint= array();
       $totalItem = count($listItem);
       $hsg = tan((90-$AnglePopular)/3.14);
       for($w=0; $w<self::$width; $w+=5)
       {
         $point = new Point();
         $point->X = $w;
         $point->y = 0;
         $pt = ptdt($hsg,$point);
         $iCount = 0;
         foreach ($listItem as $item)
         {
             $listOCRArray = $item->listOCRValue;
             foreach ($listOCRArray as $ocrValue)
             {
                 $p1 = new Point();
                 $p1->X = $ocrValue->X1;
                 $p1->Y = $ocrValue->Y1;
                 if(DistanceFromPoint2Line($pt[0], $pt[1], $pt[2], $ocrValue->$p1)<5)
                 {
                     $iCount++;
                     break;
                 }
             }
         }
         
         if($iCount>=$totalItem)
         {
             
         }
       }
   }
   
   ////////////////////
   //$arrayGroupFirstItem array of GroupInItem
   //$arrayGroupTitle array of GroupInItem
   /////////////////////
   function LoadListItemByTemplate($arrayGroupTitle, $arrayGroupFirstItem)
   {
       $ListRowItemResult = array();
       $iNumOfCol = count($arrayGroupTitle);
       //Get polygon of title
       $minTitleX=4000;
       $maxTitleX=0;
       $minTitleY=4000;
       $maxTitleY=0;
	$keyForsearch1 = $arrayGroupTitle[0]->listOCRValue[0]->description;
        $keyForsearch2 = $arrayGroupTitle[1]->listOCRValue[0]->description;
       
        //Search by $keyForsearch1 and $keyForsearch2
        $ListKeyFound1 = array();
        $ListKeyFound2 = array();
        for($i=1; $i<count(self::$OCRArray); $i++)
        {
            $item = self::$OCRArray[$i];
            if(Check2StringIsSimilar($keyForsearch1,$item->description))
            {
                $ListKeyFound1[] = $item;
            }
            if(Check2StringIsSimilar($keyForsearch2,$item->description))
            {
                $ListKeyFound2[] = $item;
            }
        }
        $keyFound1 = new OCRValue();
        $found=FALSE;
        foreach ($ListKeyFound1 as $item1) {
            
            foreach ($ListKeyFound2 as $item2)
            {
                if(abs($item1->Y1 - $item2->Y1)<50)
                {
                    $keyFound1 =$item1;
                   
                    $found=TRUE;
                    break;
                }
            }
            if($found) break;
        }
        
        if($found==FALSE) return NULL;
        
       //clusting $arrayOCRInPolygon 
       $listTitle = array();
       //Calculate first row
       //estimate 
       //--> Tinh toan dc topleft and topright cua first item
       //-->Update first posision
       $Y1 = $keyFound1->Y4 +($arrayGroupTitle[0]->P4->Y - $arrayGroupTitle[0]->P1->Y); //Topleft
       $Y2 = $Y1; //Topright (Tinhs lai cai nay khi anglepopular>0
       
	//   echo $Y1 . ": ".$Y2."<br>";
       //Find col have only 1 OCRValue in row of first item
    //   $OCRindex =new OCRValue();
       $colIndex = 0;
       for($i=count($arrayGroupFirstItem)-1; $i>=0; $i--)
       {
          $col = $arrayGroupFirstItem[$i];
          if(count($col->listOCRValue)==1)
          {
              $OCRobj = $col->listOCRValue[0];
              if(checkStringIsNumber($OCRobj->description))
              {             
                  $colIndex =$i;
                  break;
              }
          }
       }
       
      
       //Calculate col position
       $colX = array();
       for($i=0; $i<count($arrayGroupFirstItem); $i++)
       {
           $titleItem = $arrayGroupTitle[$i];
           $firstItem = $arrayGroupFirstItem[$i];
           $x1= min($titleItem->P1->X, $firstItem->P1->X);
           $x2= max($titleItem->P2->X, $firstItem->P2->X);
           $colX[]=$x1;
           $colX[]=$x2;
       }
       //Tim OCRIndex
       $listPoint  = array();
       $fp1 = new Point();
       $fp1->X = $colX[2*$colIndex];
       $fp1->Y = $Y1;
       $listPoint[]=$fp1;
       $fp2 = new Point();
       $fp2->X = $colX[2*$colIndex+1];
       $fp2->Y = $Y2;
       $listPoint[]=$fp2;
       $fp3 = new Point();
       $fp3->X = $colX[2*$colIndex+1];
       $fp3->Y = $Y2 + 100;
       $listPoint[]=$fp3;
       $fp4 = new Point();
       $fp4->X = $colX[2*$colIndex];
       $fp4->Y = $Y1 + 100;
       $listPoint[]=$fp4;
         
       $listOCR = GetListOCRValueByPolygon($listPoint, self::$OCRArray);
	$OCRindex= new OCRValue();
       if(count($listOCR)>0)
       {
           $OCRindex = $listOCR[0];
           $Y1 =$OCRindex->Y1 +20;
           $Y2 =$OCRindex->Y2 +20;
       }
	//echo "Loi P1: ".$fp1->X. ":".$fp1->Y." P2: ".$fp2->X. ":".$fp2->Y." P3: ".$fp3->X. ":".$fp3->Y." P4: ".$fp4->X. ":".$fp4->Y;
	  
       //Tinh toan 
        $bNotStopCheck=TRUE;
       $currentItem = $OCRindex;
       
        while($bNotStopCheck)
        {
            $row = new RowItem();
            $row->listGroupInItem = array();
            $arrayRows = array();
            //Xet col at colIndex
         
           //Tim next index
            $listPointNext  = array();
            $np1 = new Point();
            $np1->X = $colX[2*$colIndex];
            $np1->Y =$Y1 +5;
            $listPointNext[]=$np1;
            $np2 = new Point();
            $np2->X = $colX[2*$colIndex+1];
            $np2->Y =  $Y2 +5;;
            $listPointNext[]=$np2;
            $np3 = new Point();
            $np3->X = $colX[2*$colIndex+1];
            $np3->Y =  $Y2 +200;
            $listPointNext[]=$np3;
            $np4 = new Point();
            $np4->X = $colX[2*$colIndex];
            $np4->Y =  $Y1 +200;
            $listPointNext[]=$np4;
           
            $listNextOCR = GetListOCRValueByPolygon($listPointNext, self::$OCRArray);
            $OCRNextindex = new OCRValue();
           // echo "<br>";
            if(count($listNextOCR)>0)
            {
               // echo "Found ".$listNextOCR[0]->description .": " ;
                $OCRNextindex = $listNextOCR[0];
                $Y1 =$OCRNextindex->Y1 +20;
                $Y2 =$OCRNextindex->Y2 +20;
            }
            else {
              // 	echo "Loi P1: ".$np1->X. ":".$np1->Y." P2: ".$np2->X. ":".$np2->Y." P3: ".$np3->X. ":".$np3->Y." P4: ".$np4->X. ":".$np4->Y;
                $OCRNextindex->Y1 = $Y1 +100;
                $OCRNextindex->Y2 = $Y2 +100;
                $bNotStopCheck=FALSE;
            }
             
            for($c=0;$c<$iNumOfCol; $c++)
            {
                $currGroupInCollIndex = new GroupInItem();
                $rec =  array();
                $r1 = new Point();
                $r1->X = $colX[2*$c];
                $r1->Y = $currentItem->Y1 -20;
                $rec[]=$r1;
                $r2 = new Point();
                $r2->X = $colX[2*$c+1];
                $r2->Y =  $currentItem->Y2 -20;
                $rec[]=$r2;
                $r3 = new Point();
                $r3->X = $colX[2*$c+1];
                $r3->Y =  $OCRNextindex->Y1 -10;
                $rec[]=$r3;
                $r4= new Point();
                $r4->X = $colX[2*$c];
                $r4->Y =  $OCRNextindex->Y2 -10;
                $rec[]=$r4;
                $listCurrOCRColl = GetListOCRValueByPolygon($rec, self::$OCRArray);
               
		//if(count($listCurrOCRColl)==0) echo "Loi P1: ".$r1->X. ":".$r1->Y." P2: ".$r2->X. ":".$r2->Y." P3: ".$r3->X. ":".$r3->Y." P4: ".$r4->X. ":".$r4->Y;
		//foreach($listCurrOCRColl as $test)
		//		{
		//			echo $test->description." ";
		//		}
				
                $currGroupInCollIndex->listOCRValue = $listCurrOCRColl;
                $currGroupInCollIndex->P1 = $r1;
                $currGroupInCollIndex->P2 = $r2;
                $currGroupInCollIndex->P3 = $r3;
                $currGroupInCollIndex->P4 = $r4;
                $row->listGroupInItem[] = $currGroupInCollIndex;
               
            }
             $ListRowItemResult[] = $row;
            $currentItem = $OCRNextindex;
        }
       
        //Convert to old template
       $arrayListRow = array();
       //add title
        $arrayCol = array();
       foreach ($arrayGroupTitle->listGroupInItem as $col)
       {
           $str ="";
               foreach ($col->listOCRValue as $colOCR)
               {
                   $str = $str." ".$colOCR->description;
               }
               $arrayCol[] = $str;
       }
       $arrayListRow[] = $arrayCol;
       foreach ($ListRowItemResult as $itemRow)
       {
           $arrayCol = array();
           foreach ($itemRow->listGroupInItem as $col)
           {
               $str ="";
               foreach ($col->listOCRValue as $colOCR)
               {
                   $str = $str." ".$colOCR->description;
               }
               $arrayCol[] = $str;
           }
           
           $arrayListRow[] = $arrayCol;
       }
       
      //  return $ListRowItemResult;
       return $arrayListRow;
   }
   function ClusterringListItem($listItem)
   {
       if(count($listItem)==0) return;
       $listGroup = array();
       $firstGroup = new GroupInItem();
       $firstGroup->listOCRValue = array();
       $firstGroup->listOCRValue[] = $listItem[0];
    //   echo $listItem[0]->description;
       $firstGroup->P1 = new Point();
       $firstGroup->P1->X = $firstGroup->listOCRValue[0]->X1;
       $firstGroup->P1->Y = $firstGroup->listOCRValue[0]->Y1;
       $firstGroup->P2 = new Point();
       $firstGroup->P2->X = $firstGroup->listOCRValue[0]->X2;
       $firstGroup->P2->Y = $firstGroup->listOCRValue[0]->Y2;
       $firstGroup->P3 = new Point();
       $firstGroup->P3->X = $firstGroup->listOCRValue[0]->X3;
       $firstGroup->P3->Y = $firstGroup->listOCRValue[0]->Y3;
       $firstGroup->P4 = new Point();
       $firstGroup->P4->X = $firstGroup->listOCRValue[0]->X4;
       $firstGroup->P4->Y = $firstGroup->listOCRValue[0]->Y4;
       $listGroup[] = $firstGroup;
       $bNew = FALSE;
       for($i=1; $i<count($listItem);$i++)
       {
           $bNew = TRUE;
           for($j=0; $j<count($listGroup); $j++)
            {
               if($this->CheckDistanFromItem2Group($listItem[$i],$listGroup[$j]))
               {
                   $bNew = FALSE;
                   $listGroup[$j]->listOCRValue[] = $listItem[$i];
                   //Update new Point
                    $listGroup[$j]->P1->X = min($listGroup[$j]->P1->X,$listItem[$i]->X1);
                    $listGroup[$j]->P1->Y = min($listGroup[$j]->P1->Y,$listItem[$i]->Y1);
                    $listGroup[$j]->P2->X = max($listGroup[$j]->P2->X,$listItem[$i]->X2);
                    $listGroup[$j]->P2->Y = min($listGroup[$j]->P2->Y,$listItem[$i]->Y2);
                    $listGroup[$j]->P3->X = max($listGroup[$j]->P3->X,$listItem[$i]->X3);
                    $listGroup[$j]->P3->Y = max($listGroup[$j]->P3->Y,$listItem[$i]->Y3);
                    $listGroup[$j]->P4->X = min($listGroup[$j]->P4->X,$listItem[$i]->X4);
                    $listGroup[$j]->P4->Y = max($listGroup[$j]->P4->Y,$listItem[$i]->Y4);
                    break;
               }
            } 
            
            if($bNew)
            {
                    $newGroup =new GroupInItem();
                    $newGroup->listOCRValue = array();
                    $newGroup->listOCRValue[] = $listItem[$i];
                    $newGroup->P1 = new Point();
                    $newGroup->P1->X = $newGroup->listOCRValue[0]->X1;
                    $newGroup->P1->Y = $newGroup->listOCRValue[0]->Y1;
                    $newGroup->P2 = new Point();
                    $newGroup->P2->X = $newGroup->listOCRValue[0]->X2;
                    $newGroup->P2->Y = $newGroup->listOCRValue[0]->Y2;
                    $newGroup->P3 = new Point();
                    $newGroup->P3->X = $newGroup->listOCRValue[0]->X3;
                    $newGroup->P3->Y = $newGroup->listOCRValue[0]->Y3;
                    $newGroup->P4 = new Point();
                    $newGroup->P4->X = $newGroup->listOCRValue[0]->X4;
                    $newGroup->P4->Y = $newGroup->listOCRValue[0]->Y4;
                    $listGroup[] = $newGroup;
                    
                   
            }
       }
      return $listGroup;
   }
   
   function MappinglistGroup2List($listGroup) 
   {
       $listNewGroup = array();
       
       //Calculate max col
       $indexMaxCol=0;
       $indexGroup =$listGroup[0];
       $maxColl= count($listGroup[0]);
       for($i =0; $i<count($listGroup); $i++)
       {
           $group = $listGroup[$i];
           $numCol = count($group);
           if($numCol>$maxColl)
           {
               $maxColl= $numCol;
               $indexMaxCol=$i;
               $indexGroup = $listGroup[$i];
           }
          
       }
      
       $listRows = array();
       for($i=0; $i<count($listGroup); $i++)
       {
           $rows = array();
           $currentGroup = $listGroup[$i];
           $index=0;
           for($k=0; $k<$maxColl; $k++)
           {
               if($index==count($currentGroup)) break;
               if(self::check2ListOCRValueInCol($currentGroup[$index],$indexGroup[$k]))
               {
                   //Add str to rows
                   $group = $currentGroup[$index];
                   $s="";
                   foreach ($group->listOCRValue as $OCRitem)
                             $s = $s." " .$OCRitem->description;
                   $rows[] =$s;
                   $index++;
               }   
               else
               {
                   $rows[] =" ";
               }
           }
           $listRows[] = $rows;
       }
       
       return $listRows;
   }
   
   function check2ListOCRValueInCol($list1,$list2)
   {
     // $list1 = new  GroupInItem();
    //  $list2 = new GroupInItem();
      if( Check2LineOverlap($list1->P4->X,$list1->P3->X,$list2->P4->X,$list2->P3->X)==TRUE)
      {
            return TRUE;
        }
       
        return FALSE;
   }
   
    function GetListOCRItemManual($listTitle, $listFirstItem)
    {
        $firstItemDetail = new ItemDetail();
        if(count($listFirstItem)>0)
        {
             $firstItemDetail->TopLeft=$p1;
             $firstItemDetail->TopRight=$p2;
             $firstItemDetail->BottomLeft=$p4;
             $firstItemDetail->BottomRight=$p3;
             $firstItemDetail->FullStr= $str;
             $firstItemDetail->Key = $firstItem;
        }
    }
    
   function ReSortOCRValueInItem($listOCRValue)
   {
   
      usort($listOCRValue, array($this, "cmp"));
      
     return $listOCRValue;
   }
   function cmp($a, $b)
    {
       if(abs($a->X1-$b->X1)<20)
       {
           if($a->Y1>$b->Y1) return TRUE;
           else return FALSE;
       }
       
       if($a->X1< $b->X1)     return FALSE;
         return TRUE;
    }
}