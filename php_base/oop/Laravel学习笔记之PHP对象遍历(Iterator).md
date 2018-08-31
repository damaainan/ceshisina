## Laravel学习笔记之PHP对象遍历(Iterator)

来源：[https://segmentfault.com/a/1190000006022698](https://segmentfault.com/a/1190000006022698)

说明：本文章主要讲述PHP的对象遍历(Iterator)知识点。由于Laravel框架中就在集合(Collection)中用到了对象遍历知识点，故记录并学习之。同时，作者会将开发过程中的一些截图和代码黏上去，提高阅读效率。

Laravel中在基础集合类`IlluminateSupportCollection`、路由类中`IlluminateRoutingRouteCollection`和分页类中`IlluminatePaginationPaginator`等，都用到了对象遍历这个小知识点，这些类都是实现了`IteratorAggregate`这个接口，这个接口定义`getIterator()`，返回的是迭代器对象。PHP标准扩展库中提供了很多默认迭代器实现类，比较常用的是数组迭代器对象ArrayIterator，参考官网：[迭代器][0]。
## 对象遍历(Iterator)
### 基本遍历

PHP5提供了遍历对象属性的方法，而且默认是可见属性，如代码中foreach遍历对象属性，默认的都是可见属性：

```php
<?php
/**
 * Created by PhpStorm.
 * User: liuxiang
 * Date: 16/7/20
 * Time: 17:29
 */
class TestIterator {
    /**
     * @var string
     */
    public $name    = 'PHP';
    /**
     * @var string
     */
    public $address = 'php.net';

    /**
     * @var string
     */
    protected $sex  = 'man';

    /**
     * @var int
     */
    private $age    = 20;

}

$testIterator = new TestIterator();
foreach ($testIterator as $key => $value) {
    echo $key.':'.$value.PHP_EOL;
}

```

输出的是：

```php
name:PHP
address:php.net
```

如果需要遍历对象的不可见属性，则在对象内部定义一个遍历方法：

```php
public function unAccessIterator()
    {
        echo 'Iterator the unaccess fields:'.PHP_EOL;
        foreach ($this as $key => $value) {
            echo $key.':'.$value.PHP_EOL;
        }
    }
```

对象外部访问：

```php
$testIterator->unAccessIterator();
```

将可以遍历对象的不可见属性，输出结果：

```php
Iterator the unaccess fields:
name:PHP
address:php.net
sex:man
age:20
```
### Iterator接口

PHP提供了Iterator接口，用来定义迭代器对象来自定义遍历，所以利用Iterator接口来构造迭代器，需要实现Iterator定义的几个方法：

```php
<?php
/**
 * Created by PhpStorm.
 * User: liuxiang
 * Date: 16/7/20
 * Time: 17:29
 */
class TestIterator implements Iterator{
    /**
     * @var string
     */
    public $name    = 'PHP';
    /**
     * @var string
     */
    public $address = 'php.net';

    /**
     * @var string
     */
    protected $sex  = 'man';

    /**
     * @var int
     */
    private $age    = 20;

    /**
     * @var array
     */
    private $composerPackage;

    public function __construct($composerPackage = [])
    {
        $this->composerPackage = $composerPackage;
    }

    public function unAccessIterator()
    {
        echo 'Iterator the unaccess fields:'.PHP_EOL;
        foreach ($this as $key => $value) {
            echo $key.':'.$value.PHP_EOL;
        }
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        // TODO: Implement current() method.
        echo 'Return the current element:'.PHP_EOL;
        return current($this->composerPackage);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        // TODO: Implement next() method.
        echo 'Move forward to next element:'.PHP_EOL;
        return next($this->composerPackage);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        // TODO: Implement key() method.
        echo 'Return the key of the current element:'.PHP_EOL;
        return key($this->composerPackage);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        // TODO: Implement valid() method.
        echo 'Checks if current position is valid:'.PHP_EOL;
        return current($this->composerPackage) !== false;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.
        echo 'Rewind the Iterator to the first element:'.PHP_EOL;
        reset($this->composerPackage);
    }
}

/*
$testIterator = new TestIterator();
foreach ($testIterator as $key => $value) {
    echo $key.':'.$value.PHP_EOL;
}
$testIterator->unAccessIterator();*/

$testIterator = new TestIterator([
    'symfony/http-foundation',
    'symfony/http-kernel',
    'guzzle/guzzle',
    'monolog/monolog'
]);
foreach ($testIterator as $key => $value) {
    echo $key.':'.$value.PHP_EOL;
}

```

成员变量$composerPackage是不可见的，通过实现Iterator接口，同样可以遍历自定义的可不见属性，输出结果如下：

```
Rewind the Iterator to the first element:
Checks if current position is valid:
Return the current element:
Return the key of the current element:
0:symfony/http-foundation
Move forward to next element:
Checks if current position is valid:
Return the current element:
Return the key of the current element:
1:symfony/http-kernel
Move forward to next element:
Checks if current position is valid:
Return the current element:
Return the key of the current element:
2:guzzle/guzzle
Move forward to next element:
Checks if current position is valid:
Return the current element:
Return the key of the current element:
3:monolog/monolog
Move forward to next element:
Checks if current position is valid:
```
### IteratorAggregate接口

PHP真心为程序员考虑了很多，实现IteratorAggragate接口后只需实现`getIterator()`方法直接返回迭代器对象，就不需要实现Iterator接口需要的一些方法来创建一些迭代器对象，因为PHP已经提供了很多迭代器对象如ArrayIterator对象。所以再重构下上面代码：

```php
class TestCollection implements IteratorAggregate{
    ...
    /**
     * @var array
     */
    private $composerPackage;
    
    ...
    
    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing  **Iterator**  or
     *  **Traversable** 
     * @since 5.0.0
     */
    public function getIterator()
    {
        // TODO: Implement getIterator() method.
        return new ArrayIterator($this->composerPackage);
    }
   
}

$testCollection = new TestCollection([
    'symfony/http-foundation',
    'symfony/http-kernel',
    'guzzle/guzzle',
    'monolog/monolog'
]);
foreach ($testCollection as $key => $value) {
    echo $key.':'.$value.PHP_EOL;
}
```

同样的，能遍历$testCollection对象的不可见属性$composerPackage，输出结果：

```
0:symfony/http-foundation
1:symfony/http-kernel
2:guzzle/guzzle
3:monolog/monolog
```

文章开头聊到Laravel中就用到了IteratorAggragate这个接口，可以看看文件的源码。
`总结：PHP提供的对象遍历特性功能还是很有用处的，下一篇准备看下generator生成器知识点，generator提供了另外一种方式定义Iterator。多多使用Laravel，研究Laravel源码并模仿之，也不错哦。`欢迎关注[Laravel-China][1]。
[RightCapital][2]招聘[Laravel DevOps][3]

[0]: http://php.net/manual/zh/spl.iterators.php
[1]: https://laravel-china.org/
[2]: https://www.rightcapital.com
[3]: https://join.rightcapital.com