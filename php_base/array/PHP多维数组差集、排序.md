# [PHP多维数组差集、排序][0]

 标签： [php][1][array][2]

 2017-07-14 20:29  18人阅读  

 分类：

版权声明：本文为博主原创文章，未经博主允许不得转载。

 目录

1. [差集][8]
1. [差集][9]
1. [多维素组差集][10]
1. [多维数组排序][11]

# 1.差集
```php
    if($query){
            foreach ($query as $value){
    
                $res[$value->day][]=$value->getAttributes(['period']);
            }
        }
    
    var_dump($res);
    die;
```

    $res=array(
        '2017-07-14'=>[['period'=>'11:00-11:30'],['period'=>'11:30-12:00'],['period'=>'12:00-12:30'], ['period'=>'12:30-13:00'],['period'=>'13:00-13:30'],['period'=>'13:30-14:00']],
        '2017-07-15'=>[['period'=>'11:00-11:30'],['period'=>'11:30-12:00'],['period'=>'12:30-13:00'],['period'=>'13:00-13:30'],['period'=>'13:30-14:00']],
        '2017-07-16'=>[['period'=>'11:00-11:30'],['period'=>'11:30-12:00'],['period'=>'12:00-12:30'],['period'=>'12:30-13:00'],['period'=>'13:00-13:30'],['period'=>'13:30-14:00']],   
    );

- - -

```php
           //求出今天已经过时的时间段
        $now = date('H:i');
        $expire = array();
        if (isset($res[$today])) {
            foreach ($res[$today] as $periods) {
                $period = $periods['period'];
                $start_time = substr($period, 0, 5);
    
                if ($now > $start_time) {
                    $expire[] = $periods;
                }
            }
        }
    
    
    //二维数组差集
        $c = array();
        foreach ($res[$today] as $record) {
            if (!in_array($record, $expire)) {
                $c[] = $record;
            }
        }
    
        if ($c) {
            $res[$today] = $c;
        } else {
            unset($res[$today]);
        }
    
        $data = array();
        if ($res) {
            foreach ($res as $key => $items) {
                $ret['day'] = $key;
                foreach ($items as $item) {
                    $ret['periods'][] = $item['period'];
                }
    
                $data[] = $ret;
                unset($ret);
    
            }
        }
```

    "data": [   
    {   
    "day": "2017-07-14",   
    "periods": [   
    "13:30-14:00"   
    ]   
    },   
    {   
    "day": "2017-07-15",   
    "periods": [   
    "11:00-11:30",   
    "11:30-12:00",   
    "12:00-12:30",   
    "12:30-13:00",   
    "13:00-13:30",   
    "13:30-14:00"   
    ]   
    },   
    {   
    "day": "2017-07-16",   
    "periods": [   
    "11:00-11:30",   
    "11:30-12:00",   
    "12:00-12:30",   
    "12:30-13:00",   
    "13:00-13:30",   
    "13:30-14:00"   
    ]   
    },

- - -

# 2.差集

```php
    $arr1 = array(
    array('appid'=>'1111','sku'=>'aaaa'),
    array('appid'=>'222','sku'=>'bbbb'),
    array('appid'=>'333','sku'=>'cccc'),
    array('appid'=>'444','sku'=>'ddd')
    );
    $arr2 = array(
    array('appid'=>'1111','sku'=>'aaaa'),
    array('appid'=>'222','sku'=>'bbbb'),
    array('appid'=>'555','sku'=>'ee')
    );
    //方法一，用闭包
    $r = array_filter($arr1, function($v) use ($arr2) { return ! in_array($v, $arr2);});
    print_r($r);
    //方法二
    foreach($arr1 as $k=>$v) if(in_array($v, $arr2)) unset($arr1[$k]);
    print_r($arr1);
```

    Array   
    (   
    [2] => Array   
    (   
    [appid] => 333   
    [sku] => cccc   
    ) 

    [3] => Array   
    (   
    [appid] => 444   
    [sku] => ddd   
    )   
    )

- - -

# 3.多维素组差集

```php
    <?php
    //多维数组的差集
    function array_diff_assoc_recursive($array1,$array2){
        $diffarray=array();
        foreach ($array1 as $key=>$value){
            //判断数组每个元素是否是数组
            if(is_array($value)){
                //判断第二个数组是否存在key
                if(!isset($array2[$key])){
                    $diffarray[$key]=$value;
                    //判断第二个数组key是否是一个数组
                }elseif(!is_array($array2[$key])){
                    $diffarray[$key]=$value;
                }else{
                    $diff=array_diff_assoc_recursive($value, $array2[$key]);
                    if($diff!=false){
                        $diffarray[$key]=$diff;
                    }
                }
            }elseif(!array_key_exists($key, $array2) || $value!==$array2[$key]){
                $diffarray[$key]=$value;
            }
        }
        return $diffarray;
    }
    $array1=array(1,2,3,array(1,2,array(1)));
    $array2=array(1,2,4,array(1,2,3));
    print_r(array_diff_assoc_recursive($array1,$array2)); 
```

    Array ( [2] => 3 [3] => Array ( [2] => Array ( [0] => 1 ) ) )

- - -

# 4.多维数组排序

```php
    //list数组将根据分数排序
    foreach ($list as $key => $row) {
            $volume[$key] = $row['score'];
    }
    
    array_multisort($volume, SORT_DESC, $list);
```

- - -

```php
    <?php
    $data[] = array('volume' => 67, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 1);
    $data[] = array('volume' => 85, 'edition' => 6);
    $data[] = array('volume' => 98, 'edition' => 2);
    $data[] = array('volume' => 86, 'edition' => 6);
    $data[] = array('volume' => 67, 'edition' => 7);

    // 取得列的列表
    foreach ($data as $key => $row) {
        $volume[$key]  = $row['volume'];
        $edition[$key] = $row['edition'];
    }
    
    // 将数据根据 volume 降序排列，根据 edition 升序排列
    // 把 $data 作为最后一个参数，以通用键排序
    array_multisort($volume, SORT_DESC, $edition, SORT_ASC, $data);
    ?> 
```

    volume | edition   
    —+—–   
    98 | 2   
    86 | 1   
    86 | 6   
    85 | 6   
    67 | 2   
    67 | 7

[0]: http://blog.csdn.net/abel004/article/details/75136437
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/array
[7]: #
[8]: #t0
[9]: #t1
[10]: #t2
[11]: #t3