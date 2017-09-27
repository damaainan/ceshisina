# PHP数组的相关操作

 时间 2016-07-12 14:55:38  

原文[http://www.jianshu.com/p/387a51fe06c2][1]



### 创建数组

**索引数组**

    //索引数组
    $array1=array('a', 'b', 'c', 'd');
    //索引为整数，如果没有指定索引值则默认为零，依次递增；

**关联数组**

    //关联数组
    $arr2 = array(   
          "a" => 1,   
          "b" => 2,   
          "c" => 3, 
          "d" => 4,
     );
    //数组的key为字符串

**多维数组**

    //二维数组
    $cars = array
    (
          array("Volvo",22,18),
          array("BMW",15,13),
          array("Saab",5,2),
          array("Land Rover",17,15),
    );

**复合数组**

    //二维复合数组
    $ret = array(
          'id' => 2,
          'name' => '算法',
          'children' => array(
              array('id' => 5, 'name' => 'QUERY处理'),
              array('id' => 6, 'name' => 'URL处理'),
              array('id' => 7, 'name' => '内容处理'),
              array('id' => 8, 'name' => '统计计算'),
          ),
      );

**range函数创建索引数组**

    //该函数创建一个数组，包含从low到high（包含low和high）之间的整数或字符。如果high比low小，则返回反序的数组。
    range(low,high,step)
    $array = range(0,5);//0,1,2,3,4,5
    $array = range(0,50,10);//0,10,20,30,40,50
    $array = range("a","d");//a,b,c,d

### 遍历数组

**for循环遍历索引数组**

    $arr = array(1, 2, 3, 4);
      for ($i = 0; $i < count($arr); $i++) {
          echo $arr[$i];//1234
      };

**foreach循环遍历数组**

    $arr = array(
          "a" => 1,
          "b" => 2,
          "c" => 3,
          "d" => 4,
      );
    foreach ($arr as $key => $value) {
          var_dump($key . '=>' . $value);
    }//a=>1b=>2c=>3d=>4

**each函数遍历数组**

    //each() 函数返回当前元素的键名和键值，并将内部指针向前移动，该元素的键名和键值会被返回带有四个元素的数组中。两个元素（1 和 Value）包含键值，两个元素（0 和 Key）包含键名。如果内部指针越过了数组范围，函数将返回 FALSE。
    $sports = array('football' => 'good', 'swimming' => 'very well', 'running' => 'not good');
    while ($elem = each($sports)) {
        echo $elem[0] . ': ' . $elem[1];//football: goodswimming: very wellrunning: not good
        echo $elem['key'] . ': ' . $elem['value'];//football: goodswimming: very wellrunning: not good
    }

**list()搭配each遍历数组**

    //像 array() 一样，list不是真正的函数，而是语言结构。list()用一步操作给一组变量进行赋值。
    $sports = array('football' => 'good', 'swimming' => 'very well', 'running' => 'not good');
    for (; list($key, $value) = each($sports);) {
        echo $key . ': ' . $value;
    }//football: goodswimming: very wellrunning: not good

### 数组添加元素

**赋值添加**

    //直接对数组变量赋值$arr[key] = value;
    $arr['a'] = 1;
    var_dump($arr);//'a'=>1

**中括号[]形式添加**

    //如果方括号中没有指定索引值，则取当前最大整数索引值，新的键名将是该值加1。如果当前还没有整数索引，则键名将为0。
    $arr = array('a', 'b', 'c');
    $arr[] = 'd';
    var_dump($arr);//'a','b','c','d'

**使用array_push()函数**

    //array_push() 函数向第一个参数的数组尾部添加一个或多个元素（入栈），然后返回新数组的长度。该函数等于多次调用 $array[] = $value。
    //注释：即使数组中有字符串键名，您添加的元素也始终是数字键。
    //注释：如果用 array_push() 来给数组增加一个单元，还不如用 $array[] =，因为这样没有调用函数的额外负担。
    //注释：如果第一个参数不是数组，array_push() 将发出一条警告。这和 $var[] 的行为不同，后者会新建一个数组。
    $arr=array("red","green");
    array_push($a,"blue","yellow");
    print_r($arr);//Array ( [0] => red [1] => green [2] => blue [3] => yellow )

**使用array_unshift()函数在数组开头添加元素**

    //array_unshift() 函数用于向数组插入新元素。新数组的值将被插入到数组的开头。被加上的元素作为一个整体添加，这些元素在数组中的顺序和在参数中的顺序一样。该函数会返回数组中元素的个数。
    $a=array("a"=>"red","b"=>"green");
    array_unshift($a,"blue");
    print_r($a);//Array ( [0] => blue [a] => red [b] => green )

### 数组删除元素

使用unset()函数

    //销毁指定的变量
    //该函数允许取消一个数组中的键名，要注意数组将不会重建索引,如需重新索引，可以使用array_values()函数。
    unset($arr[index]);
    
    $arr = array('a', 'b', 'c');
    unset($arr[1]);
    var_dump($arr);//0=>'a',2=>'c'

使用array_splice()函数

    //array_splice() 函数从数组中移除选定的元素，并用新元素取代它。该函数也将返回包含被移除元素的数组。
    //array array_splice ( array &$input , int $offset [, int $length [, array $ replacement ]] )
    //array_splice() 把 input 数组中由 offset 和 length 指定的单元去掉，如果提供了 replacement 参数，则用 replacement 数组中的单元取代。返回一个包含有被移除单元的数组。注意 input 中的数字键名不被保留。
    
    //如果 offset 为正，则从 input 数组中该值指定的偏移量开始移除。如果 offset 为负，则从 input 末尾倒数该值指定的偏移量开始移除。
    $input = array("red", "green", "blue", "yellow");
    array_splice($input, 2);
    //$input is now array("red", "green")
    
    //如果省略 length，则移除数组中从 offset 到结尾的所有部分。如果指定了 length 并且为正值，则移除这么多单元。如果指定了 length 并且为负值，则移除从 offset 到数组末尾倒数 length 为止中间所有的单元。小窍门：当给出了 replacement 时要移除从 offset 到数组末尾所有单元时，用 count($input) 作为 length。
    $input = array("red", "green", "blue", "yellow");
    array_splice($input, 1, count($input), "orange"); 
    //$input is now array("red", "orange")
    
    $input = array("red", "green", "blue", "yellow");   
    array_splice($input, -1, 1, array("black", "maroon"));
    //$input is now array("red", "green", "blue", "black", "maroon") 
    
    //如果给出了 replacement 数组，则被移除的单元被此数组中的单元替代。如果 offset 和 length 的组合结果是不会移除任何值，则 replacement 数组中的单元将被插入到 offset 指定的位置。
    $input = array("red", "green", "blue", "yellow");
    array_splice($input, 3, 0, "purple"); 
    //$input is now array("red", "green", "blue", "purple", "yellow");

array_pop()删除数组最后一个元素

    //返回数组的最后一个值。如果数组是空的，或者非数组，将返回 NULL。
    $a=array("red","green","blue");
    array_pop($a);
    print_r($a);//Array ( [0] => red [1] => green )

array_shift()删除数组第一个元素

    //array_shift() 函数删除数组中第一个元素，并返回被删除元素的值。
    $a=array("a"=>"red","b"=>"green","c"=>"blue");
    array_shift($a);
    print_r ($a);//Array ( [b] => green [c] => blue )
    
    //如果键名是数字的，所有元素都会获得新的键名，从 0 开始，并以 1 递增。
    $a=array(0=>"red",1=>"green",2=>"blue");
    array_shift($a);
    print_r ($a);//Array ( [0] => green [1] => blue )

### 数组修改元素

**利用key值修改**

    $arr=('a'=>1,'b'=>2,'c'=>3);
    $arr['b']=4;
    print_r($arr);//'a'=>1,'b'=>4,'c'=>3

### 数组排序

sort()函数对数组进行升序排序

    //按照字母升序对数组中的元素进行排序
    $cars=array("Volvo","BMW","SAAB");
    sort($cars);//'BMW','SAAB','Volvo'
    
    //按照字母降序对数组中的元素进行排序
    $numbers=array(3,5,1,22,11);
    sort($numbers);//1,3,5,11,22

rsort()函数对数组进行降序排序

    $numbers=array(3,5,1,22,11);
    rsort($numbers);//22,11,5,3,1

asort()函数根据值对数组排序

    $age=array("Steve"=>"37","Bill"=>"35","Peter"=>"43");
    asort($age);//'Bill'=>35,'Steve'=>37,'Peter'=>43

ksort()函数利用键对数组排序

    $age=array("Bill"=>"35","Steve"=>"37","Peter"=>"43");
    ksort($age);//'Bill'=>35,'Peter'=>43,'Steve'=>37

arsort()函数对值进行逆序排序

    $age=array("Bill"=>"35","Steve"=>"37","Peter"=>"43");
    arsort($age);//'Peter'=>43,'Steve'=>37,'Bill'=>35

krsort()函数对键进行逆序排序

    $age=array("Bill"=>"35","Steve"=>"37","Peter"=>"43");
    krsort($age);//'Steve'=>37,'Peter'=>43,'Bill'=>35

array_multisort()函数进行多维数组排序

    //array_multisort()可以用来一次对多个数组进行排序，或者根据某一维或多维对多维数组进行排序。参数中的数组被当成一个表的列并以行来进行排序，这类似 SQL 的 ORDER BY 子句的功能。第一个数组是要排序的主要数组。数组中的行（值）比较为相同的话，就会按照下一个输入数组中相应值的大小进行排序，依此类推。
    array_multisort(array1,sorting order,sorting type,array2,array3...)
    bool array_multisort ( array &$arr [, mixed $arg = SORT_ASC [, mixed $arg = SORT_REGULAR [, mixed $... ]]] )
    //排序顺序标志：SORT_ASC、SORT_DESC
    //排序类型标志：SORT_REGULAR、SORT_NUMERIC、SORT_STRING
    
    //Eample1:最简单的情况，数组中的列是对应的着的。
    $arr1 = array(1,3,2);
    $arr2 = array(5,4,6);
    array_multisort($arr1,$arr2);
    print_r($arr1); // 1,2,3
    print_r($arr2); // 5,6,4
    
    //Example2:对数据库结果进行排序，把 volume降序排列，把 edition升序排列。
    $data[] = array('volume' => 67, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 1);
    $data[] = array('volume' => 85, 'edition' => 6);
    $data[] = array('volume' => 98, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 6);
    $data[] = array('volume' => 67, 'edition' => 7);
    //现在有了包含有行的数组，但是 array_multisort() 需要一个包含列的数组，因此用以下代码来取得列，然后排序。
    foreach ($data as $key => $row) {
          $volume[$key]  = $row['volume'];
          $edition[$key] = $row['edition'];
      }
    array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data);//array(6) { [0]=> array(2) { ["volume"]=> int(98) ["edition"]=> int(2) } [1]=> array(2) { ["volume"]=> int(86) ["edition"]=> int(1) } [2]=> array(2) { ["volume"]=> int(86) ["edition"]=> int(6) } [3]=> array(2) { ["volume"]=> int(85) ["edition"]=> int(6) } [4]=> array(2) { ["volume"]=> int(67) ["edition"]=> int(2) } [5]=> array(2) { ["volume"]=> int(67) ["edition"]=> int(7) } }
    //array_multisort()函数功能很强，读者自己参照例子和官方文档需要细细体会

