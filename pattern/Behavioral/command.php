<?php
/**
 * 命令模式
 * 命令模式:就是在依赖的类中间加一个命令类，本来可以直接调用的类方法现在通过命令来调用，已达到
 * 解耦的的目的，其次可以实现undo，redo等操作，因为你知道调了哪些命令
 */

interface Command { // 命令角色
	public function execute(); // 执行方法
}
class ConcreteCommand implements Command {
	// 具体命令方法
	private $_receiver;
	public function __construct(Receiver $receiver) {
		$this->_receiver = $receiver;
	}
	public function execute() {
		$this->_receiver->action();
	}
}
class Receiver {
	// 接收者角色
	private $_name;
	public function __construct($name) {
		$this->_name = $name;
	}
	public function action() {
		echo 'receive some cmd:' . $this->_name;
	}
}
class Invoker {
	// 请求者角色
	private $_command;
	public function __construct(Command $command) {
		$this->_command = $command;
	}
	public function action() {
		$this->_command->execute();
	}
}

$receiver = new Receiver('hello world');
$command = new ConcreteCommand($receiver);
$invoker = new Invoker($command);
$invoker->action();