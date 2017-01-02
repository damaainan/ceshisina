<?php
/**
 * 模板方法模式
 * php模板模式
 * 理解：典型的控制反转，子类复写算法，但是最终的调用都是抽象类中定义的方式，也就是说抽象类中
 * 定义了算法的执行顺序
 * 使用场景：例如短信系统，选择不同的短信商，但是发送短信的动作都是一样的,未来要增加不同的厂商
 * 只需添加子类即可
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