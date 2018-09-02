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
// 抽象工厂类IFactory：用于规范工厂；
interface IFactory
{
    public static function makeCar();
}
// 具体产品创建的简单工厂，例如ConcreateFactoryA、ConcreateFactoryB。
class FactoryBenz implements IFactory
{
    public static function makeCar()
    {
        return new Benz();
    }
}
class FactoryBmw implements IFactory
{
    public static function makeCar()
    {
        return new Bmw();
    }
}
$car = FactoryBenz::makeCar();
$car->driver();