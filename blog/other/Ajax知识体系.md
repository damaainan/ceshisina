# Ajax知识体系 

* [JavaScript][0]

目录

1. [导读][1]
1. [浏览器为ajax做了什么][2]
  1. [MSXML][3]
  1. [全平台兼容的XMLHttpRequest对象][4]
1. [ajax有没有破坏js单线程机制][5]
1. [ajax与setTimeout排队问题][6]
1. [XMLHttpRequest 属性解读][7]
  1. [inherit][8]
  1. [readyState][9]
  1. [onreadystatechange][10]
  1. [status][11]
  1. [statusText][12]
  1. [onloadstart][13]
  1. [onprogress][14]
  1. [onload][15]
  1. [onloadend][16]
  1. [timeout][17]
  1. [ontimeout][18]
  1. [response responseText][19]
  1. [responseXML][20]
  1. [responseType][21]
  1. [responseURL][22]
  1. [withCredentials][23]
  1. [abort][24]
  1. [getResponseHeader][25]
  1. [getAllResponseHeaders][26]
  1. [setRequestHeader][27]
  1. [onerror][28]
  1. [upload][29]
  1. [overrideMimeType][30]
1. [XHR一级][31]
1. [XHR二级][32]
1. [XDomainRequest][33]
1. [$.ajax][34]
  1. [参数列表][35]
  1. [支持promise][36]
  1. [使用转换器][37]
  1. [事件触发顺序][38]
1. [Axios][39]
1. [Fetch][40]
1. [ajax跨域请求][41]
  1. [什么是CORS][42]
  1. [移动端CORS兼容性][43]
  1. [CORS有关的headers][44]
  1. [CORS请求][45]
  1. [HTML启用CORS][46]
  1. [图片启用CORS][47]
1. [ajax文件上传][48]
  1. [js文件上传][49]
  1. [fetch上传][50]
  1. [jquery文件上传][51]
  1. [angular文件上传][52]
1. [ajax请求二进制文件][53]
  1. [FileReader][54]
  1. [ajax请求二进制图片并预览][55]
  1. [ajax请求二进制文本并展示][56]
1. [如何等待多个ajax请求完成][57]
1. [ajax与history的兼容][58]
  1. [pjax][59]
1. [ajax缓存处理][60]
1. [ajax的错误处理][61]
1. [ajax调试技巧][62]
  1. [hosts+nginx+node-webserver][63]
  1. [编码问题][64]
1. [后端接口测试技巧][65]
  1. [使用命令测试OPTIONS请求][66]
  1. [postman][67]
1. [ajax移动端兼容性][68]

### 导读

Ajax 全称 Asynchronous JavaScript and XML, 即异步JS与XML. 它最早在IE5中被使用, 然后由Mozilla, Apple, Google推广开来. 典型的代表应用有 Outlook Web Access, 以及 GMail. 现代网页中几乎无ajax不欢. 前后端分离也正是建立在ajax异步通信的基础之上.

### 浏览器为ajax做了什么

现代浏览器中, 虽然几乎全部支持ajax, 但它们的技术方案却分为两种:

① 标准浏览器通过 XMLHttpRequest 对象实现了ajax的功能. 只需要通过一行语句便可创建一个用于发送ajax请求的对象.

    var xhr = new XMLHttpRequest();

② IE浏览器通过 XMLHttpRequest 或者 ActiveXObject 对象同样实现了ajax的功能.

#### MSXML

鉴于IE系列各种 “神级” 表现, 我们先来看看IE浏览器风骚的走位.

IE下的使用环境略显复杂, IE7及更高版本浏览器可以直接使用BOM的 XMLHttpRequest 对象. MSDN传送门: [Native XMLHTTPRequest object][69]. IE6及更低版本浏览器只能使用 ActiveXObject 对象来创建 XMLHttpRequest 对象实例. 创建时需要指明一个类似”Microsoft.XMLHTTP”这样的ProgID. 而实际呢, windows系统环境下, 以下ProgID都应该可以创建XMLHTTP对象:

```
Microsoft.XMLHTTP

Microsoft.XMLHTTP.1.0

Msxml2.ServerXMLHTTP

Msxml2.ServerXMLHTTP.3.0

Msxml2.ServerXMLHTTP.4.0

Msxml2.ServerXMLHTTP.5.0

Msxml2.ServerXMLHTTP.6.0

Msxml2.XMLHTTP

Msxml2.XMLHTTP.3.0

Msxml2.XMLHTTP.4.0

Msxml2.XMLHTTP.5.0

Msxml2.XMLHTTP.6.0
```
简言之, Microsoft.XMLHTTP 已经非常老了, 主要用于提供对历史遗留版本的支持, 不建议使用.对于 MSXML4, 它已被 MSXML6 替代; 而 MSXML5 又是专门针对office办公场景, 在没有安装 Microsoft Office 2003 及更高版本办公软件的情况下, MSXML5 未必可用. 相比之下, MSXML6 具有比 MSXML3 更稳定, 更高性能, 更安全的优势, 同时它也提供了一些 MSXML3 中没有的功能, 比如说 XSD schema. 唯一遗憾的是, MSXML6 只在 vista 系统及以上才是默认支持的; 而 MSXML3 在 Win2k SP4及以上系统就是可用的. 因此一般情况下, MSXML3 可以作为 MSXML6 的优雅降级方案, 我们通过指定 PorgID 为 Msxml2.XMLHTTP 即可自动映射到 Msxml2.XMLHTTP.3.0. 如下所示:

    var xhr = new ActiveXObject("Msxml2.XMLHTTP");// 即MSXML3,等同于如下语句
    var xhr = new ActiveXObject("MSXML2.XMLHTTP.3.0");

MSDN有篇文章专门讲解了各个版本的MSXML. 传送门: [Using the right version of MSXML in Internet Explorer][70].

亲测了 IE5, IE5.5, IE6, IE7, IE8, IE9, IE10, IE edge等浏览器, IE5及之后的浏览器均可以通过如下语句获取xhr对象:


    var xhr = new ActiveXObject("Msxml2.XMLHTTP");// 即MSXML3
    var xhr = new ActiveXObject("Microsoft.XMLHTTP");// 很老的api,虽然浏览器支持,功能可能不完善,故不建议使用

以上, 思路已经很清晰了, 下面给出个全兼容的方法.

#### 全平台兼容的XMLHttpRequest对象
```
function getXHR(){

  var xhr = null;

  if(window.XMLHttpRequest) {

    xhr = new XMLHttpRequest();

  } else if (window.ActiveXObject) {

    try {

      xhr = new ActiveXObject("Msxml2.XMLHTTP");

    } catch (e) {

      try {

        xhr = new ActiveXObject("Microsoft.XMLHTTP");

      } catch (e) { 

        alert("您的浏览器暂不支持Ajax!");

      }

    }

  }

  return xhr;

}
```
### ajax有没有破坏js单线程机制

对于这个问题, 我们先看下浏览器线程机制. 一般情况下, 浏览器有如下四种线程:

* GUI渲染线程
* javascript引擎线程
* 浏览器事件触发线程
* HTTP请求线程

那么这么多线程, 它们究竟是怎么同js引擎线程交互的呢?

通常, 它们的线程间交互以事件的方式发生, 通过事件回调的方式予以通知. 而事件回调, 又是以先进先出的方式添加到任务队列 的末尾 , 等到js引擎空闲时, 任务队列 中排队的任务将会依次被执行. 这些事件回调包括 setTimeout, setInterval, click, ajax异步请求等回调.

**浏览器中, js引擎线程会循环从 任务队列 中读取事件并且执行, 这种运行机制称作 Event Loop (事件循环).**

对于一个ajax请求, js引擎首先生成 XMLHttpRequest 实例对象, open过后再调用send方法. 至此, 所有的语句都是同步执行. 但从send方法内部开始, 浏览器为将要发生的网络请求创建了新的http请求线程, 这个线程独立于js引擎线程, 于是网络请求异步被发送出去了. 另一方面, js引擎并不会等待 ajax 发起的http请求收到结果, 而是直接顺序往下执行.

