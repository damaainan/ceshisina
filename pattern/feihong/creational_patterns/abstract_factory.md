### 抽象工厂
抽象工厂(Abstract Factory)是应对产品族概念的。比如说，每个汽车公司可能要同时生产轿车、跑车、货车、客车，那么每一个工厂都要有创建轿车，货车和客车的方法。

在学习抽象工厂具体实例之前，应该明白两个重要的概念：**产品族** 和**产品等级** 。

>产品等级：同一个产品的不同分类或者组成。  
>产品族：是指位于不同产品等级结构中，功能相关联的产品组成的家族。

例如：汽车分奔驰、宝马，奔驰下面还有跑车、MINI车等等。其中奔驰就是产品族，跑车、MINI车是产品等级。

再例如：AMD的主板、芯片组、CPU组成一个家族，Intel的主板、芯片组、CPU组成一个家族。而这两个家族都来自于三个产品等级：主板、芯片组、CPU。


抽象工厂是汽车生产工厂的抽象，工厂方法是一个汽车工厂里每种汽车型号的抽象；简单工厂和抽象工厂含义一样，只是产品型号是固定的。

应对产品族概念而生，增加新的产品线很容易，但是无法增加新的产品。



抽象工厂UML图：  
![](http://images2015.cnblogs.com/blog/663847/201706/663847-20170625100444491-999655354.png)

简单工厂需要有4个角色：

- Product接口类：用于定义产品规范，例如ProductA、ProductB，抽象工厂有多个Product接口类；
- 具体的产品实现，例如ProductA1、ProductA2；
- 抽象工厂类IFactory：用于规范每个工厂；
- 具体产品族创建的工厂，例如Factory1、Factory2。

对应到汽车工厂的例子，这么解释：  
1、汽车厂(抽象工厂)分为奔驰汽车厂(F1)、宝马汽车厂(F2)；  
2、每个汽车厂都能生产：跑车(ProductA)、Mini车(ProductB)；  
3、可以有产品：奔驰跑车(A1)、宝马跑车(A2)、奔驰MINI(B1)、宝马MINI(B2)；  
4、那么奔驰汽车厂(F1)可以生产奔驰跑车(A1)、奔驰MINI(B1)；  
5、跑车(ProductA)、Mini车(ProductB)可以有自己的一些行为(operation)：例如`driver()`、`playMusic()`。  

其中：奔驰车、宝马车就是产品族，跑车、MINI车是产品等级。

对应到芯片工厂的例子，这么解释：  
1、芯片厂商(抽象工厂)分为Intel厂(F1)、AMD厂(F2)；  
2、每个芯片厂商都能生产：CPU(ProductA)、Mainboard(ProductB)；  
3、可以有产品：Intel CPU(A1)、AMD CPU(A2)、Intel主板(B1)、AMD主板(B2)；  
4、那么Intel厂(F1)可以生产Intel CPU(A1)、Intel主板(B1)；  
5、CPU(ProductA)、Mainboard(ProductB)可以有自己的一些行为(operation)：例如`compute()`、`installCPU()`。  

下面以汽车生产为例：  
跑车：  
``` php
namespace Yjc\AbstractFactory;

interface IProductSportCar
{
    public function driver();
}
```

MINI车：
``` php
namespace Yjc\AbstractFactory;

interface IProductMiniCar
{
    public function driver();
    public function playMusic();
}
```

实现的跑车产品：
``` php
namespace Yjc\AbstractFactory;

class BenzSport implements IProductSportCar
{
    public function driver()
    {
        echo 'driver';
    }
}

class BmwSport implements IProductSportCar
{
    public function driver()
    {
        echo 'driver';
    }
}
```

实现的MINI车产品:
``` php
namespace Yjc\AbstractFactory;

class BenzMini implements IProductMiniCar
{
    public function driver()
    {
        echo 'driver';
    }

    public function playMusic()
    {
        echo 'playMusic';
    }
}

class BmwMini implements IProductMiniCar
{
    public function driver()
    {
        echo 'driver';
    }

    public function playMusic()
    {
        echo 'playMusic';
    }
}
```

抽象工厂（能生产各种类型车）：
``` php
namespace Yjc\AbstractFactory;

interface IFactory
{
    public function makeMiniCar();//Mini车
    public function makeSportCar();//跑车
}
```

抽象工厂实现：
``` php
namespace Yjc\AbstractFactory;

//宝马工厂
class FactoryBmw implements IFactory
{

    public function makeMiniCar()
    {
        return new BmwMini();
    }

    public function makeSportCar()
    {
        return new BmwSport();
    }
}

//奔驰工厂
class FactoryBenz implements IFactory
{

    public function makeMiniCar()
    {
        return new BenzMini();
    }

    public function makeSportCar()
    {
        return new BenzSport();
    }
}
```

测试：
``` php
//生产奔驰跑车
$benzFactory = new FactoryBenz();
$benzCar = $benzFactory->makeSportCar();
$benzCar->driver();

//生产宝马MINI
$bmwFactory = new FactoryBmw();
$bmwCar = $bmwFactory->makeMiniCar();
$bmwCar->playMusic();
```

**简单工厂、工厂方法、抽象工厂总结：**

- 简单工厂： 用来生产同一等级结构中的任意产品。（对于增加新的产品，无能为力）
- 工厂方法：用来生产同一等级结构中的固定产品。（支持增加任意产品）  
- 抽象工厂：用来生产不同产品族的全部产品。（对于增加新的产品，无能为力；支持增加产品族）  

以上三种工厂方法在等级结构和产品族这两个方向上的支持程度不同。所以要根据情况考虑应该使用哪种方法。  

>参考：  
1、设计模式：简单工厂、工厂方法、抽象工厂之小结与区别 - superbeck的专栏 - 博客频道 - CSDN.NET  
http://blog.csdn.net/superbeck/article/details/4446177  
2、《JAVA与模式》之抽象工厂模式 - java_my_life - 博客园
http://www.cnblogs.com/java-my-life/archive/2012/03/28/2418836.html