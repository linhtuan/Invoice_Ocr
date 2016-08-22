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
    public $keyArray = array();
    public static $anglePopular;
    public static $width=0;
    public static $height=0;
    
    function SetOcrArray($inOcrArray)
    {
        self::$OCRArray = $inOcrArray;
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
        $p1->Y = $firstItem->Y1+5;
       
        $newPoint12 = GetNewPoint($p1, self::$anglePopular,self::$width);
        $p1 = $newPoint12[0];
        $p2 = $newPoint12[1];
     
        $p4 = new Point();
        $p4->X = $firstItem->X1;
        $p4->Y = $nextItem->Y1-20;
       
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
    
   function GetListItemByKey($key,$colNumber)
   {
       $listItem = array();        
       $firstItem = $this->GetFirstItemByKey($key,true);
     //  var_dump($firstItem);
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
           $OCRKey = $listKey[count($listKey)-1];
       
       $listItem = array();        
       $firstItem = $this->GetFirstItemByPosition($OCRKey,true);
       
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