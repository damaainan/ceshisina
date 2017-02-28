<?php 
/**
 * 简单工厂模式
--------------
现实例子
> 假设，你正在建造一所房子，你需要门。如果每次你需要一扇门你都要穿上木工服开始在房子里造扇门，将会是一团乱。取而代之的是让工厂造好。

白话
> 简单工厂模式在不暴露生成逻辑的前提下生成一个实例。
 */


//一个门的接口和实现
interface Door {
    public function getWidth() : float;// php7的写法，标明类型
    public function getHeight() : float;
}

class WoodenDoor implements Door {
    protected $width;
    protected $height;

    public function __construct(float $width, float $height) {
        $this->width = $width;
        $this->height = $height;
    }
    
    public function getWidth() : float {
        return $this->width;
    }
    
    public function getHeight() : float {
        return $this->height;
    }
}


//工厂来制造和返回门
class DoorFactory {
   public static function makeDoor($width, $height) : Door {
       return new WoodenDoor($width, $height);
   }
}



$door = DoorFactory::makeDoor(100, 200);
echo 'Width: ' . $door->getWidth();
echo 'Height: ' . $door->getHeight();