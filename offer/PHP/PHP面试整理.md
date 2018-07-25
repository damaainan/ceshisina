## PHP面试整理

来源：[https://segmentfault.com/a/1190000013782036](https://segmentfault.com/a/1190000013782036)


## PHP
### HTTP`Keep-Alive`的作用
#### 作用
`Keep-Alive`：使客户端到服务器端的连接持续有效，当出现对服务器的后继请求时，`Keep-Alive`功能避免了建立或者重新建立连接。Web服务器，基本上都支持HTTP`Keep-Alive`。
#### 缺点

对于提供静态内容的网站来说，这个功能通常很有用。但是，对于负担较重的网站来说，虽然为客户保留打开的连 接有一定的好处，但它同样影响了性能，因为在处理暂停期间，本来可以释放的资源仍旧被占用。当Web服务器和应用服务器在同一台机器上运行时，`Keep-Alive`功能对资源利用的影响尤其突出。
#### 解决
`Keep-Alive: timeout=5, max=100`

* `timeout`：过期时间5秒（对应`httpd.conf`里的参数是：`KeepAliveTimeout`），
* `max`：是最多一百次请求，强制断掉连接。就是在`timeout`时间内又有新的连接过来，同时`max`会自动减`1`，直到为`0`，强制断掉。


###`php`数组函数常见的那些?
#### 数组遍历函数

```
list();  //不是真正的函数，而是PHP的语言结构，用于给一组变量赋值，仅能用于索引数组
each();  //返回数组当前元素的键值对，并将指针移动到下一个元素位置
while(); //可配合list或each使用：while(list($key, $value) = each($arr)){each $key, $value; }
```
#### 数组内部指针控制

```
current();  //读取指针位置的内容
key();      //读取当前指针指向内容的索引值
next();     //将数组中的内部指针指向下一单元
prev();     //将数组内部指针倒回一位
end();      //将数组内部指针指向最后一个元素
reset();    //将目前指针指向第一个索引位置
```
#### 数组键值操作函数

```
array_values($arr);       //获得数组的值
array_keys($arr);         //获得数组的键名
array_flip($arr);         //数组中的值与键名互换（如果有重复前面的会被后面的覆盖）
array_search('PHP',$arr); //检索给定的值，加true则是严格类型检查
array_reverse($arr);      //将数组中的元素翻转(前后顺序)
in_array("apple", $arr);  //在数组中检索apple
array_key_exists("apple", $arr); // 检索给定的键名是否存在数组中
array_count_values($arr);        // 统计数组中所有值出现的次数
array_unique($arr);              // 删除数组中重复的值
```
#### 数组回调函数

```
array_filter(); //使用回调函数过滤数组中的元素，如果回调返回true则当前的元素被包含到返回数组中
array_walk();   //回调函数处理数组，自定义函数要有两个参数，本函数第三个参数可以作为回调第三个参数返回
array_map();    //可以处理多个数组，每个数组的长度应该相同，传入数组的个数和回调函数参数个数应该一致
```
#### 数组的分段和填充

```
array_slice($arr, 0, 3);    //将数组中的一段取出，此函数忽略键名（数组的分段）
array_splice($arr, 0, 3，array("black","maroon"));    //将数组中的一段取出，返回的序列从原数组中删除
array_chunk($arr, 3, TRUE);   //将一个数组分割成多个，TRUE为保留原数组的键名（分割多个数组）
```
#### 数组与栈，列队

```
array_push($arr, "apple", "pear");    //将一个或多个元素压入数组栈的末尾（入栈），返回入栈元素的个数
array_pop($arr);    // 将数组栈的最后一个元素弹出（出栈）
array_shift($arr);   //数组中第一个元素移出并返回（长度减1，其他元素向前移动一位，数字键名改为从零计数，文字键名不变）
array_unshift($arr,"a",array(1,2));  //在数组的开头插入一个或多个元素
```
#### 数组的排序

```
sort($arr);      //由小到大，忽略键名
rsort($arr);     //由大到小，忽略键名
asort($arr);     //由小到大，保留键名
arsort($arr);    //由大到小，保留键名
ksort($arr);     //按照键名正序排序
krsort($arr);    //按照键名逆序排序
```
#### 数组的计算

```
array_sum($arr);   //对数组内部的所有元素做求和运算（数组元素的求和）
array_merge($arr1, $arr2); //合并两个或多个（相同字符串键名，后面覆盖前面，相同的数字键名，后面的附加到后面）
 
array_diff($arr1, $arr2);       //返回差集结果数组   
array_diff_assoc($arr1, $arr2, $arr3);  //返回差集结果数组，键名也做比较
array_intersect($arr1, $arr2);  //返回交集结果数组    
array_intersect_assoc($arr1, $arr2);   //返回交集结果数组，键名也做比较 
```
#### 其他的数组函数

```
array_unique($arr);   //移除数组中重复的值，新的数组中会保留原始的键名
shuffle($arr);        // 将数组的顺序打乱
```
###`PHP`中几个输出函数`echo`，`print()`，`print_r()`，`sprintf()`，`var_dump()`的区别


* `echo`：是语句不是函数，没有返回值，可输出多个变量值，不需要圆括号。不能输出数组和对象，只能打印简单类型(如`int`,`string`)。
* `print`：是语句不是函数，有返回值 1 ，只能输出一个变量，不需要圆括号。不能输出数组和对象，只能打印简单类型(如`int`,`string`)。
* `print_r`：是函数，可以打印复合类型，例如：`stirng`、`int`、`float`、`array`、`object`等，输出`array`时会用结构表示，而且可以通过`print_r($str,true)`来使`print_r`不输出而返回`print_r`处理后的值
* `printf`：是函数，把文字格式化以后输出（参看C语言）
* `sprintf`：是函数，跟`printf`相似，但不打印，而是返回格式化后的文字（该函数把格式化的字符串写写入一个变量中，而不是输出来），其他的与`printf`一样。


```
$str = "Hello";    
$number = 123; 
$txt = sprintf("%s world. Day number %u",$str,$number)；
//输出： Hello world. Day number 123 
var_dump()：函数，输出变量的内容、类型或字符串的内容、类型、长度。常用来调试。
```

可以通过`function_exists('函数名称')`进行测试

```
var_dump(function_exists('print'));  //bool(false)
var_dump(function_exists('echo'));  //bool(false)
var_dump(function_exists('print_r')); //bool(true
```
###`heredoc``Heredoc`在正规的`PHP`文档中和技术书籍中一般没有详细讲述。他是一种`Perl`风格的字符串输出技术。使用`heredoc`技术可以实现界面与代码的准分离，比如`phpwind`模板。
`heredoc`的语法是用`”<<<”`加上自己定义成对的标签，在标签范围內的文字视为一个字符串


* 以`<<<End`开始标记开始，以`End`结束标记结束， **`结束标记必须顶头写，不能有缩进和空格，且在结束标记末尾要有分号`** 。开始标记和开始标记相同，比如常用大写的`EOT`、`EOD`、`EOF`来表示，也可以使用其他标记，只要保证开始标记和结束标记不在正文中出现就行。
* 位于开始标记和结束标记之间的变量可以被正常解析，但是函数则不可以。`在heredoc`中，变量不需要用连接符`.`或`,`来拼接，比如：


```
$a=2;
$b= <<<EOF
"zing"$a
"zing"
EOF;
echo $b; //结果连同双引号一起输出："zing"2 "zing"
```
`heredoc`常用在输出包含大量`HTML`语法文档的时候。他要比传统的`echo`输出精炼很多，如下所示:

```
function getHtml(){
    echo "<html>";
    echo "<head><title>Title</title></head>";
    echo "<body>Content</body>";
    echo "</html>";
}

function getHtml(){
    echo <<<EOT
    <html>
    <head><title>Title</title></head>
    <body>Content</body>
    </html>
EOT;
}
```
### 禁掉`cookie`的`session`使用方案，设置`session`过期的方法，对应函数

通过`url`传值，把`session id`附加到`url`上（缺点：整个站点中不能有纯静态页面，因为纯静态页面`session id`将无法继续传到下一页面）
通过隐藏表单，把`session id`放到表单的隐藏文本框中同表单一块提交过去（缺点：不适用`<a>`标签这种直接跳转的非表单的情况）
直接配置`php.ini`文件,将`php.ini`文件里的`session.use_trans_sid= 0`设为`1`,（好像在win上不支持）
用文件、数据库等形式保存`Session ID`，在跨页过程中手动调用

```
// 第一种  setcookie() 直接用setcookie设置session id的生命周期。
    $lifetime=60; //保存1分钟 
    session_start(); 
    setcookie(session_name(), session_id(), time()+$lifetime, "/");
// 第二种  session_set_cookie_params()    
    $lifetime=60;//保存1分钟
    session_set_cookie_params($lifetime);
    session_start();
    session_regenerate_id(true);
    // 其中session_regenerate_id();方法用于改变当前session_id的值，并保留session中数组的值。参数默认为 false,如果设置为true则改变session_id的值，并清空当前session数组。
```
###`json`格式数据有哪些特点
`JSON`一种轻量级的数据交换格式(`JavaScript Object Notation`, JS 对象标记)。它基于`ECMAScript`的一个子集。`JSON`采用完全独立于语言的文本格式，但是也使用了类似于C语言家族的习惯（包括`C`、`C++`、`C#`、`Java`、`JavaScript`、`Perl`、`Python等`）。这些特性使`JSON`成为理想的数据交换语言。 易于人阅读和编写，同时也易于机器解析和生成(网络传输速率)。


* `"名称/值"`对的集合 不同语言中，它被理解为对象(`object`)，记录(`record`)，结构(`struct`)，字典(`dictionary`)，哈希表(`hash table`)，键列表(`keyed list`)等
* 值的有序列表 多数语言中被理解为数组(array)


### php获取文件内容的方法，对应的函数


* `file_get_contents`得到文件的内容（可以以`get`和`post`的方式获取），整个文件读入一个字符串中
* 用`fopen`打开`url`, 以`get`方式获取内容（借助`fgets()`函数）
* 用`fsockopen`函数打开`url`（可以以`get`和`post`的方式获取），以`get`方式获取完整的数据，包括`header`和`body`
* 使用`curl`库获取内容，使用`curl`库之前，需要查看`php.ini`，查看是否已经打开了`curl`扩展


### php魔术方法与魔术常量
#### 类方法：
#####`__construct();`说明：具有构造函数的类会在每次创建新对象时先调用此方法，适合在使用对象之前做一些初始化工作。如果子类中定义了构造函数则不会隐式调用其父类的构造函数。要执行父类的构造函数，需要在子类的构造函数中调用`parent::__construct()`。如果子类没有定义构造函数则会如同一个普通的类方法一样从父类继承。
#####`__destruct();`说明：析构函数会在到某个对象的所有引用都被删除或者当对象被显式销毁时执行。
#### 方法重载：
#####`__call();`说明：在对象中调用一个不可访问方法时，`__call();`方法会被调用。
#####`__callStatic();`说明：用静态方式中调用一个不可访问方法时，`__callStatic();`方法会被调用。
#### 属性重载：(只对类中私有受保护的成员属性有效)
#####`__get();`说明：读取不可访问属性的值时，`__get()`会被调用。
#####`__set();`说明：在给不可访问属性赋值时，`__set()`会被调用。
#####`__isset();`说明：当对不可访问属性调用`isset()`或`empty()`时，`__isset()`会被调用。
#####`__unset();`说明：当对不可访问属性调用`unset()`时，`__unset()`会被调用。
#### 序列化相关：
#####`__sleep();`说明：序列化时调用，`serialize()`函数会检查类中是否存在该魔术方法。如果存在，该方法会先被调用，然后才执行序列化操作。
#####`__wakeup();`说明：`unserialize()`会检查是否存在一个`__wakeup()`方法。如果存在，则会先调用该方法，用在反序列化操作中，例如重新建立数据库连接，或执行其它初始化操作
#### 操作类和对象方法：
#####`__toString();`说明：方法用于一个类被当成字符串时调用，例如把一个类当做字符串进行输出
#####`__invoke()；`说明：当尝试以调用函数的方式调用一个对象时，`__invoke()`方法会被自动调用。
#####`__set_state()；`说明：当调用`var_export()`导出类时，此静态方法会被调用。 本方法的唯一参数是一个数组
#####`__clone();`说明：当复制完成时，如果定义了`__clone()`方法，则新创建的对象（复制生成的对象）中的`__clone()`方法会被调用，可用于修改属性的值。
####`__autoload();`说明：该方法可以自动实例化需要的类。当程序要用一个类但没有被实例化时，改方法在指定路径下查找和该类名称相同的文件。否则报错。
####`__debugInfo();`说明：php5.6增加的特性，`var_dump()`一个类时触发，返回一个包含对象属性的数组

PHP 将所有以`__`（两个下划线）开头的类方法保留为魔术方法。所以在定义类方法时，除了上述魔术方法，建议不要以`__`为前缀。在命名自己的类方法时不能使用这些方法名，除非是想使用其魔术功能。
#### 常量：

```
__LINK__//文件中的当前行号。
__FILE__//文件的完整路径和文件名。如果用在被包含文件中，则返回被包含的文件名。
__DIR__ //文件所在的目录。如果用在被包括文件中，则返回被包括的文件所在的目录，它等价于 dirname(__FILE__)。
 
__FUNCTION__//函数名称。自 PHP 5 起本常量返回该函数被定义时的名字（区分大小写）。在 PHP 4 中该值总是小写字母的。
__CLASS__ //类的名称。自 PHP 5 起本常量返回该类被定义时的名字（区分大小写）。在 PHP 4 中该值总是小写字母的。
__METHOD__//类的方法名（PHP 5.0.0 新加）。返回该方法被定义时的名字（区分大小写）。
__NAMESPACE__//当前命名空间的名称（大小写敏感）。这个常量是在编译时定义的（PHP 5.3.0 新增）
```
###`php.ini`中`safe mod`关闭 影响哪些函数和参数，至少写6个？

```
move_uploaded_file();
exec();
system();                              
passthru();
popen();
fopen();
mkdir();                              
rmdir();
rename();                            
unlink();
copy();                                 
chgrp();
chown();                              
chmod();
touch();                               
symlink();
link();                                   
parse_ini_file();
set_time_limit();                  
max_execution_time; 
mail();
```
###`isset()`、`empty()`与`is_null`的区别


* 当变量未定义时，`is_null()`和“参数本身”是不允许作为参数判断的，会报`Notice`警告错误；
* `empty`,`isset`首先都会检查变量是否存在，然后对变量值进行检测。而`is_null`和 “参数本身”只是直接检查变量值，是否为`null`，因此如果变量未定义就会出现错误！
* `isset()`：仅当`null`和未定义，返回`false`；
* `empty()`：`""`、`0`、`"0"`、`NULL`、`FALSE`、`array()`,`未定义`，均返回`true`；
* `is_null()`：仅判断是否为`null`，未定义报警告；
* 变量本身作为参数，与`empty()`一致，但接受未定义变量时，报警告；


###`MVC`的优缺点
####`MVC`的优点


* 可以为一个模型在运行时同时建立和使用多个视图。变化-传播机制可以确保所有相关的视图及时得到模型数据变化，从而使所有关联的视图和控制器做到行为同步。
* 视图与控制器的可接插性，允许更换视图和控制器对象，而且可以根据需求动态的打开或关闭、甚至在运行期间进行对象替换。
* 模型的可移植性。因为模型是独立于视图的，所以可以把一个模型独立地移植到新的平台工作。需要做的只是在新平台上对视图和控制器进行新的修改。
* 潜在的框架结构。可以基于此模型建立应用程序框架，不仅仅是用在设计界面的设计中。


####`MVC`的不足之处


* 增加了系统结构和实现的复杂性。对于简单的界面，严格遵循MVC，使模型、视图与控制器分离，会增加结构的复杂性，并可能产生过多的更新操作，降低运行效率。
* 视图与控制器间的过于紧密的连接。视图与控制器是相互分离，但确实联系紧密的部件，视图没有控制器的存在，其应用是很有限的，反之亦然，这样就妨碍了他们的独立重用。
* 视图对模型数据的低效率访问。依据模型操作接口的不同，视图可能需要多次调用才能获得足够的显示数据。对未变化数据的不必要的频繁访问，也将损害操作性能。
* 目前，一般高级的界面工具或构造器不支持MVC模式。改造这些工具以适应MVC需要和建立分离的部件的代价是很高的，从而造成使用MVC的困难。


###`session`与`cookie`的联系和区别（运行机制），`session`共享问题解决方案
#### 区别与联系：

使用`session_start()`调用`session`，服务器端在生成`session`文件的同时生成`session ID哈希值`和默认值为`PHPSESSID`的`session name`，并向客户端发送变量为`PHPSESSID(session name)`(默认)值为一个`128`位的哈希值。服务器端将通过该`cookie`与客户端进行交互，`session`变量的值经php内部系列化后保存在服务器机器上的文本文件中，和客户端的变量名默认情况下为`PHPSESSID`的`cookie`进行对应交互，即服务器自动发送了`http`头:`header(‘Set-Cookie: session_name()=session_id(); path=/’);`即`setcookie(session_name(),session_id());`当从该页跳转到的新页面并调用`session_start()`后,PHP将检查与给定`ID`相关联的服务器端存贮的`session`数据，如果没找到则新建一个数据集。
#### 共享方案：

* 使用数据库来保存`session`，就算服务器宕机了也没事，`session`照样在。

问题：程序需要定制；每次请求都进行数据库读写开销不小，另外数据库是一个单点，可以做数据库的`hash`来解 决这个问题。
* 使用`memcache`来保存`session`， 这种方式跟数据库类似，内存存取性能比数据库好很多。

问题：程序需要定制，增加了工作量；存入`memcached`中的数据都需要序列化，效率较低，断电或者重启电脑容易丢失数据；
* 通过加密的`cookie`，在A服务器上登录，在用户的浏览器上添加加密的`cookie`，当用户访问B服务器时，检查有无`Session`，如果没有，就检验`Cookie`是否有效，`Cookie`有效的话就在B服务器上重建`session`。简单，高效， 服务器的压力减小了，因为`session`数据不存在服务器磁盘上。根本就不会出现`session`读取不到的问题。

问题：网络请求占用很多。每次请求时，客户端都要通过`cookie`发送`session`数据给服务器，`session`中数据不能太多，浏览器对`cookie`的大小存在限制。不适合高访问量的情况，因为高访问量的情况下。