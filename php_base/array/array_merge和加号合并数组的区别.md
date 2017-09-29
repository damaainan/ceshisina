

`array_merge()`合并数组

    //array_merge() 将一个或多个数组的单元合并起来，一个数组中的值附加在前一个数组的后面。返回作为结果的数组。

    如果输入的数组中有相同的字符串键名，则该键名后面的值将覆盖前一个值。
    然而，如果数组包含数字键名，后面的值将不会覆盖原来的值，而是附加到后面。
    如果只给了一个数组并且该数组是数字索引的，则键名会以连续方式重新索引。


    $a1=array("red","green");
    $a2=array("blue","yellow");
    print_r(array_merge($a1,$a2));//Array ( [0] => red [1] => green [2] => blue [3] => yellow )
    
    $a=array(3=>"red",4=>"green");
    print_r(array_merge($a));//Array ( [0] => red [1] => green )


