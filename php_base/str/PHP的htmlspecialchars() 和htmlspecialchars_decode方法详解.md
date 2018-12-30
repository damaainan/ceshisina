## PHP的htmlspecialchars() 和htmlspecialchars_decode方法详解

来源：[https://segmentfault.com/a/1190000007537603](https://segmentfault.com/a/1190000007537603)

htmlspecialchars() 函数把预定义的字符转换为 HTML 实体。

预定义的字符是：


* & （和号）成为 &

* " （双引号）成为 "

* ' （单引号）成为 '

* < （小于）成为 <

* `>` （大于）成为 >

 **`注意：这个函数不能对斜杠/，反斜杠\做处理。`** 

示例：

```php
$content  = '你是/谁啊，大几\都"老梁"做做&>women<a>没<script> alert("hello");</script>';

$content = htmlspecialchars($content);  

// 结果：你是/谁啊，大几\都&quot;老梁&quot;做做&>women<a>没<script> alert(&quot;hello&quot;);</script>

// 对反斜杠进行转换
 $content = preg_replace("/\\\/", "&#092;", $content);

//  结果：你是/谁啊，大几&#092;都&quot;老梁

// 对斜杠进行过滤,入库时进行XSS检测攻击。

$content = preg_replace("/\//", "&#47;", $content);

```
### 一、HTML 实体

在 HTML 中，某些字符是预留的。
在 HTML 中不能使用小于号（<）和大于号（>），这是因为浏览器会误认为它们是标签。
如果希望正确地显示预留字符，我们必须在 HTML 源代码中使用字符实体（character entities）。
字符实体类似这样：
&entity_name;或者entity_number;

```
如需显示小于号，我们必须这样写：< 或 &#60;
```

提示：使用实体名而不是数字的好处是，名称易于记忆。不过坏处是，浏览器也许并不支持所有实体名称（对实体数字的支持却很好）。
### 二、PHP htmlspecialchars() 函数

htmlspecialchars(string,flags,character-set,double_encode)

flags 可选。规定如何处理引号、无效的编码以及使用哪种文档类型。
可用的引号类型：


* ENT_COMPAT - **`默认。仅编码双引号。`** 

* ENT_QUOTES - 编码双引号和单引号。

* ENT_NOQUOTES - 不编码任何引号。


character-set:


* UTF-8 - **`默认`** 。ASCII 兼容多字节的 8 位 Unicode
POCO 的后端为GBK，所以用这个函数的时候，尽量使用编码，而默认的编码为UTF-8

* GB2312 - 简体中文，国家标准字符集

* double_encode    可选。布尔值，规定了是否编码已存在的 HTML 实体。

* TRUE - 默认。将对每个实体进行转换。

* FALSE - 不会对已存在的 HTML 实体进行编码。


示例：

```php
$content = "women's life" . '你是/谁啊，大几\都"老梁"做做&>women<a>没<script> alert("hello");</script>';

// 如果使用默认的参数，则不会对单引号进行转换。
$new_str = htmlspecialchars($content );

//打印： women's life你是/谁啊，大几\都&quot;老梁&quot;做做&>women<a>没<script> alert(&quot;hello&quot;);</script>

// ENT_QUOTES 编码双引号和单引号。

$new_str = htmlspecialchars($content, ENT_QUOTES);
women&#039;s life你是/谁啊，大几\都&quot;老梁&quot;做做&>women<a>没<script> alert(&quot;hello&quot;);</script>
```
### 三、htmlspecialchars_decode解码

htmlspecialchars_decode(string,flags)


* string    必需。规定要解码的字符串。

* flags    可选。规定如何处理引号以及使用哪种文档类型。

* 可用的引号类型：

* ENT_COMPAT - **`默认。仅解码双引号。`** 

* ENT_QUOTES - 解码双引号和单引号。

* ENT_NOQUOTES - 不解码任何引号。


测试：

```php
解码：
$str = ‘women&#039;s life你是/谁啊，大几\都&quot;老梁&quot;做做&>women<a>没<script> alert(&quot;hello&quot;);</script>’;

// 只解码双引号
$new_str = htmlspecialchars_decode($new_str);
dump($new_str);

打印：
women&#039;s life你是/谁啊，大几\都"老梁"做"做&>women<a>没<script> alert("hello");</script>

// 解码双引号和单引号。
$content = "women's life" . '你是/谁啊，大几\都"老梁"做"做&>women<a>没<script> alert("hello");</script>';

$new_str = htmlspecialchars($content, ENT_QUOTES, gb2312, true);

$new_str = htmlspecialchars_decode($new_str, ENT_QUOTES);
print_r($new_str);

打印：
women's life你是/谁啊，大几\都"老梁"做"做&>women<a>没<script> alert("hello");</script>

```
### 四、函数封装

将上边的字符串预定义转为实体封装为一个方法，以后可以直接调用：

```php
$str =  "women's life" . '你是/谁啊，大几\都"老梁"做做&>women<a>没<script> alert("hello");</script>';

// 1.将常用的预定义字符转为实体
$new_str = htmlspecialchars($str, ENT_QUOTES, gb2312, true);

// 2.替换反斜杠
 $new_str = preg_replace("/\\\/", "&#092;", $new_str);

// 3.替换斜杠
$content = preg_replace("/\//", "&#47;", $content);

// 打印结果：
women&#039;s life你是&#47;谁啊，大几&#092;都&quot;老梁&quot;做做&>women<a>没<script> alert(&quot;hello&quot;);<&#47;script>

```
 **`编码-将HTML转为实体`** 

```php

/**
 * 将HTML转为实体
 * @param string $str     需要处理的字符串
 * @param string $charset 编码，默认为gb2312
 * @return string
 */
function html_to_entities($str, $charset = "gb2312")
{
   // 参数判断
    if(empty($str)) return "";
    
    // 1.将常用的预定义字符转为实体
    $new_str = htmlspecialchars($str, ENT_QUOTES, $charset);

    // 2.替换反斜杠
    $new_str = preg_replace("/\\\/", "&#092;", $new_str);

    // 3.替换斜杠
    $new_str = preg_replace("/\//", "&#47;", $new_str);
    
    return $new_str;
}
```
 **`解码-将实体转为HTML`** 

```php

/**
 * 将实体转为HTML
 * @param string $str     需要处理的字符串
 * @return string
 */
function entities_to_html($str)
{
   // 参数判断
    if(empty($str)) return "";
    
    // 1.将实体转为预定义字符
    $new_str = htmlspecialchars_decode($str, ENT_QUOTES);

    // 2.替换反斜杠实体
    $new_str = str_replace("&#092;", "\\", $new_str);

    // 3.替换斜杠实体
    $new_str = str_replace("&#47;", "/", $new_str);
    
    return $new_str;
}
```
### 五、小结

一般使用htmlspecialchars将字符串的预定义字符转为实体的时候，需要传递 **`ENT_QUOTES`** 参数，因为如果不传递参数，默认的只对双引号做转换，而单引号不做转换，这样不能起到防止SQL注入的风险，所以，正式用的时候，我们希望双引号和单引号及其他可能引起SQL注入的都需要进行实体转换，存入数据库，所以，以后在用这个函数处理的时候，应该传入 **`ENT_QUOTES`** 参数，然后再结合 **`preg_replace`** 方法将斜杠、反斜杠替换为实体，这样就完美了。

相关文章：
[HTML实体对照表][6]
[入库转HTML为实体的重要性-防SQL注入][7]

[0]: 
[1]: 
[2]: 
[3]: 
[4]: 
[5]: 
[6]: http://www.php100.com/html/program/html/2013/0903/1052.html
[7]: http://www.cnblogs.com/sdya/p/4568548.html