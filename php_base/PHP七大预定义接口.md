# PHP七大预定义接口 

Published on Jun 11, 2017 in [PHP][0] with [0 comment][1]

## 前言

这段时间一直在研究Laravel的源码，使用到了PHP的很多新的概念，比如Closure，比如数组式访问，于是正好研究了一下PHP的几个预定义接口。

### Closure(闭包)

代表一个匿名函数的类，我们所用到的匿名函数，都是Closure的一个实例，主要方法有:bind ， bindTo，其实之前在[Laravel][3]第一篇的时候，是有做过一个简单的介绍的，通过阅读Laravel的源代码，我们发现在很多地方都是有使用到Closure的概念的。  
Closure主要有两个方法，但是殊途同归，目的都是为了把某个匿名函数绑定到某个类上面以便执行:

1. bindTo  
他的参数有两个

```php
<?php
public Closure Closure::bindTo ( object $newthis [, mixed $newscope = 'static' ] )
```

$newthis是指需要绑定的对象，newscope是设置类的作用域。  
第一个参数可以设置null或者是一个对象的实例，主要取决于我们要在函数中操作的属性和方法是否是静态的，如果是静态的，那么第一个参数必须设置null，且第二个参数要设置一个类作用域:

* example_1:

```php
<?php
class A{
    private static $name = 'nine';
}

$callback = function(){
    self::$name = 'seven';
    echo self::$name;
};

$func = $callback->bindTo(null , A::class);
$func();
```


输出seven

* example_2:

```php
<?php
class A{
    private $name = 'nine';
}

$callback = function(){
    $this->name = 'seven';
    echo $this->name;
};

$a = new A;
$func = $callback->bindTo($a);
$func();
```


报错:Cannot access private property A::$name

* example_3:

```php
<?php
class A{
    private $name = 'nine';
}

$callback = function(){
    $this->name = 'seven';
    echo $this->name;
};

$a = new A;
$func = $callback->bindTo($a , A::class);
$func();
```


输出seven.

1. 静态方法bind  
他的参数有两个

```php
<?php
public static Closure Closure::bind ( Closure $closure , object $newthis [, mixed $newscope = 'static' ] )
```


$closure是指我们需要绑定的匿名函数，$newthis是指需要绑定的对象，newscope是设置类的作用域.  
bind其实和bindTo用法一致，只不过在于使用的方式不一样而已:

* example:

```php
<?php
<?php
class A{
    private $name = 'nine';
}

$a = new A;
$func = Closure::bind(function(){
    $this->name = 'seven';
    echo $this->name;
} , $a , A::class);
$func();
```


### ArrayAccess(数组式访问)

在Laravel中，经常会看到$this['app']这样的用法，但是我们知道，$this是一个对象，这种形式就好像对象用了数组的方式，具体是怎么实现的呢，主要就是当前类实现了接口ArrayAccess，不过需要注意的是，ArrayAccess的实现需要四个方法:

```php
<?php
class A implements ArrayAccess{
    public $name;
    public function __construct(){
        $this->name = 'nine';
    }
    public function offsetSet($offset, $value) {
        $this->$offset = $value;
    }
    public function offsetExists($offset) {
        return isset($this->$offset);
    }
    public function offsetUnset($offset) {
        unset($this->$offset);
    }
    public function offsetGet($offset) {
        return $this->$offset;
    }
}
$a = new A;
// 这里会调用offsetGet
echo $a['name'];
// 这里会调用offsetSet
$a['name'] = 'seven';
echo $a['name'];
// 这里会调用offsetExists
var_dump(isset($a['name']));
// 这里会调用offsetUnset
unset($a['name']);
var_dump(isset($a['name']));
```


输出:nine seven bool(true) bool(false)  
ArrayAccess的优点就是可以让我们可以使用数组式的调用类的属性，但是我还是觉得$a->name的形式更有逼格啊......

### Traversable(遍历)

这个比较简单，主要是用来判断一个类是否可以用foreach来遍历:

```php
<?php
var_dump(new stdClass instanceof Traversable);
```


输出false.

### Iterator(迭代器)

迭代器Iterator的主要功能就是遍历一个对象，虽然我们通过直接遍历一个对象也可以获取他的属性，但是你要知道，能获取的只是public的属性，而他的private或者protected是无法获取的，所以他的主要功能在于：在我们不了解对象的结构的情况下，帮助我们去获取他的所有的属性内容(包括私有)。

* example_1:

```php
<?php
class Test implements Iterator {
    private $position = 0;
    private $array = ['nine' , 'seven'];

// 该方法主要用户项目初始化
    function rewind() {
        echo __METHOD__ . PHP_EOL;
        $this->position = 0;
    }
//用来获取当前游标所对应的值
    function current() {
        echo __METHOD__ . PHP_EOL;
        return $this->array[$this->position];
    }
//获取当前游标
    function key() {
        echo __METHOD__ . PHP_EOL;
        return $this->position;
    }
//下移游标
    function next() {
        echo __METHOD__ . PHP_EOL;
        ++$this->position;
    }
//判断是否还有值
    function valid() {
        echo __METHOD__ . PHP_EOL;
        return isset($this->array[$this->position]);
    }
}

$obj = new Test;

$obj->rewind();

while($obj->valid()){
    echo $obj->current() . PHP_EOL;
    $obj->next();
}
//当然，这里其实我们也可以通过foreach的形式来获取，这里就不举例说明了。
```


