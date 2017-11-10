# 从放弃到入门-Yaf（细说目录）

 时间 2017-11-09 12:53:41  

原文[https://juejin.im/post/5a02fdce51882512a860be87][1]


在上一个文章中我们说了它的基本的一些结构，以及我们通过修改入口文件把它移入到 **public文件夹** 下面。那么今天我们来一起研究一下它的详细的目录结构，以及完善一下 **public文件夹** 里面的内容。 

今天主要是两大块，第一块是完善public文件夹，第二块是目录结构的详细解读。

## 完善public文件夹

我们已经把index.php文件成功移植到来我们的public文件夹下面了，那么现在我们在这个文件夹下面创建几个文件夹用来存放我们项目中所需要的一些静态文件。比如：图片、css样式、javascript等。按照鸟哥在文档中所说的，我们可以创建以下这几个文件夹，它们分别是：

css :用来存储一些我们项目中所需要的样式文件。 

img :用来存储我们项目中的一些图片资源，比如logo、背景图什么的。 

js :用来存储一些我们项目中的js代码块或者第三方库，如：jquery、vuejs等。 

那么我们现在就先创建一下它们把，为我们接下来的项目做准备：

![][4]

以下就是我们的目录结构，好了现在创建好了，我们接下来看看其他目录里面是什么样子的！

## conf文件夹

这个文件夹用于存放我们框架的配置文件，默认配置文件：application.ini。配置项可以参考鸟哥手册： [www.laruence.com/manual/yaf.…][5]

## application文件夹

这个文件夹的话是我们框架中的核心，里面包含了我们这个框架的整体架构等内容。

里面有 **5个文件夹** 以及一个 **.php** 文件，那我们现在一个一个说起吧！ 

## Bootstrap.php

大家看到这个别把它误认为是前端框架的那个bootstrap不过我相信都不会这么认为的。那我们看看它是做什么的：

Bootstrap, 也叫做引导程序. 它是Yaf提供的一个全局配置的入口, 在Bootstrap中, 你可以做很多全局自定义的工作. ——鸟哥 

我们一起来看看鸟哥的这句话，他说bootstrap.php是一个引导程序，是yaf的一个全局的配置的一个入口，也就是说我们可以在它里面做一些配置，比如加载我们的.ini配置文件，加载我们的第三方类库，如图片处理、日志处理、composer下载的类库等。我们来先看看源码：

```php
    <?php
    /**
     * @name Bootstrap
     * @author mateng
     * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
     * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
     * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
     * 调用的次序, 和申明的次序相同
     */
    class Bootstrap extends Yaf_Bootstrap_Abstract {
    
        public function _initConfig() {
            //把配置保存起来
            $arrConfig = Yaf_Application::app()->getConfig();
            Yaf_Registry::set('config', $arrConfig);
        }
    
        public function _initPlugin(Yaf_Dispatcher $dispatcher) {
            //注册一个插件
            $objSamplePlugin = new SamplePlugin();
            $dispatcher->registerPlugin($objSamplePlugin);
        }
    
        public function _initRoute(Yaf_Dispatcher $dispatcher) {
            //在这里注册自己的路由协议,默认使用简单路由
        }
    
        public function _initView(Yaf_Dispatcher $dispatcher) {
            //在这里注册自己的view控制器，例如smarty,firekylin
        }
    }
```

我们看到了它里面的每个方法都是以 `_init`来开头的，之所以以 `_init`开头主要是它们都会被yaf调用。然而这些方法都会接收一个参数：Yaf_Dispatcher $dispatcher，我们现在来试着自己定义一个方法看看：

    public function _initPdobase(Yaf_Dispatcher $dispatcher) {
        var_dump('hello,pdo!');
        exit;
    }

在这里我自定义了一个Pdobase的方法，输出了一段“hello，pdo！”，这时候会输出hello，pdo！此刻说明了它被加载了： 

![][6]

在这里我们还可以进行插件的注册，路由的定义以及自定义等，是不是非常好用呢，接下来我们看看 **controllers文件夹**。

## controllers文件夹

大家都比较熟悉现在比较流行的web架构：MVC三层架构，那么这个controllers文件夹中存放的也就是我们的 C ，也就是控制器，通过自定义控制器如：Index.php 就可以进行接收客户端请求，调用数据模型，基本逻辑处理，以及调用视图，最终完成客户的请求。它接收请求并决定调用哪个模型去处理请求，然后再确定用哪个视图来显示返回的数据。

## models文件夹

上面说到了MVC中的 C ，那么我们现在就来看看models文件夹是做什么的，它是存放我们的 M 。

“模型表示企业数据和业务规则。在MVC的三个部件中，模型拥有最多的处理任务。例如它可能用像EJBs和ColdFusion Components这样的构件对象来处理数据库，被模型返回的数据是中立的，就是说模型与数据格式无关，这样一个模型能为多个视图提供数据，由于应用于模型的代码只需写一次就可以被多个视图重用，所以减少了代码的重复性。——百度百科”

## views文件夹

这个文件夹我们看名字也知道，他是用来存放我们的视图文件，这里面和其他两个文件夹不同的是，还需要在它里面创建一个文件夹，而这个文件夹的名字要与控制器的名称一致，如我们有个：Index.php控制器，那么在views下面我们就要创建一个index文件夹，在这个文件夹里面我们来创建模板文件，如：index.phtml。这里的文件名字为我们控制器的一个方法，在后面的实战中我会详细说明。

## library文件夹

这个主要是用于存放我们的一些本地类库的文件夹。后面我们会使用到它，使用的时候我们再详细说明。

## plugins文件夹

这个是插件目录，用来存放一些插件。如：PDF文档的生成插件、phpmailer邮件的发送等。

好了，这一篇我们详细的说明了框架的目录结构，那么下一篇我们就开始创建第一个控制器，让它输出“hello，world！”


[1]: https://juejin.im/post/5a02fdce51882512a860be87

[4]: ../img/UZzq6nn.png
[5]: https://link.juejin.im?target=http%3A%2F%2Fwww.laruence.com%2Fmanual%2Fyaf.config.html
[6]: ../img/yam2Yzq.png