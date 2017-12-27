<?php
/**
 * 原型模式
根据已有的对象来创建新对象

用原型实例指定创建对象的种类，并且通过拷贝这些原型创建新的对象。Prototype模式允许一个对象再创建另外一个可定制的对象，根本无需知道任何如何创建的细节，通过将一个原型对象传给那个要发动创建的对象，这个要发动创建的对象通过请求原型对象拷贝它们自己来实施创建。它主要面对的问题是：“某些结构复杂的对象”的创建工作；由于需求的变化，这些对象经常面临着剧烈的变化，但是他们却拥有比较稳定一致的接口。

在php中，类已经实现了原型模式，php有个魔术方法__clone()方法，会克隆出一个这样的对象。

角色分析：

1.抽象原型，提供了一个克隆的接口

2.具体的原型，实现克隆的接口
 */

abstract class Cell {
	public $id;
	public $dna;
	abstract function __clone();
}

class WhaleCell extends Cell {
	public function __construct() {
		$this->id = 1;
		$this->dna = "ATCG";
	}
	public function displayDNA() {
		echo $this->dna . "\n";
	}
	function __clone() {
		$this->id = $this->id + 1;
		if ($this->id % 3 == 0) {
			$this->dna = $this->dna . "AT";
		}
		if ($this->id % 5 == 0) {
			$this->dna = $this->dna . "CG";
		}
	}
}

$whaleCell = new WhaleCell();
$whaleCell->displayDNA();
$whaleCell2 = clone $whaleCell;
$whaleCell2->displayDNA();
$whaleCell3 = clone $whaleCell2;
$whaleCell3->displayDNA();
$whaleCell4 = clone $whaleCell3;
$whaleCell4->displayDNA();
$whaleCell5 = clone $whaleCell4;
$whaleCell5->displayDNA();