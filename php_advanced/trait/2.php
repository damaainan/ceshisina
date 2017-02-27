<?php 
// /当 trait 中的方法和类中的方法相同的时候,优先级的顺序是类中的方法会将 trait 中的方法覆盖
trait HelloWorld {
    public function sayHello() {
        echo 'Hello World!';
    }
}

class TheWorldIsNotEnough {
    use HelloWorld;
    public function sayHello() {
        echo 'Hello Universe!';
    }
}

$o = new TheWorldIsNotEnough();
$o->sayHello();