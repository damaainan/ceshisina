<?php
/**
 *  门面模式

门面模式（有时候也称外观模式）是指提供一个统一的接口去访问多个子系统的多个不同的接口，它为子系统中的一组接口提供一个统一的高层接口。使用子系统更容易使用。

案例：炒股票，新股民不了解证券知识做股票，是很容易亏钱的，需要学习的知识太多了，这样新手最好把炒股的事情委托给基金公司，基金公司了解证券知识，那么新股民把自己的股票托管给基金公司去运营，这样新股民不必了解哪只股票的走势就可以完成股票的买卖。基金公司在这里就是一个门面，针对于新股民的门面。

角色分析：

门面（FacadeCompany）角色：此角色封装一个高层接口，将客户端的请求代理给适当的子系统对象，是门面模式的核心接口。

子系统（ICBC）角色：实现子系统的具体功能，处理FacadeCompany对象指派的任务。子系统没有FacadeCompany的任何信息，没有对FacadeCompany对象的引用。
 */

class Camera {
	public function turnOn() {}
	public function turnOff() {}
	public function rotate($degrees) {}
}

class Light {
	public function turnOn() {}
	public function turnOff() {}
	public function changeBulb() {}
}

class Sensor {
	public function activate() {}
	public function deactivate() {}
	public function trigger() {}
}

class Alarm {
	public function activate() {}
	public function deactivate() {}
	public function ring() {}
	public function stopRing() {}
}

class SecurityFacade {
	private $_camera1, $_camera2;
	private $_light1, $_light2, $_light3;
	private $_sensor;
	private $_alarm;

	public function __construct() {
		$this->_camera1 = new Camera();
		$this->_camera2 = new Camera();

		$this->_light1 = new Light();
		$this->_light2 = new Light();
		$this->_light3 = new Light();

		$this->_sensor = new Sensor();
		$this->_alarm = new Alarm();
	}

	public function activate() {
		$this->_camera1->turnOn();
		$this->_camera2->turnOn();

		$this->_light1->turnOn();
		$this->_light2->turnOn();
		$this->_light3->turnOn();

		$this->_sensor->activate();
		$this->_alarm->activate();
	}

	public function deactivate() {
		$this->_camera1->turnOff();
		$this->_camera2->turnOff();

		$this->_light1->turnOff();
		$this->_light2->turnOff();
		$this->_light3->turnOff();

		$this->_sensor->deactivate();
		$this->_alarm->deactivate();
	}
}

//client
$security = new SecurityFacade();
$security->activate();