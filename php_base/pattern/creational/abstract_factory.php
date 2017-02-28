<?php 
/**
 * 抽象工厂模式
----------------

现实例子
> 扩展我们简单工厂模式的例子。基于你的需求，你可以从木门店得到一扇木门，从铁门店得到一扇铁门，或者从塑料门店得到一扇塑料门。而且你需要一个有不同专长的人来安装这扇门，比如一个木匠来安木门，焊工来安铁门等。正如你看的，门和安装工有依赖性，木门需要木匠，铁门需要焊工等。

白话
> 一个制造工厂的工厂；一个工厂把独立但是相关／有依赖性的工厂进行分类，但是不需要给出具体的类。
 */


// 门 `Door` 的接口和一些实现

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


// 每种门的安装专家

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




// 抽象工厂来创建全部相关的对象，即木门工厂制造木门和木门安装专家，铁门工厂制造铁门和铁门安装专家
interface DoorFactory {
    public function makeDoor() : Door;
    public function makeFittingExpert() : DoorFittingExpert;
}

// 木头工厂返回木门和木匠
class WoodenDoorFactory implements DoorFactory {  //这样有重复  可以省略
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

// 使用
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