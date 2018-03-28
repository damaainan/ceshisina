## PHP提取多维数组指定一列的方法大全

来源：[https://segmentfault.com/a/1190000010849556](https://segmentfault.com/a/1190000010849556)


PHP中对多维数组特定列的提取，是个很常用的功能，正因为如此，PHP在5.5.0版本之后，添加了一个专用的函数array_column()。当然，如果你的PHP版本低于5.5.0，就得用别的方法处理了。

例如，对于以下这个数组：

```php
$user = array(
    '0' => array('id' => 100, 'username' => 'a1'),
    '1' => array('id' => 101, 'username' => 'a2'),
    '2' => array('id' => 102, 'username' => 'a3'),
    '3' => array('id' => 103, 'username' => 'a4'),
    '4' => array('id' => 104, 'username' => 'a5'),
);
```

我们要提取其中的 usename 列，变成：

    $username = array('a1', 'a2', 'a3', 'a4', 'a5');

方法有以下几种。
1. array_column函数法

这是最简单的方法，但是要求PHP版本必须是5.5.0及以上版本，方法：

    $username = array_column($user, 'username');

2. array_walk函数法


array_walk()函数使用用户自定义函数对数组中的每个元素做回调处理，实现当前功能的方法：

```php
$username = array();
    array_walk($user, function($value, $key) use (&$username){
        $username[] = $value['username'];
    });

```

3. array_map函数法

array_map()函数和array_walk() 作用类似，将回调函数作用到给定数组的单元上。

```php
$username = array();
array_map(function($value) use (&$username){
    $username[] = $value['username'];
}, $user);
```

4. foreach循环法

foreach循环相对上面的方法效率稍微低一些，但简单容易理解。

```php
$username = array();
foreach ($user as $value) {
    $username[] = $value['username'];
}
```

5. array_map变种

方法如下，意为把$user数组的每一项值的开头值移出，并获取移除的值作为新数组。注意此时新数组$username的键仍是原数组$user的键，如下。

    $username = array_map('array_shift', $user);

注意：该功能会获取$user中的 id 列，而不是 username 列。

另外，如果需要获取二维数组每一项的开头列或结尾列，也可以这样做：

    $username = array_map('reset', $user);
    $username = array_map('end', $user);


这三个变种方法作用比较局限，仅在获取第一列或最后一列的时候有用，在复杂的数组中就难以发挥作用了。
