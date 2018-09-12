<?php

$array1 = array('one'=>'10','two'=>'20','three'=>'20','four'=>10);
$array2 = array('one'=>'10','two'=>'30','three'=>'20','four'=>'1');
$array3 = array('one'=>'C','two'=>'A','three'=>'B','four'=>'F');

array_multisort($array1,$array2,$array3);
print_r($array1);//Array ( [four] => 10 [one] => 10 [three] => 20 [two] => 20 )
print_r($array2);//Array ( [four] => 1 [one] => 10 [three] => 20 [two] => 30 )
print_r($array3);//Array ( [four] => F [one] => C [three] => B [two] => A )




$array1 = array('one'=>'10','two'=>'20','three'=>'20','four'=>10);
$array2 = array('one'=>'10','two'=>'30','three'=>'20','four'=>'1');
$array3 = array('one'=>'C','two'=>'A','three'=>'B','four'=>'F');

array_multisort($array1,SORT_DESC,$array2,SORT_ASC,$array3);
print_r($array1);//Array ( [three] => 20 [two] => 20 [four] => 10 [one] => 10 )
print_r($array2);//Array ( [three] => 20 [two] => 30 [four] => 1 [one] => 10 )
print_r($array3);//Array ( [three] => B [two] => A [four] => F [one] => C )



$array1 = array('one'=>'10',2=>'20',3=>'20',4=>10);    
$array2 = array('one'=>'10','2'=>'30','3'=>'20','four'=>'1');    
$array3 = array('one'=>'C','2'=>'A','3'=>'B','four'=>'F');    
    
array_multisort($array1,$array2,$array3);    
  
print_r($array1); //Array ( [0] => 10 [one] => 10 [1] => 20 [2] => 20 )   
print_r($array2); //Array ( [four] => 1 [one] => 10 [0] => 20 [1] => 30 )   
print_r($array3); //Array ( [four] => F [one] => C [0] => B [1] => A )



$guys = array(
    array('name'=>'jake', 'score'=>80, 'grade' =>'A'),
    array('name'=>'jina', 'score'=>70, 'grade'=>'A'),
    array('name'=>'john', 'score'=>70, 'grade' =>'A'),
    array('name'=>'ben', 'score'=>20, 'grade'=>'B')
);
//例如我们想按成绩倒序排列，如果成绩相同就按名字的升序排列。
//这时我们就需要根据$guys的顺序多弄两个数组出来：
$scores = array(80,70,70,20);
$names = array('jake','jina','john','ben');
//然后
array_multisort($scores, SORT_DESC, $names, $guys);

foreach($guys as $v){
    print_r($v);
    echo "<br/>";
}
/*
Array ( [name] => jake [score] => 80 [grade] => A )
Array ( [name] => jina [score] => 70 [grade] => A )
Array ( [name] => john [score] => 70 [grade] => A )
Array ( [name] => ben [score] => 20 [grade] => B )
*/



$num1 = array(3, 5, 4, 3);
$num2 = array(27, 50, 44, 78);
array_multisort($num1, SORT_ASC, $num2, SORT_DESC);

print_r($num1);
print_r($num2);
//result: Array ( [0] => 3 [1] => 3 [2] => 4 [3] => 5 ) Array ( [0] => 78 [1] => 27 [2] => 44 [3] => 50 )



$arr = array(
    '0' => array(
        'num1' => 3,
        'num2' => 27 
    ),
    
    '1' => array(
        'num1' => 5,
        'num2' => 50
    ),
    
    '2' => array(
        'num1' => 4,
        'num2' => 44
    ),
    
    '3' => array(
        'num1' => 3,
        'num2' => 78
    ) 
);

foreach ( $arr as $key => $row ){
    $num1[$key] = $row ['num1'];
    $num2[$key] = $row ['num2'];
}

array_multisort($num1, SORT_ASC, $num2, SORT_DESC, $arr);

print_r($arr);
//result:Array([0]=>Array([num1]=>3 [num2]=>78) [1]=>Array([num1]=>3 [num2]=>27) [2]=>Array([num1]=>4 [num2]=>44) [3]=>Array([num1]=>5 [num2]=>50))