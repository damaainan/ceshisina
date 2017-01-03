<?php
/**
 * 责任链模式
 * 理解：把一个对象传递到一个对象链上，直到有对象处理这个对象
 * 可以干什么：我们可以做一个filter,或者gateway

职责链模式（又叫责任链模式）包含了一些命令对象和一些处理对象，每个处理对象决定它能处理那些命令对象，它也知道应该把自己不能处理的命令对象交下一个处理对象，该模式还描述了往该链添加新的处理对象的方法。

角色：

抽象处理者(Manager)：定义出一个处理请求的接口。如果需要，接口可以定义出一个方法，以设定和返回对下家的引用。这个角色通常由一个抽象类或接口实现。

具体处理者(CommonManager)：具体处理者接到请求后，可以选择将请求处理掉，或者将请求传给下家。由于具体处理者持有对下家的引用，因此，如果需要，具体处理者可以访问下家。
 */

abstract class Responsibility {
	// 抽象责任角色
	protected $next; // 下一个责任角色

	public function setNext(Responsibility $l) {
		$this->next = $l;
		return $this;
	}
	abstract public function operate(); // 操作方法
}

class ResponsibilityA extends Responsibility {
	public function __construct() {}
	public function operate() {
		if (false == is_null($this->next)) {
			$this->next->operate();
			echo 'Res_A start' . "<br>";
		}
	}
}
class ResponsibilityB extends Responsibility {
	public function __construct() {}
	public function operate() {
		if (false == is_null($this->next)) {
			$this->next->operate();
			echo 'Res_B start';
		}
	}
}

$res_a = new ResponsibilityA();
$res_b = new ResponsibilityB();
$res_a->setNext($res_b);
$res_a->operate();