当ajax请求被服务器响应并且收到response后, 浏览器事件触发线程捕获到了ajax的回调事件 onreadystatechange (当然也可能触发onload, 或者 onerror等等) . 该回调事件并没有被立即执行, 而是被添加到 任务队列 的末尾. 直到js引擎空闲了, 任务队列 的任务才被捞出来, 按照添加顺序, 挨个执行, 当然也包括刚刚append到队列末尾的 onreadystatechange 事件.

在 onreadystatechange 事件内部, 有可能对dom进行操作. 此时浏览器便会挂起js引擎线程, 转而执行GUI渲染线程, 进行UI重绘(repaint)或者回流(reflow). 当js引擎重新执行时, GUI渲染线程又会被挂起, GUI更新将被保存起来, 等到js引擎空闲时立即被执行.

以上整个ajax请求过程中, 有涉及到浏览器的4种线程. 其中除了 GUI渲染线程 和 js引擎线程 是互斥的. 其他线程相互之间, 都是可以并行执行的. 通过这样的一种方式, ajax并没有破坏js的单线程机制.

### ajax与setTimeout排队问题

通常, ajax 和 setTimeout 的事件回调都被同等的对待, 按照顺序自动的被添加到 任务队列 的末尾, 等待js引擎空闲时执行. 但请注意, 并非xhr的所有回调执行都滞后于setTImeout的回调. 请看如下代码:
```
function ajax(url, method){

  var xhr = getXHR();

  xhr.onreadystatechange = function(){

    console.log('xhr.readyState:' + this.readyState);

  }

  xhr.onloadstart = function(){

    console.log('onloadStart');

  }

  xhr.onload = function(){

    console.log('onload');

  }

  xhr.open(method, url, true);

  xhr.setRequestHeader('Cache-Control',3600);

  xhr.send();

}

var timer = setTimeout(function(){

  console.log('setTimeout');

},0);

ajax('./img/ajax01.png','GET');
```
上述代码执行结果如下图:

[![](./img/ajax27.png "ajax & setTimeout")](./img/ajax27.png "ajax & setTimeout")

由于ajax异步, setTimeout回调本应该最先被执行, 然而实际上, 一次ajax请求, 并非所有的部分都是异步的, 至少”readyState==1”的 onreadystatechange 回调以及 onloadstart 回调就是同步执行的. 因此它们的输出排在最前面.

### XMLHttpRequest 属性解读

首先在Chrome console下创建一个 XMLHttpRequest 实例对象xhr. 如下所示:

[![](./img/ajax01.png "XMLHttpRequest")](./img/ajax01.png "XMLHttpRequest")

运行以下代码.
```
var xhr = new XMLHttpRequest(),

    i=0;

for(var key in xhr){

    if(xhr.hasOwnProperty(key)){

       i++;

   }

}

console.log(i);//0
```
就会发现 XMLHttpRequest 实例对象其实并没有一个自有属性! 这是因为它是BOM对象(Browser Object Model).

#### inherit

追根溯源, 看一个对象, 需要先了解它的继承关系. 虽然 XMLHttpRequest 实例对象是BOM对象, 但它依然具有如下的继承关系. (下面以a << b表示a继承b)

xhr << XMLHttpRequest.prototype << XMLHttpRequestEventTarget.prototype << EventTarget.prototype << Object.prototype由上, xhr也具有Object等原型中的所有方法. 如toString方法.

    xhr.toString();//"[object XMLHttpRequest]"

通常, 一个xhr实例对象拥有10个普通属性+9个方法.

#### readyState

只读属性, readyState属性记录了ajax调用过程中所有可能的状态. 它的取值简单明了, 如下:

readyState 对应常量 描述 
0 (未初始化) xhr.UNSENT 请求已建立, 但未初始化(此时未调用open方法) 
1 (初始化) xhr.OPENED 请求已建立, 但未发送 (已调用open方法, 但未调用send方法) 
2 (发送数据) xhr.HEADERS_RECEIVED 请求已发送 (send方法已调用, 已收到响应头) 
3 (数据传送中) xhr.LOADING 请求处理中, 因响应内容不全, 这时通过responseBody和responseText获取可能会出现错误 
4 (完成) xhr.DONE 数据接收完毕, 此时可以通过通过responseBody和responseText获取完整的响应数据 

注意, readyState 是一个只读属性, 想要改变它的值是不可行的.

#### onreadystatechange

onreadystatechange事件回调方法在readystate状态改变时触发, 在一个收到响应的ajax请求周期中, onreadystatechange 方法会被触发4次. 因此可以在 onreadystatechange 方法中绑定一些事件回调, 比如:
```
xhr.onreadystatechange = function(e){

  if(xhr.readystate==4){

    var s = xhr.status;

    if((s >= 200 && s < 300) || s == 304){

      var resp = xhr.responseText;

      //TODO ...

    }

  }

}
```
注意: onreadystatechange回调中默认会传入Event实例, 如下:

[![](./img/ajax02.png "Event")](./img/ajax02.png "Event")

#### status

只读属性, status表示http请求的状态, 初始值为0. 如果服务器没有显式地指定状态码, 那么status将被设置为默认值, 即200.

#### statusText

只读属性, statusText表示服务器的响应状态信息, 它是一个 UTF-16 的字符串, 请求成功且status==20X时, 返回大写的 OK . 请求失败时返回空字符串. 其他情况下返回相应的状态描述. 比如: 301的 Moved Permanently , 302的 Found , 303的 See Other , 307 的 Temporary Redirect , 400的 Bad Request , 401的 Unauthorized 等等.

#### onloadstart

onloadstart事件回调方法在ajax请求发送之前触发, 触发时机在 readyState==1 状态之后, readyState==2 状态之前.

onloadstart方法中默认将传入一个ProgressEvent事件进度对象. 如下:

[![](./img/ajax03.png "ProgressEvent")](./img/ajax03.png "ProgressEvent")

ProgressEvent对象具有三个重要的Read only属性.

* lengthComputable 表示长度是否可计算, 它是一个布尔值, 初始值为false.
* loaded 表示已加载资源的大小, 如果使用http下载资源, 它仅仅表示已下载内容的大小, 而不包括http headers等. 它是一个无符号长整型, 初始值为0.
* total 表示资源总大小, 如果使用http下载资源, 它仅仅表示内容的总大小, 而不包括http headers等, 它同样是一个无符号长整型, 初始值为0.

#### onprogress

onprogress事件回调方法在 readyState==3 状态时开始触发, 默认传入 ProgressEvent 对象, 可通过 e.loaded/e.total 来计算加载资源的进度, 该方法用于获取资源的下载进度.

注意: 该方法适用于 IE10+ 及其他现代浏览器.
```
xhr.onprogress = function(e){

  console.log('progress:', e.loaded/e.total);

}
```
#### onload

onload事件回调方法在ajax请求成功后触发, 触发时机在 readyState==4 状态之后.

想要捕捉到一个ajax异步请求的成功状态, 并且执行回调, 一般下面的语句就足够了:
```
xhr.onload = function(){

  var s = xhr.status;

  if((s >= 200 && s < 300) || s == 304){

    var resp = xhr.responseText;

    //TODO ...

  }

}
```
#### onloadend

onloadend事件回调方法在ajax请求完成后触发, 触发时机在 readyState==4 状态之后(收到响应时) 或者 readyState==2 状态之后(未收到响应时).

onloadend方法中默认将传入一个ProgressEvent事件进度对象.

#### timeout

timeout属性用于指定ajax的超时时长. 通过它可以灵活地控制ajax请求时间的上限. timeout的值满足如下规则:

* 通常设置为0时不生效.
* 设置为字符串时, 如果字符串中全部为数字, 它会自动将字符串转化为数字, 反之该设置不生效.
* 设置为对象时, 如果该对象能够转化为数字, 那么将设置为转化后的数字.
```
xhr.timeout = 0; //不生效

xhr.timeout = '123'; //生效, 值为123

xhr.timeout = '123s'; //不生效

xhr.timeout = ['123']; //生效, 值为123

xhr.timeout = {a:123}; //不生效
```
#### ontimeout

