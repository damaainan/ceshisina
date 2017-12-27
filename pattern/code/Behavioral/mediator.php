<?php
/**
 * 中介者模式
 * 理解：就是不同的对象之间通信，互相之间不直接调用，而是通过一个中间对象（中介者）
 * 使用场景：对象之间大量的互相依赖

中介者模式用一个中介者对象来封装一系列的对象交互。中介者使得各对象不需要显式地相互引用，从而使其松散耦合，而且可以独立地改变它们之间的交互。

角色：

中介者接口(UnitedNations):在里面定义了各个同事之间相互交互所需要的方法。

具体的中介者实现对象(UnitedCommit):它需要了解并为维护每个同事对象，并负责具体的协调各个同事对象的交互关系。

同事类的定义(Country):通常实现成为抽象类，主要负责约束同事对象的类型，并实现一些具体同事类之间的公共功能，

具体的同事类(China):实现自己的业务，需要与其他同事对象交互时，就通知中介对象，中介对象会负责后续的交互。
 */

abstract class Mediator {
	// 中介者角色
	abstract public function send($message, $colleague);
}
abstract class Colleague {
	// 抽象对象
	private $_mediator = null;
	public function __construct($mediator) {
		$this->_mediator = $mediator;
	}
	public function send($message) {
		$this->_mediator->send($message, $this);
	}
	abstract public function notify($message);
}
class ConcreteMediator extends Mediator {
	// 具体中介者角色
	private $_colleague1 = null;
	private $_colleague2 = null;
	public function send($message, $colleague) {
		//echo $colleague->notify($message);
		if ($colleague == $this->_colleague1) {
			$this->_colleague1->notify($message);
		} else {
			$this->_colleague2->notify($message);
		}
	}
	public function set($colleague1, $colleague2) {
		$this->_colleague1 = $colleague1;
		$this->_colleague2 = $colleague2;
	}
}
class Colleague1 extends Colleague {
	// 具体对象角色
	public function notify($message) {
		echo 'colleague1：' . $message . "<br>";
	}
}
class Colleague2 extends Colleague {
	// 具体对象角色
	public function notify($message) {
		echo 'colleague2：' . $message . "<br>";
	}
}
// client
$objMediator = new ConcreteMediator();
$objC1 = new Colleague1($objMediator);
$objC2 = new Colleague2($objMediator);
$objMediator->set($objC1, $objC2);
$objC1->send("to c2 from c1");
$objC2->send("to c1 from c2");