### 数组其他常见的操作

array_keys()函数获取数组key合集

    //array_keys() 函数返回包含数组中所有键名的一个新数组。
    $arr = array('a' => 1, 'b' => 2, 'c' => 3);
    $keys = array_keys($arr);
    var_dump($keys);//'a','b','c'

array_values()获取数组值合集

    //array_values() 函数返回一个包含给定数组中所有键值的数组，但不保留键名。
    $a=array("Name"=>"Bill","Age"=>"60","Country"=>"USA");
    print_r(array_values($a));//Array ( [0] => Bill [1] => 60 [2] => USA )

array_unique()函数删除重复元素

    //array_unique() 函数移除数组中的重复的值，并返回结果数组。当几个数组元素的值相等时，只保留第一个元素，其他的元素被删除。返回的数组中键名不变。
    $a=array("a"=>"red","b"=>"green","c"=>"red");
    print_r(array_unique($a));//Array ( [a] => red [b] => green )
    
    $a=array(1,2,3,3,4,2);
    print_r(array_unique($a));//Array ( [0] => 1 [1] => 2 [2] => 3 [4] => 4 )

array_merge()合并数组

    //array_merge() 将一个或多个数组的单元合并起来，一个数组中的值附加在前一个数组的后面。返回作为结果的数组。如果输入的数组中有相同的字符串键名，则该键名后面的值将覆盖前一个值。然而，如果数组包含数字键名，后面的值将不会覆盖原来的值，而是附加到后面。如果只给了一个数组并且该数组是数字索引的，则键名会以连续方式重新索引。
    $a1=array("red","green");
    $a2=array("blue","yellow");
    print_r(array_merge($a1,$a2));//Array ( [0] => red [1] => green [2] => blue [3] => yellow )
    
    $a=array(3=>"red",4=>"green");
    print_r(array_merge($a));//Array ( [0] => red [1] => green )

array_slice()截取数组片段

    //start，开始位置。0=第一个元素。
    //preserve，可选。规定函数保留键名还是重置键名。true、false
    array_slice(array,start,length,preserve)
    
    $a=array("red","green","blue","yellow","brown");
    print_r(array_slice($a,1,2));//Array ( [0] => green [1] => blue )

### ......未完，待补充


[1]: http://www.jianshu.com/p/387a51fe06c2