ontimeout方法在ajax请求超时时触发, 通过它可以在ajax请求超时时做一些后续处理.
```
xhr.ontimeout = function(e) {

  console.error("请求超时!!!")

}
```
#### response responseText

均为只读属性, response表示服务器的响应内容, 相应的, responseText表示服务器响应内容的文本形式.

#### responseXML

只读属性, responseXML表示xml形式的响应数据, 缺省为null, 若数据不是有效的xml, 则会报错.

#### responseType

responseType表示响应的类型, 缺省为空字符串, 可取 "arraybuffer" , "blob" , "document" , "json" , and "text" 共五种类型.

#### responseURL

responseURL返回ajax请求最终的URL, 如果请求中存在重定向, 那么responseURL表示重定向之后的URL.

#### withCredentials

withCredentials是一个布尔值, 默认为false, 表示跨域请求中不发送cookies等信息. 当它设置为true时, cookies , authorization headers 或者TLS客户端证书 都可以正常发送和接收. 显然它的值对同域请求没有影响.

注意: 该属性适用于 IE10+, opera12+及其他现代浏览器.

#### abort

abort方法用于取消ajax请求, 取消后, readyState 状态将被设置为 0 (UNSENT). 如下, 调用abort 方法后, 请求将被取消.

[![](./img/ajax04.png "Event")](./img/ajax04.png "Event")

#### getResponseHeader

getResponseHeader方法用于获取ajax响应头中指定name的值. 如果response headers中存在相同的name, 那么它们的值将自动以字符串的形式连接在一起.

    console.log(xhr.getResponseHeader('Content-Type'));//"text/html"

#### getAllResponseHeaders

getAllResponseHeaders方法用于获取所有安全的ajax响应头, 响应头以字符串形式返回. 每个HTTP报头名称和值用冒号分隔, 如key:value, 并以\r\n结束.

```
xhr.onreadystatechange = function() {

  if(this.readyState == this.HEADERS_RECEIVED) {

    console.log(this.getAllResponseHeaders());

  }

}

//Content-Type: text/html"
```
以上, readyState === 2 状态时, 就意味着响应头已接受完整. 此时便可以打印出完整的 response headers.

#### setRequestHeader

既然可以获取响应头, 那么自然也可以设置请求头, setRequestHeader就是干这个的. 如下:
```

//指定请求的type为json格式

xhr.setRequestHeader("Content-type", "application/json");

//除此之外, 还可以设置其他的请求头

xhr.setRequestHeader('x-requested-with', '123456');
```
#### onerror

onerror方法用于在ajax请求出错后执行. 通常只在网络出现问题时或者ERR_CONNECTION_RESET时触发(如果请求返回的是407状态码, chrome下也会触发onerror).

#### upload

upload属性默认返回一个 XMLHttpRequestUpload 对象, 用于上传资源. 该对象具有如下方法:

* onloadstart
* onprogress
* onabort
* onerror
* onload
* ontimeout
* onloadend

上述方法功能同 xhr 对象中同名方法一致. 其中, onprogress 事件回调方法可用于跟踪资源上传的进度.
```
xhr.upload.onprogress = function(e){

  var percent = 100 * e.loaded / e.total |0;

  console.log('upload: ' + precent + '%');

}
```
#### overrideMimeType

overrideMimeType方法用于强制指定response 的 MIME 类型, 即强制修改response的 Content-Type . 如下, 服务器返回的response的 MIME 类型为 text/plain .

[![](./img/ajax05.png "response headers")](./img/ajax05.png "response headers")

```
xhr.getResponseHeader('Content-Type');//"text/plain"

xhr.responseXML;//null
```
通过overrideMimeType方法将response的MIME类型设置为 text/xml;charset=utf-8 , 如下所示:


    xhr.overrideMimeType("text/xml; charset = utf-8");
    xhr.send();

此时虽然 response headers 如上图, 没有变化, 但 Content-Type 已替换为新值.

    xhr.getResponseHeader('Content-Type');//"text/xml; charset = utf-8"

此时, xhr.responseXML 也将返回DOM对象, 如下图.

[![](./img/ajax06.png "response headers")](./img/ajax06.png "response headers")

### XHR一级

XHR1 即 XMLHttpRequest Level 1. XHR1时, xhr对象具有如下缺点:

* 仅支持文本数据传输, 无法传输二进制数据.
* 传输数据时, 没有进度信息提示, 只能提示是否完成.
* 受浏览器 同源策略 限制, 只能请求同域资源.
* 没有超时机制, 不方便掌控ajax请求节奏.

### XHR二级

XHR2 即 XMLHttpRequest Level 2. XHR2针对XHR1的上述缺点做了如下改进:

* 支持二进制数据, 可以上传文件, 可以使用FormData对象管理表单.
* 提供进度提示, 可通过 xhr.upload.onprogress 事件回调方法获取传输进度.
* 依然受 同源策略 限制, 这个安全机制不会变. XHR2新提供 Access-Control-Allow-Origin 等headers, 设置为 * 时表示允许任何域名请求, 从而实现跨域CORS访问(有关CORS详细介绍请耐心往下读).
* 可以设置timeout 及 ontimeout, 方便设置超时时长和超时后续处理.

这里就H5新增的FormData对象举个例.
```
//可直接创建FormData实例

var data = new FormData();

data.append("name", "louis");

xhr.send(data);

//还可以通过传入表单DOM对象来创建FormData实例

var form = document.getElementById('form');

var data = new FormData(form);

data.append("password", "123456");

xhr.send(data);
```
目前, 主流浏览器基本上都支持XHR2, 除了IE系列需要IE10及更高版本. 因此IE10以下是不支持XHR2的.

那么问题来了, IE7, 8,9的用户怎么办? 很遗憾, 这些用户是比较尴尬的. 对于IE8,9而言, 只有一个阉割版的 XDomainRequest 可用,IE7则没有. 估计IE7用户只能哭晕在厕所了.

### XDomainRequest

XDomainRequest 对象是IE8,9折腾出来的, 用于支持CORS请求非成熟的解决方案. 以至于IE10中直接移除了它, 并重新回到了 XMLHttpRequest 的怀抱.

XDomainRequest 仅可用于发送 GET和 POST 请求. 如下即创建过程.

    var xdr = new XDomainRequest();

xdr具有如下属性:

* timeout
* responseText

如下方法:

* open: 只能接收Method,和url两个参数. 只能发送异步请求.
* send
* abort

如下事件回调:

* onprogress
* ontimeout
* onerror
* onload

除了缺少一些方法外, XDomainRequest 基本上就和 XMLHttpRequest 的使用方式保持一致. 

必须要明确的是:

* XDomainRequest 不支持跨域传输cookie.
* 只能设置请求头的Content-Type字段, 且不能访问响应头信息.

### $.ajax

$.ajax是jquery对原生ajax的一次封装. 通过封装ajax, jquery抹平了不同版本浏览器异步http的差异性, 取而代之的是高度统一的api. jquery作为js类库时代的先驱, 对前端发展有着深远的影响. 了解并熟悉其ajax方法, 不可谓不重要.

#### 参数列表

$.ajax() 只有一个参数, 该参数为key-value设置对象. 实际上, jq发送的所有ajax请求, 都是通过调用该ajax方法实现的. 它的详细参数如下表:

