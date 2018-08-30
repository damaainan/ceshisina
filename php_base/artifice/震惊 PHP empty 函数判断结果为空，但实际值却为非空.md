## 震惊 PHP empty 函数判断结果为空，但实际值却为非空

时间：2018-05-28 09:31:45

来源：[https://laravel-china.org/articles/12530/the-shock-php-empty-function-is-empty-but-the-actual-value-is-nonempty](https://laravel-china.org/articles/12530/the-shock-php-empty-function-is-empty-but-the-actual-value-is-nonempty)

最近我在一个项目中使用 **`empty`**  时获取到了一些意料之外的结果。下面是我处理后的调试记录，在这里与你分享了。

```php
var_dump(
    $person->firstName,
    empty($person->firstName)
);
```

它的结果是：

```
string(5) "Freek"
bool(true)
```

结果出人意料。为什么变量的值为字符串，但同时会是空值呢？让我们在 **`$person->firstName`**  变量上尝试使用其它一些函数来进行判断吧：

```php
var_dump(
    $person->firstName,
    empty($person->firstName),
    isset($person->firstName),
    is_null($person->firstName)
);
```

以上结果为：

```
string(5) "Freek"
bool(true) // empty
bool(true) // isset
bool(false) // is_null
```


译者注：这边的结果可能存在问题 isset 的结果同样为 false，可以到 [这里][0] 去运行下查看结果。

 **`isset`**  和 **`is_null`**  函数执行结果符合预期判断，唯独 **`empty`**  函数返回了错误结果。

这里让我们来看看 **`person`**  类的实现代码吧：

```php
class person
{
    protected $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }
}

```

从上述代码我们可以看到 **`Person`**  对象的成员变量是通过 **`__get`**  魔术方法从 **`$attributes`**  数组中检索出来的。

当将变量传入一个普通函数时， **`$person->firstName`**  会先进行取值处理，然后再将获取到的结果作为参数传入函数内。

但是 **`empty`**  不是一个函数，而是一种数据结构。所以当将 **`$person->firstName`**  传入 **`empty`**  时，并不会先进行取值处理。而是会先判断 **`$person`**  对象成员变量 **`firstName`**  的内容，由于这个变量并未真实存在，所以返回 **`false`** 。

在正中应用场景下，如果你希望 **`empty`**  函数能够正常处理变量，我们需要在类中实现 **`__isset`**  魔术方法。

```php
class Person
{
    protected $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset($name)
    {
        $attribute = $this->$name;

        return !empty($attribute);
    }
}
```

这是当 **`empty`**  进行控制判断时，会使用这个魔术方法来判断最终的结果。

再让我们看看输出结果：

```php
var_dump(
   $person->firstName, 
   empty($person->firstName)
);
```

新的检测结果：

```
string(5) "Freek"
bool(false)
```

完美！

原文：[When empty is not empty][1]


本文章首发在 [Laravel China 社区][2]


[0]: https://www.tutorialspoint.com/execute_php_online.php
[1]: https://murze.be/when-empty-is-not-empty
[2]: https://laravel-china.org/