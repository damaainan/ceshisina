## PHP 高级面试题 - 如果没有 mb 系列函数，如何切割多字节字符串

来源：[https://segmentfault.com/a/1190000012710624](https://segmentfault.com/a/1190000012710624)

原文请关注我的博客 [https://mengkang.net/1129.html][0]
很多工程师在工作1~3年的时候最容易遇到瓶颈，不知道自己应该学习什么，面试总是吃闭门羹。那么 PHP 后面应该怎么学呢？ **`安利一波我的系列直播 [PHP 进阶之路][1]`** 
## 需求

如果需要将可能含有中文的字符串进行拆分成数组，我们下面以 utf-8 编码为例。
## 解决方案一

我习惯的方法可能是：

```php
$str = "周梦康";

$array = [];
for ($i=0,$l = mb_strlen($str,"utf-8"); $i < $l; $i++) { 
    array_push($array, mb_substr($str, $i, 1, "utf-8"));
}

var_export($array);
```

假如我们没装`mb`扩展怎么办？
## 解决方案二

今天看到一份代码，别人是这么写的：

```php
function str_split_utf8($str)  
{  
    $split = 1;  
    $array = array();  
    for ($i = 0; $i < strlen($str);) {  
        $value = ord($str[$i]);  
        if ($value > 127) {  
            if ($value >= 192 && $value <= 223) {  
                $split = 2;  
            } elseif ($value >= 224 && $value <= 239) {  
                $split = 3;  
            } elseif ($value >= 240 && $value <= 247) {  
                $split = 4;  
            }  
        } else {  
            $split = 1;  
        }  
        $key = null;  
        for ($j = 0; $j < $split; $j++, $i++) {  
            $key .= $str[$i];  
        }  
        array_push($array, $key);  
    }  
    return $array;  
}  
```
## 代码解读
`strlen`计算的是字节数，而直接使用`$str[x]`就沿用了c语言里面char数组和字符串的习惯，表示按字节来读取`$str`，也就是说每次读取的数据的ascii码值不可能大于255。而php里使用`ord`来获取ascii码值。
## 切割规则如下

| ascii 码范围 | 切割偏移量 |
|-|-|
| 0 ~ 127 | 1 字节 |
| 192 ~ 223 | 2 字节 |
| 224 ~ 239 | 3 字节 |
| 240 ~ 247 | 4 字节 |


## 为什么呢？


[http://www.ruanyifeng.com/blo...][2]
[https://segmentfault.com/a/11...][3] 口语化叙述 utf-8 的来历
### Unicode

Unicode 只是一个符号集，它只规定了符号的二进制代码，却没有规定这个二进制代码应该如何存储。
### UTF-8

UTF-8 就是在互联网上使用最广的一种 Unicode 的实现方式。UTF-8 最大的一个特点，就是它是一种变长的编码方式。它可以使用1~4个字节表示一个符号，根据不同的符号而变化字节长度。

UTF-8 的编码规则很简单，只有二条：


* 对于单字节的符号，字节的第一位设为`0`，后面`7`位为这个符号的 Unicode 码。因此对于英语字母，UTF-8 编码和 ASCII 码是相同的（能容纳0~127）。
* 对于`n`字节的符号（n > 1），第一个字节的前`n`位都设为1，第`n + 1`位设为0，后面字节的前两位一律设为`10`。剩下的没有提及的二进制位，全部为这个符号的 Unicode 码。


下表总结了编码规则，字母`x`表示可用编码的位:

| Unicode 符号范围(十六进制) | UTF-8 编码方式（二进制） | UTF-8 首字节范围 |
|-|-|-|
| 0000 0000-0000 007F | 0xxxxxxx | 0 ~ 127 |
| 0000 0080-0000 07FF | 110xxxxx 10xxxxxx | (128+64) ~ (255-32) 也就是 192 ~ 223 |
| 0000 0800-0000 FFFF | 1110xxxx 10xxxxxx 10xxxxxx | (128+64+32) ~ (255-16) 也就是 224 ~ 239 |
| 0001 0000-0010 FFFF | 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx | (128+64+32+16) ~ (255-8) 也就是 240 ~ 247 |


想必看了这个表，大家就能明白了吧。

[0]: https://mengkang.net/1129.html
[1]: https://segmentfault.com/ls/1650000011318558
[2]: http://www.ruanyifeng.com/blog/2007/10/ascii_unicode_and_utf-8.html
[3]: https://segmentfault.com/a/1190000012692022