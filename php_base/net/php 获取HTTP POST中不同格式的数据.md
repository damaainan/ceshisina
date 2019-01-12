## php 获取HTTP POST中不同格式的数据

来源：[http://www.jianshu.com/p/a8731a813078](http://www.jianshu.com/p/a8731a813078)

时间 2018-12-27 13:48:11

 
HTTP协议中的POST 方法有多中格式的数据协议,在HTTP的head中用不同的`Content-type`标识.常用的有
 `application/x-www-form-urlencoded`,这是最常见的,就是from表单的格式.在HTTP的head中是`Content-Type: application/x-www-form-urlencoded`.
 `multipart/form-data`,这个是用来上传文件的,在HTTP的head中是`Content-Type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW``Raw`这个不是特别常用,传输的数据在HTTP的body中只有一段,不是以键值对的形式存放.在HTTP的head中是`Content-Type: application/json`,`Content-Type: text`,`Content-Type: application/xml`,`Content-Type: text/xml`,等等形式
 
对于`Content-Type: application/x-www-form-urlencoded`这种form表单的数据,在php中,使用`$_POST['name']`可以直接获取, 没有什么特别的
 `Content-Type: multipart/form-data;`这种格式的数据,在php中使用`$_POST['name']`可以获取字符数据,使用`$_FILES['file']`可以获取.
 
对于`Raw`这种格式的数据,使用以上两种办法没有办法获取到,需要使用别的手段.
 
1.使用`file_get_contents("php://input")`获取;写一个简单php文件测试一下

```php
<?php
$test=file_get_contents("php://input");
echo $test;
```
 
用postman测试一下

![][0]

 
没问题,可以接收到
 
2.使用`$GLOBALS['HTTP_RAW_POST_DATA']`接收

```php
<?php
$test=$GLOBALS['HTTP_RAW_POST_DATA'];
echo $test;
```
 
用postman测试一下

![][1]

 
卧槽,竟然出错了,提示没有发现 **`HTTP_RAW_POST_DATA`**  这个数组索引,什么鬼.Google一番,在php的官网看到了这样一段话

![][2]

 原来  **`HTTP_RAW_POST_DATA`**  
这个在php5.6中已经被废弃了,在php7.0以后的版本中已经被删除了,我用的php版本为7.2,肯定就出错了
 
好吧,那就老老实实的用`file_get_contents("php://input")`获取吧
 
在实际开发中,一般都是使用框架的,我用thinkphp用比较多,在tp5.0中可以使用`Request的getInput()`函数获取`Raw`中的数据

```php
<?php

namespace app\index\controller;

use think\Request;

class Index
{
    public function index(Request $request)
    {
        echo $request->getInput();
    }
}
```
 
测试一下

![][3]

 
没有问题,可以正常获取
 
#### 关于php获取HTTP POST数据的方法先介绍到这里


[0]: ./img/ji6vuuI.png 
[1]: ./img/In6BN3a.png 
[2]: ./img/mmqY7zE.png 
[3]: ./img/yQrYfm7.png 