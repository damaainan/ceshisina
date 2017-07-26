<?php 
header("Content-type:text/html; Charset=utf-8");
/**
 * 百钱买百鸡
 */


 function Main()
 {
     //公鸡的上线
     for ($x = 1; $x < 20; $x++)
     {
         //母鸡的上线
         for ($y = 1; $y < 33; $y++)
         {
             //剩余小鸡
             $z = 100 - $x - $y;

             if (($z % 3 == 0) && ($x * 5 + $y * 3 + $z / 3 == 100))
             {
                 echo "公鸡:".$x."只，母鸡:".$y."只,小鸡:".$z."只";
             }
         }
     }
 }
 Main();


function Main2()
{
    // int x, y, z;

    for ($k = 1; $k <= 3; $k++)
    {
        $x = 4 * $k;
        $y = 25 - 7 * $k;
        $z = 75 + 3 * $k;

       echo "公鸡:".$x."只，母鸡:".$y."只,小鸡:".$z."只";
    }

    
}
Main2();