# 现代php阅读笔记系列-2

Published on 2016 - 12 - 17

 今天开始进行现代php阅读笔记系列的第二篇: 最佳实践。其中最佳实践包括标准，组件，良好实践这几方面的内容。

### 标准

* 为了提高社区内的组件的复用性，互操作性，职责单一性， php成立了自己的标准组织 php-fig. 这个标准规定了接口，自动加载机制，以及标准的风格，让框架相互合作。统一的自动加载机制，统一的接口让我们只要符合同样标准即可进行开发和使用第三方组件。
* `psr标准`包括`psr-1`,`psr-2`,`psr-3`，`psr-4`. 其中psr-1,psr-2主要讲了下关于编程代码规范的问题，例如类名必须使用驼峰命名，方法名使用xxxYyyyZzzz这种命名命名方式， php关键字使用小写(不写TRUE，写成true即可。) 此外还规定了不必使用var和_标示变量和私有方法， 直接使用public private protected来区别即可。
* psr-3规定了日志对接的统一接口。如果想写第三方组件的话，首先`composer require psr/log`, 然后需要继承`log`里面的`LoggerInterface`接口，并且实现其9个方法。大名鼎鼎的`Monolog`就是遵循psr-3接口实现的日志组件。这种思想跟golang里面那些db组件库是一个思路，都是实现标准接口进行扩展。
* psr-4是自动加载机制标准。大家都知道一般第三方组件都会使用`__autoload`魔术方法或者`spl_autoload_register`函数来完成自动加载，实现方法不一致，使用的时候就比较。psr-4中把命名空间的前缀和文件系统中的目录对应起来。例如你的空间前缀是 `\No13bus\Test`, `\No13bus\Test\MyLib`命名空间对应于 `src/MyLib` 目录， `\No13bus\Test\MyLib\Example`类对应于 `src\MyLib\Example.php`文件。

### 组件

* psr-4中虽然制定了统一的自动加载机制，但是还是需要自己在自己的组件库里面写autoload代码，后面的composer已经可以自动生成自动加载代码了。不需要自己单独写了。
* composer的具体使用方式以及中国镜像[见此][0]
* composer的私有库[搭建方式][1]
* 如何开始做自己的第一个[composer库][2]以及如何调试.
* composer.lock这个文件会保存当前项目中使用到的组件的版本号以及其他依赖信息，这个文件需要进入版本管理，团队其他成员composer install的话，就能保证他安装的是和你同样的版本库。如果要强制升级需要执行 composer update。
* 自己可以编写组件提交到 [www.packagist.org][3]. 写之前先去这个网站搜搜，别把自己的厂商名字以及库名和现有的重复了。这个网站还可以设置webhook钩子，如果github的代码更新了，packagist也会有所动作。库的功能要功能单一。

### 良好实践

* 数据校验包括三块内容： 过滤输入，验证数据，转义输出。
* 过滤输入。html的过滤方式使用 `htmlentities($html, ENT_QUOTES, 'utf-8')` 方法来转义单引号，双引号以及html实体。sql过滤的话使用PDO的相关方法来进行防sql注入。
* 过滤用户输入。 `filter_var`函数用来判断用户输入是否满足要求或者过滤一些敏感子串。这个函数相当强大，伴随有不同的过滤器有不同的功能。举个栗子:

```php
    //检测子串是否是邮箱，这样的话就不用写正则了。
    var_dump(filter_var('bob@example.com', FILTER_VALIDATE_EMAIL));
    //判断子串是否是有效的URL. FILTER_VALIDATE_URL是过滤器，FILTER_FLAG_PATH_REQUIRED是配合这个过滤器使用的相关参数。具体设置看文档。
    var_dump(filter_var('http://example.com', FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED));
    $options = array(
        'options' => array(
            'default' => 3, // value to return if the filter fails
           'max_range' => 300
            'min_range' => 10
        ),
        'flags' => FILTER_FLAG_ALLOW_OCTAL,
    );
    //判断整数是否在 [10,300]这个范围内
    $var = filter_var(200, FILTER_VALIDATE_INT, $options);
```
 上面说的是都是验证形的过滤器，要么返回false，要么返回原始子串。下面说一下 Sanitize filters， 它会过滤掉不符合条件的子串。

```php
    $number="5-2+3pp";
    var_dump(filter_var($number, FILTER_SANITIZE_NUMBER_INT));
    //输出 string(5) "5-2+3"
```

 下面说一下`filter_input`，这个会处理从前台传到php后端的请求数据(GET POST ENV等等)。抄了一个w3c的[例子][4]:

```php
    //INPUT_POST前台传来的POST数据 email是这个数据的一个变量。
    if (!filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL))
     {
            echo "E-Mail is not valid";
     }else{
            echo "E-Mail is valid";
     }
```

* 密码。 推荐使用`bcrypt`加密方式来对用户密码加密，避免彩虹表暴力破解。具体使用`password_hash`方法来生产密码，验证密码函数使用`password_verify`. `password_needs_rehash`函数用来判断当前的加密算是否是否需要重新hash。
* 日期 时间。建议使用DateTime类来处理这些时区，时间等等的格式处理。索性直接使用[Carbon][5]这个时间库来处理最好了，省心。要拥抱开源，如果不是为了学习的话，没必要重新造轮子，主要是造的还没别人好。
* 数据库连接库。推荐使用PDO。PDO使用的前提是要设置好DSN， 通过针对不同的数据库类型设置不同的DSN来连接和操作数据库，并不需要修改任何PDO的代码。
* 多字节字符串。多字节字符串的相关使用需要安装 mbstring扩展，这样才能正确统计子串的长度等属性。
* 流。流这一章节在平时工作中用的不是很多。稍微提一下 '`php://input`' 这种流可以用来接收 `$_POST`接收不到的数据，比如 通过Content-`type:form_data`和`Content-type: application/json`这种方式提交过来的数据。这种需要注意。
* 错误和异常。`try....catch`可以写多个catch捕获，比如 try....catch....catch....catch....catch 第一层捕获后后面的catch就不执行了。异常可以用`set_exception_handler`设置回调函数来处理异常。错误使用`error_reporting` 函数来确定哪些错误需要报告，哪些不需要。同样的`error`可以使用`set_exception_handler`设置回调函数来处理错误(记录日志).

[0]: http://www.phpcomposer.com/
[1]: http://www.cnblogs.com/maxincai/p/5308284.html
[2]: http://www.tuicool.com/articles/uAjyaev
[3]: http://www.packagist.org
[4]: http://www.w3school.com.cn/php/func_filter_input.asp
[5]: https://github.com/briannesbitt/Carbon