# PHP中的urlencode，htmlentities并不简单

2016.12.07 11:40  字数 1574  

最近看到一篇[好文章][1],所以就对PHP的二个函数（urlencode 和 htmlentities）做个总结,很多事情看上去很简单,但是深入下去并不那么“简单”。

#### urlencode

很多文章说到 urlencode 函数的时候，都会提到 rawurlencode 函数，二者之间非常的相似，rawurlencode 函数遵循 RFC 3986 协议，urlencode 遵循 RFC 1866 协议。二者应用场景并不一样（不太清楚rawurlencode在何种场景下使用）；urlencode 是将空格替换成“+”，rawurlencode 是将空格替换为“%20”。

二者在使用的时候，需要注意是按照何种编码进行转换的，一般是根据PHP文件编码进行转换，比如对于一个中文字符（中）。假如文件编码是 UTF-8 ，编码后得到的结果是`%E4%B8%AD`;假如文件编码是 GBK ，编码后得到的结果是`%D6%D0`。

大家有没有思考一个问题，urlencode 函数存在的意义？ 在 URL 中输入的元素必须是 US-ASCII 的子集，假如包含其它字符，那就是不安全的，所以假如需要使用则必须转码，这样才是一个合格的 URL 。

那么什么样的字符是不安全的呢？下面描述下：

* ASCII 控制字符，这些字符是不能打印的，既然不能打印，那么用户也不能输入，所以需要转码。
* Non-ASCII，比如中文字符。
* 保留字符，这很重要。 URL 之所以能够被浏览器解析就在于某些字符（比如 :，/符号）具有特殊含义（具有特殊含义才需要保留啊）。那么假如用户要“取消”这些保留字符的含义，那么就必须转码。
* 不安全字符，这些字符应该可以称为“应用字符”，对应的就是 HTML 中的“实体字符”，典型的不安全字符包括“<”,“\”,“%”等。

平时我们拷贝一个含有中文的 URL 到浏览器的时候，浏览器会自动转码。做了个实验，对于 Chrome浏览器来说，页面编码不管是 UTF-8 还是 GBK , urlencode 都是使用 UTF-8 编码进行编码的，这间接也说明应该统一使用 UTF-8。

几个注意点：

（1）在 PHP 中，仅仅对于需要“编码”的字符进行编码，不然会有错误，比如：

    $str = "中国";
    $url = "http://blog.newyingyong.cn/" . urlencode($str);
    //不要对整个URL进行编码，否则将URL中的保留字符也转码了，这样这就不是一个正确的URL

（2）在 WEB 开发中，建议对数据都进行 urlencode，这个编码与 WWW 表单 POST 数据的编码方式是一样的，同时与 application/x-www-form-urlencoded 的媒体类型编码方式一样。

（3）`$_GET` 和 `$_REQUEST` 会自动 urldecode，所以需要注意，尤其对于+字符来说，假如二次 urldecode，则会丢失该字符，因为系统进行二次解码，会将“+”符号变为空格符号。**201704备注**：`$_POST` 变量也会主动 urldecode。同时假如服务器端程序自己构建数据发送请求，需要确保进行 urlencode，举个例子仔细体会下：

    $url = 'http://localhost/t.php?parama=' . urlencode('a+b c');
    $url = 'http://localhost/t.php?a=a+b c'; //比较没有 urlencode 处理的区别
    $data = array(
        'paramb' => "a+b c",
    );
    
    $headers = array(
       'Content-Type: application/x-www-form-urlencoded',
    );
    
    $options = array(
        'http' => array(
            'method' => 'POST',
            //http_build_query 是 urlencode 的封装版，本质没有太大区别
            'content' => http_build_query($data),
            'header' => implode("\r\n", $headers),
        )
    );
    
    $rs = stream_context_create($options) ;
    echo file_get_contents($url, false, $rs);

### htmlentities

这个函数的基本定义如下：在 HTML 中，某些字符具有特殊含义（称为应用字符），那么为了表达这些字符的本来含义，就必须使用该函数进行处理。

比如在 HTML 中 “<”和“>”符号可以组合起来表达很多 HTML 元素（浏览器执行的时候就会看到效果），假如仅仅需要表示为“<”和“>”符号，那么就要使用该函数将二者转换为<和>，这些字符也叫“实体字符”。

和 htmlentities 函数比较类同的一个函数是 htmlspecialchars , 个人觉得手册中描述的很好：

> Certain characters have special significance in HTML, and should be represented by HTML  
> entities if they are to preserve their meanings. This function returns a string with these conversions made

二者的区别在于：

* htmlentities 是转换所有的字符为实体字符，而 htmlspecialchars 仅仅转换部分字符（包括 `&`、`<`、`>`、`"`、`'`）
* htmlentities 仅仅能处理 native characterset ，一般是 ASCII。而 htmlspecialchars 能指定特定字符集进行转换（这个观点已经陈旧）

在开发中，一般发送的数据假如不是富文本模式，则建议使用 htmlspecialchars 函数进行处理避免出现 XSS 攻击。当然为了不破坏原始发送数据，入库的时候不做任何改变，在出库渲染的时候进行转义处理；假如发送的数据是富文本模式，则不应该转义了，这个处理方式可能就是另外的逻辑了。

在本文中为什么同时提到 urlencode 和 htmlentities呢，在 PHP 手册中提到这么下面一段代码，个人理解了好一会还是不得其法。

    $query_string = 'foo=' . urlencode($foo) . '&bar=' . urlencode($bar);
    echo '<a href="mycgi?' . htmlentities($query_string) . '">';

在 stackoverflow 找到这么一篇[解疑][2]，说的很好。

> This is not about HTML entities in URLs. This is about you putting arbitrary data into HTML, which means you need to HTML escape any special characters in it. That this data happens to be a URL is irrelevant.

> * You need to escape any arbitrary data you put into the URL with urlencode to preserve characters with a special meaning in the URL.
> * The arbitrary blob of data you get from step one needs to be HTML escaped for the same reasons when put into HTML. As you see in your example, there's an & in your data which is required to be escaped to & by HTML rules.

> If you did not use the URL in an HTML context, there'd be no need to HTML escape it. HTML entities have no place in a URL. A URL in an HTML context must be HTML escaped though, like any other data

这二个函数的转码用的场景是不一样的，urlencode 一般是对 URL 转码。而 htmlentities 是对 HTML 的元素进行转码，之所以有联系，是因为 HTML 中的“a”,"image"标签的值可能会是一个 URL。而在 URL 中实体字符也是能够被浏览器自动转换的，所以为避免错误，可以采用上面一段的代码。


[1]: http://www.blooberry.com/indexdot/html/topics/urlencoding.htm
[2]: http://stackoverflow.com/questions/12908258/url-htmlentities-what-to-think-about-this