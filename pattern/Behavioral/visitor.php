<?php
/**
 * 访问者模式

 * 说说我对的策略模式和访问者模式的区分：
 * 乍一看，其实两者都挺像的，都是实体类依赖了外部实体的算法，但是：
 * 对于策略模式：首先你是有一堆算法，然后在不同的逻辑中去使用；
 * 对于访问者模式：实体的【结构是稳定的】，但是结构中元素的算法却是多变的，比如就像人吃饭这个动作
 * 是稳定不变的，但是具体吃的行为却又是多变的；

访问者模式表示一个作用于某对象结构中的各元素的操作。它使你可以在不改变各元素类的前提下定义作用于这些元素的新操作。

角色：

1.抽象访问者(State):为该对象结构中具体元素角色声明一个访问操作接口。该操作接口的名字和参数标识了发送访问请求给具体访问者的具体元素角色，这样访问者就可以通过该元素角色的特定接口直接访问它。

2.具体访问者(Success):实现访问者声明的接口。

3.抽象元素(Person):定义一个接受访问操作accept()，它以一个访问者作为参数。

4. 具体元素(Man):实现了抽象元素所定义的接受操作接口。

5.结构对象(ObjectStruct):这是使用访问者模式必备的角色。它具备以下特性：能枚举它的元素；可以提供一个高层接口以允许访问者访问它的元素；如有需要，可以设计成一个复合对象或者一个聚集（如一个列表或无序集合）。
 */

interface Visitor {
	// 抽象访问者角色
	public function visitConcreteElementA(ConcreteElementA $elementA);
	public function visitConcreteElementB(concreteElementB $elementB);
}

interface Element {
	// 抽象节点角色
	public function accept(Visitor $visitor);
}

class ConcreteVisitor1 implements Visitor {
	// 具体的访问者1
	public function visitConcreteElementA(ConcreteElementA $elementA) {}
	public function visitConcreteElementB(ConcreteElementB $elementB) {}
}
class ConcreteVisitor2 implements Visitor {
	// 具体的访问者2
	public function visitConcreteElementA(ConcreteElementA $elementA) {}
	public function visitConcreteElementB(ConcreteElementB $elementB) {}
}
class ConcreteElementA implements Element {
	// 具体元素A
	private $_name;
	public function __construct($name) {$this->_name = $name;}
	public function getName() {return $this->_name;}
	public function accept(Visitor $visitor) {
		// 接受访问者调用它针对该元素的新方法
		$visitor->visitConcreteElementA($this);
	}
}
class ConcreteElementB implements Element {
	// 具体元素B
	private $_name;
	public function __construct($name) {$this->_name = $name;}
	public function getName() {return $this->_name;}
	public function accept(Visitor $visitor) {
		// 接受访问者调用它针对该元素的新方法
		$visitor->visitConcreteElementB($this);
	}
}
class ObjectStructure {
	// 对象结构 即元素的集合
	private $_collection;
	public function __construct() {$this->_collection = array();}
	public function attach(Element $element) {
		return array_push($this->_collection, $element);
	}
	public function detach(Element $element) {
		$index = array_search($element, $this->_collection);
		if ($index !== FALSE) {
			unset($this->_collection[$index]);
		}
		return $index;
	}
	public function accept(Visitor $visitor) {
		foreach ($this->_collection as $element) {
			$element->accept($visitor);
		}
	}
}
// client
$elementA = new ConcreteElementA("ElementA");
$elementB = new ConcreteElementB("ElementB");
$elementA2 = new ConcreteElementB("ElementA2");
$visitor1 = new ConcreteVisitor1();
$visitor2 = new ConcreteVisitor2();
$os = new ObjectStructure();
$os->attach($elementA);
$os->attach($elementB);
$os->attach($elementA2);
$os->detach($elementA);
$os->accept($visitor1);
$os->accept($visitor2);