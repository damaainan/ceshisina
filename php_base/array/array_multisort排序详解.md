# [PHP数组array_multisort排序详解][0] 

今天特意再看了下官网的介绍，对它的多个数组的排序还是每台理解，找了些资料深入理解了下，在此总结下。

PHP中`array_multisort`函数 对多个数组或多维数组进行排序， 关联（string）键名保持不变，但数字键名会被重新索引。   
**输入数组被当成一个表的列并以行来排序——这类似于 SQL 的 ORDER BY 子句的功能。第一个数组是要排序的主要数组。数组中的行（值）比较为相同的话就按照下一个输入数组中相应值的大小来排序，依此类推。——这句话是理解此函数用法的关键。**

第一个参数必须是一个数组。接下来的每个参数可以是数组或者是下面列出的排序标志。

排序顺序标志：   
■`SORT_ASC` - 按照上升顺序排序   
■`SORT_DESC` - 按照下降顺序排序

排序类型标志：   
■`SORT_REGULAR` - 将项目按照通常方法比较   
■`SORT_NUMERIC` - 将项目按照数值比较   
■`SORT_STRING` - 将项目按照字符串比较

每个数组之后不能指定两个同类的排序标志。每个数组后指定的排序标志仅对该数组有效 - 在此之前为默认值 `SORT_ASC` 和 `SORT_REGULAR`。

参数数组被当成一个表的列并以行来排序——这类似于 SQL 的 `ORDER BY` 子句的功能。第一个数组是要排序的主要数组。数组中的行（值）比较为相同的话就按照下一个输入数组中相应值的大小来排序，依此类推。注意：作为参数的数组元素个数应保持一致，否则会报错。

先看一个对多个数组进行排序的例子。

 
```php

<?php

$array1 = array('one'=>'10','two'=>'20','three'=>'20','four'=>10);
$array2 = array('one'=>'10','two'=>'30','three'=>'20','four'=>'1');
$array3 = array('one'=>'C','two'=>'A','three'=>'B','four'=>'F');

array_multisort($array1,$array2,$array3);
print_r($array1);//Array ( [four] => 10 [one] => 10 [three] => 20 [two] => 20 )
print_r($array2);//Array ( [four] => 1 [one] => 10 [three] => 20 [two] => 30 )
print_r($array3);//Array ( [four] => F [one] => C [three] => B [two] => A )
```

在上面的例子中，首先对第一个参数数组进行排序(默认所有数组升序排序)，我们可以看出第一个数组中存在相同的值(键名‘one’和‘four’的键值相同，键名‘two’和‘three’的键值相同)，所以在排序第一个数组的相同值时就按照下一个输入数组中相应值的大小来排序(第二个数组‘four’的值小于‘one’的值，因此four的值排在one的前面)，依此类推。

在看一个，改变排序顺序的例子。

 
```php

$array1 = array('one'=>'10','two'=>'20','three'=>'20','four'=>10);
$array2 = array('one'=>'10','two'=>'30','three'=>'20','four'=>'1');
$array3 = array('one'=>'C','two'=>'A','three'=>'B','four'=>'F');

array_multisort($array1,SORT_DESC,$array2,SORT_ASC,$array3);
print_r($array1);//Array ( [three] => 20 [two] => 20 [four] => 10 [one] => 10 )
print_r($array2);//Array ( [three] => 20 [two] => 30 [four] => 1 [one] => 10 )
print_r($array3);//Array ( [three] => B [two] => A [four] => F [one] => C )
```

在这个例子中，第一个数组降序排序，碰到相同的值，按照第二个数组升序值进行排序。

注意：如果要是排序数组都是关联数组，则保留原有key名，若存在索引数组，则会按顺序重新建立索引。

 
```php

$array1 = array('one'=>'10',2=>'20',3=>'20',4=>10);    
$array2 = array('one'=>'10','2'=>'30','3'=>'20','four'=>'1');    
$array3 = array('one'=>'C','2'=>'A','3'=>'B','four'=>'F');    
    
array_multisort($array1,$array2,$array3);    
  
print_r($array1); //Array ( [0] => 10 [one] => 10 [1] => 20 [2] => 20 )   
print_r($array2); //Array ( [four] => 1 [one] => 10 [0] => 20 [1] => 30 )   
print_r($array3); //Array ( [four] => F [one] => C [0] => B [1] => A )
```

多维数组排序。

我们通常有一些多维数组需要排序：

 
```php

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
```

再来个一次对多个数组进行排序：

 
```php

$num1 = array(3, 5, 4, 3);
$num2 = array(27, 50, 44, 78);
array_multisort($num1, SORT_ASC, $num2, SORT_DESC);

print_r($num1);
print_r($num2);
//result: Array ( [0] => 3 [1] => 3 [2] => 4 [3] => 5 ) Array ( [0] => 78 [1] => 27 [2] => 44 [3] => 50 )
```

对多维数组（以二位数组为例）进行排序：

 
```php

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
```

理解有限.......可能有描述不正之处

无论从事什么行业，只要做好两件事就够了，一个是你的专业、一个是你的人品，专业决定了你的存在，人品决定了你的人脉，剩下的就是坚持，用善良專業和真诚赢取更多的信任。不忘初心 方得始终！

[0]: http://www.cnblogs.com/phpper/p/7604459.html