<?php
/**
 * 通过定义一个抽象的核心工厂类，并定义创建产品对象的接口，创建具体产品实例的工作延迟到其工厂子类去完成。这样做的好处是核心类只关注工厂类的接口定义，而具体的产品实例交给具体的工厂子类去创建。当系统需要新增一个产品是，无需修改现有系统代码，只需要添加一个具体产品类和其对应的工厂子类，是系统的扩展性变得很好，符合面向对象编程的开闭原则


具体案例：请MM去麦当劳吃汉堡，不同的MM有不同的口味，要每个都记住是一件烦人的事情，我们一般采用FactoryMethod模式，带着MM到服务员那儿，说“要一个汉堡”，具体要什么样的汉堡呢，让MM直接跟服务员说就行了。

工厂方法模式核心工厂类不再负责所有产品的创建，而是将具体创建的工作交给子类去做，成为一个抽象工厂角色，仅负责给出具体工厂类必须实现的接口，而不接触哪一个产品类应当被实例化这种细节，


工厂方式模式主要由以下几种角色组成：

抽象工厂角色（IServerFactory）：是工厂方法模式的核心，与应用程序无关。任何在模式中创建的对象的工厂类必须实现这个接口。

具体工厂角色(ChickenLegBaoFactory)：这是实现抽象工厂接口的具体工厂类，包含与应用程序密切相关的逻辑，并且受到应用程序调用以创建产品对象。

抽象产品角色(IHanbao)：工厂方法模式所创建的对象的超类型，也就是产品对象的共同父类或共同拥有的接口。

具体产品角色(ChickenLegBao)：这个角色实现了抽象产品角色所定义的接口。某具体产品有专门的具体工厂创建，它们之间往往一一对应。
 */

class Button { /* ...*/} //核心工厂类
class WinButton extends Button { /* ...*/} //创建产品对象的接口
class MacButton extends Button { /* ...*/} //创建产品对象的接口
//新增产品时  只需要增加这里即可  *Button  类即可
interface ButtonFactory {
	public function createButton($type);
}
class MyButtonFactory implements ButtonFactory {
	// 实现工厂方法
	public function createButton($type) {
//创建具体产品实例的工作
		switch ($type) {
		case 'Mac':
			return new MacButton();
		case 'Win':
			return new WinButton(); //新增产品时  只需要增加这里 和 一个工厂子类
		}
	}
}
$button_obj = new MyButtonFactory();
var_dump($button_obj->createButton('Mac'));
var_dump($button_obj->createButton('Win'));