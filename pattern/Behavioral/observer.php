<?php
/**
 * 观察者模式
 * 观察者观察被观察者，被观察者通知观察者


观察者模式（有时又被称为发布-订阅模式）。在此种模式中，一个目标物件管理所有相依于它的观察者物件，并且在它本身的状态改变时主动发出通知。这通常透过呼叫各观察者所提供的方法来实现。此种模式通常被用来实现事件处理系统。

重要角色：

抽象通知者角色（INotifier）：定义了通知的接口规则。

具体通知者角色（Boss）：实现抽象通知者的接口，接到状态改变立即向观察者下发通知。

抽象观察者角色（IObserver）：定义接到通知后所做的操作（Update）接口规则。

具体观察者角色（JingDong）：实现具体操作方法。
 */

interface IObserver {
	function onSendMsg($sender, $args);
	function getName();
}
interface IObservable {
	function addObserver($observer);
}
class UserList implements IObservable {
	private $_observers = array();
	public function sendMsg($name) {
		foreach ($this->_observers as $obs) {
			$obs->onSendMsg($this, $name);
		}
	}
	public function addObserver($observer) {
		$this->_observers[] = $observer;
	}
	public function removeObserver($observer_name) {
		foreach ($this->_observers as $index => $observer) {
			if ($observer->getName() === $observer_name) {
				array_splice($this->_observers, $index, 1);
				return;
			}
		}
	}
}
class UserListLogger implements IObserver {
	public function onSendMsg($sender, $args) {
		echo ("'$args' send to UserListLogger\n");
	}
	public function getName() {
		return 'UserListLogger';
	}
}
class OtherObserver implements IObserver {
	public function onSendMsg($sender, $args) {
		echo ("'$args' send to OtherObserver\n");
	}
	public function getName() {
		return 'OtherObserver';
	}
}
$ul = new UserList(); //被观察者
$ul->addObserver(new UserListLogger()); //增加观察者
$ul->addObserver(new OtherObserver()); //增加观察者
$ul->sendMsg("Jack"); //发送消息到观察者
$ul->removeObserver('UserListLogger'); //移除观察者
$ul->sendMsg("hello"); //发送消息到观察者