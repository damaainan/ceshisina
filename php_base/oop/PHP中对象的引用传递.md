## PHP中对象的引用传递

来源：[https://segmentfault.com/a/1190000013842411](https://segmentfault.com/a/1190000013842411)

很多数据类型都可以写时复制（copy-on-write）,如$a=$b,两个变量赋予的值相等。
对于对象就不一样：

```php
$box1 = new Parcel();
$box1->destinationCountry = 'Denmark';

$box2 = $box1;
$box2->destinationCountry = 'Brazil';

echo 'Parcels need to ship to:' . $box1->destinationCountry . ' and ' . $box2->destinationCountry;
//打印结果 
//Parcels need to ship to: Brazil and Brazil
```

现在的情况是，当将$box1赋值给$box2时，并没有复制$box1的值。相反，PHP使用了另一种方式将$box2指向同一个对象，称其为`引用（reference）`。
通过使用==操作符来比较两个对象，可以知道它们是否具有相同的类和属性。

```php
if($box1 == $box2) echo 'equivalent';
```

还可以更进一步区分它们是否引用同一个原始对象，可用同样的方式===操作符进行比较：

```php
if($box1 === $box2) echo 'exact same object!';
```

当两个变量指向相同的值时，===比较操作符才会返回true。如果对象是完全相同的，但存储在不同的位置，将返回false。
`对象总是通过引用传递`。即当传递一个对象到一个函数中，这个函数会作用于相同的对象，如果这个对象在函数内部发生变化，这种变化会反映到函数外部。这是将一个对象赋值给一个新变量的行为延伸。
对象总是以这样的方式表现，即它们提供一个对原始对象的`引用`，而不是创建自己的一个`副本`。

```php
$courier = new PigeonPost('Avian Delivery Ltd');

$other_courier = $courier;
$other_courier->name = 'Pigeon Post';

echo $courier->name; // outputs "Pigeon Post"

```

对象会提供一个指向自己的引用，而不是复制自己的一个副本。这意味着如果一个函数对传入的一个对象进行操作时，没有必要从函数中返回。这种变化会在对象的原始副本上反映出来。

如果需要为一个已经存在的对象复制一个单独的副本，可以使用`clone`这个关键字来创建。

```php
$courier = new PigeonPost('Avian Delivery Ltd');

$other_courier = clone $courier;
$other_courier->name = 'Pigeon Post';

echo $courier->name; // outputs "Avian Delivery Ltd"
```

当复制一个对象时，存储在其属性中的任何对象都将是引用而不是副本。
PHP有一个神奇的方法，即如果声明了一个对象，当复制这个对象时，会调用这个对象，这就是_clone()方法，你可以声明而且以此来决定`当复制对象时会做什么，甚至不接受复制`。
 **`流畅的接口`** 
对象总是通过引用传递，这表明无需从一个方法中返回一个对象来观察它的变化。然而，如果从一个方法中返回$this,可以在应用程序内建立一个 **`流畅的接口（fluent interface）`** ，可让你将方法链接在一起。其工作原理如下：
1.创建对象
2.调用对象的方法
3.得到从方法中返回的修正对象
4.选择返回步骤2

```php
class Parcel
{
    protected $weight;
    protected $destinationCountry;
    
    public function setWeight($weight) {
        echo "weight set to: " . $weight . "\n";
        $this->weight = $weight;
        return $this;
    }
    
    public function setCountry($country) {
        echo "destination country is: " . $country . "\n";
        $this->destinationCountry = $country;
        return $this;
    }
} 

$myParcel = new Parcel;
$myParcel->setWeight(5)->setCountry('Peru');

```

这里的关键是可以在一行代码中调用多个方法（可以加一些换行符以增加代码的可读性），并可按任意顺序调用。由于每个方法都返回生成的对象，因此可以通过返回对象再调用下一个方法。
