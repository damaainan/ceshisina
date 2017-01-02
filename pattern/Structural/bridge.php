<?php
/**
 * 桥接模式
 * 基础的结构型设计模式：将抽象和实现解耦,对抽象的实现是实体行为对接口的实现
 * 例如：人 => 抽象为属性：性别 动作：吃 => 人吃的动作抽象为interface => 实现不同的吃法
 */

abstract class Abstraction {
	// 抽象化角色，抽象化给出的定义，并保存一个对实现化对象的引用。
	protected $imp; // 对实现化对象的引用
	public function operation() {
		$this->imp->operationImp();
	}
}

class RefinedAbstraction extends Abstraction {
	// 修正抽象化角色, 扩展抽象化角色，改变和修正父类对抽象化的定义。
	public function __construct(Implementor $imp) {
		$this->imp = $imp;
	}
	public function operation() {$this->imp->operationImp();}
}

abstract class Implementor {
	// 实现化角色, 给出实现化角色的接口，但不给出具体的实现。
	abstract public function operationImp();
}

class ConcreteImplementorA extends Implementor {
	// 具体化角色A
	public function operationImp() {}
}

class ConcreteImplementorB extends Implementor {
	// 具体化角色B
	public function operationImp() {}
}

// client
$abstraction = new RefinedAbstraction(new ConcreteImplementorA());
$abstraction->operation();
$abstraction = new RefinedAbstraction(new ConcreteImplementorB());
$abstraction->operation();