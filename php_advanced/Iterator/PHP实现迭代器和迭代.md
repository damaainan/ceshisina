# [关于PHP实现迭代器和迭代][0]

 标签： [php数据结构与算法][1]

 2015-10-18 10:58  733人阅读  

 分类：

[PHP][6]的面向对象引擎提供了一个非常聪明的特性，就是，可以使用foreach()方法通过循环方式取出一个对象的所有属性，就像数组方式一样，代码如下：
```php
class Myclass{
    public $a = 'php';
    public $b = 'onethink';
    public $c = 'thinkphp';
}
$myclass = new Myclass();
//用foreach()将对象的属性循环出来
foreach($myclass as $key.'=>'.$val){
    echo '$'.$key.' = '.$val."<br/>";
}
/*返回
    $a = php
    $b = onethink
    $c = thinkphp
*/
```

如果需要实现更加复杂的行为，可以通过一个iterator（迭代器）来实现
```php
//迭代器接口
interface MyIterator{
    //函数将内部指针设置回数据开始处
    function rewind();
    //函数将判断数据指针的当前位置是否还存在更多数据
    function valid();
    //函数将返回数据指针的值
    function key();
    //函数将返回将返回当前数据指针的值
    function value();
    //函数在数据中移动数据指针的位置
    function next();
}
//迭代器类
class ObjectIterator implements MyIterator{
    private $obj;//对象
    private $count;//数据元素的数量
    private $current;//当前指针
    function __construct($obj){
        $this->obj = $obj;
        $this->count = count($this->obj->data);
    }
    function rewind(){
        $this->current = 0;
    }
    function valid(){
        return $this->current < $this->count;
    }
    function key(){
        return $this->current;
    }
    function value(){
        return $this->obj->data[$this->current];
    }
    function next(){
        $this->current++;
    }
}
interface MyAggregate{
    //获取迭代器
    function getIterator();
}
class MyObject implements MyAggregate{
    public $data = array();
    function __construct($in){
        $this->data = $in;
    }
    function getIterator(){
        return new ObjectIterator($this);
    }
}
//迭代器的用法
$arr = array(2,4,6,8,10);
$myobject = new MyObject($arr);
$myiterator = $myobject->getIterator();
for($myiterator->rewind();$myiterator->valid();$myiterator->next()){
    $key = $myiterator->key();
    $value = $myiterator->value();
    echo $key.'=>'.$value;
    echo "<br/>";
}

/*返回
    0=>2
    1=>4
    2=>6
    3=>8
    4=>10
*/
```

[0]: http://www.csdn.net/baidu_30000217/article/details/49226263
[1]: http://www.csdn.net/tag/php%e6%95%b0%e6%8d%ae%e7%bb%93%e6%9e%84%e4%b8%8e%e7%ae%97%e6%b3%95

[6]: http://lib.csdn.net/base/php