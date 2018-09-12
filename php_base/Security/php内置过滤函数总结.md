# php内置过滤函数总结

 时间 2017-11-20 15:56:31  

原文[http://uknowsec.cn/posts/notes/php内置过滤函数总结.html][1]


点击阅读全文 

PHP本身内置了很多参数过滤的函数，以方便开发者简单有效且统一地进行安全防护，而这些函数可以分为多种类型，如SQL注入过滤函数，XSS过滤函数，命令执行过滤函数，代码执行过滤函数等等。

## SQL注入过滤函数 

SQL注入过滤函数有`addslashes()`,`mysql_real_escape_string()`以及`mysql_escape_string()`,它们的作用都是给字符串添加反斜杠（`\`）来转义掉单引号（`’`）、双引号（`”`）以及`空格符NULL`。

`addslashes()`和`mysql_escape_string()`函数都是直接在敏感字符串前加反斜杠，可能会存在宽字节注入。可参考我之前的博客

[宽字节注入漏洞的利用与学习][3]

而`mysql_real_escape_string()`函数会考虑当前连接数据库的字符集编码。

### addslashes() 

`addslashes()` 函数返回在预定义字符之前添加反斜杠的字符串。

预定义字符是：

* 单引号（`'`）
* 双引号（`"`）
* 反斜杠（`\`）
* `NULL`

```php
    `addslashes(string)`
```

参数 描述 `string` 必需。规定要转义的字符串。 

```php
<?php

$id=$_GET["id"];

$str = addslashes($id);

echo $str;
```

![][4]

### mysql_escape_string() 

在PHP5.3中已经弃用`mysql_escape_string()`

```php
    mysql_escape_string()并不转义%和_
```

```php
<?php

$id=$_GET["id"];

$str = mysql_escape_string($id);

echo $str;
```

![][5]

### mysql_real_escape_string() 

`mysql_real_escape_string()` 函数转义 SQL 语句中使用的字符串中的特殊字符。

下列字符受影响：

* `\x00`
* `\n`
* `\r`
* `\x1a`

如果成功，则该函数返回被转义的字符串。如果失败，则返回 false。

```php
    mysql_real_escape_string(string,connection)
```

参数 描述 string 必需。规定要转义的字符串。 connection 可选。规定 MySQL 连接。如果未规定，则使用上一个连接。 

```php
<?php

$id=$_GET["id"];

if($str = mysql_real_escape_string($id))
    echo $str;
else
    echo 'false';
```

![][6]

## XSS过滤函数 

XSS过滤函数有`htmlspecialchars()`和`strip_tags()`

### htmlspecialchars() 

`htmlspecialchars()` 函数把预定义的字符转换为 HTML 实体。

预定义的字符是：

```
    &转换成&
    "转换成"
    '转换成'
    <转换成<
    >转换成>
```

```php
<?php

$id=$_GET["id"];

$str = htmlspecialchars($id);

echo $str;
```

![][7]

### strip_tag()函数 

`strip_tags()` 函数剥去字符串中的 HTML、XML 以及 PHP 的标签。

```php
    strip_tags(string,allow)
```

参数 描述 string 必需。规定要检查的字符串。 allow 可选。规定允许的标签。这些标签不会被删除。 

```php
<?php

$id=$_GET["id"];

$str = strip_tags($id);

echo $str;
```

![][8]

## 命令执行过滤函数 

PHP提供了`escapeshellcmd()`和`escapeshellarg()`两个函数对参数进行过滤

### escapeshellcmd() 

在Windows下过滤方式是在字符前面加上一个^符号

在Linux是在字符前加上反斜杠（`\`）

过滤字符如下： 

```
    &
    ;
    `
    |
    *
    ?
    ~
    <
    >
    ^
    (
    )
    [
    ]
    {
    }
    $
    \
    \
    x0A
    \xFF
    %
```

```php
<?php

$id=$_GET["id"];

$str = escapeshellcmd($id);

echo $str;
```

![][9]

### escapeshellarg() 

给所有参数加上一对双引号，强制为字符串

```php
<?php

$id=$_GET["id"];

$str = escapeshellarg($id);

echo $str;
```

![][10]


[1]: http://uknowsec.cn/posts/notes/php内置过滤函数总结.html

[3]: http://uknowsec.cn/posts/notes/%E5%AE%BD%E5%AD%97%E8%8A%82%E6%B3%A8%E5%85%A5%E6%BC%8F%E6%B4%9E%E7%9A%84%E5%88%A9%E7%94%A8%E4%B8%8E%E5%AD%A6%E4%B9%A0.html
[4]: ../img/3AzAFfa.png
[5]: ../img/3UBfIzI.png
[6]: ../img/iy6R3uF.png
[7]: ../img/mmUnqeN.png
[8]: ../img/qeaeiun.png
[9]: ../img/euyYrqv.png
[10]: ../img/zMFJzuj.png