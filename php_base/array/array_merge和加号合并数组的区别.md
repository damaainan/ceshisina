

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


### PHP合并数组+和array_merge()的区别

同为数组合并，但是还是有差别的: 

* 键名为数字时，array_merge()不会覆盖掉原来的值，但＋合并数组则会把最先出现的值作为最终结果返回，而把后面的数组拥有相同键名的那些值“抛弃”掉（不是覆盖）

```
    $a = array('a','b'); 
    $b = array('c', 'd'); 
    $c = $a + $b; 
    var_dump($c);
    //输出：
    // array (size=2)
    //  0 => string 'a' (length=1)
    //  1 => string 'b' (length=1) 
    var_dump(array_merge($a, $b));
    //输出：
    //array (size=4)
    // 0 => string 'a' (length=1)
    // 1 => string 'b' (length=1)
    // 2 => string 'c' (length=1)
    // 3 => string 'd' (length=1)
```
* 键名为字符时，＋仍然把最先出现的键名的值作为最终结果返回，而把后面的数组拥有相同键名的那些值“抛弃”掉，但array_merge()此时会覆盖掉前面相同键名的值 

```
    $a = array('a' => 'a' ,'b' => 'b');
    $b = array('a' => 'A', 'b' => 'B');
    $c = $a + $b;
    var_dump($c);
    //输出：
    //array (size=2)
    //'a' => string 'a' (length=1)
    //'b' => string 'b' (length=1)
    var_dump(array_merge($a, $b));
    //输出：
    //array (size=2)
    //'a' => string 'A' (length=1)
    //'b' => string 'B' (length=1)
  ```  
