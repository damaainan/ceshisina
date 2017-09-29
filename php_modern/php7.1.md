# PHP 7.1新特性的汇总介绍

[![舒铭](https://pic1.zhimg.com/74ea936d4_xs.jpg)](https://www.zhihu.com/people/phpgod)[舒铭][0]

5 个月前

**本文背景**

> PHP版本升级的时候，新的语法糖和版本迁移说明都会在附录中呈现，热爱PHP的小伙伴都应该关注一下：[PHP: 附录 - Manual][1]

**一、可空类型**

可空类型主要用于参数类型声明和函数返回值声明。

**主要的两种形式如下：**

```php
    <?php
    function answer(): ?int {
     return null; //ok
    }

    function answer(): ?int {
     return 42; // ok
    }

    function say(?string $msg) {
     if ($msg) {
     echo $msg;
     }
    }
```

从例子很容易理解，所指的就是通过 `?` 的形式表明函数参数或者返回值的类型要么为指定类型，要么为 null。

**此方法也可用于接口函数的定义：**

```php
    <?php
    interface Fooable {
     function foo(?Fooable $f);
    }
```

但有一个需要注意的地方：如果函数本身定义了参数类型并且没有默认值，即使是可空的，也不能省略，否则会触发错误。如下：

```php
    <?php
    function foo_nullable(?Bar $bar) {}

    foo_nullable(new Bar); // 可行
    foo_nullable(null); // 可行
    foo_nullable(); // 不可行
```

但是如果以上函数的参数定义为 `?Bar $bar = null`的形式，则第三种写法也是可行的。因为 `= null` 实际上相当于 `?` 的超集，对于可空类型的参数，可以设定 `null` 为默认值。

**二、list 的方括号简写**

我们知道在 PHP5.4 之前只能通过`array()`来定义数组，5.4之后添加了 `[]` 的简化写法（省略了5个字符还是很实在的）。

```php
    <?php
    // 5.4 之前
    $array = array(1, 2, 3);
    $array = array("a" => 1, "b" => 2, "c" => 3);

    // 5.4 及之后
    $array = [1, 2, 3];
    $array = ["a" => 1, "b" => 2, "c" => 3];
```

引申到另外一个问题上，如果我们要把数组的值赋值给不同的变量，可以通过 list来实现：

```php
    <?php
    list($a, $b, $c) = $array;
```

是否也可以通过 `[]` 的简写来实现呢？

```php
    <?php
    [$a, $b, $c] = $array;
```

以及下一个特性中会提到的 `list`指定 `key`：

```php
    <?php
    ["a" => $a, "b" => $b, "c" => $c] = $array;
```

PHP7.1 实现了这个特性。但是要注意的是：出现在左值中的 `[]` 并不是数组的简写，是`list()`的简写。

但是并不仅仅如此，新的 `list()`的实现并不仅仅可以出现在左值中，也能在 foreach循环中使用：

```php
    <?php
    foreach ($points as ["x" => $x, "y" => $y]) {
     var_dump($x, $y);
    }
```

不过因为实现的问题，`list()` 和 `[]` 不能相互嵌套使用：

```php
    <?php
    // 不合法
    list([$a, $b], [$c, $d]) = [[1, 2], [3, 4]];

    // 不合法
    [list($a, $b), list($c, $d)] = [[1, 2], [3, 4]];

    // 合法
    [[$a, $b], [$c, $d]] = [[1, 2], [3, 4]];
```

**三、允许在 list 中指定 key**

上文提到过，新的 `list()`的实现中可以指定key：

```php
    <?php
    $array = ["a" => 1, "b" => 2, "c" => 3];
    ["a" => $a, "b" => $b, "c" => $c] = $array;
```

这也就相当于:

```php
    <?php
    $a = $array['a'];
    $b = $array['b'];
    $c = $array['c'];
```

和以往的区别在于以往的 `list()` 的实现相当于 key 只能是 0, 1, 2, 3 的数字形式并且不能调整顺序。执行以下语句：

```php
    <?php
    list($a, $b) = [1 => '1', 2 => '2'];
```

会得到PHP error: Undefined offset: 0...的错误。

而新的实现则可以通过以下方式来调整赋值：

```php
    <?php
    list(1 => $a, 2 => $b) = [1 => '1', 2 => '2'];
```

不同于数组的是，list并不支持混合形式的 key，以下写法会触发解析错误：

```php
    <?php
    // Parse error: syntax error, ...
    list($unkeyed, "key" => $keyed) = $array;
```

更复杂的情况，list也支持复合形式的解析：

```php
    <?php
    $points = [
     ["x" => 1, "y" => 2],
     ["x" => 2, "y" => 1]
    ];

    list(list("x" => $x1, "y" => $y1), list("x" => $x2, "y" => $y2)) = $points;

    $points = [
     "first" => [1, 2],
     "second" => [2, 1]
    ];

    list("first" => list($x1, $y1), "second" => list($x2, $y2)) = $points;
```

以及循环中使用：

```php
    <?php
    $points = [
     ["x" => 1, "y" => 2],
     ["x" => 2, "y" => 1]
    ];

    foreach ($points as list("x" => $x, "y" => $y)) {
     echo "Point at ($x, $y)", PHP_EOL;
    }
```

**四、void 返回类型**

PHP7.0 添加了指定函数返回类型的特性，但是返回类型却不能指定为 `void`，7.1 的这个特性算是一个补充：

```php
    <?php
    function should_return_nothing(): void {
     return 1; // Fatal error: A void function must not return a value
    }
```

以下两种情况都可以通过验证：

```php
    <?php
    function lacks_return(): void {
     // valid
    }

    function returns_nothing(): void {
     return; // valid
    }
```

定义返回类型为 `void`的函数不能有返回值，即使返回 null也不行：

```php
    <?php
    function returns_one(): void {
     return 1; // Fatal error: A void function must not return a value
    }

    function returns_null(): void {
     return null; // Fatal error: A void function must not return a value
    }
```

此外 void也只适用于返回类型，并不能用于参数类型声明，或者会触发错误：

```php
    <?php
    function foobar(void $foo) { // Fatal error: void cannot be used as a parameter type
    }
```

类函数中对于返回类型的声明也不能被子类覆盖，否则会触发错误：

```php
    <?php
    class Foo
    {
     public function bar(): void {
     }
    }

    class Foobar extends Foo
    {
     public function bar(): array { // Fatal error: Declaration of Foobar::bar() must be compatible with Foo::bar(): void
     }
    }
```

**五、类常量属性设定**

这个特性说起来比较简单，就是现在类中的常量支持使用 public、private和 protected修饰了：

```php
    <?php
    class Token {
     // 常量默认为 public
     const PUBLIC_CONST = 0;

     // 可以自定义常量的可见范围
     private const PRIVATE_CONST = 0;
     protected const PROTECTED_CONST = 0;
     public const PUBLIC_CONST_TWO = 0;

     // 多个常量同时声明只能有一个属性
     private const FOO = 1, BAR = 2;
    }
```

此外，接口（interface）中的常量只能是 public属性：

```php
    <?php
    interface ICache {
     public const PUBLIC = 0;
     const IMPLICIT_PUBLIC = 1;
    }
```

为了应对变化，反射类的实现也相应的丰富了一下，增加了 `getReflectionConstant`和 `getReflectionConstants`两个方法用于获取常量的额外属性：

```php
    <?php
    class testClass {
     const TEST_CONST = 'test';
    }

    $obj = new ReflectionClass( "testClass" );
    $const = $obj->getReflectionConstant( "TEST_CONST" );
    $consts = $obj->getReflectionConstants();
```

**六、多条件 catch**

在以往的 `try ... catch`语句中，每个 `catch`只能设定一个条件判断：

```php
    <?php
    try {
     // Some code...
    } catch (ExceptionType1 $e) {
     // 处理 ExceptionType1
    } catch (ExceptionType2 $e) {
     // 处理 ExceptionType2
    } catch (\Exception $e) {
     // ...
    }
```

新的实现中可以在一个 catch中设置多个条件，相当于或的形式判断：

```php
    <?php
    try {
     // Some code...
    } catch (ExceptionType1 | ExceptionType2 $e) {
     // 对于 ExceptionType1 和 ExceptionType2 的处理
    } catch (\Exception $e) {
     // ...
    }
```

对于异常的处理简化了一些。

**总结**

以上就是这篇文章的全部内容了，希望本文的内容对大家学习或者使用PHP7.1能有一定的帮助，如果有疑问大家可以留言交流。

**附：源 RFC 地址**

[Nullable Types][2]  
[Square bracket syntax for array destructuring assignment][3]  
[Allow specifying keys in list()][4]  
[Generalize support of negative string offsets][5]  
[Void Return Type][6]  
[Class constant visibility modifiers][7]  
[Multi catch][8]

[0]: https://www.zhihu.com/people/phpgod
[1]: http://link.zhihu.com/?target=http%3A//php.net/manual/zh/appendices.php
[2]: http://link.zhihu.com/?target=https%3A//wiki.php.net/rfc/nullable_types
[3]: http://link.zhihu.com/?target=https%3A//wiki.php.net/rfc/short_list_syntax
[4]: http://link.zhihu.com/?target=https%3A//wiki.php.net/rfc/list_keys
[5]: http://link.zhihu.com/?target=https%3A//wiki.php.net/rfc/negative-string-offsets
[6]: http://link.zhihu.com/?target=https%3A//wiki.php.net/rfc/void_return_type
[7]: http://link.zhihu.com/?target=https%3A//wiki.php.net/rfc/class_const_visibility
[8]: http://link.zhihu.com/?target=https%3A//wiki.php.net/rfc/multiple-catch