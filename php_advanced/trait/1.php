<?php 
class Base{
    public function sayHello(){
        echo 'hello';
    }
}
trait SayHello{
    public function sayHello(){
        //调用父类的方法
        parent::sayHello();
        echo 'world';
    }
}
class MyHelloWorld extends Base{
    use SayHello;
}

$o =  new MyHelloWorld();
$o->sayHello();