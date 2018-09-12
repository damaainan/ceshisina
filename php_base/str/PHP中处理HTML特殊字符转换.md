# 浅析PHP中处理HTML特殊字符转换

 时间 2017-10-11 11:29:51  

原文[http://www.xuecaijie.com/php/151.html][1]


我们在用PHP处理页面显示内容时，经常会遇到一些特殊字符转换问题，如果处理不当就会导致页面显示混乱，不能得到目标效果。所以本文我们将和大家一起归纳总结PHP中处理HTML特殊字符转换的常用方法。

### HTML实体字符

我们知道HTML中有许多特殊的预留字符不能直接输出到浏览器，必须把它们替换为相应的实体字符才可以正常显示，比如`空格`、`<`、`>`等。

HTML常用字符实体一览：

![][4]

图片来源：http://www.w3school.com.cn/html/html_entities.asp

我们在写HTML代码时，可以直接在代码中将想显示的特殊字符写成实体字符：

```html
    <html>
    <head>
     <meta charset="utf-8">
    </head>
    <body>
    <h3>HTML中超链接a标签是这个样子的：</h3>
    <p><a href='http://www.chanzhi.org'>蝉知企业门户系统</a></p>
    
    </body>
    </html>
```

显示效果如下：

 ![][5]

### htmlentities()

在写PHP代码时，不能在字符串中直接写实体字符，PHP提供了一个将HTML特殊字符转换成实体字符的函数  `htmlentities()`  。

注：`htmlentities()`并不能转换所有的特殊字符，是转换除了空格之外的特殊字符，且单引号和双引号需要单独控制（通过第二个参数）。

第二个参数有三个值：

`ENT_COMPAT`（默认值）：只转换双引号。

`ENT_QUOTES`：两种引号都转换。

`ENT_NOQUOTES`：两种引号都不转换。

（没有只转换单引号的参数选项）

```php
    <?php
    $str = "<a href='http://www.chanzhi.org'>蝉知企业门户系统©</a>";
    
    //使用htmlentities()函数将特殊字符转换为实体字符
    $str2 = htmlentities($str);
    
    echo $str2;
    ?>
```

运行后前台显示特殊字符正常，右击查看页面源代码，可以看到PHP转换后的实体字符内容。 

 ![][6]

前台浏览器显示

 ![][7]

查看页面源代码

### htmlspecialchars()

`htmlspecialchars()`函数只对HTML语法字符进行转换，目的是避免这些特殊的字符扰乱HTML代码。

这里说的 HTML语法字符 只有5个：

显示结果 | 描述 | 实体名称
-|-|- 
`<` | 小于号 | `&lt;` 
`>` | 大于号 | `&gt;` 
`&` | 和号 | `&amp;` 
`"` | 双引号 | `&quot;` 
`'` | 单引号 | `&apos;` 

` htmlspecialchars()`将其转换成与其对应的实体字符。

我们还是以上面代码为例，对比 `htmlentities`和`htmlspecialchars`查看下效果：

```php
<?php
$str = "<a href='http://www.chanzhi.org'>蝉知企业门户系统©</a>";

//使用htmlspecialchars()函数将特殊字符转换为实体字符
$str2 = htmlspecialchars($str);

echo $str2;
```

运行效果如下：

![][8]

前台浏览器显示

![][9]

查看页面源代码

可以看到， `htmlspecialchars`只对上面5个特殊语法字符其作用，其他的特殊字符不进行转换。

###  小结： 本文我们一起学习了解了PHP处理HTML中特殊字符的方法。对比 htmlentities和htmlspecialchars 两个PHP函数的功能作用。如果大家对于字符处理还有其他疑问，欢迎和大家一起分享交流，我们共同学习，共同交流，共同进步。


[1]: http://www.xuecaijie.com/php/151.html
[4]: ../img/N7r2IbQ.png
[5]: ../img/uQ3qMjA.png
[6]: ../img/RzqI3eA.png
[7]: ../img/M7vy6f2.png
[8]: ../img/ve6bmim.png
[9]: ../img/Zj22ErF.png