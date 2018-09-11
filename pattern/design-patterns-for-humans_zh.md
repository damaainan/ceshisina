![Design Patterns For Humans](https://cloud.githubusercontent.com/assets/11269635/23065273/1b7e5938-f515-11e6-8dd3-d0d58de6bb9a.png)

***
<p align="center">
🎉 对设计模式的极简说明！🎉
</p>
<p align="center">
这个话题可以轻易让任何人糊涂。现在我尝试通过用<i>最简单</i>的方式说明它们，来让你（和我）把他们吃透。
</p>
***

🚀 简介
=================

设计模式用来解决重复的问题；**是解决特定问题的指导方针**。它们不是类(class)，包(packages)，或者库(libraries)，你不能引入它们，然后等待奇迹发生。它们是针对解决特定环境下特定问题的指导方针。

> 设计模式用来解决重复的问题；是解决特定问题的指导方针

维基百科的解释

> In software engineering, a software design pattern is a general reusable solution to a commonly occurring problem within a given context in software design. It is not a finished design that can be transformed directly into source or machine code. It is a description or template for how to solve a problem that can be used in many different situations.

⚠️ 请注意
-----------------
- 设计模式不是解决你所有问题的银弹。
- 不要尝试强行使用它们；如果做了，不好的事情可能发生。请记住设计模式是**解决**问题的方案，不是**发现**问题；所以不要过度思考。
- 如果在正确的地方以正确的方式使用，它们被证明是有帮助的；否则结果可能是一堆可怕混乱的代码。

> 下面的代码示例使用 PHP-7 书写，但你不应止步于此，因为理念是相通的。再加上,**对其他语言的支持正在路上**。

设计模式的种类
-----------------

* [创建型](#创建型模式)
* [结构型](#结构型模式)
* [行为型](#行为型模式)

创建型模式
==========================

白话
> 创建型模式侧重如何实例化一个对象或一组相关对象。

维基百科
> In software engineering, creational design patterns are design patterns that deal with object creation mechanisms, trying to create objects in a manner suitable to the situation. The basic form of object creation could result in design problems or added complexity to the design. Creational design patterns solve this problem by somehow controlling this object creation.
 
 * [简单工厂模式 Simple Factory](#-简单工厂模式)
 * [工厂方法模式 Factory Method](#-工厂方法模式)
 * [抽象工厂模式 Abstract Factory](#-抽象工厂模式)
 * [建造者模式 Builder](#-建造者模式)
 * [原型模式 Prototype](#-原型模式)
 * [单例模式 Singleton](#-单例模式)
 
🏠 简单工厂模式
--------------
现实例子
> 假设，你正在建造一所房子，你需要门。如果每次你需要一扇门你都要穿上木工服开始在房子里造扇门，将会是一团乱。取而代之的是让工厂造好。

白话
> 简单工厂模式在不暴露生成逻辑的前提下生成一个实例。

维基百科
> In object-oriented programming (OOP), a factory is an object for creating other objects – formally a factory is a function or method that returns objects of a varying prototype or class from some method call, which is assumed to be "new".

**代码例子**

首先，我们有一个门的接口和实现
```php
interface Door {
    public function getWidth() : float;
    public function getHeight() : float;
}

class WoodenDoor implements Door {
    protected $width;
    protected $height;

    public function __construct(float $width, float $height) {
        $this->width = $width;
        $this->height = $height;
    }
    
    public function getWidth() : float {
        return $this->width;
    }
    
    public function getHeight() : float {
        return $this->height;
    }
}
```
然后，我们有了工厂来制造和返回门
```php
class DoorFactory {
   public static function makeDoor($width, $height) : Door {
       return new WoodenDoor($width, $height);
   }
}
```
然后这样使用
```php
$door = DoorFactory::makeDoor(100, 200);
echo 'Width: ' . $door->getWidth();
echo 'Height: ' . $door->getHeight();
```

**什么时候使用？**

当创建一个对象不只是几个赋值和逻辑计算，把这件工作交给一个工厂而不是到处重复相同的代码就比较合适了。

🏭 工厂方法模式
--------------

现实例子
> 设想一个人事经理。一个人是不可能面试所有职位的。基于职位空缺，她必须把面试委托给不同的人。

白话
> 它提供了一个把生成逻辑移交给子类的方法。

维基百科
> In class-based programming, the factory method pattern is a creational pattern that uses factory methods to deal with the problem of creating objects without having to specify the exact class of the object that will be created. This is done by creating objects by calling a factory method—either specified in an interface and implemented by child classes, or implemented in a base class and optionally overridden by derived classes—rather than by calling a constructor.
 
 **代码例子**
 
以上面的人事经理为例。首先我们有一个面试官接口和一些实现

```php
interface Interviewer {
    public function askQuestions();
}

class Developer implements Interviewer {
    public function askQuestions() {
        echo 'Asking about design patterns!';
    }
}

class CommunityExecutive implements Interviewer {
    public function askQuestions() {
        echo 'Asking about community building';
    }
}
```

现在我们新建我们的人事经理 `HiringManager`

```php
abstract class HiringManager {
    
    // Factory method
    abstract public function makeInterviewer() : Interviewer;
    
    public function takeInterview() {
        $interviewer = $this->makeInterviewer();
        $interviewer->askQuestions();
    }
}
```
现在任何一个都可以继承它，并且生成需要的面试官
```php
class DevelopmentManager extends HiringManager {
    public function makeInterviewer() : Interviewer {
        return new Developer();
    }
}

class MarketingManager extends HiringManager {
    public function makeInterviewer() : Interviewer {
        return new CommunityExecutive();
    }
}
```
然后可以这样使用

```php
$devManager = new DevelopmentManager();
$devManager->takeInterview(); // Output: Asking about design patterns

$marketingManager = new MarketingManager();
$marketingManager->takeInterview(); // Output: Asking about community building.
```

**何时使用？**

当一个类里有普遍性的处理过程，但是子类要在运行时才确定。或者换句话说，调用者不知道它需要哪个子类。

🔨 抽象工厂模式
----------------

现实例子
> 扩展我们简单工厂模式的例子。基于你的需求，你可以从木门店得到一扇木门，从铁门店得到一扇铁门，或者从塑料门店得到一扇塑料门。而且你需要一个有不同专长的人来安装这扇门，比如一个木匠来安木门，焊工来安铁门等。正如你看的，门和安装工有依赖性，木门需要木匠，铁门需要焊工等。

白话
> 一个制造工厂的工厂；一个工厂把独立但是相关／有依赖性的工厂进行分类，但是不需要给出具体的类。
  
维基百科
> The abstract factory pattern provides a way to encapsulate a group of individual factories that have a common theme without specifying their concrete classes

**代码例子**

翻译上面门的例子。首先我们有了门 `Door` 的接口和一些实现

```php
interface Door {
    public function getDescription();
}

class WoodenDoor implements Door {
    public function getDescription() {
        echo 'I am a wooden door';
    }
}

class IronDoor implements Door {
    public function getDescription() {
        echo 'I am an iron door';
    }
}
```
然后我们有了每种门的安装专家

```php
interface DoorFittingExpert {
    public function getDescription();
}

class Welder implements DoorFittingExpert {
    public function getDescription() {
        echo 'I can only fit iron doors';
    }
}

class Carpenter implements DoorFittingExpert {
    public function getDescription() {
        echo 'I can only fit wooden doors';
    }
}
```

现在我们有了抽象工厂来创建全部相关的对象，即木门工厂制造木门和木门安装专家，铁门工厂制造铁门和铁门安装专家
```php
interface DoorFactory {
    public function makeDoor() : Door;
    public function makeFittingExpert() : DoorFittingExpert;
}

// 木头工厂返回木门和木匠
class WoodenDoorFactory implements DoorFactory {
    public function makeDoor() : Door {
        return new WoodenDoor();
    }

    public function makeFittingExpert() : DoorFittingExpert{
        return new Carpenter();
    }
}

// 铁门工厂返回铁门和对应安装专家
class IronDoorFactory implements DoorFactory {
    public function makeDoor() : Door {
        return new IronDoor();
    }

    public function makeFittingExpert() : DoorFittingExpert{
        return new Welder();
    }
}
```
然后可以这样使用
```php
$woodenFactory = new WoodenDoorFactory();

$door = $woodenFactory->makeDoor();
$expert = $woodenFactory->makeFittingExpert();

$door->getDescription();  // 输出: I am a wooden door
$expert->getDescription(); // 输出: I can only fit wooden doors

// 铁门工厂也一样
$ironFactory = new IronDoorFactory();

$door = $ironFactory->makeDoor();
$expert = $ironFactory->makeFittingExpert();

$door->getDescription();  // 输出: I am an iron door
$expert->getDescription(); // 输出: I can only fit iron doors
```

如你所见，木门工厂包含了木匠 `carpenter` 和木门 `wooden door` 而铁门工厂包含了铁门 `iron door` 和焊工 `welder`。因此我们可以确保每扇制造出来的门不会带上错误的安装工。

**何时使用？**

当创建逻辑不那么简单，而且相互之间有依赖时

👷 建造者模式
--------------------------------------------
现实例子
> 想象你在麦当劳，你要一个“巨无霸”，他们马上就给你了，没有疑问，这是简单工厂的逻辑。但如果创建逻辑包含更多步骤。比如你想要一个自定义赛百味套餐，你有多种选择来制作汉堡，例如你要哪种面包？你要哪种调味酱？你要哪种奶酪？等。这种情况就需要建造者模式来处理。

白话
> 让你能创建不同特点的对象而避免构造函数污染。当一个对象都多种特点的时候比较实用。或者在创造逻辑里有许多步骤的时候。
 
维基百科
> The builder pattern is an object creation software design pattern with the intentions of finding a solution to the telescoping constructor anti-pattern.

话虽如此，让我写一点关于伸缩构造函数反面模式。在某些时候，我们都看过下面这样的构造函数
 
```php
public function __construct($size, $cheese = true, $pepperoni = true, $tomato = false, $lettuce = true) {
}
```

如你所见；构造函数参数的数量马上就要失去控制，而且梳理参数也会变得困难。而且如果你将来想要增加更多选项，参数也会继续增加。这就叫做伸缩构造函数反面模式。

**代码例子**

正常的做法是使用创建者模式。首先我们有了要做的汉堡

```php
class Burger {
    protected $size;

    protected $cheese = false;
    protected $pepperoni = false;
    protected $lettuce = false;
    protected $tomato = false;
    
    public function __construct(BurgerBuilder $builder) {
        $this->size = $builder->size;
        $this->cheese = $builder->cheese;
        $this->pepperoni = $builder->pepperoni;
        $this->lettuce = $builder->lettuce;
        $this->tomato = $builder->tomato;
    }
}
```

然后我们有了制作者

```php
class BurgerBuilder {
    public $size;

    public $cheese = false;
    public $pepperoni = false;
    public $lettuce = false;
    public $tomato = false;

    public function __construct(int $size) {
        $this->size = $size;
    }
    
    public function addPepperoni() {
        $this->pepperoni = true;
        return $this;
    }
    
    public function addLettuce() {
        $this->lettuce = true;
        return $this;
    }
    
    public function addCheese() {
        $this->cheese = true;
        return $this;
    }
    
    public function addTomato() {
        $this->tomato = true;
        return $this;
    }
    
    public function build() : Burger {
        return new Burger($this);
    }
}
```
然后可以这样使用

```php
$burger = (new BurgerBuilder(14))
                    ->addPepperoni()
                    ->addLettuce()
                    ->addTomato()
                    ->build();
```

**何时使用？**

当对象有多种特性而要避免构造函数变长。和工厂模式的核心区别是；当创建过程只有一个步骤的时候使用工厂模式，而当创建过程有多个步骤的时候使用创造者模式。

🐑 原型模式
------------
现实例子
> 记得多利吗？那只克隆羊！不要在意细节，现在的重点是克隆

白话
> 通过克隆已有的对象来创建新对象。

维基百科
> The prototype pattern is a creational design pattern in software development. It is used when the type of objects to create is determined by a prototypical instance, which is cloned to produce new objects.

长话短说，它让你创建已有对象的拷贝，然后修改到你要的样子，而不是从头开始建造。

**代码例子**

在 PHP 里，简单的使用 `clone` 就可以了
  
```php
class Sheep {
    protected $name;
    protected $category;

    public function __construct(string $name, string $category = 'Mountain Sheep') {
        $this->name = $name;
        $this->category = $category;
    }
    
    public function setName(string $name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function setCategory(string $category) {
        $this->category = $category;
    }

    public function getCategory() {
        return $this->category;
    }
}
```
然后它可以被这样克隆
```php
$original = new Sheep('Jolly');
echo $original->getName(); // Jolly
echo $original->getCategory(); // Mountain Sheep

// Clone and modify what is required
$cloned = clone $original;
$cloned->setName('Dolly');
echo $cloned->getName(); // Dolly
echo $cloned->getCategory(); // Mountain sheep
```

你也可以使用魔法方法 `__clone` 来改变克隆逻辑。

**何时使用？**

当一个对象需要跟已有的对象相似，或者当创造过程比起克隆来太昂贵时。

💍 单例模式
------------
现实例子
> 一个国家同一时间只能有一个总统。当使命召唤的时候，这个总统要采取行动。这里的总统就是单例的。

白话
> 确保指定的类只生成一个对象。

维基百科
> In software engineering, the singleton pattern is a software design pattern that restricts the instantiation of a class to one object. This is useful when exactly one object is needed to coordinate actions across the system.

单例模式其实被看作一种反面模式，应该避免过度使用。它不一定不好，而且确有一些有效的用例，但是应该谨慎使用，因为它在你的应用里引入了全局状态，在一个地方改变，会影响其他地方。而且很难 debug 。另一个坏处是它让你的代码紧耦合，而且很难仿制单例。

**代码例子**

要创建一个单例，先让构造函数私有，不能克隆，不能继承，然后创造一个静态变量来保存这个实例
```php
final class President {
    private static $instance;

    private function __construct() {
        // Hide the constructor
    }
    
    public static function getInstance() : President {
        if (!self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    private function __clone() {
        // Disable cloning
    }
    
    private function __wakeup() {
        // Disable unserialize
    }
}
```
然后要使用的话
```php
$president1 = President::getInstance();
$president2 = President::getInstance();

var_dump($president1 === $president2); // true
```

结构型模式
==========================
白话
> 结构型模式更关注对象的组合，换句话说，实体如何彼此使用。或者说，它们帮助解答“如何建造软件组件？”

维基百科
> In software engineering, structural design patterns are design patterns that ease the design by identifying a simple way to realize relationships between entities.
  
 * [适配器模式 Adapter](#-适配器模式)
 * [桥接模式 Bridge](#-桥接模式)
 * [组合模式 Composite](#-组合模式)
 * [装饰器模式 Decorator](#-装饰器模式)
 * [门面模式 Facade](#-门面模式)
 * [享元模式 Flyweight](#-享元模式)
 * [代理模式 Proxy](#-代理模式)

🔌 适配器模式
-------
现实例子
> 假设在你的存储卡里有一些照片，你要把它们传到电脑。为了传输，你需要一个兼容电脑端口的适配器来连接存储卡和电脑。在这里，读卡器就是一个适配器。
> 另一个例子是电源转换器；一个三脚的插口不能插到两口的插座上，它需要一个电源转换器来兼容两口的插座。
> 还有一个例子是翻译将一个人说的话翻译给另一个人。

白话
> 适配器模式让你封装一个不兼容的对象到一个适配器，来兼容其他类。

维基百科
> In software engineering, the adapter pattern is a software design pattern that allows the interface of an existing class to be used as another interface. It is often used to make existing classes work with others without modifying their source code.

**代码例子**

假设一个猎人狩猎狮子的游戏。

首先我们有了一个接口狮子 `Lion` 来实现所有种类的狮子

```php
interface Lion {
    public function roar();
}

class AfricanLion implements Lion {
    public function roar() {}
}

class AsianLion implements Lion {
    public function roar() {}
}
```
以及猎人需要狩猎任何狮子 `Lion` 接口的实现。
```php
class Hunter {
    public function hunt(Lion $lion) {
    }
}
```

现在我们不得不在游戏里加一个野狗 `WildDog` ，猎人也能狩猎它。但是我们不能直接这么做，因为狗有不同的接口。为了兼容我们的猎人，我们不得不创建一个兼容的适配器
 
```php
// This needs to be added to the game
class WildDog {
    public function bark() {}
}

// Adapter around wild dog to make it compatible with our game
class WildDogAdapter implements Lion {
    protected $dog;

    public function __construct(WildDog $dog) {
        $this->dog = $dog;
    }
    
    public function roar() {
        $this->dog->bark();
    }
}
```
现在野狗 `WildDog` 可以在游戏里使用了，通过野狗适配器 `WildDogAdapter`.

```php
$wildDog = new WildDog();
$wildDogAdapter = new WildDogAdapter($wildDog);

$hunter = new Hunter();
$hunter->hunt($wildDogAdapter);
```

🚡 桥接模式
------
现实例子
> 假设你有一个包含很多网页的网站，你想要用户可以改变主题。你会怎么做？创建每个页面对应每个主题的拷备，还是只是创建不同的主题，然后根据用户的喜好来加载它们？桥接模式让你能做到后者。

![With and without the bridge pattern](https://cloud.githubusercontent.com/assets/11269635/23065293/33b7aea0-f515-11e6-983f-98823c9845ee.png)

白话
> 桥接模式倾向构造而非继承。实现细节被从一个层推送到另一个对象的另一层。

维基百科
> The bridge pattern is a design pattern used in software engineering that is meant to "decouple an abstraction from its implementation so that the two can vary independently"

**代码例子**

翻译我们上面的网页例子。这里是网页 `WebPage` 层

```php
interface WebPage {
    public function __construct(Theme $theme);
    public function getContent();
}

class About implements WebPage {
    protected $theme;
    
    public function __construct(Theme $theme) {
        $this->theme = $theme;
    }
    
    public function getContent() {
        return "About page in " . $this->theme->getColor();
    }
}

class Careers implements WebPage {
   protected $theme;
   
   public function __construct(Theme $theme) {
       $this->theme = $theme;
   }
   
   public function getContent() {
       return "Careers page in " . $this->theme->getColor();
   } 
}
```
以及主题层
```php
interface Theme {
    public function getColor();
}

class DarkTheme implements Theme {
    public function getColor() {
        return 'Dark Black';
    }
}
class LightTheme implements Theme {
    public function getColor() {
        return 'Off white';
    }
}
class AquaTheme implements Theme {
    public function getColor() {
        return 'Light blue';
    }
}
```
两个层的互动
```php
$darkTheme = new DarkTheme();

$about = new About($darkTheme);
$careers = new Careers($darkTheme);

echo $about->getContent(); // "About page in Dark Black";
echo $careers->getContent(); // "Careers page in Dark Black";
```

🌿 组合模式
-----------------

现实例子
> 任何组织都是由员工组成。每个员工都有相同的特征，即一笔薪水，一些责任，可能需要向别人汇报，可能有一些下属等。

白话
> 组合模式让调用者可以用统一的模式对待不同的对象。

维基百科
> In software engineering, the composite pattern is a partitioning design pattern. The composite pattern describes that a group of objects is to be treated in the same way as a single instance of an object. The intent of a composite is to "compose" objects into tree structures to represent part-whole hierarchies. Implementing the composite pattern lets clients treat individual objects and compositions uniformly.

**代码例子**

拿上面的员工为例。下面是不同的员工类型

```php

interface Employee {
    public function __construct(string $name, float $salary);
    public function getName() : string;
    public function setSalary(float $salary);
    public function getSalary() : float;
    public function getRoles()  : array;
}

class Developer implements Employee {

    protected $salary;
    protected $name;

    public function __construct(string $name, float $salary) {
        $this->name = $name;
        $this->salary = $salary;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setSalary(float $salary) {
        $this->salary = $salary;
    }

    public function getSalary() : float {
        return $this->salary;
    }

    public function getRoles() : array {
        return $this->roles;
    }
}

class Designer implements Employee {

    protected $salary;
    protected $name;

    public function __construct(string $name, float $salary) {
        $this->name = $name;
        $this->salary = $salary;
    }

    public function getName() : string {
        return $this->name;
    }

    public function setSalary(float $salary) {
        $this->salary = $salary;
    }

    public function getSalary() : float {
        return $this->salary;
    }

    public function getRoles() : array {
        return $this->roles;
    }
}
```

下面是一个由不同类型员工组成的组织

```php
class Organization {
    
    protected $employees;

    public function addEmployee(Employee $employee) {
        $this->employees[] = $employee;
    }

    public function getNetSalaries() : float {
        $netSalary = 0;

        foreach ($this->employees as $employee) {
            $netSalary += $employee->getSalary();
        }

        return $netSalary;
    }
}
```

然后可以这样使用

```php
// 准备员工
$john = new Developer('John Doe', 12000);
$jane = new Designer('Jane', 10000);

// 把他们加到组织里去
$organization = new Organization();
$organization->addEmployee($john);
$organization->addEmployee($jane);

echo "Net salaries: " . $organization->getNetSalaries(); // Net Salaries: 22000
```

☕ 装饰器模式
-------------

现实例子

> 想象你开一家汽车服务店，提供各种服务。现在你怎么计算收费？你选择一个服务，然后不断把价格加到已选服务的价格里，直到得到总价。这里，每种服务就是一个装饰器。

白话
> 装饰器模式让你能在运行时动态地改变一个对象的表现，通过把它们封装到一个装饰器类。

维基百科
> In object-oriented programming, the decorator pattern is a design pattern that allows behavior to be added to an individual object, either statically or dynamically, without affecting the behavior of other objects from the same class. The decorator pattern is often useful for adhering to the Single Responsibility Principle, as it allows functionality to be divided between classes with unique areas of concern.

**代码例子**

让我们以咖啡为例。首先我们有一个咖啡接口的简单实现

```php
interface Coffee {
    public function getCost();
    public function getDescription();
}

class SimpleCoffee implements Coffee {

    public function getCost() {
        return 10;
    }

    public function getDescription() {
        return 'Simple coffee';
    }
}
```
我们想要让代码可扩展，以在需要的时候改变选项。让我们增加一些扩展（装饰器）
```php
class MilkCoffee implements Coffee {
    
    protected $coffee;

    public function __construct(Coffee $coffee) {
        $this->coffee = $coffee;
    }

    public function getCost() {
        return $this->coffee->getCost() + 2;
    }

    public function getDescription() {
        return $this->coffee->getDescription() . ', milk';
    }
}

class WhipCoffee implements Coffee {

    protected $coffee;

    public function __construct(Coffee $coffee) {
        $this->coffee = $coffee;
    }

    public function getCost() {
        return $this->coffee->getCost() + 5;
    }

    public function getDescription() {
        return $this->coffee->getDescription() . ', whip';
    }
}

class VanillaCoffee implements Coffee {

    protected $coffee;

    public function __construct(Coffee $coffee) {
        $this->coffee = $coffee;
    }

    public function getCost() {
        return $this->coffee->getCost() + 3;
    }

    public function getDescription() {
        return $this->coffee->getDescription() . ', vanilla';
    }
}

```

现在让我们生成咖啡

```php
$someCoffee = new SimpleCoffee();
echo $someCoffee->getCost(); // 10
echo $someCoffee->getDescription(); // Simple Coffee

$someCoffee = new MilkCoffee($someCoffee);
echo $someCoffee->getCost(); // 12
echo $someCoffee->getDescription(); // Simple Coffee, milk

$someCoffee = new WhipCoffee($someCoffee);
echo $someCoffee->getCost(); // 17
echo $someCoffee->getDescription(); // Simple Coffee, milk, whip

$someCoffee = new VanillaCoffee($someCoffee);
echo $someCoffee->getCost(); // 20
echo $someCoffee->getDescription(); // Simple Coffee, milk, whip, vanilla
```

📦 门面模式
----------------

现实例子
> 你怎么打开电脑？你会说“按电源键”！你这么认为是因为你在用电脑外部提供的简单接口，而在内部，它必须做很做工作来实现这件事。这个复杂子系统的简单接口就是一个门面。

白话
> 门面模式提供了一个复杂子系统的简单接口。

维基百科
> A facade is an object that provides a simplified interface to a larger body of code, such as a class library.

**代码例子**

拿上面电脑为例。下面是电脑类

```php
class Computer {

    public function getElectricShock() {
        echo "Ouch!";
    }

    public function makeSound() {
        echo "Beep beep!";
    }

    public function showLoadingScreen() {
        echo "Loading..";
    }

    public function bam() {
        echo "Ready to be used!";
    }

    public function closeEverything() {
        echo "Bup bup bup buzzzz!";
    }

    public function sooth() {
        echo "Zzzzz";
    }

    public function pullCurrent() {
        echo "Haaah!";
    }
}
```
下面是门面
```php
class ComputerFacade
{
    protected $computer;

    public function __construct(Computer $computer) {
        $this->computer = $computer;
    }

    public function turnOn() {
        $this->computer->getElectricShock();
        $this->computer->makeSound();
        $this->computer->showLoadingScreen();
        $this->computer->bam();
    }

    public function turnOff() {
        $this->computer->closeEverything();
        $this->computer->pullCurrent();
        $this->computer->sooth();
    }
}
```
如何使用门面
```php
$computer = new ComputerFacade(new Computer());
$computer->turnOn(); // Ouch! Beep beep! Loading.. Ready to be used!
$computer->turnOff(); // Bup bup buzzz! Haah! Zzzzz
```

🍃 享元模式
---------

现实例子
> 你在小店里喝过茶吗？他们经常比你要的多做几杯，把剩下的留给别的客人，以此来省资源，比如煤气。享元模式就是以上的体现，即分享。

白话
> 通过尽可能分享相似的对象，来将内存使用或计算开销降到最低。

维基百科
> In computer programming, flyweight is a software design pattern. A flyweight is an object that minimizes memory use by sharing as much data as possible with other similar objects; it is a way to use objects in large numbers when a simple repeated representation would use an unacceptable amount of memory.

**代码例子**

翻译上面的茶的例子。首先我们有了茶的类型和生成器

```php
// 任何被缓存的东西都被叫做享元。 
// 这里茶的类型就是享元。
class KarakTea {
}

// 像工厂一样工作，保存茶
class TeaMaker {
    protected $availableTea = [];

    public function make($preference) {
        if (empty($this->availableTea[$preference])) {
            $this->availableTea[$preference] = new KarakTea();
        }

        return $this->availableTea[$preference];
    }
}
```

下面是我们的茶吧 `TeaShop` ，接单和提供服务

```php
class TeaShop {
    
    protected $orders;
    protected $teaMaker;

    public function __construct(TeaMaker $teaMaker) {
        $this->teaMaker = $teaMaker;
    }

    public function takeOrder(string $teaType, int $table) {
        $this->orders[$table] = $this->teaMaker->make($teaType);
    }

    public function serve() {
        foreach($this->orders as $table => $tea) {
            echo "Serving tea to table# " . $table;
        }
    }
}
```
然后可以这样使用

```php
$teaMaker = new TeaMaker();
$shop = new TeaShop($teaMaker);

$shop->takeOrder('less sugar', 1);
$shop->takeOrder('more milk', 2);
$shop->takeOrder('without sugar', 5);

$shop->serve();
// Serving tea to table# 1
// Serving tea to table# 2
// Serving tea to table# 5
```

🎱 代理模式
-------------------
现实例子
> 你有没有用过门卡来通过一扇门？有多种方式来打开那扇门，即它可以被门卡打开，或者按开门按钮打开。这扇门的主要功能是开关，但在顶层增加了一个代理来增加其他功能。下面的例子能更好的说明。

白话
> 使用代理模式，一个类表现出了另一个类的功能。

维基百科
> A proxy, in its most general form, is a class functioning as an interface to something else. A proxy is a wrapper or agent object that is being called by the client to access the real serving object behind the scenes. Use of the proxy can simply be forwarding to the real object, or can provide additional logic. In the proxy extra functionality can be provided, for example caching when operations on the real object are resource intensive, or checking preconditions before operations on the real object are invoked.

**代码例子**

拿上面安全门为例。首先我们有了门的接口和实现

```php
interface Door {
    public function open();
    public function close();
}

class LabDoor implements Door {
    public function open() {
        echo "Opening lab door";
    }

    public function close() {
        echo "Closing the lab door";
    }
}
```
然后下面是一个代理来安保任何我们要的门
```php
class Security {
    protected $door;

    public function __construct(Door $door) {
        $this->door = $door;
    }

    public function open($password) {
        if ($this->authenticate($password)) {
            $this->door->open();
        } else {
            echo "Big no! It ain't possible.";
        }
    }

    public function authenticate($password) {
        return $password === '$ecr@t';
    }

    public function close() {
        $this->door->close();
    }
}
```
然后可以这样使用
```php
$door = new Security(new LabDoor());
$door->open('invalid'); // Big no! It ain't possible.

$door->open('$ecr@t'); // Opening lab door
$door->close(); // Closing lab door
```
另一个例子是一些数据映射的实现。比如，我最近用这个模式给 MongoDB 做了一个数据映射器 ODM (Object Data Mapper)，我用魔术方法 `__call()` 给 mongo 类做了一个代理。所有执行的方法都被代理到原始的 mongo 类，返回收到的结果。但是在 `find` 或 `findOne` 的情况，数据被映射到对应的对象，这个对象会被返回，而不是 `Cursor`。

行为型模式
==========================

白话
> 它关注对象间的责任分配。它们和结构型模式的区别是它们不止明确指明结构，而且指出了它们之间传递/交流的信息的形式。或者换句或说，它们帮助回答了“如何确定软件组件的行为？”

维基百科
> In software engineering, behavioral design patterns are design patterns that identify common communication patterns between objects and realize these patterns. By doing so, these patterns increase flexibility in carrying out this communication.

* [责任链模式 Chain of Responsibility](#-责任链模式)
* [命令模式 Command](#-命令模式)
* [迭代器模式 Iterator](#-迭代器模式)
* [中介模式 Mediator](#-中介模式)
* [备忘录模式 Memento](#-备忘录模式)
* [观察者模式 Observer](#-观察者模式)
* [访问者模式 Visitor](#-访问者模式)
* [策略模式 Strategy](#-策略模式)
* [状态模式 State](#-状态模式)
* [模板模式 Template Method](#-模板模式)

🔗 责任链模式
-----------------------

现实例子
> 比如，有三个支付方式 (`A`, `B` 和 `C`) 安装在你的账户里；每种方式都有不同额度。`A` 有 100 元， `B` 有 300 元，以及 `C` 有 1000 元，选择支付方式的顺序是 `A` 然后 `B` 然后 `C`。你要买一些价值 210 元的东西。使用责任链模式，首先账户 `A` 会被检查是否能够支付，如果是，支付会被执行而链子终止。如果否，请求会转移到账户 `B`，检查额度，如果是，链子终止，否则请求继续转移直到找到合适的执行者。这里 `A`，`B` 和 `C` 是链接里的环节，它们合起来就是责任链。

白话
> 它构造了一个对象的链。请求进入一端，然后从一个对象到另一个对象直到找到合适的执行者。

维基百科
> In object-oriented design, the chain-of-responsibility pattern is a design pattern consisting of a source of command objects and a series of processing objects. Each processing object contains logic that defines the types of command objects that it can handle; the rest are passed to the next processing object in the chain.

**代码例子**

翻译上面的账户例子。首先我们有了一个基本账户，包含把账户连接起来的逻辑。以及一些账户

```php
abstract class Account {
    protected $successor;
    protected $balance;

    public function setNext(Account $account) {
        $this->successor = $account;
    }
    
    public function pay(float $amountToPay) {
        if ($this->canPay($amountToPay)) {
            echo sprintf('Paid %s using %s' . PHP_EOL, $amountToPay, get_called_class());
        } else if ($this->successor) {
            echo sprintf('Cannot pay using %s. Proceeding ..' . PHP_EOL, get_called_class());
            $this->successor->pay($amountToPay);
        } else {
            throw Exception('None of the accounts have enough balance');
        }
    }
    
    public function canPay($amount) : bool {
        return $this->balance >= $amount;
    }
}

class Bank extends Account {
    protected $balance;

    public function __construct(float $balance) {
        $this->balance = $balance;
    }
}

class Paypal extends Account {
    protected $balance;

    public function __construct(float $balance) {
        $this->balance = $balance;
    }
}

class Bitcoin extends Account {
    protected $balance;

    public function __construct(float $balance) {
        $this->balance = $balance;
    }
}
```

现在我们用上面定义的环节（即银行 Bank，贝宝 Paypal，比特币 Bitcoin）准备链

```php
// 我们准备下面这样的链
//      $bank->$paypal->$bitcoin
//
// 首选银行 bank
//      如果银行 bank 不能支付则选择贝宝 paypal
//      如果贝宝 paypal 不能支付则选择比特币 bit coin

$bank = new Bank(100);          // 银行 Bank 有余额 100
$paypal = new Paypal(200);      // 贝宝 Paypal 有余额 200
$bitcoin = new Bitcoin(300);    // 比特币 Bitcoin 有余额 300

$bank->setNext($paypal);
$paypal->setNext($bitcoin);

// 我们尝试用首选项支付，即银行 bank
$bank->pay(259);

// 输出将会是
// ==============
// Cannot pay using bank. Proceeding ..
// Cannot pay using paypal. Proceeding ..: 
// Paid 259 using Bitcoin!
```

👮 命令模式
-------

现实例子
> 一个普遍的例子是你在餐馆点餐。你 (即调用者 `Client`) 要求服务员 (即调用器 `Invoker`) 端来一些食物 (即命令 `Command`)，而服务员只是简单的把命令传达给知道怎么做菜的厨师 (即接收者 `Receiver`)。另一个例子是你 (即调用者 `Client`) 打开 (即命令 `Command`) 电视 (即接收者 `Receiver`)，通过使用遥控 (调用器 `Invoker`).

白话
> 允许你封装对象的功能。此模式的核心思想是分离调用者和接收者。

维基百科
> In object-oriented programming, the command pattern is a behavioral design pattern in which an object is used to encapsulate all information needed to perform an action or trigger an event at a later time. This information includes the method name, the object that owns the method and values for the method parameters.

**代码例子**

首先我们有一个接收者，包含了每一个可执行的功能的实现
```php
// Receiver
class Bulb {
    public function turnOn() {
        echo "Bulb has been lit";
    }
    
    public function turnOff() {
        echo "Darkness!";
    }
}
```
然后下面是每个命令执行的接口，之后我们就有了一个命令的集合
```php
interface Command {
    public function execute();
    public function undo();
    public function redo();
}

// Command
class TurnOn implements Command {
    protected $bulb;
    
    public function __construct(Bulb $bulb) {
        $this->bulb = $bulb;
    }
    
    public function execute() {
        $this->bulb->turnOn();
    }
    
    public function undo() {
        $this->bulb->turnOff();
    }
    
    public function redo() {
        $this->execute();
    }
}

class TurnOff implements Command {
    protected $bulb;
    
    public function __construct(Bulb $bulb) {
        $this->bulb = $bulb;
    }
    
    public function execute() {
        $this->bulb->turnOff();
    }
    
    public function undo() {
        $this->bulb->turnOn();
    }
    
    public function redo() {
        $this->execute();
    }
}
```
然后我们有了一个执行器 `Invoker`，调用者可以通过它执行命令
```php
// Invoker
class RemoteControl {
    
    public function submit(Command $command) {
        $command->execute();
    }
}
```
最后我们看看可以如何使用
```php
$bulb = new Bulb();

$turnOn = new TurnOn($bulb);
$turnOff = new TurnOff($bulb);

$remote = new RemoteControl();
$remote->submit($turnOn); // Bulb has been lit!
$remote->submit($turnOff); // Darkness!
```

命令模式也可以用来实现一个基础系统的事务。当你要一直在执行命令后马上维护日志。如果命令被正确执行，一切正常，否则沿日志迭代，一直对每个已执行的命令执行撤销 `undo` 。

➿ 迭代器模式
--------

现实例子
> 老式调频收音机是迭代器的好例子，用户可以在一些频道开始，然后使用前进或后退按钮来浏览每个频道。或者以 MP3 播放器或电视机为例，你可以按前进或后退按钮来浏览连续的频道。或者说，它们都提供了迭代连续的频道，歌曲或广播的接口。  

白话
> 它提供了一种方式来获得对象的元素，而不必暴露底层实现。

维基百科
> In object-oriented programming, the iterator pattern is a design pattern in which an iterator is used to traverse a container and access the container's elements. The iterator pattern decouples algorithms from containers; in some cases, algorithms are necessarily container-specific and thus cannot be decoupled.

**代码例子**

在 PHP 里，用 SPL (标准 PHP 库) 实现非常简单。翻译上面的广播例子。首先我们有了广播台 `RadioStation`

```php
class RadioStation {
    protected $frequency;

    public function __construct(float $frequency) {
        $this->frequency = $frequency;    
    }
    
    public function getFrequency() : float {
        return $this->frequency;
    }
}
```
下面是我们的迭代器

```php
use Countable;
use Iterator;

class StationList implements Countable, Iterator {
    /** @var RadioStation[] $stations */
    protected $stations = [];
    
    /** @var int $counter */
    protected $counter;
    
    public function addStation(RadioStation $station) {
        $this->stations[] = $station;
    }
    
    public function removeStation(RadioStation $toRemove) {
        $toRemoveFrequency = $toRemove->getFrequency();
        $this->stations = array_filter($this->stations, function (RadioStation $station) use ($toRemoveFrequency) {
            return $station->getFrequency() !== $toRemoveFrequency;
        });
    }
    
    public function count() : int {
        return count($this->stations);
    }
    
    public function current() : RadioStation {
        return $this->stations[$this->counter];
    }
    
    public function key() {
        return $this->counter;
    }
    
    public function next() {
        $this->counter++;
    }
    
    public function rewind() {
        $this->counter = 0;
    }
    
    public function valid(): bool
    {
        return isset($this->stations[$this->counter]);
    }
}
```
然后可以这样使用
```php
$stationList = new StationList();

$stationList->addStation(new Station(89));
$stationList->addStation(new Station(101));
$stationList->addStation(new Station(102));
$stationList->addStation(new Station(103.2));

foreach($stationList as $station) {
    echo $station->getFrequency() . PHP_EOL;
}

$stationList->removeStation(new Station(89)); // Will remove station 89
```

👽 中介模式
========

现实例子
> 一个普遍的例子是当你用手机和别人谈话，你和别人中间隔了一个电信网，你的声音穿过它而不是直接发出去。在这里，电信网就是一个中介。

白话
> 中介模式增加了一个第三方对象（叫做中介）来控制两个对象（叫做同事）间的交互。它帮助减少类彼此之间交流的耦合度。因为它们现在不需要知道彼此的实现。 

维基百科
> In software engineering, the mediator pattern defines an object that encapsulates how a set of objects interact. This pattern is considered to be a behavioral pattern due to the way it can alter the program's running behavior.

**代码例子**

下面是一个最简单的聊天室（即中介）的例子，用户（即同事）彼此发送信息。

首先，我们有一个中介，即聊天室

```php
// 中介
class ChatRoom implements ChatRoomMediator {
    public function showMessage(User $user, string $message) {
        $time = date('M d, y H:i');
        $sender = $user->getName();

        echo $time . '[' . $sender . ']:' . $message;
    }
}
```

然后我们有用户，即同事
```php
class User {
    protected $name;
    protected $chatMediator;

    public function __construct(string $name, ChatRoomMediator $chatMediator) {
        $this->name = $name;
        $this->chatMediator = $chatMediator;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function send($message) {
        $this->chatMediator->showMessage($this, $message);
    }
}
```
然后是使用
```php
$mediator = new ChatRoom();

$john = new User('John Doe', $mediator);
$jane = new User('Jane Doe', $mediator);

$john->send('Hi there!');
$jane->send('Hey!');

// 输出将会是
// Feb 14, 10:58 [John]: Hi there!
// Feb 14, 10:58 [Jane]: Hey!
```

💾 备忘录模式
-------
现实例子
> 以计算器（即发起人）为例，无论什么时候你执行一些计算，最后的计算都会保存在内存（即备忘）里，这样你就能返回到这里，并且用一些按钮（即守护者）恢复。 

白话
> 备忘录模式捕捉和保存当前对象的状态，然后用一种平滑的方式恢复。

维基百科
> The memento pattern is a software design pattern that provides the ability to restore an object to its previous state (undo via rollback).

当你要提供撤销方法时异常实用。

**代码例子**

让我们那编辑器为例，编辑器一直保存状态，在你需要的时候可以恢复。

首先下面是我们的备忘录对象，可以保存编辑器状态

```php
class EditorMemento {
    protected $content;
    
    public function __construct(string $content) {
        $this->content = $content;
    }
    
    public function getContent() {
        return $this->content;
    }
}
```

然后是我们的编辑器，即发起者，来使用备忘录对象

```php
class Editor {
    protected $content = '';
    
    public function type(string $words) {
        $this->content = $this->content . ' ' . $words;
    }
    
    public function getContent() {
        return $this->content;
    }
    
    public function save() {
        return new EditorMemento($this->content);
    }
    
    public function restore(EditorMemento $memento) {
        $this->content = $memento->getContent();
    }
}
```

然后可以这样使用

```php
$editor = new Editor();

// 输入一些东西
$editor->type('This is the first sentence.');
$editor->type('This is second.');

// 保存状态到：This is the first sentence. This is second.
$saved = $editor->save();

// 输入些别的东西
$editor->type('And this is third.');

// 输出: Content before Saving
echo $editor->getContent(); // This is the first sentence. This is second. And this is third.

// 恢复到上次保存状态
$editor->restore($saved);

$editor->getContent(); // This is the first sentence. This is second.
```

😎 观察者模式
--------
现实例子
> 一个好的例子是求职者，他们订阅了一些工作发布网站，当有合适的工作机会时，他们会收到提醒。   

白话
> 定义了一个对象间的依赖，这样无论何时一个对象改变了状态，其他所有依赖者会收到提醒。

维基百科
> The observer pattern is a software design pattern in which an object, called the subject, maintains a list of its dependents, called observers, and notifies them automatically of any state changes, usually by calling one of their methods.

**代码例子**

翻译上面的例子。首先我们有需要收到工作发布提醒的求职者
```php
class JobPost {
    protected $title;
    
    public function __construct(string $title) {
        $this->title = $title;
    }
    
    public function getTitle() {
        return $this->title;
    }
}

class JobSeeker implements Observer {
    protected $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function onJobPosted(JobPost $job) {
        // Do something with the job posting
        echo 'Hi ' . $this->name . '! New job posted: '. $job->getTitle();
    }
}
```
下面是求职者订阅的工作信息
```php
class JobPostings implements Observable {
    protected $observers = [];
    
    protected function notify(JobPost $jobPosting) {
        foreach ($this->observers as $observer) {
            $observer->onJobPosted($jobPosting);
        }
    }
    
    public function attach(Observer $observer) {
        $this->observers[] = $observer;
    }
    
    public function addJob(JobPost $jobPosting) {
        $this->notify($jobPosting);
    }
}
```
然后可以这样使用
```php
// 创建订阅者
$johnDoe = new JobSeeker('John Doe');
$janeDoe = new JobSeeker('Jane Doe');

// 创建发布者，绑定订阅者
$jobPostings = new JobPostings();
$jobPostings->attach($johnDoe);
$jobPostings->attach($janeDoe);

// 添加一个工作，看订阅者是否收到通知
$jobPostings->addJob(new JobPost('Software Engineer'));

// 输出
// Hi John Doe! New job posted: Software Engineer
// Hi Jane Doe! New job posted: Software Engineer
```

🏃 访问者模式
-------
现实例子
> 假设一些人访问迪拜。他们需要一些方式（即签证）来进入迪拜。抵达后，他们可以去迪拜的任何地方，而不用申请许可或者跑腿；他们知道的地方都可以去。访问者模式可以让你这样做，它帮你添加可以访问的地方，然后他们可以访问尽可能多的地方而不用到处跑腿。

白话
> 访问者模式可以让你添加更多的操作到对象，而不用改变他们。
    
维基百科
> In object-oriented programming and software engineering, the visitor design pattern is a way of separating an algorithm from an object structure on which it operates. A practical result of this separation is the ability to add new operations to existing object structures without modifying those structures. It is one way to follow the open/closed principle.

**代码例子**

让我们以动物园模拟器为例，在里面我们有一些动物，我们必须让他们叫。让我们用访问者模式来翻译

```php
// 被访者
interface Animal {
    public function accept(AnimalOperation $operation);
}

// 访问者
interface AnimalOperation {
    public function visitMonkey(Monkey $monkey);
    public function visitLion(Lion $lion);
    public function visitDolphin(Dolphin $dolphin);
}
```
Then we have our implementations for the animals
```php
class Monkey implements Animal {
    
    public function shout() {
        echo 'Ooh oo aa aa!';
    }

    public function accept(AnimalOperation $operation) {
        $operation->visitMonkey($this);
    }
}

class Lion implements Animal {
    public function roar() {
        echo 'Roaaar!';
    }
    
    public function accept(AnimalOperation $operation) {
        $operation->visitLion($this);
    }
}

class Dolphin implements Animal {
    public function speak() {
        echo 'Tuut tuttu tuutt!';
    }
    
    public function accept(AnimalOperation $operation) {
        $operation->visitDolphin($this);
    }
}
```
实现我们的访问者
```php
class Speak implements AnimalOperation {
    public function visitMonkey(Monkey $monkey) {
        $monkey->shout();
    }
    
    public function visitLion(Lion $lion) {
        $lion->roar();
    }
    
    public function visitDolphin(Dolphin $dolphin) {
        $dolphin->speak();
    }
}
```

然后可以这样使用
```php
$monkey = new Monkey();
$lion = new Lion();
$dolphin = new Dolphin();

$speak = new Speak();

$monkey->accept($speak);    // Ooh oo aa aa!    
$lion->accept($speak);      // Roaaar!
$dolphin->accept($speak);   // Tuut tutt tuutt!
```
我们本可以简单地给动物加一个继承层来做到这点，但是这样每当我们要给动物增加新功能的时候，我们就不得不改变动物。但是现在我们不用改变他们。比如，我们要给动物增加一个跳的行为，我们可以通过简单地增加一个新的访问者

```php
class Jump implements AnimalOperation {
    public function visitMonkey(Monkey $monkey) {
        echo 'Jumped 20 feet high! on to the tree!';
    }
    
    public function visitLion(Lion $lion) {
        echo 'Jumped 7 feet! Back on the ground!';
    }
    
    public function visitDolphin(Dolphin $dolphin) {
        echo 'Walked on water a little and disappeared';
    }
}
```
然后这样用
```php
$jump = new Jump();

$monkey->accept($speak);   // Ooh oo aa aa!
$monkey->accept($jump);    // Jumped 20 feet high! on to the tree!

$lion->accept($speak);     // Roaaar!
$lion->accept($jump);      // Jumped 7 feet! Back on the ground! 

$dolphin->accept($speak);  // Tuut tutt tuutt! 
$dolphin->accept($jump);   // Walked on water a little and disappeared
```

💡 策略模式
--------

现实例子
> 考虑排序的例子，我们实现了冒泡排序，但是数据开始增长，冒泡排序变得很慢。为了应对这个，我们实现了快速排序。但现在尽管快速排序算法对大数据集表现更好，小数据集却很慢。为了应对这一点，我们实现一个策略，冒泡排序处理小数据集，快速排序处理大数据集。

白话
> 策略模式允许你基于情况选择算法或策略。

维基百科
> In computer programming, the strategy pattern (also known as the policy pattern) is a behavioural software design pattern that enables an algorithm's behavior to be selected at runtime.
 
**代码例子**

翻译我们上面的例子。首先我们有了策略接口和不同的策略实现

```php
interface SortStrategy {
    public function sort(array $dataset) : array; 
}

class BubbleSortStrategy implements SortStrategy {
    public function sort(array $dataset) : array {
        echo "Sorting using bubble sort";
         
        // Do sorting
        return $dataset;
    }
} 

class QuickSortStrategy implements SortStrategy {
    public function sort(array $dataset) : array {
        echo "Sorting using quick sort";
        
        // Do sorting
        return $dataset;
    }
}
```
 
然后是实用策略的调用者
```php
class Sorter {
    protected $sorter;
    
    public function __construct(SortStrategy $sorter) {
        $this->sorter = $sorter;
    }
    
    public function sort(array $dataset) : array {
        return $this->sorter->sort($dataset);
    }
}
```
然后可以这样使用
```php
$dataset = [1, 5, 4, 3, 2, 8];

$sorter = new Sorter(new BubbleSortStrategy());
$sorter->sort($dataset); // 输出 : Sorting using bubble sort

$sorter = new Sorter(new QuickSortStrategy());
$sorter->sort($dataset); // 输出 : Sorting using quick sort
```

💢 状态模式
-----
现实例子
> 想象你在使用画图程序，你选择笔刷来画。现在笔刷根据选择的颜色改变自己的行为。即如果你选择红色，它就用红色画，如果是蓝色它就用蓝色等等。  

白话
> 他让你能类的状态改变时，改变其行为。

维基百科
> The state pattern is a behavioral software design pattern that implements a state machine in an object-oriented way. With the state pattern, a state machine is implemented by implementing each individual state as a derived class of the state pattern interface, and implementing state transitions by invoking methods defined by the pattern's superclass.
> The state pattern can be interpreted as a strategy pattern which is able to switch the current strategy through invocations of methods defined in the pattern's interface.

**代码例子**

让我们以编辑器作为例子，它能让你改变文本的状态，比如你选择了加粗，它开始以加粗字体书写，如果选择倾斜，就以倾斜字体等等。

首先，我们有状态接口和一些状态实现

```php
interface WritingState {
    public function write(string $words);
}

class UpperCase implements WritingState {
    public function write(string $words) {
        echo strtoupper($words); 
    }
} 

class LowerCase implements WritingState {
    public function write(string $words) {
        echo strtolower($words); 
    }
}

class Default implements WritingState {
    public function write(string $words) {
        echo $words;
    }
}
```
下面是我们的编辑器
```php
class TextEditor {
    protected $state;
    
    public function __construct(WritingState $state) {
        $this->state = $state;
    }
    
    public function setState(WritingState $state) {
        $this->state = $state;
    }
    
    public function type(string $words) {
        $this->state->write($words);
    }
}
```
然后可以这样使用
```php
$editor = new TextEditor(new Default());

$editor->type('First line');

$editor->setState(new UpperCaseState());

$editor->type('Second line');
$editor->type('Third line');

$editor->setState(new LowerCaseState());

$editor->type('Fourth line');
$editor->type('Fifth line');

// 输出:
// First line
// SECOND LINE
// THIRD LINE
// fourth line
// fifth line
```

📒 模板模式
---------------

现实例子
> 假设我们要建房子。建造的步骤类似这样 
> - 准备房子的地基
> - 建造墙
> - 建造房顶
> - 然后是地板
> 这些步骤步骤的顺序永远不会变，即你不能在建墙之前建屋顶，当时每个步骤都可以改变，比如墙可以是木头可以是聚酯或者石头。
  
白话
> 模板模式定义了一个算法会如何执行的骨架，但把这些步骤的实现移交给子类。
 
维基百科
> In software engineering, the template method pattern is a behavioral design pattern that defines the program skeleton of an algorithm in an operation, deferring some steps to subclasses. It lets one redefine certain steps of an algorithm without changing the algorithm's structure.

**代码例子**

想象我们有一个构建工具帮我们测试，纠错，构建，生成构建报告（即代码报告，查错报告），然后把应用发布到测试服务器。

首先是我们的基础类，它描述了构建算法的骨架
```php
abstract class Builder {
    
    // Template method 
    public final function build() {
        $this->test();
        $this->lint();
        $this->assemble();
        $this->deploy();
    }
    
    public abstract function test();
    public abstract function lint();
    public abstract function assemble();
    public abstract function deploy();
}
```

以下是实现

```php
class AndroidBuilder extends Builder {
    public function test() {
        echo 'Running android tests';
    }
    
    public function lint() {
        echo 'Linting the android code';
    }
    
    public function assemble() {
        echo 'Assembling the android build';
    }
    
    public function deploy() {
        echo 'Deploying android build to server';
    }
}

class IosBuilder extends Builder {
    public function test() {
        echo 'Running ios tests';
    }
    
    public function lint() {
        echo 'Linting the ios code';
    }
    
    public function assemble() {
        echo 'Assembling the ios build';
    }
    
    public function deploy() {
        echo 'Deploying ios build to server';
    }
}
```
然后可以这样使用

```php
$androidBuilder = new AndroidBuilder();
$androidBuilder->build();

// 输出:
// Running android tests
// Linting the android code
// Assembling the android build
// Deploying android build to server

$iosBuilder = new IosBuilder();
$iosBuilder->build();

// 输出:
// Running ios tests
// Linting the ios code
// Assembling the ios build
// Deploying ios build to server
```

## 🚦 收尾了同志们

终于收尾了。我会继续改进这篇文档，所以你或许需要 watch/star 这个仓库，先码后看。

## 👬 Contribution

- Report issues
- Open pull request with improvements
- Spread the word

## 翻译
[月球人](https://github.com/questionlin)

## License
MIT © [Kamran Ahmed](http://kamranahmed.info)
