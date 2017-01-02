<?php
/**
 * 访问者模式

 * 说说我对的策略模式和访问者模式的区分：
 * 乍一看，其实两者都挺像的，都是实体类依赖了外部实体的算法，但是：
 * 对于策略模式：首先你是有一堆算法，然后在不同的逻辑中去使用；
 * 对于访问者模式：实体的【结构是稳定的】，但是结构中元素的算法却是多变的，比如就像人吃饭这个动作
 * 是稳定不变的，但是具体吃的行为却又是多变的；
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