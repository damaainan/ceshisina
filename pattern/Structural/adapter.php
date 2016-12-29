<?php
/**
 * 适配器模式是一种利用适配器将现有的实现，适配到已有接口的设计模式，最常见的例子就是变压器，将已有的5V输入的电器，通过变压器，适配到220V的电源插座。
相比继承，组件可用性高，低耦合，冗余度低，因此推荐采用组件的模式来进行设计。
 */

//对象适配器
interface Target {
	public function sampleMethod1();
	public function sampleMethod2();
}

class Adaptee {
	public function sampleMethod1() {
		echo '#######';
	}
}

class Adapter implements Target {
	private $_adaptee;
	public function __construct(Adaptee $adaptee) {
		$this->_adaptee = $adaptee;
	}

	public function sampleMethod1() {
		$this->_adaptee->sampleMethod1();
	}

	public function sampleMethod2() {
		echo '!!!!!!!!';
	}
}

$adapter = new Adapter(new Adaptee());
$adapter->sampleMethod1();
$adapter->sampleMethod2();
//类适配器
interface Target2 {
	public function sampleMethod1();
	public function sampleMethod2();
}

class Adaptee2 {
	// 源角色
	public function sampleMethod1() {}
}

class Adapter2 extends Adaptee2 implements Target2 {
	// 适配后角色
	public function sampleMethod2() {}
}
$adapter = new Adapter2();
$adapter->sampleMethod1();
$adapter->sampleMethod2();