# PHP 正则表达式详解

 时间 2017-07-29 17:26:33  简书

原文[http://www.jianshu.com/p/240939e32465][1]


因为比较重要，

所以还是单独拿出来作一篇文章，

好好学习一下。

## 正则表达式：Regular expression

定义：是负责对字符串做解析对比，从而分析出字符串的构成，以便进一步对字符串做相关的处理。

注：正则表达式允许用户通过使用某种特殊字符构建匹配模式，然后把匹配模式与文件中的数据、程序输入或者Web页面的表单输入等目标对象进行比较，根据这些输入中是否包含匹配模式，来执行相应的程序。

#### 正则表达式的语法：（基础）

元字符：

^ ：匹配指定字符串开头的字符串

$ ：匹配指定字符串结尾的字符串

. ：匹配除\n之外的任何单个字符，代替任何字符

[] ：匹配指定范围内的单个字符，代替指定字符

| ：在多项之间选择一个进行匹配

\ ：转义字符

() ：标记子表达式的开始和结束位置

\* ：匹配其左边的子表达式0次或多次

\+ ：匹配其左边的子表达式1次或多次

? ：匹配其左边的子表达式0次或1次

限定符：

{n}：表示匹配该限定符左边字符n次

{n,}：表示匹配该限定符左边至少n次

{n,m}：表示匹配该限定符左边至少n次，最多m次

转义字符：

\n ：一个换行符。等价于\x0a和\cJ

\r ：一个回车符。等价于\x0d和\cM

\s ：任何空白字符，包括空格、制表符、换页符等。等价于[\f\n\r\t\v]

\S ：任何非空白字符。等价于[^\f\n\r\t\v]

\t ：一个制表符。等价于\x09和\cI

\v ：一个垂直制表符。等价于\x0b和\cK

\f ：一个换页符。等价于\x0c和\cL

\cx ：由x指明的控制字符。

字符类：

[[:alpha:]] ：匹配任何字母

[[:digit:]] ：匹配任何数字

[[:alnum:]] ：匹配任何字母和数字

[[:space:]] ：匹配任何空白字符

[[:upper:]] ：匹配任何大写字母

[[:lower:]] ：匹配任何小写字母

[[:punct:]] ：匹配任何标点符号

[[:xdigit:]] ：匹配任何16进制数字，相当于[0-9a-fA-F]

[[:blank:]] ：匹配空格和Tab，等价于[\t]

[[:cntrl:]] ：匹配所有ASCII 0到31之间的控制符

[[:graph:]] ：匹配所有的可打印字符，等价于[^ \t\n\r\f\v]

[[:print:]] ：匹配所有的可打印字符和空格，等价于[^\t\n\r\f\v]

反义：

\W ：匹配任意不是字母，数字，下划线或汉子的字符

\S ：匹配任意不是空白符的字符

\D ：匹配任意非数字的字符

\B ：匹配不是单词开头或结束的位置

    //模式：举例
    
    ^once  //匹配给定模式开头的字符串
    
    PHP$   //匹配给定模式结尾的字符串 
    
    ^Python$  //精确定位：指定字符串
    
    b.s   //这个单词可以是bes、bis、bos....
    
    b[eiou]s //这个单词只匹配 bes、bis、bos、bus
    
    b(a|e|i|o|oo)s  //这个单词匹配bas、bes、bis、bos、boos
    
    pe* //匹配perl、peel、pet、port...
    
    co+  //匹配come、code、cool、co...
    
    a{3}  //匹配aaa、cacaaad、aacoaaao...
    
    a{3,}  //匹配aaa、aaab、caaaaa...
    
    a{1,3}b  //匹配ab、aab、aaab...
    
    ab*  //和ab{0,}同义，a、ab、abb...
    
    ab+  //和ab{1,}同义，ab、abb...
    
    ab?  //和ab{0,1}同义，a、ab
    
    a?b+$  //匹配ab、abb...
    
    a(bc)*  //匹配a、abc、abcbc...
    
    [ab]  //与a|b同义，匹配a、b
    
    [a-d]  //与a|b|c|d及[abcd]同义，匹配a、b、c、d。
    
    ^[a-zA-Z_]$  //匹配所有的只有字母和下划线的字符串。如果不加^和$，凡是含有字母和下划线的字符都会被匹配。
    
    ^[a-zA-Z0-9_]{1,}$  //匹配所有包含一个以上的字母、数字或下划线的字符串。
    
    ^[0-9]{1,}$  //匹配所有正数
    
    ^\-{0,1}[0-9]{1,}$  //匹配所有的整数
    
    ^\-{0,1}[0-9]{0,}\.{0,1}[0-9]{0,}$  //匹配所有小数

PHP有两大类函数支持正则表达式，

一类是POSIX扩展函数(PHP5.2之后弃用)，

另一类是Perl兼容的正则表达式函数(PHP4.0后支持)。

#### POSIX扩展正则表达式函数（PHP5后弃用）：

ereg() ：字符串的正则匹配函数

ereg_replace() ：区分大小写的正则表达式替换

eregi() ：不区分大小写的正则表达式匹配

eregi_replace() ：不区分大小写的正则表达式替换

split() ：用正则表达式将字符串分割到数组中

spliti() ：用正则表达式不区分字母大小写将字符串分割到数组中

sql_regcase() ：产生用于不区分大小的正则表达式

    //正则表达式匹配函数
    int ereg(string $pattern, string $string [, array ®s]);  //区分大小写
    int eregi(string $pattern, string $string [, array ®s]);  //不区分大小写

```php
    <?php
    $arr_date = array(
    '2008-06-01',
    '1996-11-29',
    '2005-0x-10',
    '12-12-12',
    '2012-12-25 00:10:20'
    );
    
    for ($i=0; $i<5; ++$i){
          $date = $arr_date[$i];
          if(ereg("([0-9]){4})-([0-9]{1,2})-([0-9]{1,2})", $date , $regs)){
                  echo "日期字符串$date 符合'YYYY-MM-DD'格式：";
                  echo "$regs[1].$regs[2].$regs[3]<br></5><br/>";
          }else{
                  echo "<b>日期字符串$date 不符合'YYYY-MM-DD'格式</b><br/><br/>";
          }
    }
    ?>
```


    //替换匹配字符串的函数
    string ereg_replace(string $pattern, string $replacement, string $string);  //区分大小写
    string eregi_replace(string $pattern, string $replacement, string $string);  //不区分大小写

```php    
    <?php
    $str = "You have a car , I have a Car , We have cARs!"  //源字符串
    echo "<b>替换前字符串为：</b><br/>";
    echo $str;
    echo "<br/>";
    echo "<br/>";
    
    $pattern = "car";    //匹配字符串
    $replacement = "Apple";   //替换后字符串
    $str_rpc = eregi_replace($pattern,$replacement,$str);
    echo "<b>替换后字符串为：</b><br/>";
    echo $str_rpc;
    ?>
```

    //根据正则表达式分割字符串函数
    array split(string $pattern, string $string [, int $limit]);


```php    
    <?php
    $str = "aaa~bbb~ccc~ddd";  //定义字符串变量
    echo "字符串截取前：$str";
    echo "<br/>";
    echo "<br/>";
    
    $sep_arr = split("~",$str);
    echo "<b>字符串截取后：</b><br/>";  //分割字符串变量$str
    echo "<pre>";
    
    print_r($str_arr);
    ?>
```

    //生成正则表达式的函数
    string sql_regcase(string $string);   //不区分大小正则表达式

```php    
    <?php
    $str = "K#V3050"
    echo "<b>原字符串：</b><br/>$str";    //定义字符串变量
    echo "<br/>";
    echo "<br/>";
    
    $reg_str = sql_regcase($str);
    echo "<b>生成的正则表达式为：</b><br/>";  //生成正则表达式
    echo $reg_str;
    ?>
```

#### PERL兼容正则表达式函数（PHP4后支持，重点）

PERL兼容正则表达式使用修正符，

所谓修正符，是指正则表达式最后的补充说明。

另外，

PERL兼容正则表达式中所有的模式前后都需要加/

修正符

i ：匹配时忽略大小写

m ：除了匹配^$整个字符串开头和结尾，还匹配其中的换行符（\n）的之后和之前

s ：使原点字符（.）匹配任意一个字符同时也匹配换行符

x ：模式中的空白字符除了被转义的或在字符类中的以外完全被忽略

e ：preg_replace()在替换字符串中对逆向引用作正常的替换，将其作为PHP代码求值，并用其结果来替换所搜索的字符串

A ：模式被强制为“anchored”，即强制仅从目标字符串的开头开始匹配

D ：模式中的行结束（$）仅匹配目标字符串的结尾，否则包含换行符

S ：为加速匹配而对其进行分析，分析一个模式仅对没有单一固定其实字符的nonanchored模式有用

U ：使“?”的默认匹配成为贪婪状态

X ：一个反斜线后面跟一个没有特殊意义的字母被当成该字母本身

u ：模式字符串被当成UTF-8

preg_grep() ：返回与模式匹配的数组单元的正则表达式函数

preg_match() ：进行正则表达式匹配的函数

preg_match_all() ：进行全局正则表达式匹配的函数

preg_replace() ：执行正则表达式的搜索和替换的函数

preg_split() ：用正则表达式分割字符串的函数

    //返回与模式匹配的数组单元的正则表达式函数
    array preg_grep(string $pattern, array $input [, int $flag]);

```php    
    <?php
    $test_preg = array(
    "AK47",
    "163.com",
    "happy new year",
    "EX0000",
    "007 in USA",
    "abc123",
    "TEST-abc-315",
    "123654789",
    "Euapa00!"
    );
    
    echo "<b>原数组：</b>";
    echo "<pre>";
    print_r($test_preg);
    echo "</pre>";
    
    $preg_arr = preg_grep("/^[A-Z].*[0-9]$/",$test_preg);  //正则表达式
    echo "<br>";
    echo "<b>将原数组中以任意大写字母开头的、中间任意个字符、最后以数字结尾的字符串找出：</b>";
    echo "<pre>";
    print_r($preg_arr);  //输出匹配的元素
    echo "</pre>";
    ?>
```
    
    //进行正则表达式匹配的函数
    int preg_match(string $pattern , string $subject [, arrayy $matches [, int $flag]]);

```php    
    <?php
    $str_arr = array(
    "PHP 是优秀的Web脚本语言",
    "Perl的文本处理功能很强大"
    );
    
    foreach($str_arr as $str){
          //使用了修正符
          if(preg_match("/php/i",$str)){
                echo "在字符串'$str'中找到对'php'的匹配";
                echo "<br/>";
                echo "<br/>";
          }else{
                 echo "在字符串'$str'中<b>未</b>找到对'php'的匹配";
                echo "<br/>";
                echo "<br/>";           
          }
    }
    ?>
```
    
    //进行全局正则表达式匹配的函数
    int preg_match_all (string $pattern, string $subject, array $matches [,int $flag]);

```php
    <?php
    $html = "<b>粗体字符</b><a href=index.html>可点击的连接</a>";
    
    preg_match_all("/(<([\w]+)[^>]*>)(.*)(<\/\\2>)/", $html , $matches);
    
    for ($i=0;$i<count($matches[0]);$i++){
          echo "匹配：".$matches[0][$i]."\n";
          echo "第一部分：".$matches[1][$i]."\n";
          echo "第二部分：".$matches[2][$i]."\n";
          echo "第三部分：".$matches[3][$i]."\n\n";
    }
    ?>
```

    //执行正则表达式的搜索和替换的函数
    mixed preg_replace(mixed $pattern,mixed $replacement,mixed $subject [, int $limit]);

```php
    <?php
    $string = "The quick brown fox jumped over the lazy dog.";  
    echo "原字符串：<br/>";
    echo $string;
    echo "<br/><br/>";
    
    $patterns[0] = "/quick/";
    $patterns[1] = "/brown/";
    $patterns[2] = "/fox/";
    
    $replacements[2] = "bear";
    $replacements[1] = "black";
    $replacements[0] = "slow";
    
    $str1 = preg_replace($patterns,$replacements,$string);  //替换字符串
    echo "使用函数ksort()之前字符串替换为：<br/>";
    echo $str1;
    echo "<br/><br/>";
    
    ksort($patterns);        //排序
    ksort($replacements);    //排序
    
    $str2 = preg_replace($patterns,$replacements,$string);
    echo "使用函数ksort()之后字符串替换为：<br/>";
    echo $str2;
    echo "<br/><br/>";
    ?>
```
    
    
    //用正则表达式分割字符串的函数
    array preg_split(string $pattern,string $subject [,int $limit [, int $flag]]);
    //参数$limit=-1,$flag参数如下：
    PREG_SPLIT_NO_EMPTY：只返回非空的部分
    PREG_SPLIT_DELIM_CAPTURE：界定符模式中的括号表达式会被捕捉返回
    PREG_SPLIT_OFFSET_CAPTURE：对每个出现的匹配结果同时返回其附属的字符串偏移量。注意，这改变了返回的数组的值，使其中的每个单元也是一个数组，其中第一项为匹配字符串，第二项为它在$subject中的偏移量

```php    
    <?php
    $str = 'PHP language programming in Web';  //定义字符串变量
    echo "<b>原字符串：</b><br/>";
    echo $str;
    echo "<br/><br/>";
    
    $chars = preg_split('/ /',$str,-1,PREG_SPLIT_OFFSET_CAPTURE);  //分割字符串
    echo "<b>调用函数preg_split()后：</b>";
    echo "<pre>";
    print_r($chars);
    ?>
```

#### 几例常见的正则表达式分析

* 实例1：检查IP地址的正则表达式： 

直接上代码：

```php
    <?php
    $arr_ip = array(  //定义了一个数组
      "192.168.1.100",
      "-12.255.0.10",
      "256.1.2.255",
      "10.9c.132.69",
      "255.255.255.255",
      "123.0.0.0.1"
    );
    
    foreach ($arr_ip as $ip ) {  //验证数组里的IP
      if (validateIp($ip)) {  //验证ip
        echo "<b>$ip 是正确的IP地址</b>";
        echo "<br/><br/>";
      }else {
        echo "$ip 不是正确的IP地址";
        echo "<br/><br/>";
      }
    }
    
    function validateIp($ip){  //验证ip的函数
      $iparray = explode(".",$ip);
      for ($i=0; $i < count($iparray); $i++) {
        if ($iparray[$i]>255) {
          return (0);
        }
        return preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/",$ip);
      }
    }
    ?>
```

* 实例2：检查中文字符的正则表达式： 

直接上代码

```php
    <?php
    $str_arr = array( //测试数组
      "I am very happy",
      "快乐编程快乐生活",
      "PHP编程",
      "1997年香港回归",
      "英语学习ABC",
      "123456789"
    );
    
    $patt_ch = chr(0xa1)."-".chr(0xff); //匹配中文字符的ASCII范围
    
    foreach ($str_arr as $str){
      echo "字符串'$str'是";
      if(preg_match("/[$patt_ch]+/",$str)){  //注意在正则表达式的前后使用界定符
        echo "<b>存在中文</b>";
        echo "<br>";
        echo "<br>";
      }else {
        echo "不存在中文";
        echo "<br>";
        echo "<br>";
      }
    }
    ?>
```

* **实例3** ：检查Email地址的正则表达式

```php
    <?php
    $str_arr = array( //测试数组
      "mymail@somesite.com",
      "my_mail@somesite.com",
      "my-mail@somesite.com",
      "my.mail@somesite.com",
      "mymail@somesite.ccoomm",
      "mymail@site.cn",
      "mymail@@@site.com",
      "mymail@site",
      "MyMail@somesite.com",
      "My2007@somesite.com",
      "163mail_for-me777@somesite.com",
      "510137672@qq.com"
    );
    
    $patt_email = "/^[_a-zA-Z0-9-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4}$/"; //验证邮箱
    
    foreach ($str_arr as $str){
      echo "字符串'$str'是";
      if(preg_match($patt_email,$str)){  //注意在正则表达式的前后使用界定符
        echo "<b>合法的Email格式</b>";
        echo "<br>";
        echo "<br>";
      }else {
        echo "不合法的Email格式";
        echo "<br>";
        echo "<br>";
      }
    }
    ?>
```

* 实例4：检查URL地址的正则表达式 

直接上代码：

```php
    <?php
    $str_arr = array( //测试数组
      "http://www.liubaiqi.cn",
      "www.liubaiqi.cn",
      "http://www.liubaiqi.cn/login.html",
      "//liubaiqi.com",
      ":www.liubaiqi.cn"
    );
    
    $patt_url = "/^(http:\/\/)?[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)*.+$/"; //验证URL的正则表达式
    
    foreach ($str_arr as $str){ //遍历数组
      echo "字符串'$str'是";
      if(preg_match($patt_url,$str)){  //匹配URL
        echo "<b>合法的URL格式</b>";
        echo "<br>";
        echo "<br>";
      }else {
        echo "不合法的URL格式";
        echo "<br>";
        echo "<br>";
      }
    }
    ?>
```

小结：正则表达式的内容暂时就说这么多，如果以后遇到问题，我再补充。

在使用过程中，主要记住PERL函数的语法和正则表达式的格式，

这是现在较为常用的。其他的了解就行。


[1]: http://www.jianshu.com/p/240939e32465
