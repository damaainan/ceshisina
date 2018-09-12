# PHP self 和 static 区别

 时间 2016-05-25 16:32:35  

原文[https://blog.zhengxianjun.com/2016/05/php-self-static/][1]


PHP `self` 指向定义的 class。

PHP `static` 指向运行的 class，一般只有子类覆盖父类的 `static` 成员或者方法时，在父类中使用 `static` 会访问到子类。

```php 
<?php
class ParentClass
{
    public static function hello()
    {
        echo "ParentClass: hello\n";
    }

    public static function run()
    {
        self::hello();
        static::hello();
    }
}

class ChildClass extends ParentClass
{
    public static function hello()
    {
        echo "ChildClass: hello\n";
    }
}

ParentClass::run();

// 输出
"ParentClass: hello"
"ParentClass: hello"

ChildClass::run();

// 输出
"ParentClass: hello"
"ChildClass: hello"
```

[1]: https://blog.zhengxianjun.com/2016/05/php-self-static/
