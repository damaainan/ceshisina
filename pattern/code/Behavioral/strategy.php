<?php
/**
 * 策略模式

策略模式定义了一系列的算法，并将每一个算法封装起来，而且使它们还可以相互替换。策略模式让算法独立于使用它的客户而独立变化，即封装变化的算法。



适用场景：

1、 多个类只区别在表现行为不同，可以使用Strategy模式，在运行时动态选择具体要执行的行为。

2、 需要在不同情况下使用不同的策略(算法)，或者策略还可能在未来用其它方式来实现。

3、 对客户隐藏具体策略(算法)的实现细节，彼此完全独立。

4、客户端必须知道所有的策略类，并自行决定使用哪一个策略类，策略模式只适用于客户端知道所有的算法或行为的情况。

5、 策略模式造成很多的策略类，每个具体策略类都会产生一个新类。

有时候可以通过把依赖于环境的状态保存到客户端里面，可以使用享元模式来减少对象的数量。

角色分析：

抽象策略角色（RotateItem）：策略类，通常由一个接口或者抽象类实现。

具体策略角色（ItemX）：包装了相关的算法和行为。

环境角色（ItemContext）：持有一个策略类的引用，最终给客户端调用。
 */

interface Strategy { // 抽象策略角色，以接口实现
	public function do_method(); // 算法接口
}
class ConcreteStrategyA implements Strategy {
	// 具体策略角色A
	public function do_method() {
		echo 'do method 1';
	}
}
class ConcreteStrategyB implements Strategy {
	// 具体策略角色B
	public function do_method() {
		echo 'do method 2';
	}
}
class ConcreteStrategyC implements Strategy {
	// 具体策略角色C
	public function do_method() {
		echo 'do method 3';
	}
}
class Question {
	// 环境角色
	private $_strategy;
	public function __construct(Strategy $strategy) {
		$this->_strategy = $strategy;
	}
	public function handle_question() {
		$this->_strategy->do_method();
	}
}

// client
$strategyA = new ConcreteStrategyA();
$question = new Question($strategyA);
$question->handle_question();
$strategyB = new ConcreteStrategyB();
$question = new Question($strategyB);
$question->handle_question();
$strategyC = new ConcreteStrategyC();
$question = new Question($strategyC);
$question->handle_question();