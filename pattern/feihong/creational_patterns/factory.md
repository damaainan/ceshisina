### 工厂方法

工厂方法是针对每一种产品提供一个工厂类。通过不同的工厂实例来创建不同的产品实例。

相比简单工厂，创建对象这件事不再交由一个类来创建：把简单工厂拆分，每个产品由专门的一个简单工厂来实现，每个简单工厂实现工厂接口类。这样实现在同一等级结构中，支持增加任意产品。

工厂方法UML图：  
![](http://images2015.cnblogs.com/blog/663847/201706/663847-20170625090418757-842506804.png)


简单工厂需要有4个角色：

- Product接口类：用于定义产品规范；
- 具体的产品实现，例如ConcreateProductA、ConcreateProductB；
- 抽象工厂类IFactory：用于规范工厂；
- 具体产品创建的简单工厂，例如ConcreateFactoryA、ConcreateFactoryB。

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

抽象工厂类IFactory:
``` php
namespace Yjc\Factory;

interface IFactory
{
    public static function makeCar();
}
```

具体工厂实现：
```
namespace Yjc\Factory;

class FactoryBenz implements IFactory
{
    public static function makeCar()
    {
        return new Benz();
    }
}

class FactoryBmw implements IFactory
{
    public static function makeCar()
    {
        return new Bmw();
    }
}
```

测试：
``` php
$car = Factory\FactoryBenz::makeCar();
$car->driver();
```

思考：如果不使用工厂模式来实现我们的例子，也许代码会减少很多——只需要实现已有的车，不使用多态。但是在可维护性上，可扩展性上是非常差的（你可以想象一下添加一辆车后要牵动的类）。因此为了提高扩展性和维护性，多写些代码是值得的，尤其是复杂项目里。

>参考：  
1、设计模式：简单工厂、工厂方法、抽象工厂之小结与区别 - superbeck的专栏 - 博客频道 - CSDN.NET  
http://blog.csdn.net/superbeck/article/details/4446177  
2、简单工厂、工厂方法、抽象工厂、策略模式、策略与工厂的区别 - Danny Chen - 博客园  
http://www.cnblogs.com/zhangchenliang/p/3700820.html  