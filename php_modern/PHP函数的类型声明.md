## PHP函数的类型声明

来源：[https://tlanyan.me/argument-type-declare-in-php/](https://tlanyan.me/argument-type-declare-in-php/)

时间 2018-01-11 16:07:55


PHP7开始支持标量类型声明，强类型语言的味道比较浓。使用这个特性的过程中踩过两次坑：一次是声明boolean，最近是声明double。为避免以后继续犯类似错误，就把官方文档翻了一次。本文是看完后对PHP函数的类型声明使用做的一次总结。

从语法上，PHP的函数定义经过了几个时期：


## 远古时代（PHP 4）

定义一个函数非常的简单，使用`function name(args) {body}`的语法声明。不能指定参数和返回值类型，参数和返回值类型有无限种可能。这是到目前为止最常见的函数声明方式。


## 数组和引用类型参数值声明（PHP 5）

数组（array）、类（class）、接口（interface）、函数（callable）可以用在函数声明中。从5.6开始，支持常量（包括类常量）为默认参数，以及参数数组（以省略号…为前缀）。例如：

```php
function sum(...$numbers) {
    $sum = 0;
    foreach ($numbers as $number) {
        $sum += $number;
    }
    return $sum;
}

```

注意：如果参数的值可能为null，null必须为参数的默认值，否则调用时会出错。例如：

```php
function foo(array $arr = null) {
    ...
}

```


## 标量类型和返回值声明（PHP 7）

函数正式支持标量类型（int, bool, float等）和返回值类型（可声明类型同参数）声明。从这个版本开始，写PHP有像写java的感觉。

遗憾是如果函数返回值有可能是null，就不能指定返回值类型。例如：

```php
function getModel() : Foo {
    if ($this->_model === null) {
         $this->_model = xxxx;  // get from db or otherelse
    }
    return $this->_model;     // 如果$this->_model仍是null，运行出错
}

```


## 参数和返回值可为null以及void返回类型声明（PHP 7.1）

当参数和返回值类型有可能是null时，类型前以问号（?）修饰，可以解决null值问题（与默认参数不冲突）；类型声明新增iterable，同时还支持void类型返回值。例如：

```php
function getModel(?int $id) : ?Foo {
    if ($id !== null) {
        $this->_model = xxxx;
    } else {
        $this->_model = yyyy;
    }
    return $this->_model;
}
 
// 调用
$foo->getModel(null);
$foo->getModel(100);
 
// 函数声明了参数并且没有提供默认参数，调用时不传入参数会引发错误
// 将函数声明改成 getModel(?int $id = 100) {}，可以不传参数
$foo->getModel();

```

当函数返回值为void时，函数体的return后不能接任何类型，或者不出现return语句。

```php
function test(array $arr) : void {
    if (!count($arr)) {
        return;
    }
 
    array_walk($arr, function ($elem) {xxxx});
}

```

回顾完以上历史，可以看出到PHP 7.1，函数类型声明已经十分完善（虽然实践中用的不多）。

再说说实践中踩到的坑。参数和返回值类型声明可用的类型有：



* 类/接口
* self，只能用在自身的方法上
* array
* bool
* callable
* int
* float
* string
* iterable
  

注意列表中并没有boolean和double类型！除非你定义了这两个类型，否则用在参数和返回值中就是错误的！

这也是PHP有点蛋疼的地方。平常使用时的double和float两个关键字几乎等同，例如doubleval是floatval的别名，is_double是is_float的别名，转换时用(double)和(float)效果相同。但是到了类型声明这里就不行，同样的情况出现在bool和boolean身上。


## 总结

目前PHP 7.2稳定版已经发布，建议在新项目中尽量使用PHP 7.1及后续版本。为了写出清晰和可维护的代码，推荐声明类型。建议引用类型或者string才使用null值，int/float等标量类型的参数尽量不要用null。func_get_argc等函数，如非必要，尽量不使用。


### 参考



* [http://php.net/manual/en/functions.arguments.php][0]
    
* [http://php.net/manual/en/migration70.new-features.php][1]
    
* [http://php.net/manual/en/migration71.new-features.php][2]
    
  



[0]: http://php.net/manual/en/functions.arguments.php
[1]: http://php.net/manual/en/migration70.new-features.php
[2]: http://php.net/manual/en/migration71.new-features.php