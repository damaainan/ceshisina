## PHP扩展开发教程6 - 扩展函数的参数类型(2)

来源：[https://segmentfault.com/a/1190000014450052](https://segmentfault.com/a/1190000014450052)

PHP扩展是高级PHP程序员必须了解的技能之一，对于一个初入门的PHP扩展开发者，怎么才能开发一个成熟的扩展，进入PHP开发的高级领域呢？本系列开发教程将手把手带您从入门进入高级阶段。
本教程系列在linux下面开发（推荐使用centos），php版本用的是5.6，并假设您有一定的linux操作经验和c/c++基础。
有问题需要沟通的朋友请加QQ技术交流群32550793和我沟通。继续上一节的内容，讲解扩展函数的参数类型，下面教程内容的相关源码已经上传到github上面，见param子目录下的演示代码。

```
git clone https://github.com/elvisszhang/phpcpp_demo.git
cd param
```
## 一、代码演示：对象类作为参数的用法

我们这里使用php的DateTime类作为扩展函数的参数来演示如何传入对象。
下面是该扩展函数的C++源码。

```c
//演示时间类型操作
void pm_datetype(Php::Parameters &params)
{
    Php::Value time = params[0];
    Php::out <<"param type is : " << time.type() << std::endl;
    Php::out <<"current time is : " << time.call("format","Y-m-d H:i:s") << std::endl;
}
```

注册扩展函数的代码

```c
myExtension.add("pm_datetype", {
    /****
        "time" : 表示参数名称，用于返回的异常信息中使用
        "DateTime"：参数对象的类名
        true ：表示该参数是必须的
    ****/
     Php::ByVal("time", "DateTime", true)
});
```

PHP测试代码（test/3.php）

```php
<?php
echo PHP_EOL . '-----TEST pm_datetype($time)-----' . PHP_EOL;
$time = new DateTime();
pm_datetype($time);

echo PHP_EOL . '-----TEST pm_datetype(\'2018-04-17\')-----' . PHP_EOL;
pm_datetype('2018-04-17');
?>
```

执行测试代码，结果如下

```
# php test/3.php

-----TEST pm_datetype($time)-----
2018-04-17 19:57:57

-----TEST pm_datetype('2018-04-17')-----
PHP Catchable fatal error:  Argument 1 passed to pm_datetype() must be an instance of DateTime, string given in /data/develop/phpcpp_param/test/3.php on line 7

```

根据测试结果可见：


* 参数类型指定为特定类的对象后，传入其他类型参数将触发生成一个fatal error
* 可以使用 Php::Value的 call方法来执行对象类的函数方法，非常方便。


## 二、代码演示：匿名函数或函数名称作为参数类型

大家知道c++的模板类可以让同一个类可以处理各种不同数据类型，非常强大。
下面实现一个冒泡排序算法，使用匿名函数作为参数，让这个冒泡排序算法也能够对各种不同类型元素的数组都能进行排序，而且不管正向反向，数字还是文本或者是复杂结构元素都能排序。

下面是该扩展函数的C++源码

```c
//演示通用的冒泡排序类
Php::Value pm_sort(Php::Parameters &params){
    int i,j;
    Php::Value array = params[0];
    Php::Value cmpfunc = params[1];
    int len = array.size();
    Php::Value result,temp;
    for(i=0;i<len;i++){
        for(j=i+1;j<len;j++){
            // Php::Value 类重载了运算符 (), 使得用起来就跟内置函数一样好用
             result = cmpfunc(array.get(i), array.get(j));
             if(result.boolValue()){ //如果比较结果为true则往上冒泡
                temp = array.get(i);
                array.set(i,array.get(j));
                array.set(j,temp);
             }
        }
    }
    return array;
}
```

注册该扩展函数的代码如下

```c
myExtension.add("pm_sort", {
     Php::ByVal("a", Php::Type::Array), //第一个是数组类型
     Php::ByVal("b", Php::Type::Callable) //第二个是函数类型
});
```

PHP测试代码（test/4.php）

```php
<?php
echo PHP_EOL . '-----数字降序排列-----' . PHP_EOL;
$result = pm_sort(array(22,3,15),function($a,$b){
    //$b > $a则往上冒泡，所以是降序排列
    return $b > $a;
});

echo var_export($result);

echo PHP_EOL . '-----数字升序排列-----' . PHP_EOL;
$result = pm_sort(array(22,3,15),function($a,$b){
    //$b < $a 则往上冒泡，所以是升序排列
    return $b < $a;
});
echo var_export($result);

echo PHP_EOL . '-----学生成绩降序排列-----' . PHP_EOL;
$score = array(
    array('name' => '张三', 'score'=>78),
    array('name' => '李四', 'score'=>98),
    array('name' => '王五', 'score'=>88),
);
$result = pm_sort($score,function($a,$b){
    //$b['score'] > $a['score'] 则往上冒泡，所以是按成绩进行降序排列
    return $b['score'] > $a['score'];
});
echo var_export($result);

echo PHP_EOL . '-----字符串按长度升序排列-----' . PHP_EOL;
function cmp_strlen($a,$b){
    //strlen($b) < strlen($a) 则往上冒泡，所以是按字符串长度进行升序排列
    return strlen($b) < strlen($a);
}
$result = pm_sort(array('country','I','love','my'),'cmp_strlen');
echo var_export($result);

echo PHP_EOL . '-----名字按首字母升序排列-----' . PHP_EOL;
class MyNameSort{
    public static function cmpLetter($a,$b){
        //首字母asscii码小的，则往上冒泡，所以是按首字母进行升序排列
        return ord($b[0]) < ord($a[0]);
    }
}
$result = pm_sort(array('Jack','Tom','Michael','Smith'),'MyNameSort::cmpLetter');
echo var_export($result);
?>
```

运行测试代码，输出结果如下

```
# php test/4.php

-----数字降序排列-----
array (
  0 => 22,
  1 => 15,
  2 => 3,
)
-----数字升序排列-----
array (
  0 => 3,
  1 => 15,
  2 => 22,
)
-----学生成绩降序排列-----
array (
  0 =>
  array (
    'name' => '李四',
    'score' => 98,
  ),
  1 =>
  array (
    'name' => '王五',
    'score' => 88,
  ),
  2 =>
  array (
    'name' => '张三',
    'score' => 78,
  ),
)
-----字符串按长度升序排列-----
array (
  0 => 'I',
  1 => 'my',
  2 => 'love',
  3 => 'country',
)
-----名字按首字母升序排列-----
array (
  0 => 'Jack',
  1 => 'Michael',
  2 => 'Smith',
  3 => 'Tom',
)

```

根据上述测试代码可见


* 函数类型的参数可以是匿名函数，
* 函数类型的参数也可以是字符串类型的函数名称
* 函数类型的参数还可以是类的静态函数的函数名
* 使用函数类型参数传入有助于实现高效简洁的代码


## 三、代码演示：引用类型的参数类型

按照官网文档的说法，PHP-CPP是支持引用类型的，而且官方文档还给了一个swap（参数值对换）的演示代码。我们按官网的文档进行一下实验。很遗憾，大家会发现对于PHP5.x这个特性是不支持的，PHP7.x系列也许支持，有条件的可以试验一下看看。

下面是该扩展函数的C++源码

```c
//测试引用类型参数
void pm_swap(Php::Parameters &params)
{
    Php::Value temp = params[0];
    params[0] = params[1];
    params[1] = temp;
}
```

注册该扩展函数的代码如下

```c
myExtension.add("pm_swap", {
        Php::ByRef("a", Php::Type::Numeric),
        Php::ByRef("b", Php::Type::Numeric)
});
```

PHP测试代码（test/5.php）

```php
$a = 123;
$b = 456;
echo 'before swap: $a = ' . $a . ' $b = ' . $b . PHP_EOL;
pm_swap($a,$b);
echo 'after swap: $a = ' . $a . ' $b = ' . $b . PHP_EOL;

// 如果直接输入常量，会导致类型检测通不过，触发php error。
//pm_swap(10,20);
```

运行测试代码，输出结果如下

```
before swap: $a = 123 $b = 456
after swap: $a = 123 $b = 456
```

根据测试结果可见，$a,$b的值还是保持原样，没有交换过来，所以引用类型的参数在PHP5.x事实上不支持。PHP7.x是否支持还需要进一步实验。
## 四、参考文献

[PHP-CPP官网 - 关于函数参数][0]
[PHP-CPP官网 - 关于Lambda函数][1]

[0]: http://www.php-cpp.com/documentation/parameters
[1]: http://www.php-cpp.com/documentation/lambda-functions