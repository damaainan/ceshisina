<?php
class Test {
    // protected $num = 0; //test4
    // private $num = 0;   //test5
    // public $num = 0;    //test6
    
    public function __construct()
    {
        $this->num = 0; //test3
    }
    
    public function callBack($v, $rV) {
        $this->num++;
        $v['people'] = $rV['people'].$this->num;
        $v['hobby'] = $rV['hobby'].$this->num;
        return $v;
    }
    
    public function fun()
    {
        $arr = array(
            array('people' => '修宇','hobby' => '拉拉拉'),
            array('people' => '栋浩','hobby' => '吼吼吼'),
            array('people' => '上线哲','hobby' => '哈哈哈'),
        );
    
        $num = 0;    //test1
       
        $replace = array(
            array('people' => '帅奇', 'hobby' => '喵喵喵'),
            array('people' => '帅奇', 'hobby' => '喵喵喵'),
            array('people' => '帅奇', 'hobby' => '喵喵喵'),
        );
        $arr = array_map(array($this,'callBack'),$arr, $replace);
        return $arr;
    }
}
$test = new Test();
var_dump($test->fun());