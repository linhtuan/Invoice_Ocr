<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'KeyValue.php';
include_once 'OCRProcess.php';
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
    private static $OCRArray = array(); // Check if the point sits exactly on one of the vertices?
    public $keyArray = array();
    public static $anglePopular;
    function ListItemDetail($inOCRArray,$inAnglePopular) {
        self::$OCRArray =$inOCRArray;
        self::$anglePopular = $inAnglePopular;  
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
             if(strcasecmp($key,str_replace(array('#', '.', ':'), '',$itemOCR->description))==0)
             {
                 $labelItems = $itemOCR;
                
                 $index=$i;
                 break;
             }
        }
       
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
       
        $angle2 = GetAngleOfLine($firstItem->X4, $firstItem->Y4, $firstItem->X3, $firstItem->Y3);
       // echo  '<br>'. $p1->X .':'.$p1->Y;
        $tmpp2 = new Point();
        $tmpp2->X = $p1->X+2500;
        $tmpp2->Y = $p1->Y;
        $p2 = Rotate($p1, $tmpp2, 0,$angle2);
      //  echo '<br>'. $p2->X .':'.$p2->Y;
        $p4 = new Point();
        $p4->X = $firstItem->X1;
        $p4->Y = $nextItem->Y1-20;
        $tmpp3 = new Point();
        $tmpp3->X = $p1->X+2500;
        $tmpp3->Y = $p4->Y;
        $p3 = Rotate($p4, $tmpp3, 0,self::$anglePopular);
      //  echo '<br>'. $p3->X .':'.$p3->Y;
       //  echo '<br>'. $p4->X .':'.$p4->Y;
      // 
       
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

                $angle2 = GetAngleOfLine($nextItem->X4, $nextItem->Y4, $nextItem->X3, $nextItem->Y3);
               // echo  '<br>'. $p1->X .':'.$p1->Y;
                $tmpp2 = new Point();
                $tmpp2->X = $p1->X+2500;
                $tmpp2->Y = $p1->Y;
                $p2 = Rotate($p1, $tmpp2, 0,self::$anglePopular);
                //Verify 
                /////////////////////////////////
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
                           $indexNextTmp=$i;
                          
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
                $tmpp3 = new Point();
                $tmpp3->X = $p1->X+2500;
                $tmpp3->Y = $p4->Y;
                $p3 = Rotate($p4, $tmpp3, 0,self::$anglePopular);
                
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
    
   function GetListItemByKey($key)
   {
       $listItem = array();        
       $firstItem = $this->GetFirstItemByKey($key,true);
       $listItem = $this->GetListItem($firstItem);
      return  $listItem;
   }
   
   function ReSortOCRValueInItem($listOCRValue)
   {
       
      // $listOCRValue = $this->array_reorder_keys($listOCRValue,"X1");
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