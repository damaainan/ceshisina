# PHP数组的进阶用法


对于数组的基本操作，可参考[PHP数组的相关操作][1]

* **array_filter()过滤数组元素**  

array_filter()可以利用回调函数挨个过滤数组中的值，如果回调函数的返回值为true，当前值就会保留到结果数组中，键名保持不变。

    array array_filter ( array $array[, [callable]$callback[, int $flag = 0 ]] )
    $array//迭代的数组
    $callback//回调函数
    $flag//定义哪个变量应用于回调函数，ARRAY_FILTER_USE_KEY和ARRAY_FILTER_USE_BOTH

    //example1：筛选奇数
    function odd($var)
    { 
      // 判断书否是奇数，和1作位运算 
      return ($var & 1);
    }
    $array1 = array("a" => 1, "b" => 2, "c" => 3, "d" => 4, "e" => 5);
    print_r(array_filter($array1, "odd"));//['a'=>1,'c'=>3,'e'=>5]

    //example2：无回调函数，过滤空值
    $entry = array(
            0 => 'hello',
            1 => false,
            2 => -1,
            3 => null,
            4 => ''
        );
    print_r(array_filter($entry));//[0=>'hello',2=>-1]

    //example3：带flag参数
    $arr = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
    var_dump(array_filter($arr, function ($k) {
        return $k == 'b';
    }, ARRAY_FILTER_USE_KEY));//['b'=>2]
    var_dump(array_filter($arr, function ($v, $k) {
    return $k == 'b' || $v == 4;
    }, ARRAY_FILTER_USE_BOTH));//['b'=>2,'d'=>4]

- - -

* **array_walk()对数组中的元素应用用户自定义的函数**  

array_walk()可以将数组每个元素应用于自己定义的回调函数，如果成功则返回true，否则返回false。

    bool array_walk ( array &$array , callable $callback [, mixed $userdata = NULL ] )
    $array//数组
    $callback//用户定义的回调函数，参数是键名和键值
    $userdata//自定义的参数

    //example1
    function myfunction($value, $key)
    {
         echo "The key $key has the value $value";
    }
    $a = array("a" => "red", "b" => "green", "c" => "blue");
    array_walk($a, "myfunction");//The key a has the value red...

    //example2：自定义函数中的第一个参数指定为引用&$value，改变数组元素的值
    function myfunction(&$value,$key)
    {
         $value="yellow";
    }
    $a=array("a"=>"red","b"=>"green","c"=>"blue");
    array_walk($a,"myfunction");
    print_r($a);//['a'=>'yellow','b'=>'yellow','c'=>'yellow']

    //example3：增加自定义参数
    $fruits = array("d" => "lemon", "a" => "orange", "b" => "banana", "c" => "apple");
    function test_alter(&$item, $key, $prefix)
    {
         $item = "$prefix: $item";
    }
    array_walk($fruits, 'test_alter', 'fruit');//['d'=>'fruit: lemon','a'=>'fruit: orange','b'=>'fruit: banana','c'=>'fruit: apple']

[0]: /u/17f00ab32926
[1]: http://www.jianshu.com/p/387a51fe06c2