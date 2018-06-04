## PHP 7.3 我们将迎来灵活的 heredoc 和 nowdoc 句法结构

来源：[http://www.cnblogs.com/summerblue/p/9065745.html](http://www.cnblogs.com/summerblue/p/9065745.html)

时间 2018-05-21 09:33:00

 
![][0]
 
php.net RFC 频道已经公布了 PHP 7.3 的 Heredoc 和 Nowdoc 语法更新，此次更新专注于代码可读性:
 
  
Heredoc 和 Nowdoc 有非常严格的语法，有些时候这令很多开发者避而远之，因为他们在代码中看起来非常丑陋，令代码的可读性降低。本次的更新针对此问题，对语法做出了以下两个更改：
 

 
* 支持闭合标记符的缩进； 
* 不再强制闭合标记符的换行； 
  
 
 
 
从 PHP 7.2 的当前实现来看， 就像这个简单的例子：
 
```php
<?php
class foo {
    public $bar = <<<EOT
bar
EOT;
}
```
 
在 7.3 版本中，以下形式是可用的：
 
```php
<?php
class foo {
    public $bar = <<<EOT
    bar
EOT;
}
```
 
闭合标记的缩进决定了 heredoc/nowdoc 中每个新行的空格的数量：
 
```php
<?php

// 4 个缩进空格
echo <<<END
      a
     b
    c
END;
/*
  a
 b
c
*/
```
 
在 PHP 7.2 的当前实现中， 必须存在一个新行来结束 heredoc/nowdoc。 PHP 7.3 移除了这个约束：
 
```php
<?php

stringManipulator(<<<END
   a
  b
 c
END);

$values = [<<<END
a
b
c
END, 'd e f'];
```
 
## Heredoc 和 Nowdoc 的背景
 
Nowdoc 从 PHP 5.3.0 版本开始支持，他和 Heredoc 的不同之处，仅是双引号和单引号的差别。 Nowdoc 在开始标记周围添加了单引号，则没有解析：
 
```php
<?php

$name = 'Example';
$str = <<<'EOD'
Example of string $name
spanning multiple lines
using nowdoc syntax.
EOD;
```
 
上面的 nowdoc 会输出：
 
```php
Example of string $name
spanning multiple lines
using nowdoc syntax.
```
 
[Here 文档][1] 在 wiki 上的定义：
 
在计算机学科中，here文档，又称作 heredoc、hereis、here-字串或here-脚本，是一个文件输入或者数据流输入：可以被当成完整文件的块状代码。它可以保存文字里面的换行或是缩排等空白字元。一些语言允许在字串里执行变量替换和命令替换。
 
Heredocs 和 Nowdocs 的改进将会让你的 PHP 代码更加具有可读性，错误率也会降低。另一方面，因为会闭合标记符的缩进会被移除，所以输出会更加简洁直接。
 
## 获取更多信息
 
推荐阅读官方的更改文档 —— [flexible Heredoc and Nowdoc Syntaxes RFC][2] 。 PHP 官方的文档  [Heredoc][3] 和  [Nowdoc][4] 。
 
更多现代化 PHP 知识，请前往 [Laravel / PHP 知识社区][5]
 


[1]: https://en.wikipedia.org/wiki/Here_document
[2]: https://wiki.php.net/rfc/flexible_heredoc_nowdoc_syntaxes
[3]: https://php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
[4]: https://php.net/manual/en/language.types.string.php#language.types.string.syntax.nowdoc
[5]: https://laravel-china.org/topics/10857
[0]: https://img2.tuicool.com/uYjA7bM.png 