<?php
/**
 * 迭代器模式
 * 理解：遍历对象内部的属性，无需对外暴露内部的构成

迭代器模式：迭代器模式是遍历集合的成熟模式，迭代器模式的关键是将遍历集合的任务交给一个叫做迭代器的对象，它的工作时遍历并选择序列中的对象，而客户端程序员不必知道或关心该集合序列底层的结构。

角色：

Iterator（迭代器）：迭代器定义访问和遍历元素的接口

ConcreteIterator（具体迭代器）：具体迭代器实现迭代器接口，对该聚合遍历时跟踪当前位置

Aggregate （聚合）：聚合定义创建相应迭代器对象的接口(可选)

ConcreteAggregate（具体聚合）：具体聚合实现创建相应迭代器的接口，该操作返回ConcreteIterator的一个适当的实例(可选)

 */

class sample implements Iterator {
	private $_items;

	public function __construct(&$data) {
		$this->_items = $data;
	}
	public function current() {
		return current($this->_items);
	}

	public function next() {
		next($this->_items);
	}

	public function key() {
		return key($this->_items);
	}

	public function rewind() {
		reset($this->_items);
	}

	public function valid() {
		return ($this->current() !== FALSE);
	}
}

// client
$data = array(1, 2, 3, 4, 5);
$sa = new sample($data);
foreach ($sa AS $key => $row) {
	echo $key, ' ', $row, '<br />';
}
//Yii FrameWork Demo
class CMapIterator implements Iterator {
/**
 * @var array the data to be iterated through
 */
	private $_d;
/**
 * @var array list of keys in the map
 */
	private $_keys;
/**
 * @var mixed current key
 */
	private $_key;

/**
 * Constructor.
 * @param array the data to be iterated through
 */
	public function __construct(&$data) {
		$this->_d = &$data;
		$this->_keys = array_keys($data);
	}

/**
 * Rewinds internal array pointer.
 * This method is required by the interface Iterator.
 */
	public function rewind() {
		$this->_key = reset($this->_keys);
	}

/**
 * Returns the key of the current array element.
 * This method is required by the interface Iterator.
 * @return mixed the key of the current array element
 */
	public function key() {
		return $this->_key;
	}

/**
 * Returns the current array element.
 * This method is required by the interface Iterator.
 * @return mixed the current array element
 */
	public function current() {
		return $this->_d[$this->_key];
	}

/**
 * Moves the internal pointer to the next array element.
 * This method is required by the interface Iterator.
 */
	public function next() {
		$this->_key = next($this->_keys);
	}

/**
 * Returns whether there is an element at current position.
 * This method is required by the interface Iterator.
 * @return boolean
 */
	public function valid() {
		return $this->_key !== false;
	}
}

$data = array('s1' => 11, 's2' => 22, 's3' => 33);
$it = new CMapIterator($data);
foreach ($it as $row) {
	echo $row, '<br />';
}