序号 | 参数 | 类型 | 描述 
-|-|-|-
1 | **_accepts_** | _PlainObject_ | 用于通知服务器该请求需要接收何种类型的返回结果. 如有必要, 推荐在 $.ajaxSetup() 方法中设置一次. 
2 | **_async_** | _Boolean_ | 默认为true, 即异步. 
3 | **_beforeSend_** | _Function_ | 请求发送前的回调, 默认传入参数jqXHR和settings. 函数内显式返回false将取消本次请求. 
4 | **_cache_** | _Boolean_ | 请求是否开启缓存, 默认为true, 如不需要缓存请设置为false. 不过, dataType为”script”和”jsonp”时默认为false. 
5 | **_complete_** | _Function_ | 请求完成后的回调(请求success 和 error之后均调用), 默认传入参数jqXHR和textStatus(请求状态, 取值为 “success”,”notmodified”,”error”,”timeout”,”abort”,”parsererror”之一). 从jq1.5开始, complete可以设置为一个包含函数的数组. 如此每个函数将依次被调用. 
6 | **_contents_** | _PlainObject_ | 一个以”{字符串/正则表达式}”配对的对象, 根据给定的内容类型, 解析请求的返回结果. 
7 | **_contentType_** | _String_ | 编码类型, 相对应于http请求头域的”Content-Type”字段. 默认值为”application/x-www-form-urlencoded; charset=UTF-8”. 
8 | **_context_** | _Object_ | 设置ajax回调函数的上下文. 默认上下文为ajax请求传入的参数设置对象. 如设置为document.body, 那么所有ajax回调函数中将以body为上下文. 
9 | **_converters_** | _PlainObject_ | 一个数据类型到数据类型转换器的对象. 默认为 {"* text": window.String, "text html": true, "text json": jQuery.parseJSON, "text xml": jQuery.parseXML} . 如设置converters:{"json jsonp": function(msg){}} 
10 | **_crossDomain_** | _Boolean_ | 默认同域请求为false, 跨域请求为true. 
11 | **_data_** | _Object |, Array_ 发送到服务器的数据, 默认data为键值对格式对象, 若data为数组则按照traditional参数的值, 自动转化为一个同名的多值查询字符串. 如{a:1,b:2}将转换为”&a=1&b=2”. 
12 | **_dataFilter_** | _Function_ | 处理XMLHttpRequest原始响应数据的回调, 默认传入data和type参数, data是Ajax返回的原始数据, type是调用$.ajax时提供的dataType参数 
13 | **_dataType_** | _String_ | 预期服务器返回的数据类型, 可设置为”xml”,”html”,”script”,”json”,”jsonp”,”text”之一, 其中设置为”xml”或”text”类型时, 数据不会经过处理. 
14 | **_error_** | _Function_ | 请求失败时的回调函数, 默认传入jqXHR(jq1.4以前为原生xhr对象),textStatus(请求状态,取值为null,”timeout”,”error”,”abort” 或 “parsererror”),errorString(错误内容), 当一个HTTP错误发生时, errorThrown 接收HTTP状态的文本部分,比如”Not Found”等. 从jq1.5开始, error可以设置为一个包含函数的数组. 如此每个函数将依次被调用.注意: 跨域脚本和JSONP请求时error不被调用. 
15 | **_global_** | _Boolean_ | 表示是否触发全局ajax事件, 默认为true. 设为false将不再触发ajaxStart,ajaxStop,ajaxSend,ajaxError等. 跨站脚本和jsonp请求, 该值自动设置为false. 
16 | **_headers_** | _PlainObject_ | 设置请求头, 格式为k-v键值对对象. 由于该设置会在beforeSend函数被调用之前生效, 因此可在beforeSend函数内覆盖该对象. 
17 | **_ifModified_** | _Boolean_ | 只有上次请求响应改变时, 才允许请求成功. 它使用HTTP包的Last-Modified 头信息判断, 默认为false. 若设置为true, 且数据自从上次请求后没有更改过就会报错. 
18 | **_isLocal_** | _Boolean_ | 运行当前环境设置为”本地”,默认为false, 若设置为true, 将影响请求发送时的协议. 
19 | **_jsonp_** | _String_ | 显式指定jsonp请求中的回调函数的名称. 如jsonp:cb, jq会将cb代替callback, 以 “cb=?”传给服务器. 从jq1.5开始, 若设置jsonp:false, 那么需要明确设置jsonpCallback:”callbackName”. 
20 | **_jsonpCallback_** | _String |,Function_ 为jsonp请求指定一个回调函数名, 以取代jq自动生成的随机函数名. 从jq1.5开始, 可以将该属性设置为一个函数, 函数的返回值就是jsonpCallback的结果. 
21 | **_mimeType_** | _String_ | 设置一个MIME类型, 以覆盖xhr的MIM类型(jq1.5新增) 
22 | **_password_** | _String_ | 设置认证请求中的密码 
23 | **_processData_** | _Boolean_ | jq的ajax方法默认会将传入的data隐式转换为查询字符串(如”&a=1&b=2”), 以配合 默认内容类型 “application/x-www-form-urlencoded”, 如果不希望转换请设置为false. angular中想要禁用默认转换, 需要重写transformRequest方法. 
24 | **_scriptCharset_** | _String_ | 仅在”script”请求中使用(如跨域jsonp, dataType为”script”类型). 显式指定时, 请求中将在script标签上设置charset属性, 可在发现本地和远程编码不一致时使用. 
25 | **_statusCode_** | _PlainObject_ | 一组http状态码和回调函数对应的键值对对象. 该对象以 {404:function(){}} 这种形式表示. 可用于根据不同的http状态码, 执行不同的回调.(jq1.5新增) 
26 | **_timeout_** | _Number_ | 设置超时时间. 
27 | **_traditional_** | _Boolean_ | 是否按照默认方式序列化data对象, 默认值为false. 
28 | **_type_** | _String_ | 可以设置为8种http method之一, jq中不区分大小写. 
29 | **_url_** | _String_ | 请求的uri地址. 
30 | **_username_** | _String_ | 设置认证请求中的用户名 
31 | **_xhr_** | _Function_ | 在回调内创建并返回xhr对象 
32 | **_xhrFields_** | _PlainObject_ | 键值对对象, 用于设置原生的xhr对象, 如可用来设置withCredentials:true(jq1.5.1新增) 

#### 支持promise

$.ajax() 方法返回jqXHR对象(jq1.5起), 如果使用的不是XMLHttpRequest对象时, 如jsonp请求, 返回的jqXHR对象将尽可能模拟原生的xhr. 从jq1.5起, 返回的jqXHR对象实现了promise接口, 具有如下新方法.

新方法 被替代的老方法(jq1.8起弃用) done(function(data, textStatus, jqXHR) {}) success fail(function(jqXHR, textStatus, errorThrown) {}) error always(function(data or jqXHR, textStatus, jqXHR or errorThrown) {}) complete

从jq1.6开始, done, fail, always按照FIFO队列可以分配多个回调.

#### 使用转换器

$.ajax() 的转换器可以将支持的数据类型映射到其它数据类型. 如果需要将自定义数据类型映射到已知的类型. 需要使用 contents 选项在响应的 “Content-Type” 和实际数据类型之间添加一个转换函数.
```
$.ajaxSetup({

  contents: {

    myContentType: /myContentType/

  },

  converters: {

    "myContentType json": function(data) {

      //TODO something

      return newData;

    }

  }

});
```
转换一个支持的类型为自定义类型, 然后再返回. 如 text—>myContentType—>json.
```
$.ajaxSetup({

  contents: {

    myContentType: /myContentType/

  },

  converters: {

    "text myContentType": true,

    "myContentType json": function(data) {

      //TODO something

      return newData;

    }

  }

});
```
#### 事件触发顺序

$.ajax()方法触发的事件纷繁复杂, 有将近20个之多. 为了囊括最多的事件, 这里以一次成功的上传请求为例, 以下是它们的调用顺序(请求出现错误时的顺序, 请自行对应).

序号 | 事件名称 | 是否全局事件 | 是否能关闭 | 默认形参 
-|-|-|-|-
1 | $.ajaxPrefilter | ✔️ | ❌ | function(options, originalOptions, jqXHR){} 
2 | $(document).ajaxStar | ✔️ | ✔️ | function(){}(只在当前无激活ajax时触发) 
3 | beforeSend | ❌ | - | function(jqXHR, settings){} 
4 | $(document).ajaxSend | ✔️ | ✔️ | function(){} 
5 | xhr.onloadstart | - | - | ProgressEvent 
6 | xhr.upload.onloadstart | - | - | ProgressEvent 
7 | xhr.upload.onprogress | - | - | ProgressEvent 
8 | xhr.upload.onload | - | - | ProgressEvent 
9 | xhr.upload.onloadend | - | - | ProgressEvent 
10 | xhr.onprogress | - | - | ProgressEvent 
11 | xhr.onload | - | - | ProgressEvent 
12 | success(弃用) | ❌ | - | function(data, textStatus, jqXHR){} 
13 | $(document).ajaxSuccess | ✔️ | ✔️ | function(event, jqXHR, options){} 
14 | complete(弃用) | ❌ | - | function(jqXHR, textStatus){} 
15 | $(document).ajaxComplete | ✔️ | ✔️ | function(event, jqXHR, textStatus) 
16 | $(document).ajaxStop | ✔️ | ✔️ | function(){} 
17 | xhr.onloadend | - | - | ProgressEvent 

