<?php  
// php实现通过图的深度优先遍历输出1,2,3...n的全排列
// $n=$_REQUEST["n"];  
$n=4;  
if($n>8)  
{  
    echo "{$n}太大了，影响服务器性能";  
    return;  
}  
define("N",$n);  
$d=array();  
$v=array();  
  
for($i=0;$i<=N;$i++){  
    $d[$i]=$v[$i]=0;  
}  
  
function dfs($depth){  
    global $d,$v;  
    if($depth>=N){  
        for($i=0;$i!=N;$i++){  
            echo $d[$i];  
        }  
        echo "\r\n";  
        return;  
    }  
    for($i=1;$i<=N;$i++){  
        if($v[$i]==0){  
            $v[$i]=1;  
            $d[$depth]=$i;  
            dfs($depth+1);  
            $v[$i]=0;  
        }  
    }  
}  
  
dfs(0); 