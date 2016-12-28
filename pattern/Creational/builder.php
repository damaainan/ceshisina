<?php
/**
 * 又名：生成器模式，是一种对象构建模式。它可以将复杂对象的建造过程抽象出来（抽象类别），使这个抽象过程的不同实现方法可以构造出不同表现（属性）的对象。
建造者模式是一步一步创建一个复杂的对象，它允许用户只通过指定复杂对象的类型和内容就可以构建它们，用户不需要知道内部的具体构建细节。例如，一辆汽车由轮子，发动机以及其他零件组成，对于普通人而言，我们使用的只是一辆完整的车，这时，我们需要加入一个构造者，让他帮我们把这些组件按序组装成为一辆完整的车。
角色：
● Builder：抽象构造者类，为创建一个Product对象的各个部件指定抽象接口。
● ConcreteBuilder：具体构造者类，实现Builder的接口以构造和装配该产品的各个部件。定义并明确它所创建的表示。提供一个检索产品的接口
● Director：指挥者，构造一个使用Builder接口的对象。
● Product：表示被构造的复杂对象。ConcreateBuilder创建该产品的内部表示并定义它的装配过程。
包含定义组成部件的类，包括将这些部件装配成最终产品的接口。
 */

class Product {
	// 产品本身
	private $_parts;
	public function __construct() {$this->_parts = array();}
	public function add($part) {return array_push($this->_parts, $part);}
}

abstract class Builder {
	// 建造者抽象类
	public abstract function buildPart1();
	public abstract function buildPart2();
	public abstract function getResult();
}

class ConcreteBuilder extends Builder {
	// 具体建造者
	private $_product;
	public function __construct() {$this->_product = new Product();}
	public function buildPart1() {$this->_product->add("Part1");}
	public function buildPart2() {$this->_product->add("Part2");}
	public function getResult() {return $this->_product;}
}

class Director {
	//导演者
	public function __construct(Builder $builder) {
		$builder->buildPart1();
		$builder->buildPart2();
	}
}
// client
$buidler = new ConcreteBuilder();
$director = new Director($buidler);
$product = $buidler->getResult();
var_dump($product);