从jq1.8起, 对于函数 ajaxStart, ajaxSend, ajaxSuccess, ajaxComplete, ajaxStop , 只能为document对象绑定事件处理函数, 为其他元素绑定的事件处理函数不会起作用.

### Axios

实际上, 如果你仅仅只是想要一个不错的http库, 相比于庞大臃肿的jquery, 短小精悍的Axios可能更加适合你. 原因如下:

* Axios支持node, jquery并不支持.
* Axios基于promise语法, jq3.0才开始全面支持.
* Axios短小精悍, 更加适合http场景, jquery大而全, 加载较慢.
* vue作者尤大放弃推荐vue-resource, 转向推荐Axios. 以下为尤大原话.

> “最近团队讨论了一下, Ajax 本身跟 Vue 并没有什么需要特别整合的地方, 使用 fetch polyfill 或是 axios、superagent 等等都可以起到同等的效果, vue-resource 提供的价值和其维护成本相比并不划算, 所以决定在不久以后取消对 vue-resource 的官方推荐.”

Axios大小仅12k, 目前最新版本号为: 

[![](https://camo.githubusercontent.com/9f600e10007ac86da6a8b90c16ca1e9504901730/68747470733a2f2f696d672e736869656c64732e696f2f6e706d2f762f6178696f732e7376673f7374796c653d666c61742d737175617265 "npm version")](https://camo.githubusercontent.com/9f600e10007ac86da6a8b90c16ca1e9504901730/68747470733a2f2f696d672e736869656c64732e696f2f6e706d2f762f6178696f732e7376673f7374796c653d666c61742d737175617265 "npm version")

语法上Axios基本就和promise一样, 在then方法中处理回调, 在catch方法中处理异常. 如下:
```
axios.get("https://api.github.com/users/louiszhai")

  .then(function(response){

    console.log(response);

  })

  .catch(function (error) {

    console.log(error);

  });
```
除了get, 它还支持post, delete, head, put, patch, request请求. 具体使用攻略, 请戳这里: [axios][71] .

如需在网页上引入 Axios, 可以链接CDN [axios | Bootstrap中文网开源项目免费 CDN 服务][72] 或者将其下载到本地.

### Fetch

说到ajax, 就不得不提及fetch, 由于篇幅较长, fetch已从本文中独立出来, 请戳 [Fetch进阶指南][73] .

### ajax跨域请求

#### 什么是CORS

CORS是一个W3C(World Wide Web)标准, 全称是跨域资源共享(Cross-origin resource sharing).它允许浏览器向跨域服务器, 发出异步http请求, 从而克服了ajax受同源策略的限制. 实际上, 浏览器不会拦截不合法的跨域请求, 而是拦截了他们的响应, 因此即使请求不合法, 很多时候, 服务器依然收到了请求.(Chrome和Firefox下https网站不允许发送http异步请求除外)

通常, 一次跨域访问拥有如下流程:

[![](./img/cross-domain02.jpg)](./img/cross-domain02.jpg "")

#### 移动端CORS兼容性

当前几乎所有的桌面浏览器(Internet Explorer 8+, Firefox 3.5+, Safari 4+和 Chrome 3+)都可通过名为跨域资源共享的协议支持ajax跨域调用.

那么移动端兼容性又如何呢? 请看下图:

[![](./img/ajax25.png "cors-mobile")](./img/ajax25.png "cors-mobile")

可见, CORS的技术在IOS Safari7.1及Android webview2.3中就早已支持, 即使低版本下webview的canvas在使用跨域的video或图片时会有问题, 也丝毫不影响CORS的在移动端的使用. 至此, 我们就可以放心大胆的去应用CORS了.

#### CORS有关的headers

1) HTTP Response Header(服务器提供):

* Access-Control-Allow-Origin: 指定允许哪些源的网页发送请求.
* Access-Control-Allow-Credentials: 指定是否允许cookie发送.
* Access-Control-Allow-Methods: 指定允许哪些请求方法.
* Access-Control-Allow-Headers: 指定允许哪些常规的头域字段, 比如说 Content-Type.
* Access-Control-Expose-Headers: 指定允许哪些额外的头域字段, 比如说 X-Custom-Header.

该字段可省略. CORS请求时, xhr.getResponseHeader() 方法默认只能获取6个基本字段: Cache-Control、Content-Language、Content-Type、Expires、Last-Modified、Pragma . 如果需要获取其他字段, 就需要在Access-Control-Expose-Headers 中指定. 如上, 这样xhr.getResponseHeader(‘X-Custom-Header’) 才能返回X-Custom-Header字段的值.(该部分摘自阮一峰老师博客)
* Access-Control-Max-Age: 指定preflight OPTIONS请求的有效期, 单位为秒.

2) HTTP Request Header(浏览器OPTIONS请求默认自带):

* Access-Control-Request-Method: 告知服务器,浏览器将发送哪种请求, 比如说POST.
* Access-Control-Request-Headers: 告知服务器, 浏览器将包含哪些额外的头域字段.

3) 以下所有的header name 是被拒绝的:

* Accept-Charset
* Accept-Encoding
* Access-Control-Request-Headers
* Access-Control-Request-Method
* Connection
* Content-Length
* Cookie
* Cookie2
* Date
* DNT
* Expect
* Host
* Keep-Alive
* Origin
* Referer
* TE
* Trailer
* Transfer-Encoding
* Upgrade
* Via
* 包含以Proxy- 或 Sec- 开头的header name

#### CORS请求

CORS请求分为两种, ① 简单请求; ② 非简单请求.

满足如下两个条件便是简单请求, 反之则为非简单请求.(CORS请求部分摘自阮一峰老师博客)

1) 请求是以下三种之一:

* HEAD
* GET
* POST

2) http头域不超出以下几种字段:

* Accept
* Accept-Language
* Content-Language
* Last-Event-ID
* Content-Type字段限三个值 application/x-www-form-urlencoded、multipart/form-data、text/plain

对于简单请求, 浏览器将发送一次http请求, 同时在Request头域中增加 Origin 字段, 用来标示请求发起的源, 服务器根据这个源采取不同的响应策略. 若服务器认为该请求合法, 那么需要往返回的 HTTP Response 中添加 Access-Control-* 等字段.( Access-Control-* 相关字段解析请阅读我之前写的[CORS 跨域访问][74] )

对于非简单请求, 比如Method为POST且Content-Type值为 application/json 的请求或者Method为 PUT 或 DELETE 的请求, 浏览器将发送两次http请求. 第一次为preflight预检(Method: OPTIONS),主要验证来源是否合法. 值得注意的是:OPTION请求响应头同样需要包含 Access-Control-* 字段等. 第二次才是真正的HTTP请求. 所以服务器必须处理OPTIONS应答(通常需要返回20X的状态码, 否则xhr.onerror事件将被触发).

以上请求流程图为:

[![](./img/cross-domain01.jpg)](./img/cross-domain01.jpg "")

#### HTML启用CORS

http-equiv 相当于http的响应头, 它回应给浏览器一些有用的信息,以帮助正确和精确地显示网页内容. 如下html将允许任意域名下的网页跨域访问.

    <meta http-equiv="Access-Control-Allow-Origin" content="*">

#### 图片启用CORS

通常, 图片允许跨域访问, 也可以在canvas中使用跨域的图片, 但这样做会污染画布, 一旦画布受污染, 将无法读取其数据. 比如无法调用 toBlob(), toDataURL() 或 getImageData()方法. 浏览器的这种安全机制规避了未经许可的远程服务器图片被滥用的风险.(该部分内容摘自 [启用了 CORS 的图片 - HTML（超文本标记语言） | MDN][75])

