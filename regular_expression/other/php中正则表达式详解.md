## php中正则表达式详解

来源：[http://www.cnblogs.com/hellohell/p/5718319.html](http://www.cnblogs.com/hellohell/p/5718319.html)

## 概述

正则表达式是一种描述字符串结果的语法规则，是一个特定的格式化模式，可以匹配、替换、截取匹配的字符串。常用的语言基本上都有正则表达式，如JavaScript、java等。其实，只有了解一种语言的正则使用，其他语言的正则使用起来，就相对简单些。文本主要围绕解决下面问题展开。

* 有哪些常用的转义字符
* 什么是限定符与定位符
* 什么是单词定位符
* 特殊字符有哪些
* 什么是逆向引用以及怎样使用逆向引用
* 匹配模式
* php中怎样使用正则表达式
* php中哪些方面需要用到正则
* 怎样进行邮箱匹配，url匹配，手机匹配
* 怎样使用正则替换字符串中某些字符
* 贪婪匹配与惰性匹配区别
* 正则表达式之回溯与固态分组
* 正则优缺点有哪些


## 正则表达式的基本知识汇总

## 行定位符（^与$）

行定位符是用来描述字符串的边界。`“$”`表示行结尾`“^”`表示行开始如`"^de"`，表示以de开头的字符串 `"de$"`,表示以de结尾的字符串。

## 单词定界符

我们在查找的一个单词的时候，如an是否在一个字符串”gril and body”中存在，很明显如果匹配的话，an肯定是可以匹配字符串“gril and body”匹配到，怎样才能让其匹配单词，而不是单词的一部分呢？这时候，我们可以是哟个单词定界符\b。 
\ban\b 去匹配”gril and body”的话，就会提示匹配不到。 
当然还有一个大写的\B，它的意思，和\b正好相反，它匹配的字符串不能使一个完整的单词，而是其他单词或字符串中的一部分。如\Ban\B。

## 选择字符(|) ，表示或

选择字符表示或的意思。如Aa|aA，表示Aa或者是aA的意思。注意使用”[]”与”|”的区别，在于”[]”只能匹配单个字符，而”|”可以匹配任意长度的字符串。在使用”[]”的时候，往往配合连接字符”-“一起使用，如[a-d],代表a或b或c或d。

## 排除字符，排除操作

正则表达式提供了”^”来表示排除不符合的字符，^一般放在[]中。如[^1-5]，该字符不是1~5之间的数字。

## 限定符(？*+{n，m})

限定符主要是用来限定每个字符串出现的次数。

| 限定字符 | 含义 |
|-|-|
| ？ | 零次或一次 |
| * | 零次或多次 |
| + | 一次或多次 |
| {n} | n次 |
| {n,} | 至少n次 |
| {n,m} | n到m次 |


如(D+)表示一个或多个D

## 点号操作符

匹配任意一个字符（不包含换行符）

## 表达式中的反斜杠(`\`)

表达式中的反斜杠有多重意义，如转义、指定预定义的字符集、定义断言、显示不打印的字符。

### 转义字符

转义字符主要是将一些特殊字符转为普通字符。而这些常用特殊字符有”.”，”?”、”\”等。

### 指定预定义的字符集

| 字符 | 含义 |
|-|-|
| \d | 任意一个十进制数字[0-9] |
| \D | 任意一个非十进制数字 |
| \s | 任意一个空白字符(空格、换行符、换页符、回车符、字表符) |
| \S | 任意一个非空白字符 |
| \w | 任意一个单词字符 |
| \W | 任意个非单词字符 |


###显示不可打印的字符

| 字符 | 含义 |
|-|-|
| \a | 报警 |
| \b | 退格 |
| \f | 换页 |
| \n | 换行 |
| \r | 回车 |
| \t | 字表符 |


## 括号字符()

在正则表达式中小括号的作用主要有:


* 改变限定符如（|、* 、^)的作用范围 
如(my|your)baby，如果没有”()”，|将匹配的是要么是my，要么是yourbaby,有了小括号，匹配的就是mybaby或yourbaby。
* 进行分组，便于反向引用



## 反向引用

反向引用，就是依靠子表达式的”记忆”功能，匹配连续出现的字串或是字符。如(dqs)(pps)\1\2，表示匹配字符串dqsppsdqspps。在下面php应用中，我将详细展开学习反向引用。

## 模式修饰符

模式修饰符的作用是设定模式，也就是正则表达式如何解释。php中主要模式如下表：

| 修饰符 | 说明 |
|-|-|
| i | 忽略大小写 |
| m | 多文本模式 |
| s | 单行文本模式 |
| x | 忽略空白字符 |


## 正则表达式在php中应用

## php中字符串匹配

所谓的字符串匹配，言外之意就是判断一个字符串中，是否包含或是等于另一个字符串。如果不使用正则，我们可以使用php中提供了很多方法进行这样的判断。

### 不使用正则匹配


* strstr函数 
string strstr ( string haystack,mixedneedle [, bool $before_needle = false ]) 


* 注1：haystack是当事字符串，needle是被查找的字符串。该函数区分大小写。

* 注2：返回值是从needle开始到最后。
* 注3：关于$needle，如果不是字符串，被当作整形来作为字符的序号来使用。
* 注4：before_needle若为true,则返回前东西。




