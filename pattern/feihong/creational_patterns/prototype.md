### 原型模式
原型模式(Prototype)通过clone已经创建好的对象来创建新的对象。

- 与工厂模式类型，都是用于创建对象；
- 与工厂模式的实现不同，原型模式是先创建好一个对象，然后通过clone原型对象来创建新的对象。这样就免去了创建对象时重复初始化的开销；
- 原型模式适用于大对象的创建。创建一个大对象需要很大的开销，如果每次new就会消耗很大，原型模式仅需拷贝内存即可。

也就是说，我们如果需要反复实例化某个大对象，使用new关键字开销太大，而使用原型模式可以降低开销。

示例：
接口IProduct：
``` php
namespace yjc\Prototype;

interface IProduct{}
```
需要反复实例化的类Product：
``` php
namespace yjc\Prototype;

class Product implements IProduct{}
```
工厂类：
``` php
namespace yjc\Prototype;

class Factory
{
    private $ins;

    public function __construct(IProduct $product)
    {
        $this->ins = $product;
    }

    public function getInstance(){
        return clone $this->ins;
    }
}
```
测试：
``` php
$factory = new Factory(new Product());
$p1 = $factory->getInstance();
$p1->name = 'p1';

$p2 = $factory->getInstance();
$p2->name = 'p2';

print_r($p1->name);
print_r($p2->name);
```
输出：
```
p2
p2
```