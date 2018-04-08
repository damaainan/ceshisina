## Postman教程——授权

来源：[https://www.jellythink.com/archives/169](https://www.jellythink.com/archives/169)

时间 2018-03-19 00:20:41

 
## 前言
 
授权过程就是验证我们是否有权限从服务器访问所需的数据。发送请求时，通常必须包含参数以确保请求有权访问并返回所需的数据。Postman提供的授权类型可以让我们轻松处理Postman进行接口测试中的身份验证协议。
 
在Postman中，提供了以下的几种授权：
 

* Inherit auth from parent 
* No Auth 
* Bearer Token 
* Basic auth 
* Digest Auth 
* OAuth 1.0 
* OAuth 2.0 
* Hawk Authentication 
* AWS Signature 
* NTLM Authentication [Beta] 
 

如下图所示：
 
![][0]
 
Postman不会保存授权请求头数据和请求参数，以防止公开的敏感数据暴露，如API密钥。
 
如果想检查Postman生成的授权请求头和参数，可以单击预览请求按钮。如下图所示：
 
![][1]
 
接下来就对每一种授权方式进行详细的说明。
 
## Inherit auth from parent
 
假设现在将一个文件夹添加到集合中。在授权选项卡下，默认授权类型就被设置为“从父继承授权”。如下图所示：
 
![][2]
 
“从父继承授权”设置表示默认情况下此文件夹中的每个请求都使用来自父级的授权类型。在这个例子中，集合使用“No Auth”，因此该文件夹使用“No Auth”，表示该文件夹中的所有请求都将使用“No Auth”。
 
如果我们想要将父集合授权类型设置为“No Auth”，而该集合下的文件夹授权类型需要设置成与集合不一样，该怎么办？我们需要编辑文件夹的详细信息，从TYPE下拉列表中选择“Basic Auth”，然后输入对应的凭证。此后，此文件夹中的每个请求都依赖于“Basic Auth”，而父集合中的其余请求仍不使用任何授权。
 
## No Auth
 
默认情况下，在下拉菜单列表中“No Auth”是第一个显示的。如果接口不需要任何授权，则请使用“No Auth”。
 
## Bearer Token
 
“Bearer Token”是一个安全令牌。任何用户都可以使用它来访问数据资源，而无需使用加密密钥。下面来说说如何在Postman中如何使用“Bearer Token”：
 

* 在授权标签中，从TYPE下拉菜单中选择“Bearer Token”； 
* 根据提示设置请求的授权参数，输入令牌的值； 
* 点击发送按钮。 
 

如下图所示：
 
![][3]
 
## Basic auth
 
基本身份验证是一种比较简单的授权类型，需要经过验证的用户名和密码才能访问数据资源。这就需要我们输入用户名和对应的密码。
 
具体操作如下图所示：
 
![][4]
 
由于“Basic auth”使用明文传递，目前基本很少使用了。
 
## Digest Auth
 
在“Digest Auth”流程中，客户端向服务器发送请求，服务器返回客户端的nonce和realm值；客户端对用户名、密码、nonce值、HTTP请求方法、被请求资源URI等组合后进行MD5运算，把计算得到的摘要信息发送给服务端。服务器然后发回客户端请求的数据。
 
通过哈希算法对通信双方身份的认证十分常见，它的好处就是不必把具备密码的信息对外传输，只需将这些密码信息加入一个对方给定的随机值计算哈希值，最后将哈希值传给对方，对方就可以认证你的身份。Digest思想同样采如此，用了一种nonce随机数字符串，双方约好对哪些信息进行哈希运算即可完成双方身份的验证。Digest模式避免了密码在网络上明文传输，提高了安全性，但它仍然存在缺点，例如认证报文被攻击者拦截到攻击者可以获取到资源。
 
默认情况下，Postman从响应中提取值对应的值。如果不想提取这些值，有以下两种选择：
 

* 在所选字段的高级部分中输入您自己的值 
* 勾选“Yes,disable retrying the request”复选框。 
 

在Postman中使用“Digest Auth”如下图所示：
 
![][4]
 
关于“Digest Auth”更多的内容，大家可以参见这篇文章《 [Http Digest 认证][9] 》。
 
## OAuth 1.0
 
OAuth 1.0是一种可以让我们在不公开密码的情况下授权使用其他应用程序的授权模式。
 
在Postman中按照以下步骤使用OAuth 1.0授权：
 

* 在`Authorization`下来授权标签中选择“OAuth 1.0”授权模式；  
* 在“Add authorization data to” 下拉选择框中，选择对应的请求模式。 
 

当选择“Request Body/Request URL”时，Postman将检查请求方法是POST还是PUT，以及请求主体类型是否是`x-www-form-urlencoded`；如果是这样，Postman将增加授权参数到请求主体。对于所有其他情况，它会向URL添加授权参数。
 
实际使用时，需要按照下图所示填写对应的参数：
 
![][6]
 
## OAuth 2.0
 
OAuth 2.0作为OAuth 1.0的升级版本。在Postman中按照以下步骤进行使用：
 

* 在`Authorization`下来授权标签中选择“OAuth 2.0”授权模式；  
* 在“Add authorization data to”下拉选择框中，选择对应的请求模式； 
* 设置请求的授权参数，有以下三个选择： 
 

* 点击“Get New Access Token”按钮，在弹出的对话框中输入对应的参数；单击“Request Token”按钮获取对应的Token。接下来有了对应的Token后，就可以点击“Send”按钮发送请求了； 
* 在“Access Token”输入框中输入一个Token，或者Token对应的环境变量，然后就可以点击“Send”按钮发送请求了； 
* 在“Available Tokens”下拉框中选择已经存在的Token，然后发送请求。 
   

 
 

如下图所示：
 
![][7]
 
说到这里，读者就不得不再去进一步了解一下OAuth，这里有一篇文章值得阅读：《 [OAuth的改变][10] 》
 
## Hawk Authentication
 
hawk是一个HTTP认证方案，使用MAC(Message Authentication Code，消息认证码算法)算法，它提供了对请求进行部分加密验证的认证HTTP请求的方法，包括HTTP方法、请求URI和主机。
 
hawk方案要求提供一个共享对称密匙在服务器与客户端之间，通常这个共享的凭证在初始TLS保护阶段建立的，或者是从客户端和服务器都可用的其他一些共享机密信息中获得的。在Postman中具体使用如下图所示：
 
![][8]
 
## 总结
 
这篇文章对Postman中的认证进行了点到为止的总结。主要是还是以会用为宗旨。希望我的这篇文章对大家有帮助。
 
果冻想-一个原创技术文章分享网站。
 
2018年2月22日 于包头。
 


[9]: http://blog.csdn.net/u013238950/article/details/51392992
[10]: https://huoding.com/2011/11/08/126
[0]: ./img/FfUzyaJ.png 
[1]: ./img/byIfuuB.png 
[2]: ./img/RJBRB3U.png 
[3]: ./img/Avyyee.png 
[4]: ./img/zAraUfR.png 
[5]: ./img/zAraUfR.png 
[6]: ./img/7RFVN3N.png 
[7]: ./img/VN7fYfu.png 
[8]: ./img/AZrUnqM.png 