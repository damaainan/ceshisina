# PHP查找多个字符串的公共前缀【案例】

_发布时间：_ 2016-09-18 _作者：_ 迹忆 _浏览次数：_ 403

本篇和大家分享一个小算法的应用——查找字符串数组的公共前缀：
```
array(  
    'abcdefg',  
    'abcdfio',  
    'abcdqle'  
)
```
上面的数组字符串中，公共前缀为'abcd'。

首先想到的是从第一个字符串的第一个字符开始，依次与其它字符串的字符比较，都相等的我们将其保存起来，直到有一个不相等就结束后续的比较。

代码如下

```php
function commonPrefix($arr){
         $count = strlen($arr[0]);
    $prefix = '';
    for($i=0;$i<$count;$i++){
        $char = $arr[0][$i];
        $flag = true;
        foreach($arr as $val){
            if($char != $val[$i]){
                $flag = false;
                break;
            }
        }
        if(!$flag) break;
        $prefix .= $char;
    }
    return $prefix;
}
```

这段代码能很好的找出上面字符串数组的公共前缀。

但是上面那段程序存在一个比较严重的bug。当数组中有的字符串的长度比第一个字符串的长度小的时候就有可能出现错误。我们看下面的数组：

```
array(  
    'abcde',  
    'abc',  
    'abcrhgh',  
    'abcdfg',  
    'abcfg'  
);
```
我们看上面的数组知道，公共前缀为abc。在程序进行对比的过程中，当比较第四个字符d的时候，我们看第二个字符串是不是只有三个字符。因此$arr[1][3]没有这个变量。因此会报错。

针对这种情况有两种解决方法

方法一、每次进行对比之前判断变量是否存在，如果不存在的话直接结束后续的比较。

部分代码如下：

```php
foreach($arr as $val){
   if(!isset($val[$i]){
            $flag = false;
            break;
   }
   if($char != $val[$i]){
       $flag = false;
       break;
   }
}
```

方法二、在进行查找对比之前，先找出数组中最短字符串的长度。以此长度作为循环的终止条件。

代码如下：

```php
function commonPrefix($arr){
         $count = strlen($arr[0]);
for($i = 0;$i<count($arr);$i++){
    if(strlen($arr[$i]) <= $count){
        $count = strlen($arr[$i]);
    }
}
    $prefix = '';
    for($i=0;$i<$count;$i++){
        $char = $arr[0][$i];
        $flag = true;
        foreach($arr as $val){
            if($char != $val[$i]){
                $flag = false;
                break;
            }
        }
        if(!$flag) break;
        $prefix .= $char;
    }
    return $prefix;
}
```

以上就是整个查找字符串数组中公共前缀的代码。希望对大家有所帮助。

