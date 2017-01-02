<?php
/**
 * 享元模式
 * 就是缓存了创建型模式创建的对象，不知道为什么会归在结构型模式中，个人觉得创建型模式更合适，哈哈～
 * 其次，享元强调的缓存对象，外观模式强调的对外保持简单易用，是不是就大体构成了目前牛逼哄哄且满大
 * 的街【依赖注入容器】
 */

abstract class Resources {
	public $resource = null;
	abstract public function operate();
}
class unShareFlyWeight extends Resources {
	public function __construct($resource_str) {
		$this->resource = $resource_str;
	}
	public function operate() {
		echo $this->resource . "<br>";
	}
}
class shareFlyWeight extends Resources {
	private $resources = array();
	public function get_resource($resource_str) {
		if (isset($this->resources[$resource_str])) {
			return $this->resources[$resource_str];
		} else {
			return $this->resources[$resource_str] = $resource_str;
		}
	}
	public function operate() {
		foreach ($this->resources as $key => $resources) {
			echo $key . ":" . $resources . "<br>";
		}
	}
}

// client
$flyweight = new shareFlyWeight();
$flyweight->get_resource('a');
$flyweight->operate();
$flyweight->get_resource('b');
$flyweight->operate();
$flyweight->get_resource('c');
$flyweight->operate();
// 不共享的对象，单独调用
$uflyweight = new unShareFlyWeight('A');
$uflyweight->operate();
$uflyweight = new unShareFlyWeight('B');
$uflyweight->operate();