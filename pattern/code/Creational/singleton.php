<?php
/**
 * 单例模式



编写单例模式的三个步骤：

1.创建一个类静态变量

2.私有化构造函数与克隆函数，防止外部调用

3.提供一个外部可以调用的静态方法，实例化第一步创建的静态变量

很明显，单例模式的适用场景就是系统中的对象只需要一个就可以的时候，例如，Java中Spring的Bean工厂，PHP中的数据库连接等等，只要有这种需求就首先单例模式。
 */

class Mysql {
	//该属性用来保存实例
	private static $conn;
	//构造函数为private,防止创建对象
	private function __construct() {
		$this->conn = mysql_connect('localhost', 'root', '');
	}
	//创建一个用来实例化对象的方法
	public static function getInstance() {
		if (!(self::$conn instanceof self)) {
			self::$conn = new self;
		}
		return self::$conn;
	}
	//防止对象被复制
	public function __clone() {
		trigger_error('Clone is not allowed !');
	}

}

//只能这样取得实例，不能new 和 clone
$mysql = Mysql::getInstance();
