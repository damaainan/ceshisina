# PHP设计模式(三)：封装
<font face=黑体>
> 原文地址：[PHP设计模式(三)：封装][0]

## Introduction

面向对象编程中，一切都是对象，对一个对象的封装，也成了面向对象编程中必不可少的部分。  
和C/C++，Java，Python等语言一样，PHP也支持封装。

## 封装/Encapsulation

> 对事物的封装是指，将事物进行抽象后，提供抽象概念的实现的具体方法。

听起来很拗口，还是举鲸鱼的例子。  
对于鲸鱼来说，需要吃东西这个行为，吃本身是一个抽象的概念，因为具体到怎么吃，是咀嚼和消化的过程，甚至如何咀嚼和消化也是不可见的。对外部而言，可见的只是吃这一个接口，如何吃、怎么吃，是被封装在了鲸鱼的实现中。  
甚至可以说，消化系统，被封装到了鲸鱼这个对象中，对外部不可见，仅仅鲸鱼自己可见。

## 封装方法

和别的程序设计语言一样，PHP也只是三种封装概念：Private，Protected，Public。

### 私有/Private

私有的概念是，仅仅对象内部可见，外部不可见，如：

```php
    <?php
    class Whale {
      private $name;
      public function __construct() {
        $this->name = "Whale";
      }
      public function eat($food) {
        chew($food);
        digest($food);
      }
      private function chew($food) {
        echo "Chewing " . $food . "\n";
      }
      private function digest($food) {
        echo "Digest " . $food . "\n";
      }
    }
    ?>
```
name是鲸鱼的私有属性，chew()和digest()是鲸鱼的私有方法，对于其他类来说，都是不可见的。对于现实来说，我们如果只是注重吃，并没有必要去关心鲸鱼是如何去吃的。

### 保护/Protected

保护的概念是，仅仅是自身类和继承类可见，这个关键字的用途主要是防止滥用类的派生，另外三方库编写的时候会用到，防止误用。

```php
    <?php
    abstract class Animal {
      private $name;
      abstract public function eat($food);
      protected function chew($food) {
        echo "Chewing " . $food . "\n";
      }
      protected function digest($food) {
        echo "Digest " . $food . "\n";
      }
    }
    
    class Whale extends Animal {
      private $name;
      public function __construct() {
        $this->name = "Whale";
      }
      public function eat($food) {
        chew($food);
        digest($food);
      }
    }
    ?>
```
鲸鱼类可以通过继承使用动物类的咀嚼和消化方法，但是别的继承鲸鱼类的类就不可以再使用动物类的咀嚼和消化方法了。保护更多是用于面向对象设计，而不是为了编程来实现某个需求。

### 公共/Public

公共的概念就是，任何类、任何事物都可以访问，没有任何限制，这里不再赘述。

## Getters/Setters

Getters和Setters也叫Accessors和Mutators，在Java/C#等语言中常以get()/set()方法出现。  
对于这两个东西的争议很大，考虑下面一个类：

```php
    <?php
    class Price {
      public $priceA;
      public $priceB;
      public $priceC;
      ...
    }
    ?>
```
如果不使用Getters/Setters，我们给Price类赋值和取值一般是这样：

```php
    <?php
      $price = new Price();
      $price->priceA = 1;
      $price->priceB = 2;
      $price->priceC = 3;
      ...
      echo $price->priceA;
      echo $price->priceB;
      echo $price->priceC;
      ...
    ?>
```
但是如果使用了Getters/Setters，Price类将变成这样：

```php
    <?php
    class Price {
      private $priceA;
      private $priceB;
      private $priceC;
      public function getPriceA() {
        return $this->priceA;
      }
      public function setPriceA($price) {
        $this->priceA = $price;
      }
      ...
    }
    ?>
```
这时候赋值将变成这样：

```php
    <?php
      $price = new Price();
      $price->setpriceA(1);
      $price->setPriceB(2);
      $price->setPriceC(3);
      ...
      echo $price->getPriceA();
      echo $price->getPriceB();
      echo $price->getPriceC();
      ...
    ?>
```
是不是感觉需要多敲很多代码？这也是很多程序员不愿意使用get/set的原因，造成了大量的看似无用冗余的代码。  
为什么叫看似冗余和无用？因为Getters/Setters是编程设计方法，而不是编程实现方法。

> 在面向对象程序设计中，类和类之间的访问、交互和更新应该是通过Accessors和Mutators，也就是Getters和Setters来实现。直接访问和修改破坏了类的封装性。

为什么采用这种设计方式？因为程序设计是对现实问题的抽象，而在编程的工程中程序员扮演的角色往往是上帝。  
考虑这样一种场景：你朋友要求你改名，决定是否改名的人是你，而不是你朋友。在你的朋友的视觉（也就是你朋友的类），他不能直接去修改你的名字。  
如果你直接采用非Getters/Setters的设计方法，事实上是程序员扮演的这个上帝修改了现实规则，允许你朋友能够随意更改你的姓名，显然这是不合理的。

## Summary

合理的封装对于好的程序设计是必不可少的，虽然什么都是Public也能解决编程问题，但是这不是用程序设计解决问题的思路。

</font>

[0]: http://csprojectedu.com/2016/02/26/PHPDesignPatterns-3/