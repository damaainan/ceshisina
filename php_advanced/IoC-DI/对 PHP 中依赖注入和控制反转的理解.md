## 对 PHP 中依赖注入和控制反转的理解

来源：<https://juejin.im/post/5b430952f265da0f66400a28>

时间：2018年07月09日


## 术语介绍
### IoC


* 控制反转（Inversion of Control）
* 依赖关系的转移
* 依赖抽象而非实践


### DI


* 依赖注入（Dependency Injection）
* 不必自己在代码中维护对象的依赖
* 容器自动根据配置，将依赖注入指定对象


### AOP


* Aspect-oriented programming


### 面向方面编程


* 无需修改任何一行程序代码，将功能加入至原先的应用程序中，也可以在不修改任何程序的情况下移除


## 提出需求

某地区有各种不同的商店，每家商店都卖四种水果：苹果十元一个、香蕉二十元一个、橘子三十元一个、西瓜四十元一个，顾客可以在任意商店进行购买，每家商店需要可以随时向税务局提供总销售额。
### 初步代码实现

```php
    class Shop
    {
        // 商店的名字
        private $name;

        // 商店的总销售额
        private $turnover = 0;

        public function __construct($name){
            $this->name = $name;
        }

        // 售卖商品
        public function sell($commodity){
            switch ($commodity){
                case 'apple':
                    $this->turnover += 10;
                    echo "卖出一个苹果<br/>";
                    break;
                case 'banana':
                    $this->turnover += 20;
                    echo "卖出一个香蕉<br/>";
                    break;
                case 'orange':
                    $this->turnover += 30;
                    echo "卖出一个橘子<br/>";
                    break;
                case 'watermelon':
                    $this->turnover += 40;
                    echo "卖出一个西瓜<br/>";
                    break;
            }
        }
        // 显示商店目前的总销售额
        public function getTurnover(){
            echo $this->name.'目前为止的销售额为：'.$this->turnover;
        }
    }

    // 顾客类
    class Human
    {
        //从商店购买商品
        public function buy(Shop $shop,$commodity){
            $shop->sell($commodity);
        }
    }

    // new一个名为kfc的商店
    $kfc = new Shop('kfc');
    // new一个名为mike的顾客
    $mike = new Human();

    // mike从kfc买了一个苹果
    $mike->buy($kfc,'apple');
    // mike从kfc买了一个香蕉
    $mike->buy($kfc,'banana');

    // 输出kfc的总营业额
    echo $kfc->getTurnover();
```

可以看到，虽然代码完成了对目前需求的实现，但是此时的 **`shell()`**  方法依赖于具体的实践并且拥有绝对的控制权。一旦我们需要在商店加入一个新的商品，比如芒果mango，那我们不得不去修改商店类的 **`sell()`**  方法，违反了 **`OCP`**  原则，即 **`对扩展开放，对修改关闭`** 。

此时我们可以修改代码如下

```php
    abstract class Fruit
    {
        public $name;
        public $price;
    }
    class Shop
    {
        //商店的名字
        private $name;

        //商店的总销售额
        private $turnover = 0;

        public function __construct($name){
            $this->name = $name;
        }

        //售卖商品
        public function sell(Fruit $commodity){
            $this->turnover += $commodity->price;
            echo '卖出一个'.$commodity->name.',收入'.$commodity->price."元<br/>";
        }

        //显示商店目前的总销售额
        public function getTurnover(){
            echo $this->name.'目前为止的销售额为：'.$this->turnover;
        }
    }

    //顾客类
    class Human
    {
        //从商店购买商品
        public function buy(Shop $shop,$commodity){
            $shop->sell($commodity);
        }
    }

    class Apple extends Fruit
    {
        public $name = 'apple';
        public $price = 10;
    }
    class Bananae extends Fruit
    {
        public $name = 'banana';
        public $price = 20;
    }
    class Orange extends Fruit
    {
        public $name = 'orange';
        public $price = 30;
    }
    class Watermelon extends Fruit
    {
        public $name = 'watermelon';
        public $price = 40;
    }

    //new一个名为kfc的商店
    $kfc = new Shop('kfc');
    //new一个名为mike的顾客
    $mike = new Human();

    //mike从kfc买了一个苹果
    $mike->buy($kfc,new Apple());
    //mike从kfc买了一个香蕉
    $mike->buy($kfc,new Bananae());

    //输出kfc的总营业额
    echo $kfc->getTurnover();
```

上面的代码增加了一个名为 **`Fruit`**  的抽象类，所有的水果都独立成不同的继承了 **`Fruit`**  的类，此时 **`sell()`**  方法不再依赖具体的水果名，而是依赖于抽象的 **`Fruit`**  类，决定卖了多少钱的控制权不再包含在方法内，而是由方法外传入，这就是 **`控制反转`** ，而实现控制反转的过程就是 **`依赖注入`** 。
### 为什么需要依赖注入？

可以发现，此时，如果我们突然想要给所有的商店加入一样名为芒果的商品，我们无需去修改高层（Shop类）的代码，我们只需要添加如下代码即可

```php
    class Lemon extends Fruit
    {
        public $name = 'Lemon';
        public $price = 50;
    }
```

购买柠檬：

```php
$mike->buy($kfc,new Lemon());
```

同样如果我们需要删除某样商品（功能），我们只需要删除对应类的代码就可以了。这样就实现了 **`OCP`**  原则，使代码的扩展和维护都变得更为简单。
### 相关文章链接：


* [依赖注入那些事][0]
* [PHP程序员如何理解IoC/DI][1]


[0]: https://link.juejin.im?target=http%3A%2F%2Fwww.cnblogs.com%2Fleoo2sk%2Farchive%2F2009%2F06%2F17%2F1504693.html
[1]: https://link.juejin.im?target=https%3A%2F%2Fsegmentfault.com%2Fa%2F1190000002411255