因此如需在canvas中使用跨域的图片资源, 请参考如下apache配置片段(来自[HTML5 Boilerplate Apache server configs][76]).
```
<IfModule mod_setenvif.c>

    <IfModule mod_headers.c>

        <FilesMatch "\.(cur|gif|ico|jpe?g|png|svgz?|webp)$">

            SetEnvIf Origin ":" IS_CORS

            Header set Access-Control-Allow-Origin "*" env=IS_CORS

        </FilesMatch>

    </IfModule>

</IfModule>
```
### ajax文件上传

ajax实现文件上传非常简单, 这里我选取原生js, jq, angular 分别来比较下, 并顺便聊聊使用它们时的注意事项.(ajax文件上传的代码已上传至github, 请戳这里预览效果: [ajax 文件上传 demo | louis][77])

1) 为了上传文件, 我们得先选中一个文件. 一个type为file的input框就够了.

    <input id="input" type="file">

2) 然后用FormData对象包裹📦选中的文件.


    var input = document.getElementById("input"),
    formData = new FormData();
    formData.append("file",input.files[0]);//key可以随意定义,只要后台能理解就行

3) 定义上传的URL, 以及方法. github上我搭建了一个 [node-webserver][78], 根据需要可以自行克隆下来npm start后便可调试本篇代码.

    var url = "http://localhost:10108/test",
    method = "POST";

#### js文件上传

4.1) 封装一个用于发送ajax请求的方法.
```
function ajax(url, method, data){

  var xhr = null;

  if(window.XMLHttpRequest) {

    xhr = new XMLHttpRequest();

  } else if (window.ActiveXObject) {

    try {

      xhr = new ActiveXObject("Msxml2.XMLHTTP");

    } catch (e) {

      try {

        xhr = new ActiveXObject("Microsoft.XMLHTTP");

      } catch (e) { 

        alert("您的浏览器暂不支持Ajax!");

      }

    }

  }

  xhr.onerror = function(e){

    console.log(e);

  }

  xhr.open(method, url);

  try{

    setTimeout(function(){

      xhr.send(data);

    });

  }catch(e){

    console.log('error:',e);

  }

  return xhr;

}
```
4.2) 上传文件并绑定事件.
```
var xhr = ajax(url, method, formData);

xhr.upload.onprogress = function(e){

  console.log("upload progress:", e.loaded/e.total*100 + "%");

};

xhr.upload.onload = function(){

  console.log("upload onload.");

};

xhr.onload = function(){

  console.log("onload.");

}
```
上传结果如下所示:

[![](./img/ajax17.png "js file upload")](./img/ajax17.png "js file upload")

#### fetch上传

5) fetch只要发送一个post请求, 并且body属性设置为formData即可. 遗憾的是, fetch无法跟踪上传的进度信息.
```
fetch(url, {

  method: method,

  body: formData

  }).then(function(res){

  console.log(res);

  }).catch(function(e){

  console.log(e);

});
```
#### jquery文件上传

jq提供了各式各样的上传插件, 其原理都是利用jq自身的ajax方法.

6) jq的ajax提供了xhr属性用于自定义各种事件.
```
$.ajax({

  type: method,

  url: url,

  data: formData,

  processData : false,

  contentType : false ,//必须false才会自动加上正确的Content-Type

  xhr: function(){

    var xhr = $.ajaxSettings.xhr();//实际上就是return new window.XMLHttpRequest()对象

    if(xhr.upload) {

      xhr.upload.addEventListener("progress", function(e){

        console.log("jq upload progress:", e.loaded/e.total*100 + "%");

      }, false);

      xhr.upload.addEventListener("load", function(){

        console.log("jq upload onload.");

      });

      xhr.addEventListener("load", function(){

        console.log("jq onload.");

      });

      return xhr;

    }

  }

});
```
jq上传结果如下所示:

[![](./img/ajax18.png "jq file upload")](./img/ajax18.png "jq file upload")

有关jq ajax更多的api, 请参考中文文档 [jQuery.ajax() | jQuery API 中文文档][79] .

#### angular文件上传

7.1) angular提供了$http方法用于发送http请求, 该方法返回一个promise对象.
```
$http({

  method: method,

  url: url,

  data: formData,

}).success(function(res) {

  console.log(res);

}).error(function(err, status) {

  console.log(err);

});
```
angular文件上传的代码已上传至github, 请戳这里预览效果: [angular 文件上传 demo | louis][80].

低版本angular中文件上传的功能并不完整, 直到angular1.5.5才在$http中加入了eventHandler和uploadEventHandlers等方法, 使得它支持上传进度信息. 如下:
```
$http({

  method: method,

  url: url,

  eventHandlers: {

    progress: function(c) {//下载进度

      console.log('Progress -> ' + c);

    }

  },

  uploadEventHandlers: {

    progress: function(e) {//上传进度

      console.log('UploadProgress -> ' + e);

    }

  },

  data: formData,

}).success(function(res) {

  console.log(res);

}).error(function(err, status) {

  console.log(err);

});
```
angular1.5.5以下低版本中, 请参考成熟的实现方案 [angular-file-upload][81] 以及它提供的demo [Simple example][82] .

### ajax请求二进制文件

#### FileReader

处理二进制文件主要使用的是H5的FileReader.

PC支持性如下:

IE Edge Firefox Chrome Safari Opera 10 12 3.6 6 6 11.5 

Mobile支持性如下:

IOS Safari Opera Mini Android Browser Chrome/Android UC/Android 7.1 - 4 53 11 

以下是其API:

属性/方法名称 描述 **_error_** 表示读取文件期间发生的错误. **_readyState_** 表示读取文件的状态.默认有三个值:0表示文件还没有加载;1表示文件正在读取;2表示文件读取完成. **_result_** 读取的文件内容. **_abort()_** 取消文件读取操作, 此时readyState属性将置为2. **_readAsArrayBuffer()_** 读取文件(或blob对象)为类型化数组([ArrayBuffer][83]), 类型化数组允许开发者以数组下标的方式, 直接操作内存, 由于数据以二进制形式传递, 效率非常高. _readAsBinaryString()_ 读取文件(或blob对象)为二进制字符串, 该方法已移出标准api, 请谨慎使用. **_readAsDataURL()_** 读取文件(或blob对象)为base64编码的URL字符串, 与window.URL.createObjectURL方法效果类似. **_readAsText()_** 读取文件(或blob对象)为文本字符串. **_onload()_** 文件读取完成时的事件回调, 默认传入event事件对象. 该回调内, 可通过this.result 或 event.target.result获取读取的文件内容. 

#### ajax请求二进制图片并预览
```
var xhr = new XMLHttpRequest(),

    url = "./img/ajax01.png";

xhr.open("GET", url);

xhr.responseType = "blob";

xhr.onload = function(){

  if(this.status == 200){

    var blob = this.response;

    var img = document.createElement("img");

    //方案一

    img.src = window.URL.createObjectURL(blob);//这里blob依然占据着内存

    img.onload = function() {

      window.URL.revokeObjectURL(img.src);//释放内存

    };

    //方案二

    /*var reader = new FileReader();

    reader.readAsDataURL(blob);//FileReader将返回base64编码的data-uri对象

    reader.onload = function(){

      img.src = this.result;

    }*/

    //方案三

    //img.src = url;//最简单方法

    document.body.appendChild(img);

  }

}

xhr.send();
```
#### ajax请求二进制文本并展示
```
var xhr = new XMLHttpRequest();

xhr.open("GET","http://localhost:8080/Information/download.jsp?data=node-fetch.js");

xhr.responseType = "blob";

xhr.onload = function(){

  if(this.status == 200){

    var blob = this.response;

    var reader = new FileReader();

    reader.readAsBinaryString(blob);//该方法已被移出标准api,建议使用reader.readAsText(blob);

    reader.onload=function(){

      document.body.innerHTML = "<div>" + this.result + "</div>";

    }

  }

}

xhr.send();
```
有关二进制文件的读取, 请移步这篇博客 [HTML5新特性之文件和二进制数据的操作][84] .

