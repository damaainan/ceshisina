## 利用cpolar-内网穿透工具，将内网服务器暴露给公网访问

来源：[http://www.jianshu.com/p/4f23a100ca6b](http://www.jianshu.com/p/4f23a100ca6b)

时间 2018-11-09 18:46:26

 
### 适合场景：

```
作为一名Web开发人员，你可能会遇到以下这种场景：
```

 
* 你在办公室的刚写完一个内部测试Web站点，这时对于新版本站点的功能，你希望展示给某人看一下，这个人也许是你的客户，或是你的老板，或是产品经理、测试人员等。 
* 如果你跟要看网站的人在同一个内网下，还很方便，如果恰巧这个人不在公司（客户不可能天天跟你碰面），或者你们不在同一个局域网，你就没办法展示给他看了。这时候，只好上传到公网服务器部署，或者截图给人家看效果了。如果又遇到修改反馈意见的话，又要反复以上步骤，可能5-6遍之多。这很低效。 注意：开发人员的时间是很宝贵，尽量不要浪费在一些低端的杂事上。 
* 这时候，就可以介绍今天的主角了，cpolar，它可以帮你解决这个问题。 
* cpolar是Web开发调试的利器工具，它可以把内网的站点变成公网可以访问的站点。而不论你在何处何地，用户在何处何地，非常的方便。 
 
 
接下来看，我们就以实战操作一下，看如何将内部测试站点，暴露给公网用户访问（无需要要公网服务器部署）。
 
### 前期准备

 
* cpolar注册并下载客户端（必需） 
* 准备一个Web测试网站程序包（可选） 
 它用来模拟在本机跑着的测试Web站点，如果你已经了现成的自己的站点，可以忽略这步骤。  
 
 
### 注册cpolar帐号

 
* 去[www.cpolar.com][10] 官网注册一个帐号

![][0]
  
* 点击左上角注册按钮，填写注册信息

![][1]
  
* 注册成功后，会自动登录到用户后台界面

![][2]
 
4.下载cpolar客户端
 
上图有下载链接，根据自己的本机操作系统，下载指定的客户端，后台界面里列出了9种不同平台的客户端可供下载，包括WINDOWS、Linux、MAC、还有ARM平台（这说明树莓派也有机会，^^）。

 
* 下载客户端到本地后，解压缩，解压后得到一个cpolar的命令行客户端。
在Linux或OSX上，您可以使用以下命令从终端解压缩cpolar。 在Windows上，只需双击cpolar.zip即可。

```
$ unzip /path/to/cpolar.zip
```

 
* 配置客户端token认证串

![][3]
复制你后台的认证串命令，然后在本机的命令行窗口执行。（注意authtoken串的完整）

```
$ ./cpolar authtoken <自己的authtoken字符串>
```
 
它执行完后，并没有真正访问服务器端认证，而是保存了authtoken串到默认创建的配置文件中。
 
默认配置文件路径:在你当前用户目录下.cpolar\cpolar.yml，以后你可以增加配置项，让你更加方便的调试，现在不用管。

 
* 运行cpolar客户端，模拟连接本机的8080端口。 
 

```
$ ./cpolar http 8080
```
 
这时候我们还没有启动内网Web站点，所以8080端口上没有任何东西，执行它的目的是测试一下cpolar客户端连接服务器认证是否正常。连接后，可以看到命令行的连接状态，如果是online状态，则为正常。如果是其它状态，例如：reconnect，则可能是认证串填写不对，请重新检查执行第7步。
 
上图可以看到Tunnel status显示online，就是正常连接，cpolar服务器会分配一个随机域名。可以http访问，也可以https访问。

![][4]
 
正常连通后，按CTRL+C结束客户端。
 
### 下载Web示例站点

 
* 测试Web站点，是一个TODO List待办清单示例站点，它的最终的效果图：

![][5]
 
* 根据你的操作系统平台，下载相应的示例Web站点程序： 
 
 
  
[WINDOWS 64位][11]
[WINDOWS 32位][12] 
 
 
  
[苹果MAC 64位][13]
[苹果MAC 32位][14] 
 
 
[Linux 64位][15] （Debian、CentOS、Ubuntu）
 
[Linux 32位][16] （Debian、CentOS、Ubuntu）
 
  
[FreeBSD 64位][17]
[FreeBSD 32位][18] 
 
 
[Linux ARM 32位][19] (树莓派)
 
  
[嵌入式 MIPS][20]
[嵌入式 MIPSLE][21] 
 
 
这个Web测试站点程序就是一个文件，在命令行下的单一程序，简单，直接运行就可以。

 
* 下载示例站点后，本地解压缩，然后在命令行中执行。 
 

```
$ ./cpolar-todo-mvc
```
 
运行后，它会默认侦听在本地8080端口，如果您的8080端口已被其它程序占用，可以使用命令行参加-httpAddr，修改启动端口，命令如下：

```
$ ./cpolar-todo-mvc -httpAddr=:8082
```
 
运行成功后，会有如下提示。

![][6]
 
* 打开浏览器，输入网址：[http://localhost:8080][22] ，打开测试站点

![][7]
 
如上图所示，说明本地内网测试站点，已经搭建成功！接下来，我们利用cpolar，将这个内部站点，发布到公网。
 
## 连接cpolar客户端到内网测试站点

 
* 在命令行输入 
 

```
$ ./cpolar 8080
```

![][8]
 
* [https://542d821a.cpolar.io][23]复制命令行窗口中的cpolar分配的域名链接到浏览器，本示例中的是[https://542d821a.cpolar.io][23] ，看看发生了什么？

![][9]
3. 内部的站点，已经被发布到公网，可以被访问到了。但是作为程序员，好像有种错觉，像是DNS映射在本机一样，这是真的么？赶紧发送这个链接给你远在天边的朋友或客户，一起来验证一下。看看能不能访问得到吧。让他们告诉你，你的新内测网站做得有多棒！^ ^

 
### 总结
 
今天我们利用cpolar将自己的本机测试站点公布到了公网上，而没有使用公网服务器部署。以后可以经常发布站点内测版给给客户了。
 
其实cpolar还有更多玩法。
 
例如：
 
1. 微信公众号对接调试，不需要再部署程序，这对于程序员来说，非常方便。
 
2. 远程家里的树莓派。
 
3. 私有云盘公网访问


[10]: https://www.cpolar.com
[11]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-windows-amd64.zip
[12]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-windows-amd64.zip
[13]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-darwin-amd64.zip
[14]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-darwin-386.zip
[15]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-linux-amd64.zip
[16]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-linux-386.zip
[17]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-freebsd-amd64.zip
[18]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-freebsd-386.zip
[19]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-linux-arm.zip
[20]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-linux-mips.zip
[21]: https://www.cpolar.com/static/downloads/todo/cpolar-todo-mvc-stable-linux-mipsle.zip
[22]: http://localhost:8080
[23]: https://542d821a.cpolar.io
[24]: https://542d821a.cpolar.io
[0]: https://img0.tuicool.com/qqYVVzA.png
[1]: https://img0.tuicool.com/3uYramY.png
[2]: https://img2.tuicool.com/6VnEFvv.png
[3]: https://img0.tuicool.com/E7bUbun.png
[4]: https://img0.tuicool.com/2UvMzmz.png
[5]: https://img2.tuicool.com/byemeyE.png
[6]: https://img0.tuicool.com/J3mUJza.png
[7]: https://img0.tuicool.com/Y3QRzu7.png
[8]: https://img1.tuicool.com/EBJfAfj.png
[9]: https://img2.tuicool.com/U36JjyZ.png