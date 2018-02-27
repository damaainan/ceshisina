## PHP中二维数组去除重复项小记——可以类比php其他处理二维数组_排序，转换，去空白等等

PHP中提供了array_unique函数去除一维数组中的重复项，但是我们实际的项目开发中，从数据库select查询出来的数组经常是二维的；  
这里面可能有重复项，这就需要我们自己定义函数进行去除重复项。  
思路：  
    1、首先获取第二维数组的键名，保存在一个数组里面（假设命名为keyname_Arr）；  
    2、然后使用一个符号做分隔符（比如‘-’），将二维数组里面的键值拼接成一个字符串，生成一个临时数组；  
    3、然后使用【array_unique()函数】比较生成的这个临时数组，去掉里面的相同字符串；  
    4、然后将去除重复后的数组重新组装成二维数组：  
            在foreach()里面循环使用【explode()函数】，按‘-’分隔符拆分字符串；  
            同时在foreach()里面对拆分出来的字符串所形成的【新的临时数组tempnew】，再使用一个foreach( $tempnew as $tempk =>$tempv)，
            循环赋值 `$output[ $k ][ $keyname_Arr[ $tempk ] ] = tempv` ;
    6、最后，$output即是去除重复后的二维数组。   

来点实际代码理解一下：
```php
$keyname_Arr= array_keys(end($resource_arr));    //存储内层数组的键名

//使用'-'作为分隔将数组拼接成字符串
foreach ($resource_arr as $v){
    $v = join("-",$v);
    $temp[] = $v;
}

//去掉重复的字符串,也就是重复的一维数组
$temp = array_unique($temp);

//再将拆开的数组重新组装
foreach ($temp as $k => $v)
{
    $tempnew = explode("-",$v);//拆分去重之后的字符串

    foreach($tempnew as $tempkey => $tempval)
        $output[$k][$keyname_Arr[$tempkey]] = $tempval;
}
```