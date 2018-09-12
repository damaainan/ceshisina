<?php
class Test {
    // protected $num = 0; //test4
    // private $num = 0;   //test5
    // public $num = 0;    //test6
    
    public function __construct()
    {
        // $this->num = 0; //test3
    }
    
    public function fun()
    {
        $arr = array(
            array('people' => '修宇','hobby' => '拉拉拉'),
            array('people' => '栋浩','hobby' => '吼吼吼'),
            array('people' => '上线哲','hobby' => '哈哈哈'),
        );
        $people = '帅奇';
        $hobby = '喵喵喵';
        
        $num = 0;    //test1
        
        // $this->num = 0; //test2
        
        $arr = array_map(function($v) use($people, $hobby){
            $num++; //test1
            // $this->num++;    //test2345
            $v['people'] = $people.$num;
            $v['hobby'] = $hobby.$num;   //test1   
            
            // $v['people'] = $people.$this->num;
            // $v['hobby'] = $hobby.$this->num;    //test2 or test3 or test4 or test5 or test6
            return $v;
        },$arr);
        // return $arr;
        
        foreach($arr as $v) {
            $num++;
            $v['people'] = $people.$num;
            $v['hobby'] = $hobby.$num;
        }
        return $arr;
    }
}
$test = new Test();
var_dump($test->fun());