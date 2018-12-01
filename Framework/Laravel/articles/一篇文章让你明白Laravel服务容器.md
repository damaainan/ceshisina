## 一篇文章让你明白Laravel服务容器

来源：[http://www.jianshu.com/p/e0583692521c](http://www.jianshu.com/p/e0583692521c)

时间 2018-11-25 14:51:01


  
## DI

DI就是常说的依赖注入，那么究竟什么是依赖注入呢？

打个比方，电脑（非笔记本哈）需要键盘和鼠标我们才能进行操作，这个‘需要’换句话说就是‘依赖’键盘和鼠标。

那么，相应的，一个类需要另一个类才能进行作业，那么这也就是依赖。

看一段代码：

```php
class Computer {
        protected $keyboard;
        
        public function __construct() {
            $this->$keyboard = new Keyboard();
        }
    }
    
    这里的Computer类依赖了键盘类。
```

好，既然我们已经知道了什么是依赖，那么什么是注入呢？

我们改造一下上面的代码：

```php
class Computer {
        protected $keyboard;
        
        public function __construct(Keyboard $keyboard) {
            $this->$keyboard = $keyboard;
        }
    }
    
    $computer = new Computer(new Keyboard());
    
    
    这里的Computer类依赖注入了Keyboard类。
```

关于依赖注入，我的理解是：

所需要的类通过参数的形式传入的就是依赖注入。

理解了依赖注入，我们可以接着理解IOC。

  
## IOC

IOC是什么呢？

中文叫控制反转。啥意思呢？ 这个看明白了DI后就能很容易的理解了。

通过DI我们可以看到，一个类所需要的依赖类是由我们主动实例化后传入类中的。

控制反转和这个有什么关系呢？

控制反转意思是说将依赖类的控制权交出去，由主动变为被动。

看一段laravel代码：

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SessionController extends Controller
{

    public function login(Request $request)
    {
        //这就是IOC，我们不需要主动传入类了一切由laravel去实现
    }
}
```

看到这你可能有疑问了，这是怎么实现的呢？

这就是靠服务容器了，请往下接着看。

  
## 服务容器

看了很多文章，我一致认为服务容器就是一种设计模式。

它的目的就是解耦依赖。

它有点类似于我前面说的《享元模式》。区别在于服务容器解决了所有依赖的实现。

这里我们再从头至尾的看一遍，怎么一步步演化出服务容器。

依然是电脑的例子，我们知道电脑依赖键盘鼠标，可是键盘鼠标也有很多种呀。

先看一个最原始的代码例子：

```php
class Computer {
        protected $keyboard;
        
        public function __construct($type == null) {
            
            switch($type) {
                case 'common':
                    $this->keyboard = new CommonKeyboard();
                    break;
                case 'awesome':
                    $this->keyboard = new AweSomeKeyboard();
                    break;
                default:
                    $this->keyboard = new Keyboard();
                    break;
            }
          
        }
    }
```

或许你一眼就看出了问题在哪。

如果我们又要增加一钟键盘，那我们又得对这个类进行修改。这样下去，这个类会变得庞大且耦合程度过高。

那么我们可以怎么修改呢？


* 工厂模式
    

这样我们可以避免直接的修改Computer类。

```php
简单工厂
    class Factory {
        
        public static function getInstance($type){
            switch($type) {
                case 'common':
                    $this->keyboard = new CommonKeyboard();
                    break;
                case 'awesome':
                    $this->keyboard = new AweSomeKeyboard();
                    break;
                default:
                    $this->keyboard = new Keyboard();
                    break;
            }
        }
    }
    
    class Computer {
        protected $keyboard;
        
        public function __construct($type == null) {
            $this->keyboard = Factory::getInstance($type);
        }
    }
```

这样使用简单工厂模式后，我们后续的修改可以不用对Computer类进行操作而只要修改工厂类就行了。这就相当于对Computer类进行了解耦。

Computer类虽不在依赖那些键盘类了，但是却变为依赖工厂类了。

后续添加新类型的键盘就必须对工厂类进行修改。

所以这个工厂类还不能很好的满足要求，我们知道电脑对键盘的接口都是一致的，键盘必须实现这一接口才能被电脑识别，那我们对Computer和Keyboard类进行修改。


* DI（依赖注入）
    

```php
interface Board {
        public function type();
    }
    
    class CommonBoard implements Board {
        public function type(){
            echo '普通键盘';
        }
    }
    
    class MechanicalKeyboard implements Board {
        public function type(){
            echo '机械键盘';
        }
    }
    
    class Computer {
        protected $keyboard;
        
        public function __construct (Board $keyboard) {
            $this->keyboard = $keyboard;
        }
    }
    
    $computer = new Computer(new MechanialKeyBoard());
```

可是这样也有问题，如果我们后续对这台电脑使用的键盘不满意要进行替换呢？ 我们又回到原点了，必须去修改传入的键盘类。

能不能做成可配置的呢？


* IOC服务容器（超级工厂）
    

```php
class Container
{
    protected $binds;

    protected $instances;

    public function bind($abstract, $concrete)
    {
        if ($concrete instanceof Closure) {
            $this->binds[$abstract] = $concrete;
        } else {
            $this->instances[$abstract] = $concrete;
        }
    }

    public function make($abstract, $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        array_unshift($parameters, $this);

        return call_user_func_array($this->binds[$abstract], $parameters);
    }
}
```

这就是一个简单的IOC服务容器。

这个怎么解决我们上述的问题呢？

```php
$container = new Container;
    
    $container->bind('Board', function($container){
        return new CommonBoard;
    });
    
    $container->bind('Computer',function($container,$module){
        return new Computer($container->make($module));
    });
    
    $computer = $container->make('Computer',['Board']);
```

这里生产出来的Computer类就是一个使用普通键盘的电脑类了。

解释一下代码：

```php
bind(name,function($container){
        return new Name;
    })
    
    这里的name和Name之间的关系是：
    当我需要name类的时候你就给我实例化Name类。
    
    make(name)方法是对name进行生产返回一个实例。
```

如果我们要更换键盘怎么办呢？

```php
$container->bind('Board', function($container){
        return new MechanicalBoard;
    });
    
    $container->bind('Computer',function($container,$module){
        return new Computer($container->make($module));
    });
    
    $computer = $container->make('Computer',['Board']);
```

只要对bind绑定的Board类的实现进行修改，我们就可以很容易替换掉键盘了。这就是一个服务容器。

对服务容器进行一个理解：

容器就是一个装东西的，好比碗。而服务就是这个碗要装的饭呀，菜呀，等等东西。当我们需要饭时，我们就能从这个碗里拿到。如果你想在饭里加点菜（也就是饭依赖注入了菜），我们从碗里直接拿饭就可以了，而这些依赖都由容器解决了（这也就是控制反转）。

我们需要做的就是对提供的服务进行维护。

我们看一段真实的在laravel框架上能跑的代码：

[代码][0]

当然laravel框架的服务容器比这里的要复杂很多了，但我们明白了它的使用目的以及使用场景就不难去入手laravel了。

PS: 个人微信公众号'涂晓伟'，关注送laravel，linux,nginx等学习资料！！！

PS: 知乎专栏 '程序边缘',热烈欢迎！！！


[0]: https://implode.io/4mT8O4