<?php
/**
 * 提供一个创建一系列相关或相互依赖对象的接口，而无须指定它们具体的类。抽象工厂模式又称为Kit模式，属于对象创建型模式。
此模式是对工厂方法模式的进一步扩展。在工厂方法模式中，一个具体的工厂负责生产一类具体的产品，即一对一的关系，但是，如果需要一个具体的工厂生产多种产品对象，那么就需要用到抽象工厂模式了

为了便于理解此模式，这里介绍两个概念：
● 产品等级结构：产品等级结构即产品的继承结构，如一个抽象类是电视机，其子类有海尔电视机、海信电视机、TCL电视机，则抽象电视机与具体品牌的电视机之间构成了一个产品等级结构，抽象电视机是父类，而具体品牌的电视机是其子类。
● 产品族 ：在抽象工厂模式中，产品族是指由同一个工厂生产的，位于不同产品等级结构中的一组产品，如海尔电器工厂生产的海尔电视机、海尔电冰箱，海尔电视机位于电视机产品等级结构中，海尔电冰箱位于电冰箱产品等级结构中。
角色：
● 抽象工厂（AbstractFactory）：担任这个角色的是抽象工厂模式的核心，是与应用系统的商业逻辑无关的。
● 具体工厂（Factory）：这个角色直接在客户端的调用下创建产品的实例，这个角色含有选择合适的产品对象的逻辑，而这个逻辑是与应用系统商业逻辑紧密相关的。
● 抽象产品（AbstractProduct）：担任这个角色的类是抽象工厂模式所创建的对象的父类，或它们共同拥有的接口
● 具体产品（Product）：抽象工厂模式所创建的任何产品对象都是一个具体的产品类的实例。
 */

class Button {} //抽象工厂
class Border {}
class MacButton extends Button {} //具体工厂
class WinButton extends Button {}
class MacBorder extends Border {}
class WinBorder extends Border {}
interface AbstractFactory {
//抽象产品
	public function CreateButton();
	public function CreateBorder();
}
class MacFactory implements AbstractFactory {
//具体产品
	public function CreateButton() {return new MacButton();}
	public function CreateBorder() {return new MacBorder();}
}
class WinFactory implements AbstractFactory {
	public function CreateButton() {return new WinButton();}
	public function CreateBorder() {return new WinBorder();}
}

// class FreeFactory implements AbstractFactory{
// class FreeFactory {
// private $str;

// public function CreateButton(){return new ;}
// }
// $v = new FreeFactory();
// var_dump($v);

$win = new WinFactory();
$mac = new MacFactory();
var_dump($win);
var_dump($mac);