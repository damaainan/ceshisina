## 一篇文章搞明白CORS跨域

来源：[http://blog.51cto.com/13592288/2322263](http://blog.51cto.com/13592288/2322263)

时间 2018-11-26 17:05:45

 
面试问到数据交互的时候，经常会问跨域如何处理。大部分人都会回答JSONP，然后面试官紧接着就会问：“JSONP缺点是什么啊？”这个时候坑就来了，如果面试者说它支持GET方式，然后面试官就会追问，那如果POST方式发送请求怎么办？基础扎实一些的面试者会说，使用CORS跨域，不扎实的可能就摇摇头了。
 
这还没结束，如果公司比较正规或者很在乎技术功底，你面试的又是重要岗位，HR还想砍你的工资，就会再补一刀，CORS跨域有什么问题呢？这时候能回答上来的就没几个了，就算是你答出来兼容性不好，需要IE10+浏览器,对方依然有话说，那兼容性怎么处理呢？应试者就没话了，要么被Pass掉，即便留下来，谈工资的时候就没底气了。
 
CORS跨域实在是面试官pass一个人的利器。
 
为什么会这样呢？
 
1.遇到CORS请求的情况不多，开发者使用这个场景的很少，大部分都JSONP搞定了。
 
2.开发者自身技能不扎实，偷懒心态，平常没有意识和意愿去提升自己的技术水平。
 
3.相关的学习资料少、纯前端小白搭建可测试的环境难度大。
 
面对这条拦路虎，我们今天就彻底解决掉它，让它不再是我们的软肋，而是彰显我们技术实力的亮点。
 
首先，什么是CORS?
 
![][0]

```
CORS是一个W3C标准，全称是"跨域资源共享"（Cross-origin resource sharing）。
它允许浏览器向跨源服务器，发出XMLHttpRequest请求，从而克服了AJAX只能同源使用的限制。
```
 
优缺点
 
优点：

```
1.支持POST以及所有HTTP请求
2.安全性相对JSOP更高
3.前端要做的事儿比较少
```
 
缺点：

```
1.不兼容老版本浏览器，如IE9及其以下
2.需要服务端支持
3.使用起来稍微复杂了些
```
 
怎么用？
 
前端部分：

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CORS跨域请求</title>
    <script>
        function createCORSRequest(method, url) {
            var xhr = new XMLHttpRequest();
            if ("withCredentials" in xhr) {
                xhr.open(method, url, true);
            } else if (typeof XDomainRequest != "undefined") {
                xhr = new XDomainRequest();
                xhr.open(method, url);
            } else {
                xhr = null;
            }
            return xhr;
        }

        window.onload = function () {
            var oBtn = document.getElementById('btn1');
            oBtn.onclick = function () {
                var xhr = createCORSRequest("get", "http://wpdic.com/cors.php");
                if (xhr) {
                    xhr.onload = function () {
                        var json = JSON.parse(xhr.responseText);
                        alert(json.a);
                    };
                    xhr.onerror = function () {
                        alert('请求失败.');
                    };
                    xhr.send();
                }
            };
        };
    </script>
</head>
<body>
    <input type="button" value="获取数据" id="btn1">
</body>
</html>
```
 
注意点：
 
1.上面代码兼容IE8,因为用了XDomainRequest
 
2.其它代码你就当成XMLHttpRequset用，别考虑什么2.0不2.0的
 
3.如果你想post数据，可以往 xhr.send()里面搞
 
4.这里不建议大家研究"simple methdod"之类的知识，代码弄懂了会用就行，遇到问题了再查也不晚
 
后台部分：

```php
<?php
header('content-type:application:json;charset=utf8');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:GET,POST');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
$str = '{"a":1,"b":2,"c":3,"d":4,"e":5}'; 
echo $str;
?>
```
 
注意点：
 
1.Access-Control-Allow-Origin: 表示允许任何域名跨域访问，如果需要指定某域名才允许跨域访问，只需把Access-Control-Allow-Origin:  改为Access-Control-Allow-Origin:允许的域名,实际工作也要这么做2.Access-Control-Allow-Methods:GET,POST 规定允许的方法，建议控制严格些，不要随意放开DELETE之类的权限
 
2.Access-Control-Allow-Credentials
 
该字段可选。它的值是一个布尔值，表示是否允许发送Cookie。默认情况下，Cookie不包括在CORS请求之中。设为true，即表示服务器明确许可，Cookie可以包含在请求中，一起发给服务器。这个值也只能设为true，如果服务器不要浏览器发送Cookie，删除该字段即可。
 
最后，面试常考问题：
 
CORS和JSONP的应用场景区别？

```
CORS要求浏览器(>IE10)和服务器的同时支持，是跨域的根本解决方法，由浏览器自动完成。优点在于功能更加强大支持各种HTTP Method，缺点是兼容性不如JSONP。
```


[0]: https://img2.tuicool.com/eeqqiuJ.jpg