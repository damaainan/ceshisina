## 使用PostMan进行自动化测试

来源：[https://segmentfault.com/a/1190000014144322](https://segmentfault.com/a/1190000014144322)

最近在进行一个老项目的升级，第一步是先将node版本从`4.x`升级到`8.x`，担心升级会出现问题，所以需要将服务的接口进行验证；
如果手动输入各种URL，人肉check，一个两个还行，整个服务。。大几十个接口，未免太浪费时间了-.-；
因为是一个纯接口服务的项目，所以打算针对对应的API进行一波自动化测试；
所以就开始寻找对应的工具，突然发现，平时使用的`PostMan`貌似也是支持写测试用例的-.-，所以就照着文档怼了一波；
一下午的时间，很是激动，之前使用`PostMan`仅限于修改`Header`，添加`Body`发送请求，从来没有考虑过拿`PostMan`来进行测试，一下午的使用，感觉发现了新大陆。
## PostMan的安装

貌似下载和使用`PostMan`必须要翻墙-.-
因为现在提供两种形态的App：


* `chrome`的插件 （已经快要被废弃了，推荐使用独立App） 
* 独立的App


而且在使用时需要登录账号，我这边是直接登录的`Google`账号-。-貌似有其它方式，但是我并没有去尝试。

独立App版云盘地址（`Mac`版本，今天刚下载的6.0.10，需要的请自取）：
链接:[https://pan.baidu.com/s/18CDp...][14]  密码:`mrpf`下载完毕解压后直接运行即可，然后就是注册账号之类的，目测账号这一块主要是用于后续的小组分享需要（可以直接将你的调用记录分享给其他人）。
## 发送一个请求

这是`PostMan`最基础的一个用法，用来发送一个请求。
可以设置`Header`，`Body`等信息。


![][0]
## Collections

我们可以将每次发送的请求进行保存，方便下次请求该接口时，直接调用即可，
如果保存请求的话，会被保存到一个`Collections`里去，类似一个集合。
`PostMan`提供了方法，能够一键运行整个`Collections`中所有的请求。


![][1] 


![][2]

然后我们就可以在需要的时候，直接运行集合中所有的请求了。


![][3]

保存请求记录的时候，在下边选择对应的`Collection`即可


![][4]
## 开始API测试
### 测试脚本位置

![][5] 
`PostMan`针对请求编写的测试脚本，在这个位置，采用的是`JavaScript`语法，右侧是一些预先配置的代码片段。
以及我们可以在`Pre-request Script`中编写脚本，用于在发送请求前执行。
### 一些简单的语法
`PostMan`也提供了一种断言，来帮助做一些验证。

```js
tests['Status code is 200'] = responseCode.code === 200

tests['Data length >= 10'] = JSON.parse(responseBody).data.length >= 10
```

赋值为`true`即表示通过，`false`为失败。
`tests`的直接赋值作用比较局限，如果在脚本中进行一些其他异步操作，则需要用到`pm.test`了。

```js
setTimeout(() => {
  pm.test("test check", function () {
    pm.expect(false).to.be.true
  })
})
```

只用上边的`tests`赋值+`pm.test/pm.expect`已经能够满足我们的需求了，其余的一些只是在这之上的语法糖而已。
[各种语法示例][15]
### 在测试脚本中发送请求

我们可以在拿到一个`API`返回结果后，根据该结果发送一些新的请求，然后添加断言。

```js
let responseJSON = JSON.parse(responseBody)

// 获取关注的第一个用户，并请求他的用户信息
pm.sendRequest(responseJSON[0].url, function (err, response) {
  let responseJSON = response.json()

  pm.test('has email', function () {
    pm.expect(responseJSON.email).is.be.true // 如果用户email不存在，断言则会失败
  })
});
```

如果我们有一些动态接口要进行测试，可以尝试这种写法。
一级接口返回`List`
二级接口根据`List`的`ID`进行获取对应信息。
### 如何处理大量重复的断言逻辑

针对单个API，去编写对应的断言脚本，这个是没有什么问题的。
但是如果是针对一个项目的所有`API`去编写，类似于判断`statusCode`这样的断言就会显得很冗余，所以`PostMan`也考虑到了这点。
在我们创建的`Collection`以及下层的文件夹中，我们可以直接编写针对这个目录下的所有请求的断言脚本。


![][6] 


![][7] 
这里的脚本会作用于目录下所有的请求。
这样我们就可以将一些通用性的断言挪到这里了，在每个请求的`Tests`下编写针对性的断言脚本。
### 变量的使用
`PostMan`提供了两种变量使用，一个是`global`，一个是`environment`。
#### global

代码操作的方式：

```js
pm.globals.set("variable_key", "variable_value") // set variable
pm.globals.get("variable_key") // get variable
pm.globals.unset("variable_key") // remove variable
```

通过GUI设置：


![][8] 


![][9]

设置完后我们就可以这样使用了：


![][10]

基本上在所有的可输入的地方，我们都能够使用这些变量。
#### environment

环境变量，这个是权重比`global`要高一些的变量，是针对某些环境来进行设置的值。
操作方式类似。

在使用代码操作的方式时，只需将`globals`替换为`environment`即可。
在发起一个请求，或者一键发送所有请求时，我们可以勾选对应的环境，来使用不同的变量。


![][11]

在针对大量API测试时，拿`environment`来设置一个`domain`将是一个不错的选择。
这样在请求中我们只需这样写即可：

```js
{{domain}}/res1
{{domain}}/res2

domain: https://api.github.com
```
### 一个简单的示例：

通过直接运行一个`Collection`，我们可以很直观的看到所有的接口验证情况。


![][12] 


![][13]
## 参考资料

[https://www.getpostman.com/do...][16]

之前使用`PostMan`，最多就是模拟一下`POST`请求，最近刚好碰到类似的需求，发现原来`PostMan`还可以做的更多。
这篇只是使用`PostMan`进行API测试的最基础操作，还有一些功能目前我并没有用到，例如集成测试、生成`API`文档之类的。

接口相当于是获取和操作服务资源的方式，肯定属于产品的核心。
所以测试是必须的，在交付QA同学之前，自己进行一遍测试，想必一定能节省一部分的时间。

[14]: https://pan.baidu.com/s/18CDp2MUQCLgk_USlmVc-Gw
[15]: https://www.getpostman.com/docs/v6/postman/scripts/test_examples
[16]: https://www.getpostman.com/docs/v6/
[0]: ./img/bV7vJu.png
[1]: ./img/bV7vJx.png
[2]: ./img/bV7vJC.png
[3]: ./img/bV7vJD.png
[4]: ./img/bV7vJF.png
[5]: ./img/bV7vJG.png
[6]: ./img/bV7vJV.png
[7]: ./img/bV7vJ6.png
[8]: ./img/bV7vJ8.png
[9]: ./img/bV7vJ9.png
[10]: ./img/bV7vKa.png
[11]: ./img/bV7vKc.png
[12]: ./img/bV7vKd.png
[13]: ./img/bV7vKh.png