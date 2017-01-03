<?php
/**
 * 装饰器模式
对现有的对象增加功能
 * 和适配器的区别：适配器是连接两个接口，装饰器是对现有的对象包装

装饰器模式又叫装饰者模式。装饰模式是在不必改变原类文件和使用继承的情况下，动态地扩展一个对象的功能。它是通过创建一个包装对象，也就是装饰来包裹真实的对象。

角色：

组件对象的接口:可以给这些对象动态的添加职责

所有装饰器的父类:需要定义一个与组件接口一致的接口，并持有一个Component对象，该对象其实就是被装饰的对象。

具体的装饰器类:实现具体要向被装饰对象添加的功能。用来装饰具体的组件对象或者另外一个具体的装饰器对象。

 */

interface Component {
	public function operation();
}

abstract class Decorator implements Component {
	// 装饰角色
	protected $_component;
	public function __construct(Component $component) {
		$this->_component = $component;
	}
	public function operation() {
		$this->_component->operation();
	}
}

class ConcreteDecoratorA extends Decorator {
	// 具体装饰类A
	public function __construct(Component $component) {
		parent::__construct($component);
	}
	public function operation() {
		parent::operation(); //  调用装饰类的操作
		$this->addedOperationA(); //  新增加的操作
	}
	public function addedOperationA() {echo 'A加点酱油;';}
}
class ConcreteDecoratorB extends Decorator {
	// 具体装饰类B
	public function __construct(Component $component) {
		parent::__construct($component);
	}
	public function operation() {
		parent::operation();
		$this->addedOperationB();
	}
	public function addedOperationB() {echo "B加点辣椒;";}
}

class ConcreteComponent implements Component {
	//具体组件类
	public function operation() {}
}

// clients
$component = new ConcreteComponent();
$decoratorA = new ConcreteDecoratorA($component);
$decoratorB = new ConcreteDecoratorB($decoratorA);
$decoratorA->operation();
echo '<br>--------<br>';
$decoratorB->operation();