## 路由技术


## 0x00 路由实现原理

用户通过指定的URL范式对后台进行访问，URL路由处理类进行处理后，转发到逻辑处理类，逻辑处理类将请求结果返回给用户。

#### 约定URL范式和规则

约定一套自己喜欢的，对搜索引擎友好，对用户友好的URL规则

#### URL处理类(即路由实现的核心)

对用户请求的URL进行解析处理，获取到用户请求的类，方法，以及Query参数等，并将请求转发给逻辑处理类。

#### 逻辑处理类

处理网站的真实业务逻辑。

## 0x01 URL范式约定

目前来说，有两种比较流行的URL格式，一种是普通模式，一种是 `pathinfo` 模式。

#### 普通模式

在 ThinkPHP 框架中，默认的URL格式即为普通模式，普通模式URL如下:

    index.php?m=home&c=user&a=login&v=value

其中 m 参数的值为模块名称， c 参数的值为控制器名称， a 参数的值为方法名称，之后的参数则为该方法中所要接收的其他 GET 请求参数

#### pathinfo模式

在 CodeIgniter 框架中，默认的URL格式为 pathinfo 模式，如下：

    index.php/controller/method/prarme1/value1

这块的意义也已经标注的很明白了，在 method 以后，就是方法接收的 GET 参数了，格式就是 名称/值## 0x02 URL路由处理类（核心）

此处我们选用最简单的普通单模块模式进行演示，只为说明简单的原理，如下：

    index.php?c=user&a=login&v=value

我们约定参数 c 为控制器名称，参数 a 为方法名称，之后的均是 GET 参数

```php
<?php
include 'index.class.php';
include 'user.class.php';
// 对用户请求URL进行处理
$query = $_GET;
$controller = isset($query['c']) ? $query['c'] : 'indexController';
$action = isset($query['a']) ? $query['a'] : 'index';
if (class_exists($controller)) {
        if (method_exists($controller, $action)) {
            unset($_GET['c']);
            unset($_GET['a']);
            // 实例化用户请求类并调用方法
            (new $controller())->$action();
        } else {
            echo '控制器' . $controller . '中不存在方法' . $action;
        }
} else {
        echo '不存在控制器' . $controller;
}
```
其中 unset() 掉两个get参数，只是为了对真正调用的方法造成其他影响。

## 0x03 逻辑处理类

逻辑处理类就是最终的业务逻辑，也就是真正的回应用户请求的代码片段。下面只是一个简单的示例：

```php

<?php
/* index.class.php 文件源码 */
class indexController {
        public function index(){
            var_dump($_GET);
        }
}
```

```php
<?php
/* user.class.php 文件源码 */
class user {
        public function index() {
            echo '这里是User控制器';
        }
        public function login() {
            var_dump($_GET);
        }
}
```
## 0x04 结束

这里只是最简单的PHP路由技术的原理，其实真正为一个项目或者框架进行路由开发，可能需要能够兼容很多复杂的情况，需要对各种情况都要考虑到。

