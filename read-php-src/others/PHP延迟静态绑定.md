## PHP延迟静态绑定

来源：[https://www.jellythink.com/archives/237](https://www.jellythink.com/archives/237)

时间 2018-04-01 23:47:25



## 嗅到了坏的味道

这段时间看项目后台的PHP代码，看到了类似于以下的一段代码，我把它抽出来：

```php
<?php
    class DBHandler {
        function get() {}
    }

    class MySQLHandler extends DBHandler {
        // 这里一个create
        public static function create() {
            echo "MySQL";
            return new self();
        }

        public function get() {
            echo "MySQL get()";
        }
    }

    class MemcachedHandler extends DBHandler {
        // 这里又有一个create
        public static function create() {
            echo "Memcached";
            return new self();
        }

        public function get() {
            echo "Memcached get";
        }
    }

    function get(DBHandler $handler) {
        $handler->get();
    }

    $dbHandler = MySQLHandler::create();
    get($dbHandler);
?>
```

有没有嗅到坏代码的味道？可以看到，在MySQLHandler和MemcachedHandler类中，都有一个create函数，除掉我的输出语句，发现它们一模一样，这就是代码冗余。是的，需要进行代码重构。


## 进行简单的重构

代码重构无处不在，只要你想，你觉的有改进，就需要敲起键盘开始干活。来吧，对上面的代码进行重构，如下：

```php
<?php
    class DBHandler {
        public static function create() {
            echo "create";
            return new self();
        }

        function get() {}
    }

    class MySQLHandler extends DBHandler {
        public function get() {
            echo "MySQL get()";
        }
    }

    class MemcachedHandler extends DBHandler {
        public function get() {
            echo "Memcached get";
        }
    }

    function get(DBHandler $handler) {
        $handler->get();
    }

    $dbHandler = MySQLHandler::create();
    get($dbHandler);
?>
```

将create函数移到DBHandler类中，看起来还不错，至少少了一坨那糟糕的代码。


## 貌似是错的

运行一下，却发现，并没有打印出我们期望的`MySQL get()`。什么情况？这说明，并没有调用MySQLHandler的get函数，但是代码明明调用了啊，这说明，`new self()`这句代码有问题。这有什么问题？这就需要说到今天总结的重点了————延迟静态绑定。


## 延迟静态绑定

在PHP5.3以后引入了延迟静态绑定。再看下面这段代码：

```php
<?php
    class A {
        public static function who() {
            echo __CLASS__;
        }
        public static function test() {
            self::who();
        }
    }

    class B extends A {
        public static function who() {
            echo __CLASS__;
        }
    }

    B::test();
?>
```

上面的代码输出了A，但是我希望它输出B，这就是问题的所在。这也是`self`和`__CLASS__`的限制。使用`self::`或者`__CLASS__`对当前类的静态引用，取决于定义当前方法所在的类。所以，这就很好的解释了为什么上面的代码输出了A。但是，如果我们需要输出B呢？可以这么干：

```php
<?php
    class A {
        public static function who() {
            echo __CLASS__;
        }
        public static function test() {
            static::who(); // 这里有变化，后期静态绑定从这里开始
        }
    }

    class B extends A {
        public static function who() {
            echo __CLASS__;
        }
    }

    B::test();
?>
```

后期静态绑定本想通过引入一个新的关键字表示运行时最初调用的类来绕过限制。简单地说，这个关键字能够让你在上述例子中调用 test() 时引用的类是 B 而不是 A。最终决定不引入新的关键字，而是使用已经预留的 static 关键字。

这就是后期静态绑定的根本————static关键字的另类用法。对于文章一开始的例子，可以这么改：

```php
return new static(); // 改变这里，后期静态绑定
```

这种使用后期静态绑定，在使用PHP实现23中设计模式的时候，你会感到很轻松的。


## 总结

就是一个很简单的知识点，但是却非常有用，总结起来，还是查了一些资料，补充一下知识点。温故而知新。好了，希望对大家有帮助。如果大家有什么建议，让我的文章写的更好，尽管提出来，我需要大家的帮助。

果冻想，认真玩技术的地方。

2015年2月2日 于深圳。


