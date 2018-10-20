## php – 如何在array数组上使用array_unique？

来源：[https://codeday.me/bug/20181015/297658.html](https://codeday.me/bug/20181015/297658.html)

时间 2018-10-15 14:24:49


  
我有一个数组

```
Array(
[0] => Array
    (
        [0] => 33
        [user_id] => 33
        [1] => 3
        [frame_id] => 3
    )

[1] => Array
    (
        [0] => 33
        [user_id] => 33
        [1] => 3
        [frame_id] => 3
    )

[2] => Array
    (
        [0] => 33
        [user_id] => 33
        [1] => 8
        [frame_id] => 8
    )

[3] => Array
    (
        [0] => 33
        [user_id] => 33
        [1] => 3
        [frame_id] => 3
    )

[4] => Array
    (
        [0] => 33
        [user_id] => 33
        [1] => 3
        [frame_id] => 3
    )
```

)

你可以看到键0与1,3和4相同.键2与它们不同.

对它们运行array_unique函数时,只剩下的是

```
Array (
[0] => Array
    (
        [0] => 33
        [user_id] => 33
        [1] => 3
        [frame_id] => 3
    )
```

)

为什么array_unique不能按预期工作的任何想法？


  
这是因为array_unique使用字符串比较来比较项目.从[docs][0]：

```
Note: Two elements are considered  equal if and only if (string) $elem1  === (string) $elem2. In words: when the string representation is the same.  The first element will be used.


```

数组的字符串表示形式就是数组,不管它的内容如何.

您可以使用以下操作来做所需的操作：

```php
$arr = array(
    array('user_id' => 33, 'frame_id' => 3),
    array('user_id' => 33, 'frame_id' => 3),
    array('user_id' => 33, 'frame_id' => 8)
);

$arr = array_intersect_key($arr, array_unique(array_map('serialize', $arr)));

//result:
/*
array
  0 => 
    array
      'user_id' => int 33
      'user' => int 3
  2 => 
    array
      'user_id' => int 33
      'user' => int 8
*/
```

以下是它的工作原理：


>每个数组项被序列化.这个

将基于阵列是独一无二的

内容.

>这个结果是通过array_unique来运行的,

所以只有数组有唯一的

留下签名.

> array_intersect_key将会占用

独特的项目的关键

地图/唯一功能(由于源数组的键被保留)并拉

他们从您的原始来源

阵列.


http://stackoverflow.com/questions/2561248/how-do-i-use-array-unique-on-an-array-of-arrays


[0]: http://php.net/manual/en/function.array-unique.php