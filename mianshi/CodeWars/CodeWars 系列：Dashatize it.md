## [CodeWars 系列：Dashatize it (6 kyu)](https://blog.stephencode.com/p/codewars_dashatize_it.html) 

2017/07/07 

这是本系列的第一篇，也是算法系列文章的第一篇，大一的时候有一段时间经常刷算法题，在CSDN上也写过几篇。但后来也没去可以练习了，只是偶尔会断断续续的刷几题。目前重开这个系列，其实是有所觉悟了 :)

大概介绍下这个网站：[CodeWars][0] 是国外的一个提高编程水平的在线题库，结合了社区功能，可以与其他学习者交流和分享。并通过有趣的 **段位** 机制，增加解题乐趣。

不管你喜不喜欢，反正最近我挺迷这个的~

我目前的主要开发语言是 PHP，所以大部分都是通过 PHP 语言来解题的，由于这个语言本身对很多功能进行了函数封装，所以在解题上来说其实相对会简单很多。当然，我也会在每一个方案前说明使用哪个语言解题的 :)

## 题目介绍

> Given a number, return a string with dash ‘-‘ marks before and after each odd integer, but do not begin or end the string with a dash mark.

给定一个数 N，返回这样一个字符串 S：在每一个奇数（即把数 N 的每一位都当成一个数字）的前后带着短横线“-”，但是在字符串 S 的开头和结尾不能有这个短横线。

Ex:

```
    dashatize(274) -> '2-7-4'
    dashatize(6815) -> '68-1-5'
```

## 解决思路

### 无脑暴力版

我是一个愚钝的人，每次想解决方案的时候都会从最无脑的思路走起，看到这一题的时候，首先想到的是先把这个数 N 拆成一个数组，然后循环判断是否为奇数，如果为奇数则在该数字前后拼接上短横线。

来看一下第一个版本（PHP）：

```
    function dashatize(int $num): string
    {
        $result = '';
        // 拆成数组
        $array = str_split($num);
        // 拼接
        foreach ($array as $item) {
            if ($item % 2 != 0) {
                $result .= '-' . $item . '-';
            } else {
                $result .= $item;
            }
        }
        // 去掉双横线
        $result = str_replace('--', '-', $result);
        // 去掉开头的短横线
        $preffix = substr($result, 0, 1);
        if ($preffix == '-') {
            $result = substr($result, 1, strlen($result));
        }
        // 去掉结尾的短横线
        $suffix = substr($result, strlen($result) - 1, strlen($result));
        if ($suffix == '-') {
            $result = substr($result, 0, strlen($result) - 1);
        }
        return $result;
    }
```
其实写完这个算法的时候就觉得有很大的提升精简的空间，仔细看一下，我在去掉字符串前后两个短横线的地方做了较多工作（而且很不优雅。。。）

### 暴力无脑精简版

如何去掉前后的短横线呢？咦！那不就是去掉字符串前后的空格一样的道理么，去吧！ trim() 函数！

```
    function dashatize(int $num): string
    {
        $array  = str_split($num);
        $result = '';
        foreach ($array as $item) {
            if ($item % 2 != 0) {
                $result .= '-' . $item . '-';
            } else {
                $result .= $item;
            }
        }
        $result = str_replace('--', '-', $result);
        return trim($result, '-');
    }
```
一下子精简了七八行代码，真爽，trim() 函数用途很简单，就是去掉一个字符串开头和结尾处的指定字符串。

循环里面的 if/else 也有点碍眼呢，用三元运算符替代吧！

```
    function dashatize(int $num): string
    {
        $array  = str_split($num);
        $result = '';
        foreach ($array as $item) {
            $result .= ($item % 2 != 0) ? "-{$item}-" : $item;
        }
        $result = str_replace('--', '-', $result);
        return trim($result, '-');
    }
```

再把一些不必要的中间变量剔除掉：

```
    function dashatize(int $num): string
    {
        $result = '';
        foreach (str_split($num) as $item) {
            $result .= ($item % 2 != 0) ? "-{$item}-" : $item;
        }
        return trim(str_replace('--', '-', $result), '-');
    }
```
### 高效友人版

这里收录了我的一个好朋友 [千千][1] 的 C++ 版本，有朋友一起做题的感觉真的很棒~

```
    #include <bits/stdc++.h>
    char str[1005];
    void solve()
    {
        int len=strlen(str);
        for(int i=0; i<len; i++)
        {
            if(str[i]&1)
            {
                if(i>0&&!(str[i-1]&1))putchar('-');
                putchar(str[i]);
                if(i<len-1)putchar('-');
            }
            else putchar(str[i]);
        }
        putchar('\n');
    }
    int main()
    {
        while(gets(str))
        solve();
        return 0;
    }
```
### 优雅正则版

其实这是一个替换的算法题，论替换，最优雅的莫过于使用正则。只需匹配奇数，然后替换成前后带有短横线的奇数，这样就不须要先将数字拆成数组然后循环了。

```
    $number_string = preg_replace('/([13579])/', '-$1-', $num);
```
接下来就只需把双横线替换成单横线，去掉前后横线：

```
    function dashatize(int $num): string {
        return trim(str_replace('--', '-', preg_replace('/([13579])/', '-$1-', $num)), '-');
    }
```
## 总结

这里用到了正则表达式中的一个知识点 使用括号的子字符串匹配，这边稍微介绍一下：

一个正则表达式模式使用括号，将导致相应的子匹配被记住。例如，/a(b)c /可以匹配字符串“abc”，并且记得“b”。回调这些括号中匹配的子串，使用数组元素[1],……[n]。

使用括号匹配的子字符串的数量是无限的。返回的数组中保存所有被发现的子匹配。

像本例中括号包住的 [13579]，只要匹配到这几个字符中的其中一个，就可以使用 $1 代表该字符。

[0]: https://www.codewars.com
[1]: https://www.dreamwings.cn/