<?php
/**
 * 享元模式
 * 就是缓存了创建型模式创建的对象，不知道为什么会归在结构型模式中，个人觉得创建型模式更合适，哈哈～
 * 其次，享元强调的缓存对象，外观模式强调的对外保持简单易用，是不是就大体构成了目前牛逼哄哄且满大
 * 的街【依赖注入容器】


享元模式使用共享物件，用来尽可能减少内存使用量以及分享资讯给尽可能多的相似物件；它适合用于只是因重复而导致使用无法令人接受的大量内存的大量物件。通常物件中的部分状态是可以分享。常见做法是把它们放在外部数据结构，当需要使用时再将它们传递给享元。

角色分析：

享元工厂角色（FWFactory）：创建并管理BlogModel对象。

所有具体享元父接口角色（BolgModel）：接受并作用与外部状态。

具体享元角色（JobsBlog）：具体变化点，为内部对象增加储存空间。
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