<?php
/**
 * 代理模式
 * 对对象加以【控制】
 * 和适配器的区别：适配器是连接两个接口（【改变】了接口）
 * 和装饰器的区别：装饰器是对现有的对象包装（【功能扩展】）

代理模式为其他对象提供一种代理以控制对这个对象的访问。在某些情况下，一个对象不适合或者不能直接引用另一个对象，而代理对象可以在客户端和目标对象之间起到中介的作用。


角色介绍：

抽象主题角色（IGiveGift）：定义了Follower和Proxy公用接口，这样就在任何使用Follower的地方都可以使用Proxy。

主题角色（Follower）：定义了Proxy所代表的真实实体。

代理对象（Proxy）：保存一个引用使得代理可以访问实体，并提供一个与Follower接口相同的接口，这样代理可以用来代替实体(Follower)。
 */

abstract class Subject {
	// 抽象主题角色
	abstract public function action();
}
class RealSubject extends Subject {
	// 真实主题角色
	public function __construct() {}
	public function action() {}
}
class ProxySubject extends Subject {
	// 代理主题角色
	private $_real_subject = NULL;
	public function __construct() {}
	public function action() {
		$this->_beforeAction();
		if (is_null($this->_real_subject)) {
			$this->_real_subject = new RealSubject();
		}
		$this->_real_subject->action();
		$this->_afterAction();
	}
	private function _beforeAction() {
		echo '在action前,我想干点啥....';
	}
	private function _afterAction() {
		echo '在action后,我还想干点啥....';
	}
}
// client
$subject = new ProxySubject();
$subject->action();