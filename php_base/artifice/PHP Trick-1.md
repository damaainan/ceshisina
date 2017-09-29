# PHP Trick 总结与探讨（一）

 时间 2017-04-18 23:38:59  锅子博客

原文[https://www.gzpblog.com/20170418/871.html][1]


本文原创作者：VillanCh，发表于FreeBuf.COM；原文链接： [PHP Trick 总结与探讨（一）][3]

本文不是 PHP 教程，本文只针对 PHP 的各种奇淫技巧进行整理和证明，在进行渗透测试（WEB 安全）的学习中，经常需要涉及到 PHP 编程，或者 PHP 编写 Webshell 或者一句话木马，一般的 PHP 马经常被各种软件发现被杀。与此同时，网络经常流传一些免杀马（当然免杀一句话很有可能藏后门大家都是知道的），那么那些免杀是怎么做出来的？打开看，可能就是非常难看难以阅读的 PHP 代码（为了混淆各种杀软的侦察）。

当然，怎么样写出那种奇奇怪怪的东西呢？这就取决于你对 PHP 这门语言的了解程度：你是一个 PHP 开发者，通过 PHP 培训获得一定的技能？还是一个渗透测试人员，通过平时在渗透测试过程中接触 PHP，查看 PHP 官方手册学习 PHP。

嗯……不管你是怎么样的契机接触了 PHP，并且看到了这篇文章，我都希望你看到这篇文章的时候，能够知道一些之前不知道的东西；或者你都对这些技巧了然于胸，能够帮助你回忆这些很有趣的 PHP Trick。

## PHP 中的大小写

当然这个东西对于开发者来说，其实是没什么太大的作用：没有哪个 Boss 允许你把 PHP 中的关键字写的参差不齐，大小写不一；作为对比，一个渗透测试人员和安全研究者通常需要使用 PHP 大小写来绕过一些限制策略，想这个大家都是渗透体会的，不光 PHP，在 XSS 和 SQLinject 中，大小写的随机组合有时候能达到出其不意的效果对吧？

那么具体来说 PHP 中可以忽略大小写的东西有什么呢？

* 用户定义类
* 用户定义函数
* 内置结构
* 关键字

那么剩下的基本都要大小写敏感了哦，当然，一定要记住 **变量是区分大小写的！**

下面举一些例子，当然这个例子没有实际的意义，但是尽量包含了上面说到的几个点（当然还包含了我们下面要讲的东西）：

    <?pHp
    fUnCtIoN test(){
    ?>
    <?phP echo 'First!'; }
    
    test(); ?>
    <br>
    <? teSt() ?>
    <br>
    <?Php tEst() ?>
    <br>
    <?PhP TeSt() ?>
    <br>

执行结果：

    First!
    First!
    First!
    First!

嗯，不知道大家有没有觉得惊奇呢？上面的代码居然是可以运行的！没错啊，这是不是我们经常在 webshell 和“一句话木马”中见到的一种写法？原理是什么？当然，我们发现上面的写法其实前四行很奇怪。当然我们后面再讨论这个问题吧，至少，我们现在发现了 PHP 中的大小写实在是松散。

## PHP 标签

### PHP 标签类型：

**1. XML 型标签**

对于这种标签类型，我觉得没有必要解释太多：

    <?php echo "In PHP Tag~"?>

大家需要知道的也就是：这个标签中的 php 的声明不是大小写敏感的，你可以 <?PhP … ?> 也是完全可行的。

**2. 短标签 （SGML 型标签）**

对于这种标签，我个人把他们分成两类：

* 需要 PHP 配置支持的
* 不需要 PHP 配置支持的

下面具体来介绍：

需要配置支持：<? echo “In Tag!” ?>

这种标签其实是非常常见的，对不对？

    <? echo "In Tag!" ?>

当然这种标签发挥作用，要取决于你的 PHP 配置是否开启了 short_open_tag。

需要说明的是，一旦使用关闭了 short_open_tag 的话， <? ... ?> 的内容是不会显示在用户界面上的，也就是这些东西直接不见了，也不会执行，就当是被 DROP 掉了吧~ 

不需要配置支持：<?=”In Tag!”?>

这个标签其实也是非常厉害，并不需要开启 short_open_tag 就可以起作用，但是，缺点就是这个标签相当于一个 echo, 所以用法也相当受到限制：

    输出一个字符串
    <?='This short Tag just for echo~'?>
    函数调用
    <?=test()?>

当然可以函数调用还不够么？能怎么样玩耍就需要大家的智慧了。

**3. ASP 风格标签**

其实这个也不用多说，大家一看便知：

    <% echo 'IN TAG!' %>

如果想要使用这种风格的标签，需要确保 asp_tags 打开，并且一定要注意的是，这个和短标签的区别是：当短标签配置是关闭的时候，短标签（包括短标签内部）的东西是不会让用户看到的！然而如果 asp_tags 关闭时，你使用这种标签就会造成他的内容被用户看到，包括 ASP 风格标签和标签内部的内容。 

**4. Script 风格标签**

这个标签类型大家可能之前也还是见过的:

    <script language=PhP>Echo 'In Tags'</script>

没错，这个可以使用，而且 scriptlanguagephp 的大小写随意转换，拿去做混淆自然挺不错的。 

### 标签的 Trick

那么我稍微做一下整理：证明一下上面讲的都是正确的，大家可以看一下这个有趣的例子：
```
<?php
FuNcTiON test(){
?>
<?php echo 'This is in the test function'?>
<? Echo '<br>Short Tag may be useful' ;?>

 <script language=Php>echo '<br> Now in script style !';};</script>

<br>

<?=test()?>
```
很有趣的是，我把一个 test 函数肢解在了三种标签中，最后使用 <?=?> 短标签来调用，发现函数的定义并没有被破坏，而且，最后成功调用了，这难道不是非常的振奋人心么？嗯，当然我尝试了很多种奇奇怪怪的玩法，但是就只有这种是可以的，如果读者有神奇的玩法，可以分享。

## 流程控制的另一种写法

其实我并不是一个 PHPer 而是一个 Pythoner， PHP 的大括号让我非常难受，非常生气，然后我就很开心的使用了另外一种写法：

    <?php if (33>4)
     >
    <?php echo TRUE;?>
    <br>
    <?php echo 'This IF have been seperated!';?>
    <?php else: echo 'Impossible??'?>
    <?php endif;?>

执行结果是

    1
    This IF have been seperated!

怎么说呢，这样的话我就不用再写丑陋的大括号了，但是好像有需要写标签？嗨呀，反正这样也算是一种新的姿势吧！

同样的不仅仅是 if elseif else 可以使用这种写法，switch 对应 endswitch； for 对应的 endfor；while 对应 endwhile；foreach 对应 endforeach …

## PHP 类型问题（弱类型）

PHP 的弱类型问题由来已久吧，当然我们先用一个例子来开始我们这一部分的话题：

浮点型比较： 两个浮点型数据比较，实际只会比较前几位（解释器是假定 PHP 浮点型不可能完全精确的），这样就造成很奇怪的现象，我们这里就举两个例子来说吧！ 

```php
    <?php
    echo 'This page is for float_trick! <br>';
    
    
    $fill = '0.';
    for ($i = 0; $i < 20; $i ++ ):
     $fill = $fill.'0';
     $val1 = floatval($fill.'1');
     $val2 = floatval($fill.'2');
    
     echo $val1,'==',$val2, ' result: ', $val1 == $val2, '<br>';
    
    
    endfor;
    
    echo '----------------------------------------------------------------------<br>';
    
    $fill = '2.';
    for ($i = 0; $i < 20; $i ++ ):
     $fill = $fill.'0';
     $val1 = floatval($fill.'1');
     $val2 = floatval($fill.'2');
    
    echo $val1,'==',$val2, ' result: ', $val1 == $val2, '<br>';
    
    
    endfor;
    
    echo '----------------------------------------------------------------------<br>';
    
    $fill = '2.';
    for ($i = 0; $i < 20; $i ++ ):
    $fill = $fill.'3';
    $val1 = floatval($fill.'1');
    $val2 = floatval($fill.'2');
    
    echo $val1,'==',$val2, ' result: ', $val1 == $val2, '<br>';
    
    
    endfor;
    ?>
```
执行的结果为：

    This page is for float_trick!
    0.01==0.02 result:
    0.001==0.002 result:
    0.0001==0.0002 result:
    1.0E-5==2.0E-5 result:
    1.0E-6==2.0E-6 result:
    1.0E-7==2.0E-7 result:
    1.0E-8==2.0E-8 result:
    1.0E-9==2.0E-9 result:
    1.0E-10==2.0E-10 result:
    1.0E-11==2.0E-11 result:
    1.0E-12==2.0E-12 result:
    1.0E-13==2.0E-13 result:
    1.0E-14==2.0E-14 result:
    1.0E-15==2.0E-15 result:
    1.0E-16==2.0E-16 result:
    1.0E-17==2.0E-17 result:
    1.0E-18==2.0E-18 result:
    1.0E-19==2.0E-19 result:
    1.0E-20==2.0E-20 result:
    1.0E-21==2.0E-21 result:
    ----------------------------------------------------------------------
    2.01==2.02 result:
    2.001==2.002 result:
    2.0001==2.0002 result:
    2.00001==2.00002 result:
    2.000001==2.000002 result:
    2.0000001==2.0000002 result:
    2.00000001==2.00000002 result:
    2.000000001==2.000000002 result:
    2.0000000001==2.0000000002 result:
    2.00000000001==2.00000000002 result:
    2.000000000001==2.000000000002 result:
    2.0000000000001==2.0000000000002 result:
    2==2 result:
    2==2 result:
    2==2 result: 1
    2==2 result: 1
    2==2 result: 1
    2==2 result: 1
    2==2 result: 1
    2==2 result: 1
    ----------------------------------------------------------------------
    2.31==2.32 result:
    2.331==2.332 result:
    2.3331==2.3332 result:
    2.33331==2.33332 result:
    2.333331==2.333332 result:
    2.3333331==2.3333332 result:
    2.33333331==2.33333332 result:
    2.333333331==2.333333332 result:
    2.3333333331==2.3333333332 result:
    2.33333333331==2.33333333332 result:
    2.333333333331==2.333333333332 result:
    2.3333333333331==2.3333333333332 result:
    2.3333333333333==2.3333333333333 result:
    2.3333333333333==2.3333333333333 result:
    2.3333333333333==2.3333333333333 result: 1
    2.3333333333333==2.3333333333333 result: 1
    2.3333333333333==2.3333333333333 result: 1
    2.3333333333333==2.3333333333333 result: 1
    2.3333333333333==2.3333333333333 result: 1
    2.3333333333333==2.3333333333333 result: 1

当然我们发现 0.00000000000X 会默认被记成科学计数法，进行比较不会丢失精度。

但是对于我们发现转换来转换去，PHP 的 Float 类型也顶多能存储 16 位数的浮点型。

我们看完上面的小实验，我觉得大家应该就已经明白了 Float 在 PHP 中是如何被处理的，以及 floatval 这个函数的结果。

* 最多存储 16 位小数。
* 会把 1.00000000000000000000000000000000001 自动去掉后面多余的（16位之外的数），如果16位之内都为 0，则自动转为 int。
* 0.0000000000xxx 开头会视情况自动转为科学记数法，而且不会造成精度丢失。

### 谈一谈其他的类型问题

intval 与 floatval 对于某些特殊情况的类型转换。

**intval**

这一串代码，大致我们来看一下，str 转换成 int 的下面情况

```php
    <?php
    echo '$ret is a str';
    echo '<br>';
    $ret = '123.12.123';
    echo $ret, ' intval=> ', intval($ret);
    echo '<br>';
    $ret = '123Saaf';
    echo $ret, ' intval=> ', intval($ret);
    echo '<br>';
    $ret = 'adf123Saaf';
    echo $ret, ' intval=> ', intval($ret);
    echo '<br>';
    $ret = '12.3Saaf';
    echo $ret, ' intval=> ', intval($ret);
    echo '<br>';
    $ret = 'ads1.23Saaf';
    echo $ret, ' intval=> ', intval($ret);
    echo '<br>';
    $ret = '123.789';
    echo $ret, ' intval=> ', intval($ret);
    echo '<br>';
    $ret = 123.789;
    echo $ret, ' intval=> ', intval($ret);
    echo '<br>';
    ?>
```

执行结果为：

    $ret is a str
    123.12.123 intval=> 123
    123Saaf intval=> 123
    adf123Saaf intval=> 0
    12.3Saaf intval=> 12
    ads1.23Saaf intval=> 0
    123.789 intval=> 123
    123.789 intval=> 123

前面为等待转换的 string，后面为经过 intval 函数以后的值。我们现在来简单总结一下：

一个 String 被 intval 转换从前到后取一个 int。

如果开头不是 int 的话，那么就是 0。

转换一个浮点型数，直接去掉小数部分（不是四舍五入）

floatval

floatval 和 intval 有点类似，大家看一下下面的例子就知道了，我就不浪费时间讨论总结了。

```php
    <?php
    $ret = '123.456.76';
    echo $ret, ' floatval=> ', floatval($ret);
    echo '<br>';
    $ret = '123.456.76asd';
    echo $ret, ' floatval=> ', floatval($ret);
    echo '<br>';
    $ret = '123.456adf';
    echo $ret, ' floatval=> ', floatval($ret);
    echo '<br>';
    $ret = '.123.456';
    echo $ret, ' floatval=> ', floatval($ret);
    echo '<br>';
    $ret = 'qerqer123.456adf';
    echo $ret, ' floatval=> ', floatval($ret);
    echo '<br>';
    $ret = '456adf';
    echo $ret, ' floatval=> ', floatval($ret);
    echo '<br>';
    $ret = '123';
    echo $ret, ' floatval=> ', floatval($ret);
    echo '<br>';
    $ret = 'asd76';
    echo $ret, ' floatval=> ', floatval($ret);
    echo '<br>';
    ?>
```
执行结果为

    123.456.76 floatval=> 123.456
    123.456.76asd floatval=> 123.456
    123.456adf floatval=> 123.456
    .123.456 floatval=> 0.123
    qerqer123.456adf floatval=> 0
    456adf floatval=> 456
    123 floatval=> 123
    asd76 floatval=> 0

### 松散比较问题

盗用 drops 的一个图吧，私以为这个图就可以基本说明一切了。

![][4]

松散比较可以出现在 ‘==’ 中，还可以出现在函数传值和 switch 中。

### strcmp

接下来我照着上面的图做了一个 strcmp 的各种类型比较的表格。

||true |false |1 | 0 | -1 | ’1′ | ’0′ | ‘-1′|  NULL | array() | ‘php’| ” 
-|-|-|-|-|-|-|-|-|-|-|-|-|-
true | 0 | 1 | 0 | 1 | 1 | 0 | 1 | 1 | 1 | | -1 | 1 
false | -1 | 0 | -1 | -1 | -2 | -1 | 0 | -2 | 0 | | -3 | 0 
1 | 0 | 1 | 0 | 1 | 1 | 0 | 1 | 1 | 1 | | -1 | 1 
0 | -1 | 1 | -1 | 0 | 1 | -1 | 1 | 1 | 1 | | -1 | 1 
-1 | -1 | 2 | -1 | -1 | 0 | -1 | 2 | 0 | 2 | | -1 | 2 
’1′ | 0 | 1 | 0 | 1 | 1 | 0 | 1 | 1 | 1 | | -1 | 1 
’0′ |  -1 | 1 | -1 | 0 | 1 | -1 | 1 | 1 | 1 | | -1 | 1 
‘-1′ | -1 | 2 | -1 | -1 | 0 | -1 | 2 | 0 | 2 | | -1 | 2 
NULL | -1 | 0 | -1 | -1 | -2 | -1 | 0 | -2 | 0 | | -3 | 0 
array() | ||||||||||
‘php’ | 1 | 3 | 1 | 1 | 1 | 1 | 3 | 1 | 3 | | 0 | 3 
” | -1 | 0 | -1 | -1 | -2 | -1 | 0 | -2 | 0 | | -3 | 0 

方便大家对这个东西有更深的理解。

出现这些问题的，具体原因其实有很多，除了 intval 之类的类型转换，也有其他的（关于 strcmp 内部实现的分析不在讨论范围）。

上面的表格的源代码在这里：当然 wooyun drops 的表格你也可以在下面的代码中稍微改一下就可以验证了。

    <?php
     $items[0] = True;
     $items[1] = False;
     $items[2] = 1;
     $items[3] = 0;
     $items[4] = -1;
     $items[5] = "1";
     $itmes[6] = '0';
     $items[7] = '-1';
     $items[8] = NULL;
     $items[9] = array();
     $items[10] = 'php';
     $items[11] = '';
    
     ?>
    
     <table border=1>
     <tr>
        <th></th>
        <th>true</th>
        <th>false</th>
        <th>1</th>
        <th>0</th>
        <th>-1</th>
        <th>'1'</th>
        <th>'0'</th>
        <th>'-1'</th>
        <th>NULL</th>
        <th>array()</th>
        <th>'php'</th>
        <th>''</th>
     </tr> 
     <tr>
        <td>true</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp(true, $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
      <tr>
        <td>false</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp(false, $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
       <tr>
        <td>1</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp(1, $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
       <tr>
        <td>0</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp(0, $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
       <tr>
        <td>-1</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp(-1, $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
       <tr>
        <td>'1'</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp('1', $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
       <tr>
        <td>'0'</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp('0', $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
       <tr>
        <td>'-1'</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp('-1', $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
        <tr>
        <td>NULL</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp(NULL, $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
        <tr>
        <td>array()</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp(array(), $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
        <tr>
        <td>'php'</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp('php', $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
        <tr>
        <td>''</td>
        <?php 
        for($i=0; $i<12; $i++){
            $result = @strcmp('', $items[$i]);
        echo'<td>'.$result.'</td>';}?>
     </tr>
    
     </table>

当然上面代码的输出结果大家可能会看到一大堆的 Warning 和 Notice，但是我们使用 @ 来忽略。当然忽略并不是说我们不关心，在网站中如果出现了这种情况，一个正常的程序员是不会让用户看到异常的，这样很容易让用户感到“恐慌”，同时也可以避免一些敏感的信息泄露。当然并不是说异常不重要，实际上如果有了这些 Warning 和 Notice。

## 本篇结语

由于篇幅的原因，私以为总结全部堆在一起看起来并不是特别舒服。

当然我也只能把我自己懂的这点东西总结出来希望和大家分享，但是总是感觉意犹未尽吧，因为还有很多东西我们都没有写出来。

那么，之后吧，之后我们再来详细讨论一下 PHP 的其他的奇怪的东西：

* HPP — HTTP Parameter Pollution
* 截断
* 协议封装

水平有限，如果上面有什么不正确的，希望读者不吝指出。




[1]: https://www.gzpblog.com/20170418/871.html
[3]: https://www.gzpblog.com/go/?url=aHR0cDovL3d3dy5mcmVlYnVmLmNvbS9hcnRpY2xlcy9yb29raWUvMTE5OTY5Lmh0bWw=
[4]: http://img2.tuicool.com/AFZj6bz.jpg