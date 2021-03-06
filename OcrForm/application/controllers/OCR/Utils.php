<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'OCRValue.php';
include_once 'Point.php';
include_once 'pointLocation.php';
/// <summary>
/// Funtion get angle of line
/// </summary>
/// <param name="p1"></param>
/// <param name="p2"></param>
/// <returns>Angle of line</returns>
function GetAngleOfLine($X1,$Y1,$X2,$Y2)
{
            $xDiff = $X2 - $X1;
            $yDiff = $Y2 - $Y1;
            return atan2($yDiff, $xDiff) * (180 / pi());
}
 /// <summary>
 /// Get angle popular of all line
 /// </summary>
/// <param name="listBill"></param>
 /// <returns>Angle of popular</returns>
function AnglePopular($OCRArray)
{
    $listAngle = array();
    foreach($OCRArray as $item)
    {
        if(strlen($item->description)>6)
        {
            $angle = GetAngleOfLine($item->X4,$item->Y4,$item->X3,$item->Y3);
            $listAngle[]= round($angle);
        }
    }
    //Sort $listangle
    sort($listAngle);
    //Find most popular angle
    $maxNumOfAngle=0;
    $iCountNumOfAngle=0;
    $indexMax =0;
    for($i=0; $i<(count($listAngle)-1); $i++)
    {
        $iCountNumOfAngle=0;
        $angle = $listAngle[$i];
        $indexTmp=$i;
         for($j=$i+1; $j<count($listAngle); $j++)
         {
             if($angle==$listAngle[$j])
             {
                 $iCountNumOfAngle++;
             }
             else
             {
                 $i=$j;
                 break;
             }
         }      
         if($maxNumOfAngle<$iCountNumOfAngle)
         {
             $maxNumOfAngle=$iCountNumOfAngle;
             $indexMax = $indexTmp;
            
         }
    }   
    
    return $listAngle[$indexMax];
}

function Rotate($p0, $p1, $angle,$anglePopular)
{
      $difAngle = -$angle + $anglePopular;
            // if(Math.Abs(difAngle)>5) difAngle =-angle;
      $theta = (pi() / 180.0) * $difAngle;
      $newPoint = new Point();
      $newPoint->X = (int)(cos($theta) * ($p1->X - $p0->X) - sin($theta) * ($p1->Y - $p0->Y) + $p0->X);
      $newPoint->Y = (int)(sin($theta) * ($p1->X - $p0->X) + cos($theta) * ($p1->Y - $p0->Y) + $p0->Y);

      return $newPoint;
}

 /// <summary>
        ///  Caculate distance from Point to Line
        /// </summary>
        /// <param name="A"></param>
        /// <param name="B"></param>
        /// <param name="B"></param>
        ///  <param name="Point"></param>
        /// <returns>Distance</returns>
function DistanceFromPoint2Line($A,$B,$C,$p)
{
    $tu = abs($p->X * $A + $p->Y * $B + $C);
    $mau = sqrt($A * $A + $B * $B);
    $distance = $tu / $mau;
    return $distance;
}

function StringIsDate($str)
{
      //Validate str is date time
       $arrayDate = date_parse($str);
       $year = $arrayDate['year'];
       $month = $arrayDate['month'];
       $day = $arrayDate['day'];
       
       if(!empty($year) && !empty($month) && !empty($day))
       { 
           return TRUE;
       }
	   if(!empty($month) && !empty($day))
       { 
           return TRUE;
       }
	 if(!empty($year) && !empty($month))
       { 
           return TRUE;
       }
	
       return FALSE;
}
 /// <summary>
        ///  Function for validate bill info 
        /// </summary>
        /// <param name="str">ID of bill</param>
        ///
