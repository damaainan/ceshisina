<?php
 
//策略功能接口
 
interface Strategy {
    public function help();
}
 
//实际对外接口
 
interface Factory{
    public function action();
}
 
//打120
 
class Call120 implements Strategy{
 
    public function help(){
        echo "打120";
    }
 
}
 
//人工呼吸
 
class Firstaid implements Strategy{
 
    public function help(){
        echo '人工呼吸';
    }
 
}
 
//实际对外的人工呼吸接口
 
class Helpaid implements Factory{
 
    protected $object;
 
    public function action(){
        $this->object = new Firstaid();
        $this->object->help();
    }
 
}
 
//实际对外的120接口
 
class Help120 implements Factory{
 
    public $object;
 
    public function action(){
        $this->object = new Call120();
        $this->object->help();
    }
 
}
 
$Help = new Help120();
$Help->action();//output 打120