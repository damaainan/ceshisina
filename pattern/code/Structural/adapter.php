<?php
/**
 *适配器模式（有时候也称包装样式或者包装）将一个类的接口适配成用户所期待的（适配器模式要解决的核心问题）。一个适配允许通常因为接口不兼容而不能在一起工作的类工作在一起，做法是将类自己的接口包裹在一个已存在的类中。

待适配（ForeignPlayer）角色：此角色的接口规则内部的接口规则不一致，但内部需要调用该角色的方法功能。

内部接口（IPlayer）角色：这是一个抽象角色，此角色给出内部期待的接口规则。

适配器（Adapter）角色：通过在内部包装一个Adapter对象，把待适配接口转换成目标接口，此角色为适配器模式的核心角色，也是适配器模式所解决问题的关键。


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