# [使用Postman管理API接口][0]

* [postman][1]
* [api][2]

[**Corwien**][3] 1月20日发布 



> 为了使服务端开发的API接口管理正规化流程化，所以，使用Postman这款神器来管理我们的接口，以方便后续项目的迭代开发。

## 一、什么是Postman

[Postman][12]最基础的功能就是发送http请求，支持GET/PUT/POST/DELETE，还有很多其他的http方法。我的理解就是可以通过这一款神器，达到模拟浏览器提交各种API请求，比如我们最常用的是和APP端的协作开发，服务端开发好API接口后，APP端同事需要调用我们的接口，往往我们服务端管理许多接口，如果需要对某一个接口进行测试，都是在服务端对相关接口的方法进行调试，如果没有问题，则直接给APP端，这样虽然操作起来比较简单，但是不太效，有时我们也用curl模拟进行请求，看接口是否正常，但是这样也比较慢，不直观，如果牵扯到权限验证access_token，则又会比较麻烦,所以，这里强烈推荐使用Postman这款比较强大的模拟请求工具来进行接口管理。

## 二、使用

我们使用[laravel-china.org][13]提供的[PHPHub开源项目][14]的API来对Postman进行练手，所以，在这里要感谢他们的开源共享精神！

### 1.具体步骤

#### 1.下载 [Postman][12]#### 2.导入 [接口信息文件][15] 

![][16]

#### 3.导入 [环境设置文件][17]点击设置标识，然后进入管理环境

![][18]

将该链接的json文件下载到本地，然后导入：

![][19]

我们可以看一下我们导入的环境：

![][20]

导入的这些参数，在待会请求时会是环境中全局的参数，这样就可以避免每次请求时需要给接口添加特定参数的麻烦了，如url为接口域名，在使用时我们只需要这样封装https://{{url}}/users/me即可，还有如果请求用户的时候，需要用户权限的验证，如我们在拿到授权后，可以将该Token添加到环境变量中，这时如果后边需要，即可通过变量拿到，所以非常方便。

### 2. 示例

让我们切换到PHPHub API Staging环境下，然后通过POST 提交{{url}}/oauth/access_token获取客户端的access_token 

![][21]

我们需要从上面的请求结果中获取到用户Token，并将这个值保存到环境变量中，以供后续使用。将下面这段代码添加到测试编辑器中：

    var data = JSON.parse(responseBody);
    
    if (data.access_token) {
      tests["Body has access_token"] = true;
      postman.setEnvironmentVariable("access_token", data.access_token);
    }
    else {
      tests["Body has access_token"] = false;
    }

![][22]

最后获取登陆的Token，即为password_token也加入到环境变量中

![][23]

该phpHub开放的API接口参数说明：

![][24]

如果为一般APP端请求，则需要传递client_token，才能获取一般的信息，如文章信息，回复信息等，如果需要发表文章，则需要用户登陆授权，既拿到password_token才可以进行需要权限的操作，因为APP端没有session，cookie，所以，只能通过各种Token来从服务端获取授权进行相关的操作。  
参考博文：

[PHPHub Staging API 已开放][13]  
[基于Postman的API自动化测试][25]

[0]: /a/1190000008181875
[1]: /t/postman/blogs
[2]: /t/api/blogs
[3]: /u/corwien
[12]: https://www.getpostman.com/
[13]: https://laravel-china.org/topics/3097
[14]: https://github.com/summerblue/phphub5
[15]: https://raw.githubusercontent.com/summerblue/phphub5/master/resources/docs/api/PHPHub-server.postman_collection.json
[16]: ../img/bVIuEE.png
[17]: https://raw.githubusercontent.com/summerblue/phphub5/master/resources/docs/api/PHPHub-Staging.postman_environment.json
[18]: ../img/bVIuGE.png
[19]: ../img/bVIuFC.png
[20]: ../img/bVIuHw.png
[21]: ../img/bVIuKQ.png
[22]: ../img/bVIuMK.png
[23]: ../img/bVIuMe.png
[24]: ../img/bVIuOF.png
[25]: https://segmentfault.com/a/1190000005055899