* stristr函数与strstr函数相同，只是它不区分大小写
* strpo函数 
int strpos ( string haystack,mixedneedle [, int $offset = 0 ] ) 
注1：可选的 offset 参数可以用来指定从 haystack 中的哪一个字符开始查找。返回的数字位置是相对于 haystack 的起始位置而言的。

* stripos -查找字符串 **`首次出现`** 的位置（ **`不区分大小`** 定）
* strrpos -计算指定字符串在目标字符串中 **`最后一次出现`** 的位置
* strripos -计算指定字符串在目标字符串中 **`最后一次出现`** 的位置（ **`不区分大小写`** ）



### 使用正则进行匹配

在php中，提供了preg_math()和preg_match_all函数进行正则匹配。关于这两个函数原型如下：

```php
int preg_match|preg_match_all ( string $pattern , string $subject [, array &$matches [, int $flags = 0 [, int $offset = 0 ]]] )
```

搜索subject与pattern给定的正则表达式的一个匹配. 
pattern:要搜索的模式，字符串类型。 
subject :输入字符串。 
matches:如果提供了参数matches，它将被填充为搜索结果。 matches[0]将包含完整模式匹配到的文本，matches[1]将包含第一个捕获子组匹配到的文本，以此类推。 
flags:flags可以被设置为以下标记值：PREG_OFFSET_CAPTURE 如果传递了这个标记，对于每一个出现的匹配返回时会附加字符串偏移量(相对于目标字符串的)。 注意：这会改变填充到matches参数的数组，使其每个元素成为一个由 第0个元素是匹配到的字符串，第1个元素是该匹配字符串 在目标字符串subject中的偏移量。 
offset:通常，搜索从目标字符串的开始位置开始。可选参数 offset 用于 指定从目标字符串的某个未知开始搜索(单位是字节)。 
返回值：preg_match()返回 pattern 的匹配次数。 它的值将是0次（不匹配）或1次，因为 preg_match()在第一次匹配后 将会停止搜索。 preg_match_all()不同于此，它会一直搜索subject直到到达结尾。 如果发生错误 preg_match()返回 FALSE。

### 实例


* 实例1 
判断字符串”[http://blog.csdn.net/hsd2012](http://blog.csdn.net/hsd2012)“中是否包含csdn? 
解法一（不适用正则）： 
如果不适用正则，我们使用strstr或者strpos中任意一个都可以，在此，我将使用strstr函数，代码如下：



```php
$str='http://blog.csdn.net/hsd2012';
function checkStr1($str,$str2)
{
    return strstr1($str,$str2)?true:false;
}
echo checkStr($str,'csdn');
```

解法二：使用正则 
因为我们只需要判断是否存在即可，所以选择preg_match。

```php
$str='http://blog.csdn.net/hsd2012';
$pattern='/csdn/';
function checkStr2($str,$str2)
{
    return preg_match($str2,$str)?true:false;
}
echo checkStr2($str,$pattern);
```


* 实例2（考察 **`单词定界符`** ） 
判断字符串”I am a good boy”中是否包含单词go 
首先判断是单词，而不是字符串，因此比较的时候，需要比较是否包含’ go ‘，即在字符串go前后有一个空格。 
解析：如果使用非正则比较，只需要调用上面的checkStr1()函数即可，注意，第二个参数前后要加一个空格,即’ go ‘。如果使用正则， 
我们可以考虑使用单词定界符\b，那么$pattern=’/\bgo\b/’;然后调用checkStr2函数即可.
* 例3（考察 **`反向引用`** ) 
判断字符串”I am a good boy”中是否包含3个相同的字母 
解析：此时，如果我们不使用正则，将会很难判断，因为字母太多了，我们不可能去将所有字母分别与该字符串比较，那样工作量也比较大。这时候涉及到了正在的反向引用。在php正则表达式中，通过\n，来表示第n次匹配到的结果。如\5代表第五次匹配到的结果。那么本题的`$pattern='/(\w).*\1.*\1/';` 
主要注意的是，在使用反向匹配的时候都需要使用(),反向匹配时，匹配()里面出现的字符或字符串。



## php中字符串替换

### 不使用正则

php中当替换字符串的时候，如果不适用正则，我们通常使用substr、mb_substr、str_replace、substr_replace关于这几个函数区别如下表。

| 函数符 | 功能 | 描述 |
|-|-|-|
| str_replace(find,replace,string,count) | 使用一个字符串替换字符串中的另一些字符。 | find 必需。规定要查找的值。replace 必需。规定替换 find 中的值的值。string 必需。规定被搜索的字符串。count 可选。一个变量，对替换数进行计数。 |
| substr_replace(string,replacement,start,length) | 把字符串的一部分替换为另一个字符串。适合用于替换自定位置的字符串。 | string 必需。规定要检查的字符串。replacement 必需。规定要插入的字符串。start 必需。规定在字符串的何处开始替换。 |


### 使用正则

如果使用正则替换，php中提供了`preg_replace _callback`和`preg_replace` 函数，`preg_replace` 原型如下： 
mixed preg_replace ( mixed pattern,mixedreplacement , mixed subject[,intlimit = -1 [, int &count]])函数功能描述：在字符串subject中，查找pattern,然后使用replacement 去替换，如果有limit则代表限制替换limit次。pregreplacecallback与pregreplace功能相识，不同的是pregreplaceback使用一个回调函数callback来代替replacement.−例1将字符串”hello,中国”中的hello替换为′你好′;
