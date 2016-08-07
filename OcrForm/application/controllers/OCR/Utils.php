<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once 'OCRValue.php';
include_once 'Point.php';
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
        if(strlen($item->description)>4)
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
 /// <summary>
        ///  Function for validate bill info 
        /// </summary>
        /// <param name="str">ID of bill</param>
        ///
       function ValidateBill($str)
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