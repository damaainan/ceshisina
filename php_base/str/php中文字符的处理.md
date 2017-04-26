## 字符串的定义和使用

PHP 中能够通过**四种**方法设置字符串：

**单引号字符串**  
单引号字符串类似于 Python 中的原始字符串,也就是说单引号字符串没有变量解析功能和特殊字符转义功能。比如$str='hello\nworld'，其中的\n并没有换行功能。

**双引号字符串**  
双引号字符串具备单引号字符串没有的变量解析功能和特殊字符转义功能。

个人对于十六进制和八进制的字符串特殊转义很感兴趣，特别补充：

    \[0-7]{1,3} #八进制表达方式
    \x[0-9A-Fa-f]{1,2} #十六进制表达方式

**heredoc**  
这种表达式类似于 Python 中的长字符串，能够定义包含多行的字符串。其语法定义很严格，使用起来需要注意。

    $str=<<<EOD
    hello\n
    world
    EOD;

**Nowdoc**  
Nowdoc类似于单引号字符串，不会解析变量。比较适合定义一大段文本且无需对其中的特殊字符进行转义。

**变量解析**  
PHP字符串最强大的部分就是变量解析，可以在运行时根据上下文解析变量（这才是解释型语言），可以产生很多妙用。

简单的变量解析就是在字符串中可以包含“变量”，“数组”，“对象属性”，复杂的语法规则就是使用{}符号来进行操作（组成一个表达式）。

通过一个例子看看变量解析的强大之处

    class beers {
        const softdrink = 'softdrink';
        public static $ale = 'ale';
        public $data = array(1,3,"k"=>4);
    }
    
    $softdrink = "softdrink";
    $ale = "ale";
    $arr = array("arr1","arr2","arr3"=>"arr4","arr4"=>array(1,2));
    $arr4 = "arr4";
    $obj = new beers;
    echo "line1:{$arr[1]}\n";
    echo "line2:{$arr['arr4'][0]}\n"; 
    echo "line3:{$obj->data[1]}\n";
    echo "line4:{${$arr['arr3']}}\n";
    echo "line5:{${$arr['arr3']}[1]}\n";
    echo "line6:{${beers::softdrink}}\n";
    echo "line7:{${beers::$ale}}\n";

## 字符串转换

PHP 语言比 Python 简单的另外一个原因就是类型的隐式转换，会简化很多操作，这里通过字符串转换来说明。

#### 字符串类型强制转换

    $var = 10 ;
    $dvar = (string)$var ;
    echo $dvar . "_" . gettype($dvar);

strval()函数是获取变量的字符串值：

    $var = 10.2 ;
    $dvar = strval($var) ;
    echo gettype($var) . "_" . $dvar . "_" . gettype($dvar);

settype()函数是设置变量的类型：

    $str = "10hello";
    settype($str, "integer");
    echo $str ;

在强制类型转换过程中，将其他类型的值转换为字符串的时候会遵循一定的规则，比如一个布尔值 boolean 的 TRUE 被转换成 string 的 “1”。相关规则最好还是理解下。

#### 自动类型转换

上面的二个转换属于显示转换，而更要关注的是自动类型转换，  
在一个需要字符串的表达式中，会自动转换为类型，具体见例子：

    $bool = true;
    $str = 10 + "hello"
    echo $bool . "_" . $str ;

## PHP 字符串的本质

引用 PHP 文档的解释：

> PHP 中的 string 的实现方式是一个由字节组成的数组再加上一个整数指明缓冲区长度。并无如何将字节转换成字符的信息，由程序员来决定。字符串由什么值构成没有限制，包括值为 0 的字节可以出现在字符串的任何位置。

> PHP并不特别指明字符串的编码，那字符串到底是怎样编码的呢，这取决于程序员。字符串会按照 PHP 文件的编码来对字符串进行编码。比如你的文件编码是 GBK，那么你代码内容都是 GBK的。

补充**二进制安全**这个概念，其值为 0 （NULL）的字节可以处于字符串任何位置，而 PHP 的部分非二进制函数底层是调用的 C 函数，会把 NULL 后面的字符忽略。

只要 PHP 的文件编码是能兼容 ASCII 的，那么字符串操作就可以很好的被处理。但是字符串操作本质上还是 Native 的（不管文件编码是什么），所以在使用的时候需要注意：

* 某些函数假定字符串是以单字节编码的，但并不需要将字节解释为特定的字符。比如 sbustr() 函数。
* 很多函数是需要显示的传递编码参数，不然会从 PHP.INI 文件中获取默认值，比如 htmlentities() 函数。
* 还有一些函数和本地区域有关，这些函数也只能是单字节操作的。

一般情况下，虽然 PHP 内部不支持 Unicode 字符，但是支持 UTF-8 编码，绝大部分情况下不会有什么问题，但是下列的情况可能就处理不了了：

