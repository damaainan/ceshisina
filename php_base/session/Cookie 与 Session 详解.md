## Cookie 与 Session 详（hu）解（che）【拿去面试用】

来源：[https://segmentfault.com/a/1190000014166553](https://segmentfault.com/a/1190000014166553)


## 1、Cookie


* Cookie 是浏览器访问服务器后，服务器传给浏览器的一段数据
* 该数据以一个称为“Set-Cookie”的 HTTP 报头格式从 Web 服务器发出。 浏览器以称为“Cookie”的 HTTP 报头格式将 Cookie 送回服务器
* Cookie 在浏览器端是以文件形式保存的
* 此后每次浏览器访问该服务器，都 **`必须`** 带上这段数据
* 包含多个字段 '过期时间'、'路径'、'域名'
* Cookie 是在客户端保持状态的方案，补充 HTTP 无状态协议的特点


## 2、Session


* Session 是在服务端保持状态的方案
* 用户打开浏览器访问网站，服务端生成的sessionID，传递到浏览器的 Cookie 保存 ，在每次请求时都会自动带上 sessionID ，然后服务器端根据 sessionID 找到对应的 Session 值


## 3、HTTP协议

上文有提到 Cookie 与 Session 都产生于用来解决 HTTP 协议的无状态，无连接的特点，那么到底什么可以被称作“无状态”呢？
于是我们可以简单整理一下通信的过程（见下图），位于应用层以下的TCP/IP协议对数据的层层封装，使得我们从一个客户端到服务端（或者可以说另一个客户端）的数据交换看起来是端到端的，隐藏一层又一层的数据封装和校验。
那么，“无状态”在这个模型中到底体现在什么地方呢？粗略的可以理解为【C端是一个求爱者，S端是一个人见人爱的女神】，
我从C端给S端发了一句“你喜欢我吗”，
S端回复了一句“喜欢呀”，
C端再问“你喜欢我什么”
S端却回了一句“我什么时候喜欢你了”
......
其实我意在说明，“无状态”即为无感情，无上下文，是一次匿名的交互，写这个前看了许多文章中，把“无状态”归为HTTP协议的_缺陷_，但是我觉得更是一种_特点_,从WIKI百科的描述中可以见到这句话

High-traffic websites often benefit from web cache servers that deliver content on behalf of upstream servers to improve response time
可以将其理解为，HTTP协议为“高速通信”带来了好处，也是历史选择了这种机制。但是随着发展，我们需要不能再匿名的去交流，我们需要知道对方是谁，我们期待下面的对话场景：
C端：“你喜欢我吗”
S端：“喜欢你啊”
C端：“喜欢我什么”
S端：“所有”
......
emmmmmm...这可能是我们想要的效果，我们需要知道对方是谁，知道上下文是什么，需要维护对方是谁的这个_状态_。于是 Cookie 和 Session 就出现了，用于在C端和S端来分别维护“我是谁”的状态。
如果想听更好的故事，下面的可能更加的奏效

常去的一家咖啡店有喝5杯咖啡免费赠一杯咖啡的优惠，然而一次性消费5杯咖啡的机会微乎其微，这时就需要某种方式来纪录某位顾客的消费数量。想象一下其实也无外乎下面的几种方案： 
1、该店的店员很厉害，能记住每位顾客的消费数量，只要顾客一走进咖啡店，店员就知道该怎么对待了。这种做法就是协议本身支持状态。 
2、发给顾客一张卡片，上面记录着消费的数量，一般还有个有效期限。每次消费时，如果顾客出示这张卡片，则此次消费就会与以前或以后的消费相联系起来。这种做法就是在客户端保持状态。 
3、发给顾客一张会员卡，除了卡号之外什么信息也不纪录，每次消费时，如果顾客出示该卡片，则店员在店里的纪录本上找到这个卡号对应的纪录添加一些消费信息。这种做法就是在服务器端保持状态。
![][0]
## 4、Cookie 与 Session 的关系

先引用一句WIKI 上在介绍Session时候对Cookie的解释吧

Client-side sessions use cookies and cryptographic techniques to maintain state without storing as much data on the server. When presenting a dynamic web page, the server sends the current state data to the client (web browser) in the form of a cookie. The client saves the cookie in memory or on disk. With each successive request, the client sends the cookie back to the server, and the server uses the data to "remember" the state of the application for that specific client and generate an appropriate response.
我就翻译第一句话吧. 客户端session使用cookie和加密技术来保持状态
可以很容易的发现，他们的作用大致一样，存储位置不同，
下面再用一段PHP程序来解释吧

![][1]

后端代码：

```php
<?php
session_start();
setcookie('name','tao') ;
setcookie('gender','male') ;
var_dump(session_id());
var_dump($_COOKIE);
```

第一次访问；
$_COOKIE并没有值

![][2]

第二次访问：
$_COOKIE有值了


![][3]

所以可以很明显的看出，`setcookie()`对于 `$_COOKIE` 的影响并不是立即生效的.这是因为`setcookie()`是用来给客户端发送一个HTTP Cookie 的值， 但是 `$_COOKIE` 则是来获取客户端传递的 Cookie 值。所以两者的作用域不一样。因此才会出现这种情况。
## 5、写在最后

关于 Session 和 Cookie 的理解就到此为止，我觉得应该设计到更多知识，包括但不限于浏览器的运行机制，Cookie 实现机制， Session 持久化，php.ini 对session的影响etc.

如果有不对的地方，记得来交互意见 :)

[0]: ../img/1460000014166556.png
[1]: ../img/1460000014166557.png
[2]: ../img/1460000014170447.png
[3]: ../img/1460000014170448.png


