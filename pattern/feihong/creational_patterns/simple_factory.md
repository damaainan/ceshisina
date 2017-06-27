### 简单工厂
简单工厂模式的工厂类一般是使用静态方法，通过接收的参数的不同来返回不同的对象实例。

也就是使用的时候通过传参来确定生成不同的对象。

简单工厂UML图：  
![](http://images2015.cnblogs.com/blog/663847/201706/663847-20170625084348116-1798368462.png)

简单工厂需要有3个角色：

- Product接口类：用于定义产品规范；
- 具体的产品实现，例如ConcreateProductA、ConcreateProductB；
- 简单工厂类SimpleFactory：用于生成具体的产品。

使用的时候通过传参数给简单工厂类，可以生成想要的产品。

代码：
ICar.php：定义产品规范：
``` php
namespace Yjc\SimpleFactory;

interface ICar
{
    public function driver();
}
```

具体产品实现：
```
namespace Yjc\SimpleFactory;

class Benz implements ICar
{
    public function driver()
    {
        echo 'benz driver.';
    }
}

class Bmw implements ICar
{
    public function driver()
    {
        echo 'bmw driver.';
    }
}
```

简单工厂类SimpleFactory：
``` php
namespace Yjc\SimpleFactory;

class SimpleFactory
{
    public static function makeCar($type){
        switch ($type){
            case 'benz':
                return new Benz();
                break;
            case 'bmw':
                return new Bmw();
                break;
            default:
                throw new \Exception('not support type!');
                break;
        }
    }
}
```

参数代码：
``` php
$car = SimpleFactory::makeCar('benz');
$car->driver();
```

简单工厂的优点/缺点：  

优点：简单工厂模式能够根据外界给定的信息，决定究竟应该创建哪个具体类的对象。明确区分了各自的职责和权力，有利于整个软件体系结构的优化。

缺点：很明显工厂类集中了所有实例的创建逻辑，容易违反GRASPR的高内聚的责任分配原则。

>参考：  
1、设计模式：简单工厂、工厂方法、抽象工厂之小结与区别 - superbeck的专栏 - 博客频道 - CSDN.NET  
http://blog.csdn.net/superbeck/article/details/4446177  
2、简单工厂、工厂方法、抽象工厂、策略模式、策略与工厂的区别 - Danny Chen - 博客园  
http://www.cnblogs.com/zhangchenliang/p/3700820.html  