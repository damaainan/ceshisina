<?php
    $arr_next=array();
    function get_next($str_s,&$arr_next){
        $i=0;            //初始化i为0，因为i指向的是要求next值得字符
        $j=-1;            //j始终指向期望与i指向的字符相等的字符，如果不相等，就只能让j向后退
        $arr_next[0]=-1;    //第一个字符的next值为-1表示j如果退到了这一步就已经无路可退了
        while($i<strlen($str_s)){
            //如果j已经无路可退或者i指向的字符和j指向的字符相等，则i和j同时向后移动
            //原理是如果j已经无路可退了，那么让j++，他就等于0，然后i指向的字符就等于0，那么第i个字符前面的
            //字符和从第一个开始的字符没有一个是相等的。如果它们两个指向的字符相等，那么就让i的next值等于当
            //前的j，表示i指向的字符往前面走的字符和从第一个开始的字符有j个是相等的。
            if($j==-1 || $str_s[$i]==$str_s[$j]){
                ++$j;
                ++$i;
                $arr_next[$i]=$j;
            }else
            //如果当前i指向的字符和j指向的字符不相等，而且j也不是没有退路，那么就让j退到当前字符的next
            //值上去，因为j的next值是下一个和j当前指向的字符具有相同属性的字符，也就是他们两个字符的前面
            //都有x个字符和开头的x个字符是相等的。
                $j=$arr_next[$j];
        }
        unset($arr_next[$i]);
    }
    function kmp($str_s,$str_d){
        $arr_next=array();
        get_next($str_s,$arr_next);
        $i=$j=0;
        //i和j都不能超过他们字符串的长度，加入i超过了，那么必然没有找到目标串，j超过了，那么一定是找到了。
        while($i<strlen($str_s) && $j<strlen($str_d)){
            if($j==-1||$str_s[$i]==$str_d[$j]){
                ++$i;
                ++$j;
            }else
                $j=$arr_next[$j];
        }
        if($j==strlen($str_d))
            return $i-strlen($str_d);
        else
            return 0;
    }

    $pattern='abshhh';  
    $string='ababababshhabhshhhabshhhabsabsabs';  
    $s=kmp($string,$pattern);  
    var_dump($s);  