输出:

```
Test::rewind
Test::valid
Test::current
nine
Test::next
Test::valid
Test::current
seven
Test::next
Test::valid
```


其执行顺序是:rewind->volid->current->key->next->volid->current->key...。  
当然，既然可以正序，那么我们也可以轻而易举的把结果倒序来遍历，把next里面的逻辑改成--$this->position即可，虽然说PHP的数组功能已经相当强大了，但是Iterator则会更加灵活和定制化。

### IteratorAggregate(聚合式迭代器)

聚合式迭代器IteratorAggregate和迭代器的功能，这个接口只需要实现一个方法即可getIterator：

* example_1:

```php
<?php
class Test implements IteratorAggregate {
    protected $name = 'nine';
    public $age = 18;

    public function getIterator(){
        return new ArrayIterator($this);
    }
}

$obj = (new Test)->getIterator();
$obj->rewind();
while($obj->valid()){
    echo $obj->current() . PHP_EOL;
    $obj->next();
}
```


输出:18.

* example_2:

```php
<?php
class Test implements IteratorAggregate {
    private $_data;

    public function __construct(){
        $this->_data = ['nine' , 'seven'];
    }

    public function getIterator(){
        return new ArrayIterator($this->_data);
    }
}

$obj = (new Test)->getIterator();
$obj->rewind();
while($obj->valid()){
    echo $obj->current() . PHP_EOL;
    $obj->next();
}
```


输出:nine seven。  
由此可见，最终内容取决于我们给new ArrayIterator注入的数组。当然，这里也可以用foreach来遍历，结果一样。  
其实我们去观察ArrayIterator的源码的时候可以发现，他继承自Iterator，实现了他的几个方法，所以当我们在遍历通过他返回的实例时，实际上就是在调用rewind->valid...这几个方法。

### Serializable(序列化)

序列化也是一个相对比较简单的接口，主要实现两个方法serialize以及unserialize即可:

```php
<?php
class MyClass implements Serializable {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function serialize() {
        return serialize($this->data);
    }
    
    public function unserialize($data) {
        $this->data = unserialize($data);
    }
}
$a = new MyClass('hello , world');
var_dump($a->serialize());
```


输出string(21) "s:13:"hello , world";"，这样，我们就可以在序列化的过程中做一些其他的逻辑操作了。

### Generator(生成器)

Generator实现了Iterator，但是他无法被继承，同时也生成实例。  
既然实现了Iterator，所以正如上文所介绍，他也就有了和Iterator相同的功能:rewind->valid->current->key->next...，Generator的语法主要来自于关键字yield。yield就好比一次循环的中转站，记录本次的活动轨迹，返回一个Generator的实例。  
Generator的优点在于，当我们要使用到大数据的遍历，或者说大文件的读写，而我们的内存不够的情况下，能够极大的减少我们对于内存的消耗，因为传统的遍历会返回所有的数据，这个数据存在内存上，而yield只会返回当前的值，不过当我们在使用yield时，其实其中会有一个处理记忆体的过程，所以实际上这是一个用时间换空间的办法。

* example_1:

```php
<?php
$start_time = microtime(true);
function xrange($num = 100000){
    for($i = 0 ; $i < $num ; ++ $i){
        yield $i;
    }
}

$generator = xrange();
foreach ($generator as $key => $value) {
    echo $key . '=' . $value . PHP_EOL;
}
echo 'memory:' . memory_get_usage() . ' time:' . (microtime(true) - $start_time) . PHP_EOL;
```


输出:memory:229056 time:0.25725412368774.

```php
<?php
$start_time = microtime(true);
function xrange2($num = 100000){
    $arr = [];
    for ($i=0; $i <$num ; ++$i) { 
        array_push($arr , $i);
    }
    return $arr;
}

$arr = xrange2();
foreach ($arr as $key => $value) {
    # code...
}
echo 'memory:' . memory_get_usage() . ' time:' . (microtime(true) - $start_time);
```


输出:memory:14877528 time:0.039144992828369。

本文由 [nine][4] 创作，采用 [知识共享署名4.0][5] 国际许可协议进行许可  
本站文章除注明转载/出处外，均为本站原创或翻译，转载前请务必署名  
最后编辑时间为: Jun 12, 2017 at 05:49 pm

[0]: http://www.hellonine.top/index.php/category/PHP/
[1]: #comments
[3]: http://www.hellonine.top/index.php/archives/6/#directory073928889396108333
[4]: http://www.hellonine.top/index.php/author/1/
[5]: https://creativecommons.org/licenses/by/4.0/