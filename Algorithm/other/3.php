<?php
class LCS{
    public static function main(){
        //设置字符串长度  
        $substringLength1 = 20;  
        $substringLength2 = 20;  //具体大小可自行设置 
        $opt=array_fill(0,21,array_fill(0,21,null));  
         
        // 随机生成字符串  
        $x = self::GetRandomStrings($substringLength1);  
        $y = self::GetRandomStrings($substringLength2);  
         
        $startTime = microtime(true);
 
        // 动态规划计算所有子问题  
        for ($i = $substringLength1 - 1; $i >= 0; $i--){  
            for ($j = $substringLength2 - 1; $j >= 0; $j--){  
                if ($x[$i] == $y[$j])  
                    $opt[$i][$j] = $opt[$i + 1][$j + 1] + 1;          
                else 
                    $opt[$i][$j] = max($opt[$i + 1][$j], $opt[$i][$j + 1]);      
            }  
        } 
 
        echo "substring1:".$x."\r\n";  
        echo "substring2:".$y."\r\n";  
        echo "LCS:";  
 
 
        $i = 0;
        $j = 0;  
        while ($i < $substringLength1 && $j < $substringLength2){  
            if ($x[$i] == $y[$j]){  
                echo $x[$i];  
                $i++;  
                $j++;  
            } else if ($opt[$i + 1][$j] >= $opt[$i][$j + 1])  
                $i++;  
            else 
                $j++;  
        }  
 
        $endTime = microtime(true);
 
        echo "\r\n";
        echo "Totle time is " . ($endTime - $startTime) . " s";  
    }
 
    public static function GetRandomStrings($length){
        $buffer = "abcdefghijklmnopqrstuvwxyz";
        $str="";
        for($i=0;$i<$length;$i++){
            $random=rand(0,strlen($buffer)-1); 
            $str.=$buffer[$random];
        }
        return $str;
    }
}
 
 
 
LCS::main();