* 非 UTF-8 编码字符串如何进行转换
* 一个 UTF-8 编码的网页，但是用户在提交表单的时候，可能使用 GBK 的编码（不遵守 meta tag）
* 一个 UTF-8 编码的 PHP 文件，使用 strlen("中国")返回的是 6 而不是实际的字符数（2）

那么如何解决该问题呢？ PHP 提供了 mbstring 扩展 ！

## 多字节字符串

mbstring 扩展默认不是打开的，安装的时候需要 --enable-mbstring。

我们首先看看 PHP.INI 中对于 mbstring 指令的配置，花了好久才逐步明白。

* mbstring.language 这个参数我就理解为 UTF-8 了
* mbstring.internal_encoding 这个编码和 PHP 文件编码没有关系，只是在大部分 mbstring 函数里面需要指定待处理字符串的编码，假如不显示指定，默认就获取该参数的值，该参数的值在高版本 PHP 中用 default_charset 参数代替了。
* mbstring.http_input 该参数指定 HTTP input 的默认编码（不包含 GET 参数）。一般和 HTML 页面的编码保持一致，该参数的值用 default_charset 参数代替。
* mbstring.http_output 该参数误导我了，HTTP output 是什么，PHP 输出不就是页面，怎么会有这概念？
* mbstring.encoding_translation，这个参数重点说下，默认是关闭的，假如打开，PHP 会对 POST 变量和上传文件的名称自动转换编码为 mbstring.internal_encoding 指定的值，不过我没有试验过，大家可以上传一个中文名的文件。建议关闭，让程序员来处理相关问题。

后面看看 mbstring 扩展的一些函数：

* mb_http_input()：检测 HTTP input 字符编码，觉得对于文件上传的文件名有必要处理。
* mb_convert_encoding()：比较常用的函数，注意第三个参数。
* mb_detect_order()：设置/获取字符编码的检测顺序。
* mb_list_encodings()：返回系统支持的编码列表。

重点说明下：PHP 文件支持的编码有一定要，要兼容 ASCII。

但是不要使用 BIG-5 作为 PHP 文件编码，尤其字符串以 identifiers 或 literals 形式出现，假如实在 PHP 文件编码要是 BIG-5，那么对于输入输出的内容尽量转换为 UTF-8。

## Zend Multibyte

最后说下 Zend Multibyte 这个概念，理解的不是特别深刻，首先不要和 mbstring 扩展混在一块。 Zend Multibyte 模式默认是关闭的，可以通过 zend.multibyte 指令打开。然后通过 declare() 函数来指定 PHP 解析器的编码。

那这个指令出现的意义是什么？上面说过 PHP 文件的编码需要是兼容 ASCII 的，那么类似于 BIG-5 这样的非兼容 ASCII 编码怎么办，可以通过这个指令来操作，当 PHP 解析器读取 mbstring.script_encoding 编码并用该编码来解析 PHP 文件。



#### PHP 层面如何处理 UTF-8

当操作 Unicode 字符集的时候，请务必安装 mbstring 扩展，并使用相应的函数代替原生的字符串函数。举个例子，一个文件编码为 UTF-8 的 PHP 代码，假如使用 strlen() 函数是错误的，请使用 mb_strlen() 函数代替。

mbstring 扩展大部分的函数都需要基于一个编码（内部编码）来处理，请务必统一使用 UTF-8 编码，这个大部分可以在 PHP.INI 中配置。

从 PHP 5.6 开始，default_charset 配置可以替换为 mbstring.http_input，mbstring.http_output 。  
另外一个重要的配置就是 mbstring.language，这个默认值是 Neutral（UTF-8）。

注意文件编码和 mbstring 扩展的内部编码不是同一个概念。

概括的说来：

* PHP.INI 中涉及到 mbstring 扩展的部分尽量使用 UTF-8。
* 请用 mbstring 扩展函数代替原生字符串操作函数。
* 在使用相关函数的时候，请务必了解你操作的字符的编码是什么，在使用对应函数的时候，显示的写上 UTF-8 编码参数，比如 htmlentities() 函数的第三个参数显示写上 UTF-8。

#### 文件 IO 操作 如何处理 UTF-8

这里举个例子，假如你要打开一个文件，但是不知道文件内容是什么编码的，那么如何处理呢？  
最佳实践就是，在打开的时候统一转换成 UTF-8，修改内容后就再转回原来的编码并保存到文件。看代码把：

    if ( mb_internal_encoding()!="UTF-8") {
            mb_internal_encoding("UTF-8");
    }
    $file = "file.txt"; //一个编码为gbk的中文文件
    $str= file_get_contents($file);
    //不管来源是什么编码，统一显示的时候转换为 UTF-8
    if (mb_check_encoding($str, "GBK")) {
        $str =  mb_convert_encoding($str, "UTF-8", "GBK");
        $str ="修改内容";
        $str =  mb_convert_encoding($str, "GBK", "UTF-8"); //原样转回去
        file_put_contents($file, $str);
    }
