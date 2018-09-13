PHP SPL标准库总共有6个接口，如下：

1.Countable  
2.OuterIterator   
3.RecursiveIterator   
4.SeekableIterator  
5.SplObserver   
6.SplSubject

其中OuterIterator、RecursiveIterator、SeekableIterator都是继承Iterator类的，下面会对每种接口作用和使用进行详细说明。

**Coutable接口：**

实现Countable接口的对象可用于count()函数计数。
```php
class Mycount implements Countable  
{  
    public function count()  
    {  
        static $count = 0;  
        $count++;  
        return $count;  
    }  
}  
  
$count = new Mycount();  
$count->count();  
$count->count();  
  
echo count($count); //3  
echo count($count); //4
```
说明：

调用count()函数时，Mycount::count()方法被调用  
count()函数的第二个参数将不会产生影响

**OuterIterator接口：**

自定义或修改迭代过程。
```php 
//IteratorIterator是OuterIterator的一个实现类  
class MyOuterIterator extends IteratorIterator {  
  
    public function current()  
    {  
        return parent::current() . 'TEST';  
    }  
}  
  
foreach(new MyOuterIterator(new ArrayIterator(['b','a','c'])) as $key => $value) {  
    echo "$key->$value".PHP_EOL;  
}  
/*  
结果：  
0->bTEST  
1->aTEST  
2->cTEST  
*/
```
在实际运用中，OuterIterator极其有用：
```php 
$db = new PDO('mysql:host=localhost;dbname=test', 'root', 'mckee');  
$db->query('set names utf8');  
$pdoStatement = $db->query('SELECT * FROM test1', PDO::FETCH_ASSOC);  
$iterator = new IteratorIterator($pdoStatement);  
$tenRecordArray = iterator_to_array($iterator);  
print_r($tenRecordArray);
```
**RecursiveIterator接口：**  
用于循环迭代多层结构的数据，RecursiveIterator另外提供了两个方法：

RecursiveIterator::getChildren 获取当前元素下子迭代器  
RecursiveIterator::hasChildren 判断当前元素下是否有迭代器
```php 
class MyRecursiveIterator implements RecursiveIterator  
{  
    private $_data;  
    private $_position = 0;  
      
    public function __construct(array $data) {  
        $this->_data = $data;  
    }  
      
    public function valid() {  
        return isset($this->_data[$this->_position]);  
    }  
      
    public function hasChildren() {  
        return is_array($this->_data[$this->_position]);  
    }  
      
    public function next() {  
        $this->_position++;  
    }  
      
    public function current() {  
        return $this->_data[$this->_position];  
    }  
      
    public function getChildren() {  
        print_r($this->_data[$this->_position]);  
    }  
      
    public function rewind() {  
        $this->_position = 0;  
    }  
      
    public function key() {  
        return $this->_position;  
    }  
}  
  
$arr = array(0, 1=> array(10, 20), 2, 3 => array(1, 2));  
$mri = new MyRecursiveIterator($arr);  
  
foreach ($mri as $c => $v) {  
    if ($mri->hasChildren()) {  
        echo "$c has children: " .PHP_EOL;  
        $mri->getChildren();  
    } else {  
        echo "$v" .PHP_EOL;  
    }  
  
}  
/*  
结果：  
0  
1 has children:  
Array  
(  
[0] => 10  
[1] => 20  
)  
2  
3 has children:  
Array  
(  
[0] => 1  
[1] => 2  
)  
*/
```
**SeekableIterator接口：**

通过seek()方法实现可搜索的迭代器，用于搜索某个位置下的元素。
```php 
class MySeekableIterator implements SeekableIterator {  
  
    private $position = 0;  
      
    private $array = array(  
    "first element" ,  
    "second element" ,  
    "third element" ,  
    "fourth element"  
    );  
      
    public function seek ( $position ) {  
        if (!isset( $this -> array [ $position ])) {  
            throw new OutOfBoundsException ( "invalid seek position ( $position )" );  
        }  
          
        $this -> position = $position ;  
    }  
      
    public function rewind () {  
        $this -> position = 0 ;  
    }  
      
    public function current () {  
        return $this -> array [ $this -> position ];  
    }  
      
    public function key () {  
        return $this -> position ;  
    }  
      
    public function next () {  
        ++ $this -> position ;  
    }  
      
    public function valid () {  
        return isset( $this -> array [ $this -> position ]);  
    }  
}  
  
try {  
  
    $it = new MySeekableIterator ;  
    echo $it -> current (), "\n" ;  
      
    $it -> seek ( 2 );  
    echo $it -> current (), "\n" ;  
      
    $it -> seek ( 1 );  
    echo $it -> current (), "\n" ;  
      
    $it -> seek ( 10 );  
  
} catch ( OutOfBoundsException $e ) {  
    echo $e -> getMessage ();  
}  
/*  
结果：  
first element  
third element  
second element  
invalid seek position ( 10 )  
*/
```
**SplObserver和SplSubject接口:**  
SplObserver和SplSubject接口用来实现观察者设计模式，观察者设计模式是指当一个类的状态发生变化时，依赖它的对象都会收到通知并更新。使用场景非常广泛，比如说当一个事件发生后，需要更新多个逻辑操作，传统方式是在事件添加后编写逻辑，这种代码耦合并难以维护，观察者模式可实现低耦合的通知和更新机制。  
看看SplObserver和SplSubject的接口结构：
```php 
//SplSubject结构 被观察的对象  
interface SplSubject{  
    public function attach(SplObserver $observer); //添加观察者  
    public function detach(SplObserver $observer); //剔除观察者  
    public function notify(); //通知观察者  
}  
  
//SplObserver结构 代表观察者  
interface SplObserver{  
    public function update(SplSubject $subject); //更新操作  
}
```
看下面一个实现观察者的例子：
```php  
class Subject implements SplSubject  
{  
    private $observers = array();  
      
    public function attach(SplObserver $observer)  
    {  
        $this->observers[] = $observer;  
    }  
      
    public function detach(SplObserver $observer)  
    {  
        if($index = array_search($observer, $this->observers, true)) {  
            unset($this->observers[$index]);  
        }  
    }  
  
    public function notify()  
    {  
        foreach($this->observers as $observer) {  
            $observer->update($this);  
        }  
    }  
  
  
}  
  
class Observer1 implements SplObserver  
{  
    public function update(SplSubject $subject)  
    {  
        echo "逻辑1代码".PHP_EOL;  
    }  
}  
  
class Observer2 implements SplObserver  
{  
    public function update(SplSubject $subject)  
    {  
        echo "逻辑2代码".PHP_EOL;  
        echo "逻辑2代码".PHP_EOL;  
    }  
}  
  
  
$subject = new Subject();  
$subject->attach(new Observer1());  
$subject->attach(new Observer2());  
  
$subject->notify();  
/*  
结果：  
逻辑1代码  
逻辑2代码  
*/
```
