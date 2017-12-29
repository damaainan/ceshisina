<?php 
class MyClass{
    public $eventMap = array();
    function on($evtname , $handle ){ //注册一个事件上的响应回调函数
        $this->eventMap[$evtname]=$handle;
    }
    function trigger($evtname , $scope=null){ //触发一个事件，也就是循环调用所有响应这个事件的回调函数
        call_user_func_array( $this->eventMap[$evtname] , $scope);
    }
}



$MyClass = new MyClass;
    $MyClass->on('post' , function($a , $b ){
        echo " a = $a ; \n ";
        echo " b = $b ; \n ";
        echo " a + b = ".( $a + $b) . ";\r\n ";
        } );
    $MyClass->trigger('post' , array( 123 , 321 )  );//框架内部触发


class test{
    static $static = "this is  static ";
    public $nomal = "this is nomal ";
    function demo($a , $b ){
        echo " a = $a ;\r\n";
        echo " b = $b ;\r\n";
        echo " static = ".self::$static." ;\r\n";
        echo " nomal = ".$this->nomal." ;\r\n";
        echo " add = ".$this->add." ;\r\n";
    }
}

$test = new test;
$test->add = " this is new add ";
$MyClass->on('post' ,array( $test , 'demo' ) );
$MyClass->trigger('post' , array( 123 , 321 )  );