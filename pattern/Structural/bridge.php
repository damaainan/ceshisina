<?php
/**
 * 桥接模式
 * 基础的结构型设计模式：将抽象和实现解耦,对抽象的实现是实体行为对接口的实现
 * 例如：人 => 抽象为属性：性别 动作：吃 => 人吃的动作抽象为interface => 实现不同的吃法

桥接模式：在软件系统中，某些类型由于自身的逻辑，它具有两个或多个维度的变化，那么如何应对这种“多维度的变化”？这就要使用桥接模式——将抽象部分与它的实现部分分离，使他们可以独立地变化。

角色介绍：



抽象化(AbstractRoad)角色：抽象化给出的定义，并保存一个对实现化对象的引用。

修正抽象化(SpeedWay)角色：扩展抽象化角色，改变和修正父类对抽象化的定义。

实现化(AbstractCar)角色：这个角色给出实现化角色的接口，但不给出具体的实现。必须指出的是，这个接口不一定和抽象化角色的接口定义相同，实际上，这两个接口可以非常不一样。

具体实现化(Bus)角色：这个角色给出实现化角色接口的具体实现。
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