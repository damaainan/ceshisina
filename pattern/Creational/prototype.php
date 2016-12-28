<?php
/**
 * 原型模式
根据已有的对象来创建新对象
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