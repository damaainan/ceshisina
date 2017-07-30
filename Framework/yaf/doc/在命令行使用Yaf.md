## 使用样例

要使得yaf在命令行模式下运行, 有俩种方式, 第一种方式专门为用Yaf开发Contab等任务脚本设计的方式, 这种方式下, 对Yaf的唯一要求就是能自动加载所需要的Model或者类库, 所以可以简单的通过 **Yaf_Application::execute**来实现. 

而第二种方式, 是为了在命令行下模拟请求, 运行和Web请求一样的流程, 从而可以用来在命令行下测试我们的Yaf应用, 对于这种方式, 唯一的关键点就是请求体, 默认的请求是由`yaf_application`实例化, 并且交给`yaf_dispatcher`的, 而在命令行模式下, `Yaf_Application`并不能正确的实例化一个命令行请求, 所以需要变更一下, 请求需要手动实例化.

**例 1. 实例化一个[Yaf_Request_Simple][1]**

```php
         <?php
         $request = new Yaf_Request_Simple();
         print_r($request);
```

  
如上面的例子, `Yaf_Request_Simple`的构造函数可以不接受任何参数, 在这种情况下, `Yaf_Request_Simple`会在命令行参数中, 寻找一个字符串参数, 如果找到, 则会把请求的request_uri置为这个字符串.


> 注意 当然, Yaf_Request_Simple是可以接受5个参数的, 具体的可见Yaf_Request_Simple类说明. 

现在让试着运行上面的代码:

**例 2.**

         $ php request.php 

输出: 

         Yaf_Request_Simple Object
         (
         [module] => 
         [controller] => 
         [action] => 
         [method] => CLI
         [params:protected] => Array
         (
         )
    
         [language:protected] => 
         [_base_uri:protected] => 
         [uri:protected] => 
         [dispatched:protected] => 
         [routed:protected] => 
         )

现在让我们变更下我们的运行方式:

**例 3.**

         $ php request.php  "request_uir=/index/hello"

输出: 

         Yaf_Request_Simple Object
         (
         [module] => 
         [controller] => 
         [action] => 
         [method] => CLI
         [params:protected] => Array
         (
         )
    
         [language:protected] => 
         [_base_uri:protected] => 
         [uri:protected] => index/hello  //注意这里
         [dispatched:protected] => 
         [routed:protected] => 
         )

看到差别了么?

当然, 我们也可以完全指定`Yaf_Request_Simple::__construct`的5个参数:

**例 4. 带参数实例化一个Yaf_Request_Simple**

```php
         <?php
         $request = new Yaf_Request_Simple("CLI", "Index", "Controller", "Hello", array("para" => 2));
         print_r($request);
```

运行输出: 

         $ php request.php 
         Yaf_Request_Simple Object
         (
         [module] => Index
         [controller] => Controller
         [action] => Hello
         [method] => CLI
         [params:protected] => Array
         (
         [para] => 2
         )
    
         [language:protected] => 
         [_base_uri:protected] => 
         [uri:protected] => 
         [dispatched:protected] => 
         [routed:protected] => 1    //注意这里
         )

  
可以看到一个比较特别的就是, routed属性变为了TRUE, 这就代表着如果我们手动指定了构造函数的参数, 那么这个请求不会再经过路由, 而直接是路由完成状态.

## 分发请求

现在请求已经改造完成了, 那么接下来就简单了, 我们只需要把我们传统的入口文件:

**例 5. 入口文件**

```php
         <?php
         $app = new Yaf_Application("conf.ini");
         $app->bootstrap()->run();
```

  
改为:

**例 6. 入口文件**

```php
         <?php
         $app = new Yaf_Application("conf.ini");
         $app->getDispatcher()->dispatch(new Yaf_Request_Simple());
```

  
这样, 我们就可以通过在命令行中运行Yaf了

参见 

**Yaf_Request_Simple**

