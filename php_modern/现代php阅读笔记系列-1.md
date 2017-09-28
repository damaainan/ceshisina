# 现代php阅读笔记系列-1[∞][0]

December 10, 2016 Tags:[php][1]

之前在[php技能精进计划v1.0][2]这篇文章中推荐了一些书单，其中的已经读完，收获不小。由此见识到了现代php的语言特性，最佳实践，单元测试，性能优化，现代化的持续集成/部署解决方案等等。之前php那种给人quick and dirty的印象在读完这本书后也发生了转变。下面就书中的章节进行下总结。今天是这个系列笔记的第一篇---语言特性。

### 命名空间 namespace

举个栗子:

```php
    //foo1.php
    function test(){
        echo 'hello world';
    }
    //foo2.php
    require('foo1.php');
    //blabla 一堆代码
    function test(){
        echo 'hello world';
    }
```

在开发过程中会遇到引入别人写的库的情况，比如上面的foo2.php，如果在foo2.php中存在一个和foo1.php中的相同的函数 test, 那么代码执行的时候回报致命错误: Fatal error: Cannot redeclare test.....， 命名空间就是为了解决这样的冲突存在的。直接上代码:

```php
    //foo1.php
    namespace No13bus;
    function test(){
        echo 'hello world';
    }
    //foo2.php
    require('foo1.php');
    use function No13bus/test as tt; 
    //blabla 一堆代码
    function test(){
        echo 'heihei';
    }
    echo tt();  //返回: hello world
    echo test(); //返回: heihei
```

上面的代码有几点说明:

* 将foo1.php中的函数归并于自己的命名空间并且设置别名(as)可以避免函数命名冲突。
* use不仅仅使用别的命名空间的类，还可以用其方法和常量，不过要加上 function和constant关键字才可以。
* foo2.php中的函数以及其余类，常量的命名空间没有声明，默认是全局空间, 一般用 \ 表示， 而不是 No13bus。 全局空间的方法和类只要require了，即可调用，无需use. 比如常见的Exception类:


```php
    try{
        $a = 2/0;
    } catch(\Exception $e){
        echo $e->getMessage();
    }
```

* use function No13bus/test as tt; 如果修改为 use function No13bus/test; 还是会报错: Fatal error: Cannot declare function xxxxxx because the name is already in use....
* use的位置要放到所在文件的全局作用域(namespace的下方, 类代码的上方)，这样会减少运行时解析，全部在编译期间解析，这样做的好处还有一个就是避免use的类定义和下面的代码有重复定义和声明问题。
* trait性状这个php特性也是可以用use的，不过这个时候的use可以放到类里面，因为use trait的话，相当于把trait里面的代码复制了一份到类里面，是类的水平扩展，这个和命名空间其实是有区别的。

### 类方法限定符

* 方法前不加任何修饰符的话，默认是public。 此外不管是private, protect只能是类代码内部调用，类实例是无法调用的。protect是子类可以调用父类的方法/属性。
* 设计原则是类的方法/属性从严到宽， 初期所有方法/属性都写为 private，待相应的接口权限慢慢开放后，从private-->protect-->public.
* 针对接口编程 而不是实现
* 方法参数类型可以进行强制制定, 目前支持的类型有 array, 对象, interface, callable。

### trait性状

* 可以类比python的mixin编程模式，因为php不支持多重继承父类，通过trait可以继承多个性状达到水平扩展php类功能的作用。Laravel里面Auth认证服务可以看到很多trait设计思路的影子。

### 自动加载

* require和include的区别: require出错的话直接出错结束执行，include的话会报警，但是还是会继续执行代码。所以require用来加载各种公共库比较好， inlcude适合加载数据模板，不影响渲染。
* require和require_once区别: require_once会避免重复引入定义，但是消耗性能。require相反。性能和便捷性需要自身平衡。
* set_include_path 可以将库的文件夹加到包含路径里面去，能够节省一部分的代码量。
* 如果程序需要引入很多外部库和文件，那么代码就像这样:


```php
    // load.php
    require 'aaa_class.php'; //包含 MyClass类
    require 'bbb_class.php';
    require 'ccc_class.php';
    require 'ddd_class.php';
    require 'eee_class.php';
    require 'fff_class.php';
    .................
```

require的代码越来越多，打开这个页面的时候加载打开速度会很慢，并且这种意大利面条的代码看着很恶心。下面__autoload方法该出来了。

```php
    // autoload.php
    function __autoload($classname) {
            $filename = "./". $classname .".php";
            include_once($filename);
        // .......
    }
    // load.php
    require('autoload.php');
    $c = new MyClass();
```

通过这种方式完成了自动加载，但是这个方法有2个问题，第一函数不能自定义，必须是__autoload, 还有就是函数只能加载一次，不能在运行时进行改变。下面就有了升级版 spl_autoload_register.

```php
    spl_autoload_register('lib_loader');
    spl_autoload_register('class_loader');
    spl_autoload_register('Foo::test');
    Class Foo{
        public static test($classname){
                $filename =  $classname .".php";
                include_once($filename);
        }
    }
    function lib_loader($classname) {
        $filename = "./lib_loader/". $classname .".php";
        include_once($filename);
    }
    function class_loader($classname) {
        $filename = "./class_loader/". $classname .".php";
        include_once($filename);
    }
```

