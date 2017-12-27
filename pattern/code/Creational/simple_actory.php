<?php
/**
 * 简单工厂模式不属于23种常用面向对象设计模式之一。简单工厂模式是由一个工厂对象决定创建出哪一种产品类的实例。简单工厂模式是工厂模式家族中最简单实用的模式，可以理解为是不同工厂模式的一个特殊实现。其实质是由一个工厂类根据传入的参数，动态决定应该创建哪一个产品类（这些产品类继承自一个父类或接口）的实例。

角色及职责：

工厂（SimpleFactory）角色：简单工厂模式的核心，它负责实现创建所有实例的内部逻辑。工厂类可以被外界直接调用，创建所需的产品对象。

抽象产品（IProduct）角色：简单工厂模式所创建的所有对象的父类，它负责描述所有实例所共有的公共接口。

具体产品（Concrete Product）角色：是简单工厂模式的创建目标，所有创建的对象都是充当这个角色的某个具体类的实例。


需求：根据提供相应的属性值由简单工厂创建具有相应特性的产品对象。
 */

/**抽象产品角色
 * Interface IProduct   产品接口
 */
interface IProduct {
	/**X轴旋转
		     * @return mixed
	*/
	function XRotate();

	/**Y轴旋转
		     * @return mixed
	*/
	function YRotate();
}

/**具体产品角色
 * Class XProduct        X轴旋转产品
 */
class XProduct implements IProduct {
	private $xMax = 1;
	private $yMax = 1;

	function __construct($xMax, $yMax) {
		$this->xMax = $xMax;
		$this->yMax = 1;
	}

	function XRotate() {
		echo "您好，我是X轴旋转产品，X轴转转转。。。。。。";
	}

	function YRotate() {
		echo "抱歉，我是X轴旋转产品，我没有Y轴。。。。。。";
	}
}

/**具体产品角色
 * Class YProduct        Y轴旋转产品
 */
class YProduct implements IProduct {
	private $xMax = 1;
	private $yMax = 1;

	function __construct($xMax, $yMax) {
		$this->xMax = 1;
		$this->yMax = $yMax;
	}

	function XRotate() {
		echo "抱歉，我是Y轴旋转产品，我没有X轴。。。。。。";
	}

	function YRotate() {
		echo "您好，我是Y轴旋转产品，Y轴转转转。。。。。。";
	}
}

/**具体产品角色
 * Class XYProduct        XY轴都可旋转产品
 */
class XYProduct implements IProduct {
	private $xMax = 1;
	private $yMax = 1;

	function __construct($xMax, $yMax) {
		$this->xMax = $xMax;
		$this->yMax = $yMax;
	}

	function XRotate() {
		echo "您好，我是XY轴都可旋转产品，X轴转转转。。。。。。";
	}

	function YRotate() {
		echo "您好，我是XY轴都可旋转产品，Y轴转转转。。。。。。";
	}
}

/**工厂角色
 * Class ProductFactory
 */
class ProductFactory {
	static function GetInstance($xMax, $yMax) {
		if ($xMax > 1 && $yMax === 1) {
			return new XProduct($xMax, $yMax);
		} elseif ($xMax === 1 && $yMax > 1) {
			return new YProduct($xMax, $yMax);
		} elseif ($xMax > 1 && $yMax > 1) {
			return new XYProduct($xMax, $yMax);
		} else {
			return null;
		}
	}
}

$pro = array();
$pro[] = ProductFactory::GetInstance(1, 12);
$pro[] = ProductFactory::GetInstance(12, 1);
$pro[] = ProductFactory::GetInstance(12, 12);
$pro[] = ProductFactory::GetInstance(0, 12);

foreach ($pro as $v) {
	if ($v) {
		echo "<br/>";
		$v->XRotate();
		echo "<br/>";
		$v->YRotate();
	} else {
		echo "非法产品！<br/>";
	}
	echo "<hr/>";
}