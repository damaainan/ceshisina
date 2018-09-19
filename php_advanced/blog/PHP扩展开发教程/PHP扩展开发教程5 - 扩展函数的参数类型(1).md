## PHP扩展开发教程5 - 扩展函数的参数类型(1)

来源：[https://segmentfault.com/a/1190000014415677](https://segmentfault.com/a/1190000014415677)

PHP扩展是高级PHP程序员必须了解的技能之一，对于一个初入门的PHP扩展开发者，怎么才能开发一个成熟的扩展，进入PHP开发的高级领域呢？本系列开发教程将手把手带您从入门进入高级阶段。
本教程系列在linux下面开发（推荐使用centos），php版本用的是5.6，并假设您有一定的linux操作经验和c/c++基础。
有问题需要沟通的朋友请加QQ技术交流群32550793和我沟通。前面两节介绍了如何用PHP-CPP编写常用的扩展函数，扩展类。对怎么使用PHP-CPP开发扩展应该已经很熟悉了，下面晋级学习一下关于扩展函数参数类型方面的内容。

下面教程内容的相关源码已经上传到github上面。

```
git clone https://github.com/elvisszhang/phpcpp_param.git
```
## 一、相关知识：PHP-CPP参数类型指定方式

有时候，我们开发的函数，我们希望只能传入特定的类型，例如字符串运算的函数只能传入字符串参数，数值运算的函数只能传入数字参数，数组操作的函数只能传入数组参数。

下面是扩展中如何指定一个函数参数类型的样例代码

```c
#includevoid example(Php::Parameters &params)
{
}

extern "C" {
    PHPCPP_EXPORT void *get_module() {
        static Php::Extension myExtension("my_extension", "1.0");
        myExtension.add<example>("example", {
            Php::ByVal("a", Php::Type::Numeric),
            Php::ByVal("b", "ExampleClass"),
            Php::ByVal("c", "OtherClass")
        });
        return myExtension;
    }
}
```

上面的 Php::ByVal 是代表值类型的参数的设定方式，聪明点的你应该猜到相对应还有一个 Php::ByRef的代表引用类型的参数设定方式。可惜的是在php5.x扩展上面，经过实验证明，引用方式的参数设定无效。
## 二、相关知识：Php::ByVal函数说明

Php::ByVal 是代表值类型的参数的设定方式，总共有两个函数原型定义。

第一种原型是给标量，数组，对象，匿名函数等参数类型使用的，其C++的函数定义如下。

```c
/**
 *  ByVal 值类型的参数的设定方式
 *  @param  name        参数名称
 *  @param  type        参数类型
 *  @param  required    参数是否必填，默认必填
 */
ByVal(const char *name, Php::Type type, bool required = true);
```

第二种原型是给具有特定类名的函数参数使用的，其C++的函数定义如下。

```c
/**
 *  ByVal 值类型的参数的设定方式
 *  @param  name        参数名称
 *  @param  classname   参数类名
 *  @param  nullable    是否可以为空
 *  @param  required    参数是否必填，默认必填
 */
ByVal(const char *name, const char *classname, bool nullable = false, bool required = true);
```

值得注意的是，PHP不存在所谓函数参数名这个说法，上面函数参数里面的name只是起一个助记符的作用，主要是在参数类型错误等异常情况下，抛出异常的错误信息里面使用，方便用户知道具体是哪个参数有问题。## 三、相关知识：Php::Type参数类型说明

ByVal函数中的Php::Type是个C++的枚举量，代表PHP-CPP的函数参数，总共支持以下11种类型。

```c
Php::Type::Null     - 表示任何类型都可以传入
Php::Type::Numeric  - 整数类型
Php::Type::Float    - 数值类型，支持整数、单精度浮点数、双精度浮点数
Php::Type::Bool     - 布尔类型
Php::Type::Array    - 数组类型
Php::Type::Object         - 对象类型
Php::Type::String         - 字符串类型
Php::Type::Resource       - 资源类型（保存有为打开文件、数据库连接、图形画布区域等的特殊句柄）
Php::Type::Constant       - 常量类型
Php::Type::ConstantArray  - 常量数组类型
Php::Type::Callable       - 函数类型
```

除了 Php::Type::Array or Php::Type::Object 这两个类型的参数使用起来比较特殊，必须使用专用的对应的C++类来操作。其他类型使用起来基本上差别不大，因为PHP::Value这个类已经做了类型重载处理，。
## 四、代码演示：阶乘运算

对于阶乘运算函数，我们都知道，需要而且只需传入一个正整数类型即可。
下面是该扩展函数的C++源码

```c
//演示阶乘
Php::Value pm_factorial(Php::Parameters &params)
{
    int n = (int)params[0];
    if(n < 0 )
        return 0;
    int i,f=1;
    for(i=1;i<=n;i++)
        f *= i;
    return f;
}
```

注册扩展函数的代码

```c
myExtension.add("pm_factorial", {
            Php::ByVal("a", Php::Type::Numeric)
});
```

PHP测试代码（test/1.php）

```php
<?php
echo PHP_EOL . '-----TEST pm_factorial()-----' . PHP_EOL;
var_dump( pm_factorial() );

echo PHP_EOL . '-----TEST pm_factorial(\'abc\')-----' . PHP_EOL;
var_dump( pm_factorial('abc','def') );

echo PHP_EOL . '-----TEST pm_factorial(\'5\')-----' . PHP_EOL;
var_dump( pm_factorial('5') );

echo PHP_EOL . '-----TEST pm_factorial(0)-----' . PHP_EOL;
var_dump( pm_factorial(0) );


echo PHP_EOL . '-----TEST pm_factorial(10)-----' . PHP_EOL;
var_dump( pm_factorial(10) );

echo PHP_EOL . '-----TEST pm_factorial(-10)-----' . PHP_EOL;
var_dump( pm_factorial(-10) );

echo PHP_EOL . '-----TEST pm_factorial(5.3)-----' . PHP_EOL;
var_dump( pm_factorial(5.3) );
?>
```

测试返回结果

``` 
# php test/1.php

-----TEST pm_factorial()-----
PHP Warning:  pm_factorial() expects at least 1 parameter(s), 0 given in /data/develop/phpcpp_param/test/1.php on line 3
NULL

-----TEST pm_factorial('abc')-----
int(1)

-----TEST pm_factorial('5')-----
int(120)

-----TEST pm_factorial(0)-----
int(1)

-----TEST pm_factorial(10)-----
int(3628800)

-----TEST pm_factorial(-10)-----
int(0)

-----TEST pm_factorial(5.3)-----
int(120)

```

根据以上测试结果，可以总结出：


* 对于整数类型参数，会自动把浮点数强制转成整形。
* 参数不足的时候，将会生成一个PHP警告，返回值为NULL。
* 整数类型的参数自动会转换字符串类型，无法转换时则转换成0。


## 五、代码演示：两个数值（浮点数）类型参数相加

以演示两个数字相加为例，我们希望传入两个参数，而且两个参数都是数值类型。
下面是该扩展函数的C++源码

```c
//演示两个数相加
Php::Value pm_add(Php::Parameters &params)
{
    return params[0] + params[1];
}
```

注册扩展函数的代码

```c
myExtension.add("pm_add", {
            Php::ByVal("a", Php::Type::Float),
            Php::ByVal("b", Php::Type::Float)
});
```

PHP测试代码（test/2.php）

```php
<?php
echo PHP_EOL . '-----TEST pm_add()-----' . PHP_EOL;
var_dump( pm_add() );

echo PHP_EOL . '-----TEST pm_add(1)-----' . PHP_EOL;
var_dump( pm_add(1) );

echo PHP_EOL . '-----TEST pm_add(\'abc\',\'def\')-----' . PHP_EOL;
var_dump( pm_add('abc','def') );

echo PHP_EOL . '-----TEST pm_add(\'1\',\'2\')-----' . PHP_EOL;
var_dump( pm_add('1','2') );

echo PHP_EOL . '-----TEST pm_add(1,2)-----' . PHP_EOL;
var_dump( pm_add(1,2) );

echo PHP_EOL . '-----TEST pm_add(1.3,2.4)-----' . PHP_EOL;
var_dump( pm_add(1.3,2.4) );
?>
```

运行测试代码，返回结果

``` 
# php test/2.php 

-----TEST pm_add()-----
PHP Warning:  pm_add() expects at least 2 parameter(s), 0 given in /data/develop/phpcpp_param/test/1.php on line 3
NULL

-----TEST pm_add(1)-----
PHP Warning:  pm_add() expects at least 2 parameter(s), 1 given in /data/develop/phpcpp_param/test/1.php on line 6
NULL

-----TEST pm_add('abc','def')-----
int(0)

-----TEST pm_add('1','2')-----
int(3)

-----TEST pm_add(1,2)-----
int(3)

-----TEST pm_add(1.3,2.4)-----
float(3.7)

```

根据以上测试结果，可以总结出：


* 对于数值类型参数，整数，浮点数都支持。
* 参数不足的时候，将会生成一个PHP警告，返回值为NULL。
* 数值类型的参数自动会转换字符串类型的数值，无法转换时则转换成0。


由于篇幅有限，其他的参数类型下一章节继续演示。
## 六、参考文献

[PHP-CPP官网 - 关于函数参数][0]

[0]: http://www.php-cpp.com/documentation/parameters