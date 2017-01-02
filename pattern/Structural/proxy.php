<?php
/**
 * 代理模式
 * 对对象加以【控制】
 * 和适配器的区别：适配器是连接两个接口（【改变】了接口）
 * 和装饰器的区别：装饰器是对现有的对象包装（【功能扩展】）
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