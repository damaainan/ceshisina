<?php      
    /** 
     * @todo 改进型KMP算法，模式串的移动字符数组 
     * @param $pattern 模式串 
     * @param $next 以用模式串的数组 
     * @author houweizong@gmail.com 
     * **/  
    function findNextVal($pattern,&$next){  
        $next[0] = -1;  
        $j = 0;  
        $k = -1;  
        $length = strlen( $pattern );  
        while( $j < $length ){  
            if( $k==-1 || $pattern[$j] == $pattern[$k] ){  
                $j++;  
                if( $pattern[$j] == $pattern[$k] ){  
                    $k++;  
                    $next[$j] = $k;  
                }else{  
                    $k<0 && $k++;      
                    $next[$j] = $next[$k]+1;                      
                }   
            }else{    
                $k = $next[$k];  
            }  
        }  
    }  
    /** 
     * @todo KMP算法，模式串的移动字符数组 
     * @param $pattern 模式串 
     * @param $next 以用模式串的数组 
     * @author houweizong@gmail.com 
     * **/  
    function findNext($pattern,&$next){  
        $next[0] = -1;  
        $j = 0;  
        $k = -1;  
        $length = strlen( $pattern );  
        while( $j < $length ){  
            while( $k > -1 && $pattern[$j] == $pattern[$k] ){  
                $j++;  
                $k++;  
                if($j==$length) break;  
                $next[$j]=$k;  
            }  
                if($k<=0){  
                    $k = 0;  
                    $j+=1;  
                    if($j==$length) break;  
                    $j !=0 && $next[$j] = 0;  
                }else{  
                    $k = $next[$k];  
                }  
        }  
    }  
    /** 
     * @todo kmp算法查找字符串 
     * @param string @string 目标串 
     * @param string @pattern 模式串 
     * @author houweizong@gmail.com 
     * **/  
    function kmp($string,$pattern){  
        $next=array();  
        findNextVal($pattern,$next);  
        $ml=strlen($string);  
        $pl=strlen($pattern);  
        $i=0;  
        $j=0;  
        while($i<$ml-$pl){  
            while($j==0||$j<$pl&&$string[$i]==$pattern[$j]){  
                $i++;  
                $j++;      
            }  
            if($j==$pl) return $i-$pl;  
            $j=$next[$j];  
        }  
        return -1;  
    }  
    $pattern='abshhh';  
    $string='ababababshhabhshhhabshhhabsabsabs';  
    $s=kmp($string,$pattern);  
    var_dump($s);  
