<?php
class Test {
    public function callBack($v, $k, $num) {
        $num++;
        var_dump($num.':'.$k.'=>'.$v);
    }
    
    public function fun()
    {
        $arr = array('people' => '修宇','hobby' => '拉拉拉');
        
        $num = 0;
        
        array_walk($arr, array($this, 'callBack'), $num);
    }
}
$test = new Test();
$test->fun();