## 如何在 ThinkPHP 中整合 Laravel Eloquent ORM

来源：[https://segmentfault.com/a/1190000009038349](https://segmentfault.com/a/1190000009038349)

![][0]
## 前言

之前维护的旧项目采用的 ThinkPHP 3.2，后面学习了 Laravel 后，觉得 TP 的 Model 功能没有 Laravel 强大和方便，并想把 Laravel 里的 Eloquent 用在 TP 里。

好在 Laravel 的 ORM 是独立成包的，可以用于符合要求的其他 PHP 系统中。
## 整合

要使用的是 [illuminate/database][1] 。

* 安装 illuminate/database
根据自己使用的 PHP 版本，通过 composer 安装对应的  illuminate/database 版本，例如


```
composer require illuminate/database:5.3.* 
```

* 接入到 TP 中
在 ThinkPHPLibraryThinkThink.class.php 文件中的`start`方法的最后一行的` App::run(); `上方添加如下代码:


```php
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection([
            'driver'    => C('DB_TYPE'),
            'host'      => C('DB_HOST'),
            'database'  => C('DB_NAME'),
            'username'  => C('DB_USER'),
            'password'  => C('DB_PWD'),
            'charset'   => C('DB_CHARSET'),
            'collation' => C('DB_COLLATION'),
            'prefix'    => C('DB_PREFIX'),
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();
```

* 解决 E 方法冲突 
illuminate/database 的  vendorilluminatesupporthelpers.php 方法中存在一个方法


```php
  /**
     * Escape HTML special characters in a string.
     *
     * @param  \Illuminate\Contracts\Support\Htmlable|string  $value
     * @return string
     */
 function e($value)
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }
    
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
```

与 TP 的 E 方法冲突。

```php
/**
 * 抛出异常处理
 * @param string $msg 异常消息
 * @param integer $code 异常代码 默认为0
 * @throws Think\Exception
 * @return void
 */
function E($msg, $code=0) {
    throw new Think\Exception($msg, $code);
}
```

我选择注释了 illuminate/database 的方法，搜索后发现没有其他地方用到这个方法，故注释。

完成后就可以愉快地使用 Laravel 的 ORM 来 coding 了。
## tips

* TP 的数据库一般不会有 created_at 和 updated_at 字段，而 illuminate/database 会自动维护这两个字段，所以需要在创建的 Model 文件里，添加如下代码


```php
public $timestamps = false;
```

本文排版遵照 [中文文案排版指北（简体中文版）][2]

Enjoy it !
如果觉得文章对你有用，可以请我喝杯咖啡~

[1]: https://github.com/illuminate/database
[2]: https://github.com/mzlogin/chinese-copywriting-guidelines
[0]: ../../img/1460000009038352.png