<?php
/**
 * 状态模式
 * 理解：行为随着状态变化
 * 区别：
 * - 策略的改变由client完成，client持有context的引用；而状态的改变是由context或状态自己,
 * 就是自身持有context
 * - 简单说就是策略是client持有context，而状态是本身持有context
 * 使用场景：大量和对象状态相关的条件语句
 */

interface State { // 抽象状态角色
	public function handle(Context $context); // 方法示例
}
class ConcreteStateA implements State {
	// 具体状态角色A
	private static $_instance = null;
	private function __construct() {}
	public static function getInstance() {
		// 静态工厂方法，返还此类的唯一实例
		if (is_null(self::$_instance)) {
			self::$_instance = new ConcreteStateA();
		}
		return self::$_instance;
	}

	public function handle(Context $context) {
		echo 'concrete_a' . "<br>";
		$context->setState(ConcreteStateB::getInstance());
	}

}
class ConcreteStateB implements State {
	// 具体状态角色B
	private static $_instance = null;
	private function __construct() {}
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new ConcreteStateB();
		}
		return self::$_instance;
	}

	public function handle(Context $context) {
		echo 'concrete_b' . "<br>";
		$context->setState(ConcreteStateA::getInstance());
	}
}
class Context {
	// 环境角色
	private $_state;
	public function __construct() {
		// 默认为stateA
		$this->_state = ConcreteStateA::getInstance();
	}
	public function setState(State $state) {
		$this->_state = $state;
	}
	public function request() {
		$this->_state->handle($this);
	}
}
// client
$context = new Context();
$context->request();
$context->request();
$context->request();
$context->request();