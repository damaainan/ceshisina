## XSS攻击原理分析与防御技术

来源：[https://segmentfault.com/a/1190000013315450](https://segmentfault.com/a/1190000013315450)

跨站脚本攻击(Cross Site Scripting)，缩写为XSS。恶意攻击者往Web页面里插入恶意javaScript代码，当用户浏览该页之时，嵌入其中Web里面的javaScript代码会被执行，从而达到恶意攻击用户的目的。

### 一个简单的XSS攻击
![][0]

代码：

```js
async function(ctx, next){
    ctx.set('X-XSS-Protection',0);
    ctx.render('index',{from:ctx.query.from});
};
```

注意：插入X-XSS-Protection头部使浏览器XSS拦截器失效。

开始攻击：

![][1]

大家发现网页执行了一段脚本，而且这段脚本是用户恶意输入的。这就是XSS攻击最简单的一个案例。把原本应该显示纯文本的地方，执行了一段黑客写入的脚本。

那XSS攻击有什么危害呢？

1、盗取各类用户帐号
2、控制企业数据，包括读取、篡改、添加、删除企业敏感数据的能力
3、盗窃企业重要的具有商业价值的资料
4、非法转账
5、强制发送电子邮件
6、网站挂马
7、控制受害者机器向其它网站发起攻击

### XSS攻击的分类
 **`1、反射型`** 

又称为非持久性跨站点脚本攻击。漏洞产生的原因是攻击者注入的数据反映在响应中。非持久型XSS攻击要求用户访问一个被攻击者篡改后的链接，用户访问该链接时，被植入的攻击脚本被用户游览器执行，从而达到攻击目的。也就是我上面举的那个简单的XSS攻击案例，通过url参数直接注入。然后在响应的数据中包含着危险的代码。

当黑客把这个链接发给你，你就中招啦！

 **`2、存储型`** 

又称为持久型跨站点脚本，它一般发生在XSS攻击向量(一般指XSS攻击代码)存储在网站数据库，当一个页面被用户打开的时候执行。持久的XSS相比非持久性XSS攻击危害性更大,容易造成蠕虫，因为每当用户打开页面，查看内容时脚本将自动执行。

该网页有一个发表评论的功能，该评论会写入后台数据库，并且访问主页的时候，会从数据库中加载出所有的评论。

当我添加一个评论，并且暗藏一个脚本，如下图：

![][2]

当别人访问主页的时候，刚刚黑客写入的评论里面的脚本被浏览器当成代码执行了，用户莫名其妙受到攻击：

![][3]

上面就是两种XSS攻击的两种基本类型。当然黑客不会弹出一个框框给你，告诉你被攻击，黑客不会这么傻的~他可以在用户不知情的情况下，盗取用户的cookie，改变网页业务逻辑等等。

### XSS攻击的注入点
 **`1、HTML节点内容`** 
这个其实就是我之前演示的，HTML节点中暗藏攻击脚本。

![][4]

 **`2、HTML属性`** 
这里img的src属性是由用户传递过来的值，当用户把图片地址写成：1"%20onerror="alert(%27哈哈被攻击%27)
大家看下面发生了什么：

![][5]

![][6]

 **`3、JavaScript代码 （字符串提前关闭）`** 
当JavaScript代码中有一个变量是由用户提供的数据，这个数据也有可能之前被写入了数据库。如下图，当用户输入的内容为：
小柚子";alert(%27哈哈你被攻击了！%27);"

![][7]

![][8]

 **`4、富文本`** 
大家都知道，富文本其实就是一段HTML。既然它是一段HTML，那么就存在XSS攻击。而且富文本攻击的防御相对比较麻烦。

### XSS攻击防御
chrome浏览器自带防御,可拦截反射性XSS（HTML内容和属性），js和富文本的无法拦截，所以我们必须得自己做一些防御手段。

 **`1、HTML节点内容的防御`** 

将用户输入的内容进行转义：

```js
var escapeHtml = function(str) {
    str = str.replace(/</g,'<');
    str = str.replace(/</g,'>');
    return str;
}
```

```js
ctx.render('index', {comments, from: escapeHtml(ctx.query.from || '')});
```

![][9]

 **`2、HTML属性的防御`** 

对空格，单引号，双引号进行转义

```js
var escapeHtmlProperty = function (str) {
    if(!str) return '';
    str = str.replace(/"/g,'&quto;');
    str = str.replace(/'/g,'&#39;');
    str = str.replace(/ /g,'&#32;');
    return str;
}
```

```js
ctx.render('index', {posts, comments,
    from:ctx.query.from || '',
    avatarId:escapeHtmlProperty(ctx.query.avatarId || '')});
```

![][10]

 **`3、JavaScript的防御`** 

对引号进行转义

```js
var escapeForJS = function(str){
        if(!str) return '';
        str = str.replace(/\\/g,'\\\\');
        str = str.replace(/"/g,'\\"');
        return str;
}
```

![][11]

 **`4、富文本的防御`** 
富文本的情况非常的复杂，js可以藏在标签里，超链接url里，何种属性里。

```js
<script>alert(1)</script>
<a href="javascript:alert(1)"></a>
<img src="abc" onerror="alert(1)"/>
```

所以我们不能过用上面的方法做简单的转义。因为情况实在太多了。

现在我们换个思路，
提供两种过滤的办法：

1）黑名单  
我们可以把`<script/> onerror` 这种危险标签或者属性纳入黑名单，过滤掉它。但是我们想，这种方式你要考虑很多情况，你也有可能漏掉一些情况等。

2）白名单  
这种方式只允许部分标签和属性。不在这个白名单中的，一律过滤掉它。但是这种方式编码有点麻烦，我们需要去解析html树状结构，然后进行过滤，把过滤后安全的html在输出。
这里提供一个包，帮助我们去解析html树状结构，它使用起来和jquery非常的类似。

```js
npm install cheerio --save
```

```js
var xssFilter = function(html) {
    if(!html) return '';
    var cheerio = require('cheerio');
    var $ = cheerio.load(html);
    //白名单
    var whiteList = {
        'html' : [''],
        'body' : [''],
        'head' : [''],
        'div' : ['class'],
        'img' : ['src'],
        'a' : ['href'],
        'font':['size','color']
    };

    $('*').each(function(index,elem){
        if(!whiteList[elem.name]) {
            $(elem).remove();
            return;
        }
        for(var attr in elem.attribs) {
            if(whiteList[elem.name].indexOf(attr) === -1) {
                $(elem).attr(attr,null);
            }
        }

    });

    return $.html();
}

console.log(xssFilter('<div><font color="red">你好</font><a href="http://www.baidu.com">百度</a><script>alert("哈哈你被攻击了")</script></div>'));

```

大家可以看到：

![][13]

`<script>`不在白名单中，所以被过滤掉了。

 **`5、CSP(Content Security Policy)`** 

内容安全策略（Content Security Policy，简称CSP）是一种以可信白名单作机制，来限制网站中是否可以包含某来源内容。默认配置下不允许执行内联代码（`<script>`块内容，内联事件，内联样式），以及禁止执行eval() , newFunction() , setTimeout([string], ...) 和setInterval([string], ...) 。

 **`示例：`** 

1.只允许本站资源

```
Content-Security-Policy： default-src ‘self’
```

2.允许本站的资源以及任意位置的图片以及 [https://segmentfault.com][16] 下的脚本。

```
Content-Security-Policy： default-src ‘self’; img-src *;
script-src https://segmentfault.com
```

[15]: http://www.baidu.com
[16]: https://segmentfault.com
[0]: ../img/bV31qW.png
[1]: ../img/bV31Px.png
[2]: ../img/bV31PW.png
[3]: ../img/bV31PY.png
[4]: ../img/bV31QU.png
[5]: ../img/bV31RO.png
[6]: ../img/bV31RM.png
[7]: ../img/bV31Vh.png
[8]: ../img/bV31Vd.png
[9]: ../img/bV310b.png
[10]: ../img/bV311b.png
[11]: ../img/bV311y.png
[13]: ../img/bV314w.png