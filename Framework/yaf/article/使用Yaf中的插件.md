# [使用Yaf中的插件][0]

 [Yaf框架][1] 3个月前 (03-18) 395浏览  [0评论][2]

目录

* [1 Yaf中支持的7个Hook][3]
* [2 插件用法][4]
    * [2.1 编写插件][5]
    * [2.2 注册插件][6]
    * [2.3 插件目录][7]

* [3 完整案例][8]

Yaf支持用户定义插件来扩展Yaf的功能, 这些插件都是一些类. 它们都必须继承自Yaf_Plugin_Abstract.

插件要发挥功效, 也必须现实的在Yaf中进行注册, 然后在适当的实际, Yaf就会调用它.

也许大家会问这个插件是个什么概念，有什么用呢。

其实我们用插件主要是用到Yaf框架中支持的Hook（钩子），Yaf中定义了7个Hook。

## 1 Yaf中支持的7个Hook 

Yaf的工作流程如下，7个Hook插在流程的不同位置。

![][9]

1. routerStartup：这个会在路由之前出发，也就是路由之前会调用这个Hook ，这个是7个事件中, 最早的一个. 但是一些全局自定的工作, 还是应该放在Bootstrap中去完成
1. routerShutdown：这个在路由结束之后触发，需要注意的是，只有路由正确完成之后才会触发这个Hook
1. dispatchLoopStartup：分发循环开始之前被触发
1. preDispatch：分发之前触发，如果在一个请求处理过程中, 发生了forward, 则这个事件会被触发多次
1. postDispatch：分发结束之后触发，此时动作已经执行结束, 视图也已经渲染完成. 和preDispatch类似, 此事件也可能触发多次
1. dispatchLoopShutdown：分发循环结束之后触发 此时表示所有的业务逻辑都已经运行完成, 但是响应还没有发送
1. preResponse：响应之前触发

其实挺希望有一个render之前的Hook的，可惜Yaf没有。

## 2 插件用法 

以上只是对插件做了一个基本的介绍，如果此前没有接触过类似设计的可能一下子稀里糊涂的。

其实也没关系，当前最重要的就是记住，有这么个印象，在后续我们使用的过程当中慢慢的就能接受了。

插件类是用户编写的, 但是它需要继承自Yaf_Plugin_Abstract.

对于插件来说, 上一节提到的7个Hook, 不需要全部关心。

只需要在插件类中定义和上面事件同名的方法, 这个方法就会在该事件触发的时候被调用.

而插件方法, 可以接受俩个参数, Yaf_Request_Abstract实例和Yaf_Response_Abstract实例.

### 2.1 编写插件 

一个插件类例子如下:
```php
<?php
 class UserPlugin extends Yaf_Plugin_Abstract
{
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
    }
}
```
### 2.2 注册插件 

插件要生效, 还需要向Yaf_Dispatcher注册, 那么一般的插件的注册都会放在Bootstrap中进行.

一个注册插件的例子如下:
```php
<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{

 public function _initPlugin(Yaf_Dispatcher $dispatcher) {
 $user = new UserPlugin();
 $dispatcher->registerPlugin($user);
 }
}
```
### 2.3 插件目录 

一般的, 插件应该放置在APPLICATION_PATH下的plugins目录。

这样在自动加载的时候, 加载器通过类名, 发现这是个插件类, 就会在这个目录下查找.

当然, 插件也可以放在任何你想防止的地方, 只要你能把这个类加载进来就可以。

## 3 完整案例 

这就是插件的使用过程，对于上面我们做一个总结，用代码来说话。

首先，我们定义好自己的插件类：
```php
/**
 * 插件类定义
 * UserPlugin.php
 */
class UserPlugin extends Yaf_Plugin_Abstract {
    //在路由之前触发，这个是7个事件中, 最早的一个. 但是一些全局自定的工作, 还是应该放在Bootstrap中去完成 
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin routerStartup called <br/>\n";
    }
   //路由结束之后触发，此时路由一定正确完成, 否则这个事件不会触发 
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin routerShutdown called <br/>\n";
    }
   //分发循环开始之前被触发 
    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin DispatchLoopStartup called <br/>\n";
    }
    //分发之前触发    如果在一个请求处理过程中, 发生了forward, 则这个事件会被触发多次 
    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin PreDispatch called <br/>\n";
    }
    //分发结束之后触发，此时动作已经执行结束, 视图也已经渲染完成. 和preDispatch类似, 此事件也可能触发多次 
    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin postDispatch called <br/>\n";
    }
    //分发循环结束之后触发，此时表示所有的业务逻辑都已经运行完成, 但是响应还没有发送 
    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin DispatchLoopShutdown called <br/>\n";
    }

    public function preResponse(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        echo "Plugin PreResponse called <br/>\n";
    }
}
```
然后注册我们的插件，在Bootstrap注册插件
```php
class Bootstrap extends Yaf_Bootstrap_Abstract{
    /**
     * 注册一个插件
     * 插件的目录是在application_directory/plugins
     */
    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        $user = new UserPlugin();
        $dispatcher->registerPlugin($user);
    }
}
```
就这样，插件就会在我们的项目运行过程中自动调用相关的Hook，我们可以在这些Hook中部署自己的业务逻辑。

比如用户是否需要登录，权限判断等。

**参考资料：**

1. [Yaf零基础学习总结7-学习使用Yaf中的插件][10]
1. [1.3. 流程图 第 1 章 关于Yaf][11]

[0]: http://www.awaimai.com/2070.html
[1]: http://www.awaimai.com/category/php/yaf
[2]: http://www.awaimai.com/2070.html#respond
[3]: #1_Yaf7Hook
[4]: #2
[5]: #21
[6]: #22
[7]: #23
[8]: #3
[9]: ../img/yaf_sequence.png
[10]: http://www.lai18.com/content/407154.html
[11]: http://www.laruence.com/manual/yaf.sequence.html