从上面可以看出, spl_autoload_register可以使用自定义的函数来进行自动加载，甚至可以使用类方法。并且自动加载函数可以从上到下执行。但是还是有一个问题，每次我引入外部库的时候，都需要维护和添加这些代码，很是麻烦。ok，终极大boss来了， Composer。将上面的lib_loader和class_loader通过composer来加载。一般是先有这么个composer.json文件。

```json
    {
    "require": {
            "google/apiclient": "1.0.*@beta",
            "guzzlehttp/guzzle": "~4.0"
        },
         "autoload": {
            "classmap": [
                "lib_loader",
                "class_loader"
            ]
        }
    }
```

然后执行 Composer install， 在调用2个类的文件开头写 require 'vendor/autoload.php'; 即可在按需加载类。

### 生成器 generator

* 玩过node和python的同学都知道yield这个关键字，借助yield的上下文运行时的切换，可以做到协程的效果，能够大大减少内存的使用量以及效率。比如导出和导入大量数据的时候，可以使用生成器避免内存爆出。具体看鸟哥的介绍[在此][3]，这里我们直接上代码了:


```php
    ini_set("memory_limit","200M");
    //生成器
    function makerange($length){
        $ret = [];
        for($i =1;$i<$length;$i++){
            $ret[] = $i;
        }
        return $ret;
    }
    
    function makerange_g($length){
        for($i =1;$i<$length;$i++){
            yield $i;
        }
    }
    
    $myfile = fopen("testfile.txt", "w");
    //$re = makerange(1000000);
    $re = makerange_g(1000000);
    foreach($re as $item){
        fwrite($myfile, $item . PHP_EOL);
    }
    echo memory_get_usage() / 1024 / 1024; //打印到此处内存的占用量
```

使用makerange函数的时候会发现最后打印的内存占用在130M左右，使用makerange_g的时候内存占有率在0.5M左右。这就是协程的威力。说句题外话，有的时候一个页面请求达到上百M，充斥着10来个sql，每个sql的数据量还很大，建议是每个sql得到的结果使用完了之后就unset释放掉，否则内存崩溃，会报php Allowed memory size of bytes exhausted这样的错误。

### 闭包

* 闭包一般会用在 array_map 这样的函数里面作为一个匿名函数参量来使用。
* 闭包的use关键字可以给闭包带来新的状态变量
* 闭包的bindTo方法的使用场景可以用来访问绑定闭包的对象中受保护和私有的成本变量。举一个路由分发的例子。

```php
    class App{
            protected $routes = [];
            protected $responseStatus = '200 OK';
            protected $responseContent = 'text/html';
            protected $responseBody = 'hello world';
    
            public function addRoute($routePath, $routeCallback)
            {
                $this->routes[$routePath] = $routeCallback->bindTo($this, __CLASS__);
            }
    
            public function dispatch($currentPath){
                foreach ($this->routes as $routepath=>$callback) {
                    if($routepath == $currentPath){
                        $callback();
                    }
                }
    
                header('HTTP/1.1 ' . $this->responseStatus);
                header('Content-type: ' . $this->responseContent);
                header('Content-length: ' . mb_strlen($this->responseBody));
                echo $this->responseBody;
            }
    }
    
    $app = new App();
    $app->addRoute('/user/no13bus', function(){
        //闭包调用内部收保护的变量 使用use是不能做到的。
        $this->responseContent = 'application/json;charset=utf8';
        $this->responseBody = '{"name":"no13bus"}';
    });
    
    $app->dispatch('/user/no13bus');
    // 返回 '{"name":"no13bus"}
```

### opcache缓存技术

* zend opcache 利用字节码缓存，它会缓存预先编译好的字节码, 提高解释型语言的运行时效率。
* opcache的设置问题

```
    opcache.enable=1        #是否开启opcache缓存
    opcache.revalidate_freq=60      #检查脚本时间戳是否有更新的周期，以秒为单位。 设置为 0 会导致针对每个请求， OPcache 都会检查脚本更新，这个配置在下文中会提到
    opcache.validate_timestamps=1 # zend opcache是否需要检测php脚本的变化
```
* validate_timestamps参数调试环境设置为1。 生产环境设置建议设置为0，zend opcache察觉不到php脚本的变化，必要时可以手动清空。使用opcache_reset函数即可。
* opcache.revalidate_freq=0的话就是 设置为 0 会导致针对每个请求， OPcache 都会检查脚本更新，调试模式的时候设置为0即可。

### 内置服务器

* php内置了一服务器，可以不借助apache自己跑起来。

```
    php -S localhost:8080 -c /path/php.ini //端口 配置文件可以指定来开启服务器
```
* 刚转到php的时候非常不习惯，因为没有交互命令行，什么结果都得要刷浏览器才能看见，php后来的版本里面提供了一个稍微可用的交互命令行。 执行 php -a 即可打开，这点php较之python, node 差了好些。后来偶然发现了一个超级强大的命令行[Psysh][4]， 喜欢的可以玩玩。Laravel自带的shell就是它实现的。

[0]: http://blog.no13bus.com/post/mordern_php1
[1]: /tags/php
[2]: http://blog.no13bus.com/post/php_improve_plan
[3]: http://www.laruence.com/2015/05/28/3038.html
[4]: http://www.php100.com/html/dujia/2016/0216/8989.html