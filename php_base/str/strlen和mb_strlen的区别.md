# [PHP中strlen和mb_strlen的区别][0]

2016年07月13日发布 



> 在php中常见的计算字符串长度的函数有：strlen和mb_strlen.当字符全是英文字符的时候，两者是一样。这里主要比较一下，中英文混排的时候，两个计算结果。

在PHP中，**strlen**与**mb_strlen**是求字符串长度的函数，但是对于一些初学者来说，如果不看手册，也许不太清楚其中的区别。

下面通过例子，讲解这两者之间的区别。

先看例子：

```php
    <?php  
    //测试时文件的编码方式为gbk
    $str='中文a字1符';  
    echo strlen($str).'<br>';//10 
    echo mb_strlen($str,'utf8').'<br>';//7  
    echo mb_strlen($str,'gbk').'<br>';//6  
    echo mb_strlen($str,'gb2312').'<br>';//6 
    echo mb_strlen($str).'<br>';//10
    ?> 
```
结果分析：在`strlen`计算时，对待 一个gbk的中文字符是2个长度，所以“中文a字1符”长度是2*4+2=10,在`mb_strlen`计算时，选内码为gbk时，会 将一个中文当做一个字符长度来处理。

利用这两个函数则可以联合计算出一个中英文混排的串的占位是多少（一个中文字符的占位是2，英文字符是1）

    echo (strlen($str) + mb_strlen($str,'UTF8')) / 2; 

例如 “中文a字1符” 的strlen($str)值是10，mb_strlen($str)值是6，则可以计算出“中文a字1符”的占位是10.

    echo mb_internal_encoding(); 

PHP内置的字符串长度函数strlen无法正确处理中文字符串，它得 到的只是字符串所占的字节数。对于GB2312的中文编码，strlen得到的值是汉字个数的2倍，而对于UTF-8编码的中文，就是3倍的差异了（在 UTF-8编码下，一个汉字占3个字节）。

采用`mb_strlen`函数可以较好地解决这个问题。`mb_strlen`的用法和 `strlen`类似，只不过它有第二个可选参数用于指定字符编码。例如得到UTF-8的字符串$str长度，可以用 `mb_strlen($str,'UTF-8')`。如果省略第二个参数，则会使用PHP的内部编码。内部编码可以通过 `mb_internal_encoding()`函数得到。

需要注意的是，`mb_strlen`并不是PHP核心函数，使用前需要确保在php.ini中加载了`php_mbstring.dll`，即确保“extension=php_mbstring.dll”这一行存在并且没有被注释掉，否则会出现未定义函 数的问题。

[0]: /a/1190000005952997
[1]: /t/php/blogs
[2]: /u/corwien
