<?php
// Product接口类：用于定义产品规范；
interface ICar
{
    public function driver();
}
// 具体的产品实现，例如ConcreateProductA、ConcreateProductB；
class Benz implements ICar
{
    public function driver()
    {
        echo 'benz driver.';
    }
}

class Bmw implements ICar
{
    public function driver()
    {
        echo 'bmw driver.';
    }
}
// 简单工厂类SimpleFactory：用于生成具体的产品。
class SimpleFactory
{
    public static function makeCar($type){
        switch ($type){
            case 'benz':
                return new Benz();
                break;
            case 'bmw':
                return new Bmw();
                break;
            default:
                throw new \Exception('not support type!');
                break;
        }
    }
}
$car = SimpleFactory::makeCar('benz');
$car->driver();