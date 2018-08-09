<?php 

function test(){
 $a=1;
 $b=&$a;
 echo (++$a)+(++$a)+(++$a);
}
test();