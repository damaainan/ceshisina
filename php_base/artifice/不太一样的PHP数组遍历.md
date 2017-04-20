# 0x00 使用 key() 和 next()

* mixed key ( array &$array )
* mixed next ( array &$array )

其中 key 是获取当前数组当前指针的 key，next 是让数组的指针后移。注意：在所以数组之中 keys($array) 的值为 0。

    # 代码
    $arr = ["one" => "zhangsan", "two" => "lisi", "three" => "wangwu"];
    while ($key = key($arr)) {
        echo $key, "\t", $arr[$key] . PHP_EOL;
        next($arr);
    }
    
    # 结果
    one     zhangsan
    two     lisi
    three   wangwu
    
    

# 0x01 使用 current() 和 next()

* mixed current ( array &$array )

current 是获取当前数组当前指针的 value。

    # 代码
    $arr = ["one" => "zhangsan", "two" => "lisi", "three" => "wangwu"];
    while ($value = current($arr)) {
        echo $value . PHP_EOL;
        next($arr);
    }
    
    # 结果
    zhangsan
    lisi
    wangwu
    

# 0x02 使用 each()

* array each ( array &$array )

each 是获取当前数组当前指针的 key／value，并将指针推进一个位置。

    # 代码
    $arr = ["one" => "zhangsan", "two" => "lisi", "three" => "wangwu"];
    while (list($key, $value) = each($arr)) {
        echo $key, "\t", $value . PHP_EOL;
    }
    
    # 结果
    one     zhangsan
    two     lisi
    three   wangwu
    

# 0x03 指针的其它操作

事实上对数组的遍历从易用性上我们还是会倾向于选择 for & foreach，但是这不限制我们去了解对数组指针的操作的学习。

* mixed next ( array &$array ) // 将数组指针指向下一个
* mixed prev ( array &$array ) // 将数组指针指向前一个位置，如果不存在则返回 FASLE
* mixed reset ( array &$array ) // 将数组指针设置到数组的开始的位置
* mixed end ( array &$array ) // 指针移动到数组的最后一个元素