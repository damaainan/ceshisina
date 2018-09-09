## [使用VarDumper进行优雅的PHP调试](https://segmentfault.com/a/1190000003032168)

> 原文来自： [https://jellybool.com/post/a-brand-new-way-to-test-php-with-symfony-va...][0]

相信很多PHP开发者在写代码的时候都会经常用到`var_dump()`这个函数，很多人都会直接用类似`die(var_dump($var))`来查看一个变量或者一个实例到底是长什么样的，稍微有一些人可能还直接封装过：比如直接叫一个`vdd()`等，以便于自己在调试自己的代码的时候使用。这种方式一直陪伴着我走过了这么久的编程时光，以至于造成了对`var_dump()`出来的现实样式都有一点审美疲劳了：因为`var_dump()`出来的可以说是完全没有什么美感啊，至少对于像我们这些代码工作者来说：你竟然没有高亮！！不能接受。

## 相遇

然后之前苦于没有找到很好的解决方案，也就是一直这样忍受着过来了，直到昨天我发现了这货：

[Symfony VarDumper][1]

测试样式是长这样的：

![][2]

我第一眼看到这个的时候就马上爱上这货了，忍不住要写点东西来分享一下：

先来说说[Symfony VarDumper][1]的优点，[Symfony VarDumper][1]不仅可以做到像var_dump()一样调试，而且可以做得更好，并不是只靠脸生活的：

1. 你可以轻松配置输出数据的格式：HTML 或者 命令行样式
1. 对于一些可能重复太多的数据，VarDumper智能过滤将其折叠起来，并且你可以很完美地看到你的数据的结构是什么样的，不清楚的话等下可以看下面的截图。
1. 每个打印出来的对象或变量都有特定的样式。

## 安装使用之

说了这么多之后，我们终于要来一睹庐山真面目了。首先是安装，最简单的方法就是直接使用`composer`安装，创建一个新的文件夹`php/`，我们来测试一下：

```
    cd php/
    
    composer require symfony/var-dumper
```

再来创建一个`index.php`，将自动加载文件`autoload.php`包含进来：

```
    <?php
    require __DIR__.'/vendor/autoload.php';
```

首先在index.php写一个简单的数组来测试一下：

```
    <?php
    require __DIR__.'/vendor/autoload.php';
    
    $var = array(
        'a simple string' => 'in an array of 5 elements',
        'a float' => 1.0,
        'an integer' => 1,
        'a boolean' => true,
        'an empty array' => array(),
    );
    dump($var);
    
```

出来的结果是这样的：

![][3]

有没有觉得很不错！这里还要说一点的是：如果你觉得[Symfony VarDumper][1]自带的样式不够美观，你可以直接到`Dumper/HtmlDumper.php`去修改你的自己的样式，比如你很喜欢github风，你完全可以自己在这个文件里面写你自己的css样式。

上面对于数组的表现[Symfony VarDumper][1]貌似做得很完美，不仅给我们舒适的高亮，还很清晰的给了我们这个数组的结构。那么对于php中的stdObject，[Symfony VarDumper][1]的表现会是如何呢？我们来看看：

```
    class Test {
        public $prop1 = 10;
        private $prop2 = 20;
        protected $prop3 = 30;
        private $prop4 = 40;
    
        public function __construct($value) {
            $this->undefinedProp = $value;
        }
    }
    
    $test = new Test(50);
    
    dump($test);
```

出来的结果是这样的，注意它的高粱颜色有不一样了：

![][4]

这里可以看到：`public`就用 `+` 表示，`private` 就用 `-` 表示，而`protected` 就用 `#` 表示。不见如此，如果你仔细看图，你会看到当鼠标浮在对应的属性上面的时候，会有一个小小的提示框来提醒我们这个具体是什么，很完美啊。

我们既然需要测试，那么在类中添加对应的方法呢，这个到底会给我们什么样的调试反馈呢？

```
    class Test {
        public $methodOne;
        protected $methodTwo;
    
        public function __construct() {
            $this->methodTwo = function() {
                return 'I am method 2';
            };
        }
    
        public function buildFunction() {
            $this->methodThree = function() {
                return 'I am method 3';
            };
        }
    
        public function __call($method, $args)
        {
            if (isset($this->$method)) {
                $func = $this->$method;
                return call_user_func_array($func, $args);
            }
        }
    
    }
    
    $test = new Test();
    $methodOne = function() {
        return 'I am method 1';
    };
    $test->methodOne = $methodOne;
    $test->buildFunction();
    $test->methodOne();
    
    dump($test);
    
```

表现依然很惊艳：

![][5]

在上图中，你不仅可以很清晰地知道各个方法的类名是什么，也可以知道this代表的是什么，甚至还可以知道这个代码段是从第几行开始第几行结束的！666...

## 最后

可能很多同学看了这篇文章之后会觉得我们在自定义样式时直接改文件不太好，因为这个时候，如果你切换到其他的项目，你还是得重新再安装一次，难道还得再改一次？不是这样的，其实我推荐大家的做法是：全局安装[Symfony VarDumper][1]，这样不仅可以解决样式一次性问题，还可以让你在任何项目中使用[Symfony VarDumper][1]，安装方法如下：

**第一步，全局安装：**

```
    composer global require symfony/var-dumper;
```

**第二：配置php.ini**

在php.ini中找到`auto_prepend_file`，然后写上你相对应的路径，比如像下面这样的：

在相应文件引入此全局配置即可
    
    require "D:/composer/vendor/autoload.php"; // 非 root 目录页面使用报错，命令行正常

```
     auto_prepend_file = ${HOME}/.composer/vendor/autoload.php 
```

**最后，更新composer**

直接命令行执行：

```
    composer global update
```

到这里，你就可以配置好一个很优雅的调试界面了。反正我是很喜欢，不知道你是什么感受。

**Happy Hacking**

[0]: https://jellybool.com/post/a-brand-new-way-to-test-php-with-symfony-vardumper
[1]: https://github.com/symfony/var-dumper
[2]: ./img/6cc571331198.png
[3]: ./img/285e24b636e1.png
[4]: ./img/4c8a25331d8d.gif
[5]: ./img/081e98d01541.png