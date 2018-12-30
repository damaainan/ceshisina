## php是否使用addslashes()或stripslashes()

来源：[https://segmentfault.com/a/1190000000499420](https://segmentfault.com/a/1190000000499420)

addslashes() 函数在指定的预定义字符前添加反斜杠。

这些预定义字符是：

单引号 (')

双引号 (")

反斜杠 ()

NULL

addslashes(string)

string  必需。规定要检查的字符串。

该函数可用于为存储在数据库中的字符串以及数据库查询语句准备合适的字符串。

默认情况下，PHP 指令 magic_quotes_gpc 为 on，对所有的 GET、POST 和 COOKIE 数据自动运行 addslashes()。不要对已经被 magic_quotes_gpc 转义过的字符串使用 addslashes()，因为这样会导致双层转义。遇到这种情况时可以使用函数 get_magic_quotes_gpc() 进行检测。

```php
<?php
$str = "Who's John Adams?";
echo $str . " This is not safe in a database query.<br/>";
echo addslashes($str) . " This is safe in a database query.";
?>

```


输出：

```
Who's John Adams? This is not safe in a database query.
Who's John Adams? This is safe in a database query.

```


stripslashes() 函数删除由 addslashes() 函数添加的反斜杠。

stripslashes(string)

string  必需。规定要检查的字符串。

该函数用于清理从数据库或 HTML 表单中取回的数据。

```php
<?php
echo stripslashes("Who\'s John Adams?");
?>

```


输出：

```
Who's John Adams?

```