function ValidateBillOrDate($str,$isDate)
{
    if($isDate==TRUE)
    {
        //Validate str is date time
        return StringIsDate($str);
      /* $arrayDate = date_parse($str);
       $year = $arrayDate['year'];
       $month = $arrayDate['month'];
       $day = $arrayDate['day'];
       
       if(!empty($year) && !empty($month) && !empty($day))
       {
           
           return TRUE;
       }
      
       return FALSE;
       */
    }
    else
    {
          // str = str.Replace("~", "");
        if (strlen($str) > 20) return FALSE;
        if (strpos($str, 'Date') != FALSE) {
            return FALSE;
        }
        if (strpos($str, 'Amount') != FALSE) {
            return FALSE;
        }
       
         return preg_match('/[0-9]/', $str);
    }

}

  function Pdf2Image($pathFile,$pageNum)
    {
        $im = new Imagick();
        $im->setResolution(96, 96);     //set the resolution of the resulting jpg
        $im->readImage('file.pdf['+$pageNum + ']');    //[0] for the first page
        $im->setImageFormat('jpg');
        header('Content-Type: image/jpeg');
        
        $imagedata = $im->getImageBlob();
        $base64String = base64_decode($imagedata);
        
        return $base64String;
    }
    
    
function CheckPointInRectangle($posX,$posY,$X1,$Y1,$X2,$Y2,$X3,$Y3,$X4,$Y4)
{
    $pointLocation = new pointLocation();
    $point = $posX." ".$posY;
    $polygon = array($X1." ".$Y1,$X2." ".$Y2,$X3." ".$Y3,$X4." ".$Y4);
    return $pointLocation->pointInPolygon($point, $polygon);
}

function CheckPointInPolygon($posX,$posY,$listPoint)
{
    $pointLocation = new pointLocation();
    $point = $posX." ".$posY;
    $polygon = array();
    foreach ($listPoint as $itemPoly)
    {
        $X = $itemPoly->X;
        $Y = $itemPoly->Y;
        $poly = $X." ".$Y;
        $polygon[] = $poly;
    }
    $X = $listPoint[0]->X;
    $Y = $listPoint[0]->Y;
    $poly = $X." ".$Y;
    $polygon[] = $poly;
 
    $ret = $pointLocation->pointInPolygon($point, $polygon);
   
    return $ret;
}
function Check2LineOverlap($x11,$x12,$x21,$x22)
{
   
    if($x11<=$x21)
    {
        if($x12>=$x21)            return TRUE;
        return FALSE;
    }
    else
    {
        if($x11<=$x22) return TRUE;
        return FALSE;
    }
    return FALSE;
}
function GetNewPoint($ip1,$anglePopular,$width)
{
    $newPoint = array();
    $tmpp1 = new Point();
    $tmpp1->X = 10;
   
    $tmpp1->Y = $ip1->Y;
    $p1 = Rotate($ip1, $tmpp1, 0,$anglePopular);
    $newPoint[]=$p1;
    
    $tmpp2 = new Point();
    $tmpp2->X = $width+10;
    $tmpp2->Y = $ip1->Y;
    $p2 = Rotate($ip1, $tmpp2, 0,$anglePopular);
    $newPoint[]=$p2;
    
    
    return $newPoint;
}

function ptdt($angle,$p)
{
    $a = $angle;
    $b=-1;
    $c = $p->Y-$a*$p->X;
    
    $pt = array();
    $pt[]=$a;
    $pt[]=$b;
    $pt[]=$c;
    return $pt;
}

function Check2ListIsSimilar($listOCR1, $listOCR2)
{
    $numSimilar= 0;
    foreach ($listOCR1 as $item1) {
        foreach ($listOCR2 as $item2)
        {
            $perc=0;
            //if(strcasecmp($key,str_replace(array('#', '.', ':'), '',$itemOCR->description))==0)
            similar_text(strtolower($item1->description),strtolower($item2->description),$perc);
            if($perc>85)
            {
                $numSimilar++;
                break;
            }
        }
        
    }
    if($numSimilar>(count($listOCR1)/1.3))
        return TRUE;
    
    return FALSE;
}
function Check2StringIsSimilar($str1, $str2)
{
  
            $perc=0;
            //if(strcasecmp($key,str_replace(array('#', '.', ':'), '',$itemOCR->description))==0)
            similar_text(strtolower($str1),strtolower($str2),$perc);
            if($perc>85)
            {
                return TRUE;
            }
     
   
    return FALSE;
}
function checkStringIsNumber($new_str)
    {
        if(is_numeric($new_str) ) //Check string is Int
        {
           return TRUE;
        }
        else
        {
            if(is_numeric(str_replace(".","",$new_str)) && strpos($new_str, ".") != false) //Check string is double
            {
                return TRUE;
            }
        }
        
        return FALSE;
    }