### 如何等待多个ajax请求完成

原生js可以使用ES6新增的Promise. ES6的Promise基于 [Promises/A+][85] 规范(该部分 [Fetch入门指南][86] 一文也有提及).

这里先提供一个解析responses的函数.
``` 

function todo(responses){

  responses.forEach(function(response){

    response.json().then(function(res){

      console.log(res);

    });

  });

}
```
原生js使用 Promise.all 方法. 如下:
```
var p1 = fetch("http://localhost:10108/test1"),

    p2 = fetch("http://localhost:10108/test2");

Promise.all([p1, p2]).then(function(responses){

  todo(responses);

  //TODO do somethings

});

//"test1"

//"test2"
```
jquery可以使用$.when方法. 该方法接受一个或多个Deferred对象作为参数, 只有全部成功才调用resolved状态的回调函数, 但只要其中有一个失败，就调用rejected状态的回调函数. 其实, jq的Deferred是基于 Promises/A规范实现, 但并非完全遵循. (传送门: [jQuery 中的 Deferred 和 Promises (2)][87] ).
```
var p1 = $.ajax("http://localhost:10108/test1"),

    p2 = $.ajax("http://localhost:10108/test2");

$.when(p1, p2).then(function(res1, res2){

  console.log(res1);//["test1", "success", Object]

  console.log(res2);//["test2", "success", Object]

  //TODO do somethings

});
```
如上, $.when默认返回一个jqXHR对象, 可以直接进行链式调用. then方法的回调中默认传入相应的请求结果, 每个请求结果的都是数组, 数组中依次是responseText, 请求状态, 请求的jqXHR对象.

angular中可以借助 $q.all() 来实现. 别忘了, $q 需要在controller中注入. 此外, $q 相关讲解可参考 [AngularJS: ng.$q][88] 或 [Angular $q service学习笔记][89] .
```
var p1 = fetch("http://localhost:10108/test1"),

    p2 = fetch("http://localhost:10108/test2");

$q.all([p1, p2]).then(function(responses){

  todo(responses);

  //TODO do somethings

});

//"test1"

//"test2"

$q.all() 实际上就是对 Promise.all 的封装.
```
### ajax与history的兼容

ajax的一大痛点就是无法支持浏览器前进和后退操作. 因此早期的Gmail 采用 iframe, 来模拟ajax的前进和后退.

如今, H5普及, pjax大行其道. pajax 就是 ajax+history.pushState 组合的一种技术. 使用它便可以无刷新通过浏览器前进和后退来改变页面内容.

先看下兼容性.

IE Edge Firefox Chrome Safari Opera iOS Safari Android Browser Chrome for Android pushState/replaceState 10 12 4 5 6 11.5 7.1 4.3 53 history.state 10 4 18 6 11.5 

可见IE8,9并不能使用 H5的history. 需要使用垫片 [HTML5 History API expansion for browsers not supporting pushState, replaceState][90] .

#### pjax

pjax简单易用, 仅需要如下三个api:

* history.pushState(obj, title, url) 表示往页面history末尾新增一个历史项(history entry), 此时history.length会+1.
* history.replaceState(obj, title, url) 表示替换当前历史项为新的历史项. 此时history.length保持不变.
* window.onpopstate 仅在浏览器前进和后退时触发(history.go(1), history.back() 及location.href=”xxx” 均会触发), 此时可在history.state中拿到刚刚塞进去的state, 即obj对象(其他数据类型亦可).

我们注意到, 首次进入一个页面, 此时 history.length 值为1, history.state 为空. 如下:

[![](./img/ajax19.png "history.state")](./img/ajax19.png "history.state")

1) 为了在onpopstate事件回调中每次都能拿到 history.state , 此时需要在页面载入完成后, 自动替换下当前url.

    history.replaceState("init", title, "xxx.html?state=0");

2) 每次发送ajax请求时, 在请求完成后, 调用如下, 从而实现浏览器history往前进.

    

    history.pushState("ajax请求相关参数", title, "xxx.html?state=标识符");

3) 浏览器前进和后退时, popstate 事件会自动触发, 此时我们手动取出 history.state 
```
window.addEventListener("popstate", function(e) {

    var currentState = history.state;

    //TODO 拼接ajax请求参数并重新发送ajax请求, 从而回到历史页面

    //TODO 或者从state中拿到关键值直接还原历史页面

});
```
popstate 事件触发时, 默认会传入 PopStateEvent 事件对象. 该对象具有如下属性.

[![](./img/ajax20.png "PopStateEvent")](./img/ajax20.png "PopStateEvent")

如有不懂, 更详细讲解请移步 : [ajax与HTML5 history pushState/replaceState实例 « 张鑫旭-鑫空间-鑫生活][91] .

### ajax缓存处理

js中的http缓存没有开关, 受制于浏览器http缓存策略. 原生xhr请求中, 可通过如下设置关闭缓存.
```
xhr.setRequestHeader("If-Modified-Since","0");

xhr.setRequestHeader("Cache-Control","no-cache");

//或者 URL 参数后加上  "?timestamp=" + new Date().getTime()
```
jquery的http缓存是否开启可通过在settings中指定cache.
```
$.ajax({

  url : 'url',

  dataType : "xml",

  cache: true,//true表示缓存开启, false表示缓存不开启

  success : function(xml, status){    

  }

});
```
同时jquery还可以全局设置是否缓存. 如下将全局关闭ajax缓存.

    
    $.ajaxSetup({cache:false});

除此之外, 调试过程中出现的浏览器缓存尤为可恶. 建议开启隐私浏览器或者勾选☑️控制台的 Disable cache 选项. (这里以Chrome举例, 其他浏览器类似)

[![](./img/ajax21.png "PopStateEvent")](./img/ajax21.png "PopStateEvent")

### ajax的错误处理

前面已经提过, 通常只要是ajax请求收到了http状态码, 便不会进入到错误捕获里.(Chrome中407响应头除外)

实际上, $.ajax 方法略有区别, jquery的ajax方法还会在类型解析出错时触发error回调. 最常见的便是: dataType设置为json, 但是返回的data并非json格式, 此时 $.ajax 的error回调便会触发.

### ajax调试技巧

有关调试, 如果接口只是做小部分修改. 那么可以使用charles(Mac) 或者fiddler(Windows), 做代理, 将请求的资源替换为本地文件, 或者使用其断点功能, 直接编辑response.

如果是新增接口的调试, 可以本地搭建node服务. 利用hosts文件配置dns + nginx将http请求转发到本地node服务器. 简易的node调试服务器可参考我的 [node-webserver][78] . 如下举一个栗子🌰:

#### hosts+nginx+node-webserver

假设我们要调试的是 www.test.com 的GET接口. 以下所有步骤以Mac为例, 其他系统, 请自行搜索🔍文件路径.

1) hosts配置.

    

    sudo vim /etc/hosts
    #新增一行 127.0.0.1 www.test.com

2) nginx 配置
```
brew install nginx #安装

#安装成功后进入目标目录

cd /usr/local/etc/nginx/

cd servers #默认配置入口为nginx.conf.同时servers目录下*.conf文件已自动加入到配置文件列表中

vim test.conf

#粘贴如下内容

server {

  listen       80;

  server_name  www.test.com;

  index index.html;

  error_page   500 502 503 504  /50x.html;

  location = /50x.html {

    root   html;

  }

  location / {

    proxy_pass http://localhost:10108/;

    proxy_redirect off;

    proxy_set_header Host $host;

    proxy_set_header        X-Read-IP       $remote_addr;

    proxy_set_header        X-Forwarded-For $proxy_add_x_forwarded_for;

  }

}

#:wq保存并退出

#启动nginx

sudo nginx -s reload #如果启动了只需重启即可

sudo nginx #如果没有启动,便启动之
```
3) node-webServer 配置

参考 [node-webserver][78] . 启动服务前只需更改index.js, 在第9行后插入如下内容:
```
'get': {

  '/': {

    getKey : 'Welcome to Simple Node  WebServer!'

  },

  '接口api': '你的response内容'//插入的代码                               

},
```
如需在nginx中配置CORS, 请看这里: [Nginx通过CORS实现跨域][92].

