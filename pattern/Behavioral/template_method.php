<?php
/**
 * 模板方法模式
 * php模板模式
 * 理解：典型的控制反转，子类复写算法，但是最终的调用都是抽象类中定义的方式，也就是说抽象类中
 * 定义了算法的执行顺序
 * 使用场景：例如短信系统，选择不同的短信商，但是发送短信的动作都是一样的,未来要增加不同的厂商
 * 只需添加子类即可

模板模式准备一个抽象类，将部分逻辑以具体方法以及具体构造形式实现，然后声明一些抽象方法来迫使子类实现剩余的逻辑。不同的子类可以以不同的方式实现这些抽象方法，从而对剩余的逻辑有不同的实现。先制定一个顶级逻辑框架，而将逻辑的细节留给具体的子类去实现。

角色：

抽象模板角色（MakePhone）：抽象模板类，定义了一个具体的算法流程和一些留给子类必须实现的抽象方法。

具体子类角色（XiaoMi）：实现MakePhone中的抽象方法，子类可以有自己独特的实现形式，但是执行流程受MakePhone控制。
 */

abstract class AbstractClass {
	// 抽象模板角色
	public function templateMethod() {
		// 模板方法 调用基本方法组装顶层逻辑
		$this->primitiveOperation1();
		$this->primitiveOperation2();
	}
	abstract protected function primitiveOperation1(); // 基本方法
	abstract protected function primitiveOperation2();
}
class ConcreteClass extends AbstractClass {
	// 具体模板角色
	protected function primitiveOperation1() {}
	protected function primitiveOperation2() {}

}

$class = new ConcreteClass();
$class->templateMethod();