<?php 
/**
 * 代理模式
-------------------
现实例子
> 你有没有用过门卡来通过一扇门？有多种方式来打开那扇门，即它可以被门卡打开，或者按开门按钮打开。这扇门的主要功能是开关，但在顶层增加了一个代理来增加其他功能。下面的例子能更好的说明。

白话
> 使用代理模式，一个类表现出了另一个类的功能。
 */




interface Door {
    public function open();
    public function close();
}

class LabDoor implements Door {
    public function open() {
        echo "Opening lab door";
    }

    public function close() {
        echo "Closing the lab door";
    }
}



class Security {
    protected $door;

    public function __construct(Door $door) {
        $this->door = $door;
    }

    public function open($password) {
        if ($this->authenticate($password)) {
            $this->door->open();
        } else {
            echo "Big no! It ain't possible.";
        }
    }

    public function authenticate($password) {
        return $password === '$ecr@t';
    }

    public function close() {
        $this->door->close();
    }
}


$door = new Security(new LabDoor());
$door->open('invalid'); // Big no! It ain't possible.

$door->open('$ecr@t'); // Opening lab door
$door->close(); // Closing lab door