#### 编码问题

XMLHttpRequest 返回的数据默认的字符编码是utf-8, post方法提交数据默认的字符编码也是utf-8. 若页面编码为gbk等中文编码, 那么就会产生乱码.

### 后端接口测试技巧

通常, 如果后端接口开发OK了, 前端同学需要通过一些手段来确认接口是能正常访问的.

#### 使用命令测试OPTIONS请求
```
curl -I -X OPTIONS -H "Origin: http://example.com" http://localhost:10108/

# response

HTTP/1.1 200 OK

X-Powered-By: Express

Content-Type: text/json;charset=UTF-8

Access-Control-Allow-Credentials: true

Access-Control-Allow-Headers: x-requested-with,Content-Type

Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS

Access-Control-Allow-Origin: http://example.com

Access-Control-Max-Age: 3600

Server: Node WebServer

Website: https://github.com/Louiszhai/node-webserver

Date: Fri, 21 Oct 2016 09:00:40 GMT

Connection: keep-alive

Transfer-Encoding: chunked
```
以上, http状态码为200, 表示允许OPTIONS请求.

GET, POST 请求与GET类似, 其他请求亦然.
```
curl -I -X GET -H "Origin: http://example.com" http://localhost:10108/

#HTTP/1.1 200 OK

curl -I -X POST -H "Origin: http://example.com" http://localhost:10108/test

#HTTP/1.1 200 OK
```
#### postman

除此之外, 我们还可以通过chrome的postman扩展进行测试. 请看postman素洁的界面:

[![](./img/ajax26.png)](./img/ajax26.png "")

postman支持所有类型的http请求, 由于其向chrome申请了cookie访问权限及所有http(s)网站的访问权限. 因此可以放心使用它进行各种网站api的测试.

同时, 强烈建议阅读本文的你升级postman的使用技巧, 这里有篇: [基于Postman的API自动化测试][93] , 拿走不谢.

### ajax移动端兼容性

移动端的支持性比较弱, 使用需谨慎. 看表.

IOS Safari Opera Mini Android Browser Android Chrome Android UC XMLHttpRequest 8.4 - 4.4.4 53 11(part) fetch - - 52 53 - 

本篇为ajax而生, 通篇介绍 XMLHTTPRequest 相关的知识, 力求简明, 本欲为梳理知识, 为读者答疑解惑, 但因本人理解所限, 难免有所局限, 希望正在阅读的你取其精华去其糟粕. 谢谢.

- - -

本文就讨论这么多内容,大家有什么问题或好的想法欢迎在下方参与[留言和评论][94].

本文作者: [louis][95]

本文链接: [http://louiszhai.github.io/2016/11/02/ajax/][96]

参考文章

* [XMLHttpRequest Standard][97]
* [XMLHttpRequest Level 2 使用指南 - 阮一峰的网络日志][98]
* [你真的会使用XMLHttpRequest吗？ - WEB前端路上踩过的坑儿 - SegmentFault][99]
* [ajax与HTML5 history pushState/replaceState实例 « 张鑫旭-鑫空间-鑫生活][91]
* [跨域资源共享 CORS 详解 - 阮一峰的网络日志][100]
* [jQuery.ajax() | jQuery API 中文文档 -- jQuery 中文网][79]

[0]: /tags/JavaScript/
[1]: #导读
[2]: #浏览器为ajax做了什么
[3]: #MSXML
[4]: #全平台兼容的XMLHttpRequest对象
[5]: #ajax有没有破坏js单线程机制
[6]: #ajax与setTimeout排队问题
[7]: #XMLHttpRequest-属性解读
[8]: #inherit
[9]: #readyState
[10]: #onreadystatechange
[11]: #status
[12]: #statusText
[13]: #onloadstart
[14]: #onprogress
[15]: #onload
[16]: #onloadend
[17]: #timeout
[18]: #ontimeout
[19]: #response-responseText
[20]: #responseXML
[21]: #responseType
[22]: #responseURL
[23]: #withCredentials
[24]: #abort
[25]: #getResponseHeader
[26]: #getAllResponseHeaders
[27]: #setRequestHeader
[28]: #onerror
[29]: #upload
[30]: #overrideMimeType
[31]: #XHR一级
[32]: #XHR二级
[33]: #XDomainRequest
[34]: #ajax
[35]: #参数列表
[36]: #支持promise
[37]: #使用转换器
[38]: #事件触发顺序
[39]: #Axios
[40]: #Fetch
[41]: #ajax跨域请求
[42]: #什么是CORS
[43]: #移动端CORS兼容性
[44]: #CORS有关的headers
[45]: #CORS请求
[46]: #HTML启用CORS
[47]: #图片启用CORS
[48]: #ajax文件上传
[49]: #js文件上传
[50]: #fetch上传
[51]: #jquery文件上传
[52]: #angular文件上传
[53]: #ajax请求二进制文件
[54]: #FileReader
[55]: #ajax请求二进制图片并预览
[56]: #ajax请求二进制文本并展示
[57]: #如何等待多个ajax请求完成
[58]: #ajax与history的兼容
[59]: #pjax
[60]: #ajax缓存处理
[61]: #ajax的错误处理
[62]: #ajax调试技巧
[63]: #hosts-nginx-node-webserver
[64]: #编码问题
[65]: #后端接口测试技巧
[66]: #使用命令测试OPTIONS请求
[67]: #postman
[68]: #ajax移动端兼容性
[69]: https://blogs.msdn.microsoft.com/ie/2006/01/23/native-xmlhttprequest-object/
[70]: https://blogs.msdn.microsoft.com/xmlteam/2006/10/23/using-the-right-version-of-msxml-in-internet-explorer/
[71]: http://www.bootcdn.cn/axios/readme/
[72]: http://www.bootcdn.cn/axios/
[73]: http://louiszhai.github.io/2016/11/02/fetch/
[74]: http://louiszhai.github.io/2016/01/11/cross-domain/#CORS__u8DE8_u57DF_u8BBF_u95EE]
[75]: https://developer.mozilla.org/zh-CN/docs/Web/HTML/CORS_enabled_image
[76]: https://github.com/h5bp/server-configs-apache/blob/fc379c45f52a09dd41279dbf4e60ae281110a5b0/src/.htaccess#L36-L53
[77]: http://louiszhai.github.io/res/ajaxUpload.html
[78]: https://github.com/Louiszhai/node-webserver
[79]: http://www.jquery123.com/jQuery.ajax/
[80]: http://louiszhai.github.io/res/angularUpload.html
[81]: https://github.com/nervgh/angular-file-upload
[82]: http://nervgh.github.io/pages/angular-file-upload/examples/simple/
[83]: http://blog.csdn.net/lichwei1983/article/details/43893025
[84]: http://www.cnblogs.com/jscode/archive/2013/04/27/3572239.html
[85]: https://promisesaplus.com/
[86]: http://louiszhai.github.io/2016/10/19/fetch/
[87]: http://www.css88.com/archives/4750/comment-page-1
[88]: https://code.angularjs.org/1.2.6/docs/api/ng.$q
[89]: https://segmentfault.com/a/1190000000402555
[90]: https://github.com/devote/HTML5-History-API
[91]: http://www.zhangxinxu.com/wordpress/2013/06/html5-history-api-pushstate-replacestate-ajax/
[92]: http://mp.weixin.qq.com/s?__biz=MzI3MTI2NzkxMA==&mid=2247484408&idx=1&sn=5c64dd43ff2060e1c4a22d93e4e887c9&scene=1&srcid=0901vPdwJR0crm8vJmjboYzI#rd
[93]: https://segmentfault.com/a/1190000005055899
[94]: #respond
[95]: https://github.com/Louiszhai
[96]: http://louiszhai.github.io/2016/11/02/ajax/
[97]: https://xhr.spec.whatwg.org/
[98]: http://www.ruanyifeng.com/blog/2012/09/xmlhttprequest_level_2.html
[99]: https://segmentfault.com/a/1190000004322487
[100]: http://www.ruanyifeng.com/blog/2016/04/cors.html