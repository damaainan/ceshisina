## array_map回调函数内部变量作用域分享

来源：[https://segmentfault.com/a/1190000013944295](https://segmentfault.com/a/1190000013944295)


```php

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
?>
```


如上code 目的为在回调用使变量自增 , 经测除fun函数内部变量只被++一次 其余类的属性均无此作用域问题 ！
foreach 无该作用域问题 !
想来原理很简单 这里array_map用的是匿名回调函数 .  回调函数的层级本就与fun方法应相同 , 只不过匿名闭包使回调函数写在array_map中.
$num变量为fun函数内部变量 , 其作用域再fun函数内. 综上所述回调函数与fun函数同级. 故$num并不作用在回调函数内. 故无法自增.
同理类的属性作用域即在fun函数也在回调函数 !


回调非匿名使用场景原理如下 :

```php
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
?>
```


上述array_map函数想传计数器都不好传 , 于是使用array_walk
array_walk 类的属性

```php
<?php
class Test {
    // protected $num = 0; //test4
    private $num = 0;   //test5
    // public $num = 0;    //test6
    
    public function __construct()
    {
        // $this->num = 0; //test3
    }
    
    public function callBack($v, $k) {
        $this->num++;
        var_dump($this->num.':'.$k.'=>'.$v);
    }
    
    public function fun()
    {
        $arr = array('people' => '修宇','hobby' => '拉拉拉');
        array_walk($arr, array($this,'callBack'));
    }
}
$test = new Test();
$test->fun();
?>
```


array_walk fun函数内的局部变量 :

```php
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
?>
```


由array_walk fun函数内的局部变量情况 就引发array_walk、和array_map底层实现的探究
※很多帖子说array_walk与foreach一样 , 这么看不一样 . 使用时要注意 !
