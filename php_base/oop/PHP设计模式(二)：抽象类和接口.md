# PHP设计模式(二)：抽象类和接口

<font face=黑体>
> 原文地址：[PHP设计模式(二)：抽象类和接口][0]

## Introduction

对于编程来说，对事物的抽象是一个老生常谈的话题，抽象问题更利于面向对象编程以及程序设计模式。  
和C/C++，Java，Python等语言一样，PHP也支持面向对象，但是又有略微区别，如PHP支持在接口中定义常量，但是不支持抽象变量。

## 抽象/Abstraction

> 对事物的抽象是指，区别两个不同事物之间的本质特征，这两个事物应该在某个视角上有明确的区分界限。

如，鲸鱼和鲤鱼，这两个事物在动物的视角上，有明确的区分界限，属于不同的动物；但是在水生动物的视角上，他们属于同一种动物的抽象。  
合理的对问题进行抽象，构造模型，将更容易通过编程来解决问题。  
记住：抽象是编程解决问题的基础，越复杂的问题，越需要一开始就对问题进行抽象，而不是直接写代码。

## 抽象类/Abstract Class

抽象类是一个编程概念，PHP中叫Abstract Classes。在设计模式中，抽象类不能够被实例化/初始化，但是可以依靠具体类的继承来实现。  
有点抽象，对吧？用代码来解释：
```php
<?php
abstract class Animal {
  public $name;
  abstract public function eat($food);
}
```
定义了动物这个抽象类，动物的属性是名字name，然后有一个方法是吃食物eat food。  
为什么动物是抽象类？因为动物这个物种并不是一个存在于自然界的东西，它是人类脑海里抽象出的东西。存在自然界的是鲸鱼和鲤鱼这样的确定性动物。  
比如鲸鱼的概念，应该是属于动物，继承Animal类，我们定义鲸鱼这个类以及吃东西的方法：

```php
<?php
class Whale extends Animal {
  public function __construct() {
    $this->name = "Whale";
  }
  public function eat($food) {
    echo $this->name . " eat " . $food . ".\n";
  }
}
```
现在我们可以初始鲸鱼类，并且调用吃的方法了：

```php
<?php
$whale = new Whale();
$whale->eat("fish");
```
运行一下：

    $ php Whale.php
    Whale eat fish.

## 接口/Interface

PHP也支持面向过程编程概念中的接口，下面同样用鲸鱼的例子来讲述：

```php
<?php
interface IAction {
  public function eat($food);
  public function swim();
}
```
同样定义一个鲸鱼类，来实现上述接口：

```php
<?php
class Whale implements IAction {
  public function eat($food) {
    echo "Whale eat " . $food . "\n.";
  }
  public swim() {
    echo "Whale is swimming.\n";
  }
}
```
现在我们可以初始鲸鱼类，并且调用吃的方法了：

```php
<?php
$whale = new Whale();
$whale->eat("fish");
```
运行一下：

    $ php Whale.php
    Whale eat fish.

## 抽象类vs接口

上面的抽象类和接口的例子，看上去是不是类似？事实上，对于PHP编程来说，抽象类可以实现的功能，接口也可以实现。  
抽象类的接口的区别，不在于编程实现，而在于程序设计模式的不同。  
一般来讲，抽象用于不同的事物，而接口用于事物的行为。  
如：水生生物是鲸鱼的抽象概念，但是水生生物并不是鲸鱼的行为，吃东西才是鲸鱼的行为。  
对于大型项目来说，对象都是由基本的抽象类继承实现，而这些类的方法通常都由接口来定义。  
此外，对于事物属性的更改，建议使用接口，而不是直接赋值或者别的方式，如：

```php
<?php
interface IAction {
  public function eat();
}
class Whale implements IAction {
  public function eat() {
    echo "Whale eat fish.\n";
  }
}
class Carp implements IAction {
  public function eat() {
    echo "Carp eat moss.\n";
  }
}

class Observer {
  public function __construct() {
    $whale = new Whale();
    $carp = new Carp();
    $this->observeEat($whale);
    $this->observeEat($carp);
  }
  function observeEat(IAction $animal) {
    $animal->eat();
  }
}
$observer = new observer();
```
运行一下：

    $ php Observer.php
    Whale eat fish.
    Carp eat moss.

## Summary

好的设计模式是严格对问题进行抽象，虽然抽象类和接口对于编程实现来说是类似的，但是对于程序设计模式是不同的。

</font>

[0]: http://csprojectedu.com/2016/02/24/PHPDesignPatterns-2/