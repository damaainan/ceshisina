<?php 
/**
 * 适配器模式
-------
现实例子
> 假设在你的存储卡里有一些照片，你要把它们传到电脑。为了传输，你需要一个兼容电脑端口的适配器来连接存储卡和电脑。在这里，读卡器就是一个适配器。
> 另一个例子是电源转换器；一个三脚的插口不能插到两口的插座上，它需要一个电源转换器来兼容两口的插座。
> 还有一个例子是翻译将一个人说的话翻译给另一个人。

白话
> 适配器模式让你封装一个不兼容的对象到一个适配器，来兼容其他类。
 */
// 假设一个猎人狩猎狮子的游戏

// 一个接口狮子 `Lion` 来实现所有种类的狮子
interface Lion {
    public function roar();
}

class AfricanLion implements Lion {
    public function roar() {
        echo "AfricanLion";
    }
}

class AsianLion implements Lion {
    public function roar() {
        echo "AsianLion";
    }
}


// 猎人需要狩猎任何狮子 `Lion` 接口的实现
class Hunter {
    public function hunt(Lion $lion) {
        echo $lion->roar();
    }
}


// 在游戏里加一个野狗 `WildDog`
class WildDog {
    public function bark() {
        echo "bark";
    }
}

// Adapter around wild dog to make it compatible with our game
class WildDogAdapter implements Lion {
    protected $dog;

    public function __construct(WildDog $dog) {
        $this->dog = $dog;
    }
    
    public function roar() {
        $this->dog->bark();
    }
}

// 使用

$wildDog = new WildDog();
$wildDogAdapter = new WildDogAdapter($wildDog);

$hunter = new Hunter();
$hunter->hunt($wildDogAdapter);


$africanLion = new AfricanLion();
$hunter->hunt($africanLion);

$asianLion = new AsianLion();
$hunter->hunt($asianLion);