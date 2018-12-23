## PHP中array_map()、array_walk()的使用总结

来源：[https://blog.wj2015.com/2018/12/07/php中array_map、array_walk的使用总结/](https://blog.wj2015.com/2018/12/07/php中array_map、array_walk的使用总结/)

时间 2018-12-07 10:10:19

 
 ![][0]
 
  
Contents
 

## 一句话描述  
 
array_map() 把数组每个元素都执行一下回调函数，回调函数的返回值作为数组对应key的新值
 
array_walk() 将数组每个元素都调用一下回调函数，回调函数返回值仅控制是否继续执行
 

## 参数描述  
 
array array_map ( callablecallback , array  array1 [, array $… ] )
 
【解析】array_map 传入无限个参数，第一个参数是回调函数，第二个及以后的参数是作用的目标数组
 
bool array_walk ( array &array , callable  callback [, mixed $userdata = NULL ] )
 
【解析】array_walk 传入三个参数，第一个参数是作用的目标数组，第二个参数是回调函数，第三个参数比较少用，可以传入用户自定义的数据
 

## 用途  
 `array_map()`可以用于遍历数组元素，并生成一个新的数组；`array_walk()`会将回调函数直接作用于每个数组元素上；
 
但是两者在使用上是有一定的区别存在的，然后借用 linus 一句名言：`talk is cheap, show me your code!`，请上代码！
 

#### 背景  
 
假设我们有一组学生数据如下，接下来的演示操作将基于此数据

```php
<?php
    $students = array(
        array('name' => '张三', 'age' => 21),
        array('name' => '赵四', 'age' => 22),
        array('name' => '王五', 'age' => 21)
    );
    $prefix = array(
        '尼古拉丁',
        '尼古拉斯',
        '史上最帅',
    );
    $score = array(
        9,
        8,
        10,
    );
```
 

#### 需求1：将所有学生的分数 * 10  
 
   
使用特性：遍历单个数组
 解法1：经典 `foreach`
 
【解析】`$value`使用引用的方式传递，可以直接修改数组的值

```php
<?php
    foreach ($students as &$value) {
        $value['score'] *= 10;
    }
    print_r($students);
```
 
解法2：`array_map`迭代
 
【解析】如果第二个参数往后只有一个数组，则会遍历此数组，并将数组的值作为回调函数的第一个参数传递进去(注意：只有值会被传递进去)，然后保持原数组的key不变，回调函数对应的返回值为新的value组成新数组并返回。

```php
<?php
    $students = array_map(function($value) {
        $value['score'] *= 10;
        return $value;
    }, $students);
    print_r($students);
```
 
解法2：`array_walk`迭代
 
【解析】第一个参数传入目标数组，第二个参数传入一个回调函数，第三个参数传入一个用户自定义的值，比如这里需要 * 10，我们就可以把这个数字作为第三个参数传递进去。
 
迭代时，会将数组的 value key 作为回调函数的 第一个参数和第二个参数，userdata 作为第三个参数传入（注意：如果需要改变数组的数据，第一个参数请使用引用传递）

```php
<?php
    array_walk($students, function(&$value, $key, $userdata) {
        $value['score'] *= $userdata;
    }, 10);
    print_r($students);
```
 

#### 结果均为：

```
Array
(
    [0] => Array
        (
            [name] => 张三
            [age] => 21
            [score] => 90
        )

    [1] => Array
        (
            [name] => 赵四
            [age] => 22
            [score] => 80
        )

    [2] => Array
        (
            [name] => 王五
            [age] => 21
            [score] => 100
        )

)
```
 

#### 需求2：将头衔 $prefx 拼接在 name 属性前  
 
   
使用特性：第三方数据传入
 解法1：经典 `foreach`
 
【解析】`$value`使用引用的方式传递，可以直接修改数组的值

```php
<?php
    foreach ($students as $key => &$value) {
        $value['name'] = $prefix[$key].'·'.$value['name'];
    }
```
 
解法2：array_map 匿名函数使用 use 传入三方参数，并使用 array_map 多数组参数的特性
 
【解析】`array_map`可以接受多个数组，并将多个数组的值依次作为回调函数的 第一个、第二个……参数传入，由于传入单数组不会回调数组的key，所以需要使用 array_keys($students) 提取数组的键作为回调的第二个参数。

```php
<?php
    $students = array_map(function ($value, $key) use ($prefix) {
        $value['name'] = $prefix[$key].'·'.$value['name'];
        return $value;
    }, $students, array_keys($students));
```
 
解法3: array_walk 第三个参数传入第三方参数
 
【解析】与 foreach 的写法类似，不过变成了函数迭代的形式

```php
<?php
    array_walk($students, function(&$value, $key, $userdata) {
        $value['name'] = $userdata[$key].'·'.$value['name'];
    }, $prefix);
```
 

#### 结果均为：

```
Array
(
    [0] => Array
        (
            [name] => 尼古拉丁·张三
            [age] => 21
        )

    [1] => Array
        (
            [name] => 尼古拉斯·赵四
            [age] => 22
        )

    [2] => Array
        (
            [name] => 史上最帅·王五
            [age] => 21
        )

)
```
 

#### 需求3：将头衔prefix 和  score 还有 $students 中的 name 组成新的数组  
 
使用特性：array_map 的多数组参数
 
使用场景：一般用于组合多个相同长度数组的元素，并形成有关联关系的数组
 
解法：使用 array_map 的多数组参数可以很方便的组合多个数组

```php
<?php
    $new_arr = array_map(function($val1, $val2, $val3){
        return [
            'prefix' => $val1,
            'score' => $val2,
            'name' => $val3['name'],
        ];
    }, $prefix, $score, $students);
    print_r($new_arr);
```
 

#### 输出结果：

```
Array
(
    [0] => Array
        (
            [prefix] => 尼古拉丁
            [score] => 9
            [name] => 张三
        )

    [1] => Array
        (
            [prefix] => 尼古拉斯
            [score] => 8
            [name] => 赵四
        )

    [2] => Array
        (
            [prefix] => 史上最帅
            [score] => 10
            [name] => 王五
        )

)
```
 
【注】foreach 或者 array_walk 均可以实现这个效果，可以自行探索下
 

#### 回调函数传入普通方法或者对象的方法  
 
callback 参数除了可以传入 「匿名函数」 之外，还可以传入 普通方法、实例化的对象方法、类的静态方法等
 

#### 回调普通方法  

```php
function addVal($value) {
    return $value + 10;
}
$arr = array(1, 2, 3, 4);
$arr = array_map('addVal', $arr);
print_r($arr);
```
 

#### 回调对象的某个方法  

```php
class Example {
    public function addVal($val) {
        return $val + 10;
    }
}
$exampleObj = new Example();
$arr = array(1, 2, 3, 4);
$arr = array_map(array($exampleObj, 'addVal'), $arr);
print_r($arr);
```
 

#### 回调类的静态方法  

```php
class Example {
    public static function addVal($val) {
        return $val + 10;
    }
}
$arr = array(1, 2, 3, 4);
$arr = array_map(array('Example', 'addVal'), $arr);
print_r($arr);
```
 

#### 输出结果

```
Array
(
    [0] => 11
    [1] => 12
    [2] => 13
    [3] => 14
)
```
 

#### 速度和选择  
 
参考网上的各种测试，三种方法里边，`foreach`的速度最快，其次是`array_map`，最后是`array_walk`，但是`array_walk`写起来会灵活一些，特别是第三个参数`userdata`，可以很方便的传入第三方数据，而不用想办法通过 全局变量、 function use 等方式传入三方参数。
 
因为 array_map 和 array_walk 均支持传入函数处理，所以在代码复用性上会有一定的优势。


[0]: https://img2.tuicool.com/fYbI7jV.jpg