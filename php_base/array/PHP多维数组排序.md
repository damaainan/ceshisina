### [PHP多维数组排序][0]

突然想起了一道面试题，把一个多维数组排序。   
例：

```php
<?php
//有一个多维数组
$a = array(
    array('key1'=>940, 'key2'=>'blah'),
    array('key1'=>23, 'key2'=>'this'),
    array('key1'=>894, 'key2'=>'that')
);
//那么怎么对key1或者key2进行排序呢,这里就需要使用到usort($arr, 'myfunction')函数了，它的作用是对$arr使用我们自定义的方法进行排序，具体使用方法可以查看手册
//1.对key1的值进行排序
function asc_key1_sort($x, $y) {
    //可以输出一下看看是怎么比较的
    echo 'Iteration:'.$x['key1'].' vs '.$y['key1'];
    if($x['key1'] > $y['key1']) {
        echo 'true<br/>';
        return true;
    }elseif($x['key1'] < $y['key1']) {
        echo 'false<br/>';
        return false;
    }else {
        echo '0';
        return 0;
    }
}
//进行排序
usort($a, 'asc_key1_sort');
var_dump($a);
//2.对key2字符进行排序
function asc_key2_sort($x, $y) {
    //可以使用strcasecmp()函数进行排序
    echo 'Iteration:'.$x['key2'].' vs '.$y['key2'].'<br/>';
    return strcasecmp($x['key2'], $y['key2']);
}
//进行排序
usort($a, 'asc_key2_sort');
var_dump($a);
```

如果我的多维数组中也有key值呢？

```php
<?php
//有一个多维数组
$a = array(
    123 => array('key1'=>940, 'key2'=>'blah'),
    349 => array('key1'=>23, 'key2'=>'this'),
    43  => array('key1'=>894, 'key2'=>'that')
);
//那么怎么对key1或者key2进行排序呢,这里就需要使用到usort($arr, 'myfunction')函数了，它的作用是对$arr使用我们自定义的方法进行排序，具体使用方法可以查看手册
//1.对key1的值进行排序
function asc_key1_sort($x, $y) {
    //可以输出一下看看是怎么比较的
    echo 'Iteration:'.$x['key1'].' vs '.$y['key1'];
    if($x['key1'] > $y['key1']) {
        echo 'true<br/>';
        return true;
    }elseif($x['key1'] < $y['key1']) {
        echo 'false<br/>';
        return false;
    }else {
        echo '0';
        return 0;
    }
}
//进行排序
usort($a, 'asc_key1_sort');
var_dump($a);
//2.对key2字符进行排序
function asc_key2_sort($x, $y) {
    //可以使用strcasecmp()函数进行排序
    echo 'Iteration:'.$x['key2'].' vs '.$y['key2'].'<br/>';
    return strcasecmp($x['key2'], $y['key2']);
}
//进行排序
usort($a, 'asc_key2_sort');
var_dump($a);
```

这样的排序结果不会保留123，349，43。这时候只要把usort()换成uasort就好啦！

[0]: /sinat_21125451/article/details/51119978