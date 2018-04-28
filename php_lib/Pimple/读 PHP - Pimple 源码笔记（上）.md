## 读 PHP - Pimple 源码笔记（上）

来源：[https://segmentfault.com/a/1190000014480078](https://segmentfault.com/a/1190000014480078)

也就是闲时为了写文章而写的一篇关于 Pimple 源码的阅读笔记。
Pimple 代码有两种编码方式，一种是以 PHP 编写的，另一种是以 C 扩展编写的方式，当然个人能力有限呀，也就看看第一种了。

Pimple 链接  
[官网 WebSite][0]   
[GitHub - Pimple][1]   
[Pimple 中文版文档][2]

## 前提知识
### ArrayAccess（数组式访问）接口

提供像访问数组一样访问对象的能力的接口。

[http://php.net/manual/zh/clas...][3]
一个 Class 只要实现以下规定的 4 个接口，就可以是像操作数组一样操作 Object 了。

```php
ArrayAccess {
    /* 方法 */
    abstract public boolean offsetExists ( mixed $offset )
    abstract public mixed offsetGet ( mixed $offset )
    abstract public void offsetSet ( mixed $offset , mixed $value )
    abstract public void offsetUnset ( mixed $offset )
}
```

伪代码如下

```php

class A implements \ArrayAccess {
    // 实现了 4 个接口
}

$a = new A();

// 可以这么操作
$a['x'] = 'x'; // 对应 offsetSet  
echo $a['x']; // 对应 offsetGet  
var_dump(isset($a['x'])); // 对应 offsetExists  
unset($a['x']); // 对应 offsetUnset  

```

特别说明，只支持上面四种操作，千万别以为实现了 ArrayAccess，就可以使用 foreach 了，要实现循环 = 迭代，要实现 [Iterator（迭代器）接口][4]，其实 PHP 定义了很多 [预定义接口][5] 有空可以看看。
### SPL - SplObjectStorage
#### SPL

[SPL][6] 是 Standard PHP Library（PHP标准库）的缩写，一组旨在解决标准问题的接口和类的集合。SPL 提供了一套标准的数据结构，一组遍历对象的迭代器，一组接口，一组标准的异常，一系列用于处理文件的类，提供了一组函数，具体可以查看文档。
#### SplObjectStorage

[SplObjectStorage][7] 是 SPL 标准库中的数据结构对象容器，用来存储一组对象，特别是当你需要唯一标识对象的时候 。

```php
SplObjectStorage implements Countable , Iterator , Serializable , ArrayAccess {

    /* 
     * 向 SplObjectStorage 添加一个 object，$data 是可选参数
     * 因为 SplObjectStorage 实现了 ArrayAccess 的接口，所以可以通过数组的形式访问，这里相当于设置 object 为数组的 key ，data 是对应的 value，默认 data 是 null
     */
    public void attach ( object $object [, mixed $data = NULL ] )
    
    /* 
     * 检查 SplObjectStorage 是否包含 object ，相当于 isset 判断
     */
    public bool contains ( object $object )
    
    /* 
     * 从 SplObjectStorage 移除 object ，相当于 unset 
     */
    public void detach ( object $object )
    // 其他接口定义可以自行查看文档
    
}
```

SplObjectStorage 实现了 [Countable][8]、Iterator、Serializable、ArrayAccess 四个接口，可实现统计、迭代、序列化、数组式访问等功能，其中 Iterator 和 ArrayAccess 在上面已经介绍过了。
### 魔术方法 __invoke()

[__invoke()][9] 当尝试以调用函数的方式调用一个对象时，__invoke() 方法会被自动调用。

看一个例子吧，一目了然。

```php
<?php
class CallableClass 
{
    function __invoke($x) {
        var_dump($x);
    }
}
$obj = new CallableClass;
$obj(5);
var_dump(is_callable($obj));

//output 

//int(5)
//bool(true)

```
## 读源码
### 目录接口

```php
pimple
├── CHANGELOG
├── LICENSE
├── README.rst
├── composer.json
├── ext // C 扩展，不展开
│   └── pimple
├── phpunit.xml.dist
└── src
    └── Pimple
        ├── Container.php
        ├── Exception // 异常类定义，不展开
        ├── Psr11
        │   ├── Container.php
        │   └── ServiceLocator.php
        ├── ServiceIterator.php
        ├── ServiceProviderInterface.php
        └── Tests // 测试文件，不展开

```

PS， Markdown 写目录格式真是麻烦，后来找了一个工具 [tree][10] 可以直接生成结构。
### Container.php

```php
class Container implements \ArrayAccess
{
    private $values = array(); // 存储 value 的数组
    private $factories; // 存储工厂方法的对象，是 SplObjectStorage 的实例
    private $protected; // 存储保护方法的对象，是 SplObjectStorage 的实例
    
    // 存储被冻结的服务，新设置一个 service 的时候，可以在还没有调用这个 service 的时候，覆盖原先设置，这时不算冻结
    // 一旦调用了这个 service 之后，就会存入 $frozen 数组，如果这时还想重新覆盖这个 service 会报错，判断逻辑在 offsetSet 实现。
    private $frozen = array(); 
    
    private $raw = array(); // 存储 service 原始设置内容，用于 ::raw() 方法读取 
    private $keys = array(); // 存储 key
    
    public function __construct(array $values = array())
    {
        $this->factories = new \SplObjectStorage();
        $this->protected = new \SplObjectStorage();

        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }
    
    public function offsetSet($id, $value){}
    public function offsetGet($id){}
    public function offsetExists($id){}
    public function offsetUnset($id){}
    public function factory($callable){}
    public function protect($callable){}
    public function raw($id){}
    public function extend($id, $callable){}
    public function keys(){}
    public function register(ServiceProviderInterface $provider, array $values = array()){}
}
```

Container 实现了 ArrayAccess 接口，这就可以理解为什么可以通过数组的方式定义服务了。
#### 重要的 function 分析

1、offsetSet、offsetExists、offsetUnset 主要实现 ArrayAccess 的接口很容易看懂
2、factory、protect 主要逻辑是判断传入的 $callable 是否有 __invoke ，如果有的话，通过 SplObjectStorage::attach，存储 object 中
3、raw 获取设置的原始内容
4、key 获取所有的 key
5、register() 注册一些通用的 service

6、offsetGet()

```php
    public function offsetGet($id)
    {
        if (!isset($this->keys[$id])) {    // 如果没有设置过，报错
            throw new UnknownIdentifierException($id);
        }
        
        if (
            isset($this->raw[$id])  // raw 里已经有值，一般来说就是之前已经获取过一次实例，再次获取的时候，就返回相同的值
            || !\is_object($this->values[$id]) // 对应的 value 不是 object ，而是一个普通的值
            || isset($this->protected[$this->values[$id]]) // 存在于 protected 中
            || !\method_exists($this->values[$id], '__invoke') // 对应的 value 不是闭包
        ) {
            return $this->values[$id]; // 返回 values 数组里的值
        }

        if (isset($this->factories[$this->values[$id]])) { // 如果工厂方法里面设置了相关方法
            return $this->values[$id]($this); // 直接调用这个方法，传入参数($this)，也就是匿名函数中可以访问当前实例的其他服务
        }

        $raw = $this->values[$id];
        $val = $this->values[$id] = $raw($this); // 初始化一般的 service ，传入($this) ，以后再调用都获取相同的实例
        $this->raw[$id] = $raw; // 把原始内容存入 raw 数组

        $this->frozen[$id] = true; // 在初始化之后冻结这个 key ，不能被覆盖

        return $val;
    }
```

7、extend()

扩展一个 service，如果已经被冻结了，也不能被扩展。
与上文说的直接覆盖还是有区别的，直接覆盖就是完全不管之前定义的 service ，使用 extend 是可以在原始定义上做出修改

```php
    public function extend($id, $callable)
    {
        // ... 一些判断逻辑省略
        
        // 如果是 protected 的 service 还不被支持 extend 
        if (isset($this->protected[$this->values[$id]])) {
            @\trigger_error(\sprintf('How Pimple behaves when extending protected closures will be fixed in Pimple 4. Are you sure "%s" should be protected?', $id), \E_USER_DEPRECATED);
        }

        if (!\is_object($callable) || !\method_exists($callable, '__invoke')) {
            throw new ExpectedInvokableException('Extension service definition is not a Closure or invokable object.');
        }

        $factory = $this->values[$id];

        // 主要是这两行代码
        $extended = function ($c) use ($callable, $factory) {
            return $callable($factory($c), $c);
        };

        if (isset($this->factories[$factory])) {
            $this->factories->detach($factory);
            $this->factories->attach($extended);
        }

        return $this[$id] = $extended;
    }
```
## 未完待续。

还有一篇，主要关于 PSR11 兼容性的。

原创文章，欢迎转载。转载请注明出处，谢谢。
原文链接地址：[http://dryyun.com/2018/04/18/...][11]
作者: [dryyun][12]  
发表日期: 2018-04-18 14:36:40

[0]: https://pimple.symfony.com/
[1]: https://github.com/silexphp/Pimple
[2]: https://dryyun.com/2018/04/17/php-pimple/
[3]: http://php.net/manual/zh/class.arrayaccess.php
[4]: http://php.net/manual/zh/class.iterator.php
[5]: http://php.net/manual/zh/reserved.interfaces.php
[6]: http://php.net/manual/zh/book.spl.php
[7]: http://php.net/manual/zh/class.splobjectstorage.php
[8]: http://php.net/manual/zh/class.countable.php
[9]: http://php.net/manual/zh/phpuage.oop5.magic.php#object.invoke
[10]: http://mama.indstate.edu/users/ice/tree/
[11]: http://dryyun.com/2018/04/18/read-pimple-soure-code/
[12]: https://dryyun.com/