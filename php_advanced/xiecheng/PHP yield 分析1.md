# PHP yield 分析，以及协程的实现，超详细版(上)

 时间 2017-12-02 16:12:00  

原文[http://www.cnblogs.com/lynxcat/p/7954456.html][1]


参考资料 

1. http://www.laruence.com/2015/05/28/3038.html
1. http://php.net/manual/zh/class.generator.php
1. http://www.cnblogs.com/whoamme/p/5039533.html
1. http://php.net/manual/zh/class.iterator.php

PHP的 yield 关键字是php5.5版本推出的一个特性，算是比较古老的了，其他很多语言中也有类似的特性存在。但是在实际的项目中，目前用到还比较少。网上相关的文章最出名的就是鸟哥的那篇了，但是都不够细致理解起来较为困难，今天我来给大家超详细的介绍一下这个特性。 

```php
function gen(){
　　while(true){
　　　　yield "gen\n";
　　}
}

$gen = gen();

var_dump($gen instanceof Iterator);
echo "hello, world!";
```

如果事先没了解过yield，可能会觉得这段代码一定会进入死循环。但是我们将这段代码直接运行会发现，输出hello, world!，预想的死循环没出现。

究竟是什么样的力量，征服了while(true)呢，接下来就带大家一起来领略一下yield关键字的魅力。

首先要从foreach说起,我们都知道对象，数组和对象可以被foreach语法遍历，数字和字符串缺不行。其实除了数组和对象之外PHP内部还提供了一个 Iterator 接口，实现了Iterator接口的对象，也是可以被foreach语句遍历，当然跟普通对象的遍历就很不一样了。

以下面的代码为例:

```php
class Number implements Iterator{
　　protected $key;
　　protected $val;
　　protected $count;

　　public function __construct(int $count){
　　　　$this->count = $count;
　　}

　　public function rewind(){
　　　　$this->key = 0;
　　　　$this->val = 0;
　　}

　　public function next(){
　　$this->key += 1;
　　$this->val += 2;
　　}

　　public function current(){
　　　　return $this->val;
　　}

　　public function key(){
　　return $this->key + 1;
　　}

　　public function valid(){
　　　　return $this->key < $this->count;
　　}
}


foreach (new Number(5) as $key => $value){
　　echo "{$key} - {$value}\n";
}
```

这个例子将输出

1 - 0

2 - 2

3 - 4

4 - 6

5 - 8

关于上面的number对象，被遍历的过程。如果是初学者，可能会出现有点懵的情况。为了深入的了解Number对象被遍历的时候内部是怎么工作的，我将代码改了一下，将接口内的每个方法都尽心输出，借此来窥探一下遍历时对象内部方法的的执行情况。

```php
　　class Number implements Iterator{  
    protected $i = 1;
    protected $key;
    protected $val;
    protected $count; 
    public function __construct(int $count){
        $this->count = $count;
        echo "第{$this->i}步:对象初始化.\n";
        $this->i++;
    }
    public function rewind(){
        $this->key = 0;
        $this->val = 0;
        echo "第{$this->i}步:rewind()被调用.\n";
        $this->i++;
    }
    public function next(){
        $this->key += 1;
        $this->val += 2;
        echo "第{$this->i}步:next()被调用.\n";
        $this->i++;
    }
    public function current(){
        echo "第{$this->i}步:current()被调用.\n";
        $this->i++;
        return $this->val;
    }
    public function key(){
        echo "第{$this->i}步:key()被调用.\n";
        $this->i++;
        return $this->key;
    }
    public function valid(){
        echo "第{$this->i}步:valid()被调用.\n";
        $this->i++;
        return $this->key < $this->count;
    }
}

$number = new Number(5);
echo "start...\n";
foreach ($number as $key => $value){
    echo "{$key} - {$value}\n";
}
echo "...end...\n";
```

以上代码输出如下

```
    第1步:对象初始化.
    start...
    第2步:rewind()被调用.
    第3步:valid()被调用.
    第4步:current()被调用.
    第5步:key()被调用.
    0 - 0
    第6步:next()被调用.
    第7步:valid()被调用.
    第8步:current()被调用.
    第9步:key()被调用.
    1 - 2
    第10步:next()被调用.
    第11步:valid()被调用.
    第12步:current()被调用.
    第13步:key()被调用.
    2 - 4
    第14步:next()被调用.
    第15步:valid()被调用.
    第16步:current()被调用.
    第17步:key()被调用.
    3 - 6
    第18步:next()被调用.
    第19步:valid()被调用.
    第20步:current()被调用.
    第21步:key()被调用.
    4 - 8
    第22步:next()被调用.
    第23步:valid()被调用.
    ...end...
    
    View Code
```
看到这里，我相信大家对Iterator接口已经有一定认识了。会发现当对象被foreach的时候，内部的valid,current,key方法会依次被调用，其返回值便是foreach语句的key和value。循环的终止条件则根据valid方法的返回而定。如果返回的是true则继续循环，如果是false则终止整个循环，结束遍历。当一次循环体结束之后，将调用next进行下一次的循环直到valid返回false。而rewind方法则是在整个循环开始前被调用，这样保证了我们多次遍历得到的结果都是一致的。

那么这个跟yield有什么关系呢，这便是我们接下来要说的重点了。首先给大家介绍一下我总结出来的 yield 的特性,包含以下几点。

1.yield只能用于函数内部，在非函数内部运用会抛出错误。

2.如果函数包含了yield关键字的，那么函数执行后的返回值永远都是一个Generator对象。

3.如果函数内部同事包含yield和return 该函数的返回值依然是Generator对象，但是在生成Generator对象时，return语句后的代码被忽略。

4.Generator类实现了Iterator接口。

5.可以通过返回的Generator对象内部的方法，获取到函数内部yield后面表达式的值。

6.可以通过Generator的send方法给yield 关键字赋一个值。

7.一旦返回的Generator对象被遍历完成，便不能调用他的rewind方法来重置

8.Generator对象不能被clone关键字克隆

首先看第1点，可以明白我们文章开头的gen函数执行后返回的是一个Generatory对象，所以代码可以继续执行下去输出hello, world!，因此$gen是一个Generator对象，由于其实现了Iterator，所以这个对象可以被foreach语句遍历。下面我们来看看对其进行遍历，会是什么样的效果。为了防止被死循环，我加多了一个break语句只进行十次循环，方便我们了解yield的一些特性。

代码如下:

```php
$i = 0;
foreach ($gen as $key => $value) {
    echo "{$key} - {$value}";
    if(++$i >= 10){
        break;
    }
}
```

以上代码输出为

0 - gen

1 - gen

2 - gen

3 - gen

4 - gen

5 - gen

6 - gen

7 - gen

8 - gen

9 - gen

通过观察不难发现其中的规律。在包含yield的函数返回的对象被foreach遍历时, 函数体内部的代码会被对应的执行。PHP 会分析其内部的代码从而生成对应的Iterator接口的方法。

其中key方法实现是返回的是yield出现的次序，从0开始递增。

current方法则是yield后面表达式的值。

而valid方法则在当前yield语句存在的时候返回true, 如果当前不在yield语句的时候返回false。

next方法则执行从当前到下一个yield、或者return、或者函数结束之间的代码。

网上也有文章让大家把yield理解为暂时停止函数的执行，等待外部的激活从而再次执行。虽然看起来确实像那么回事，但我不建议大家这么理解，因为他本身是返回一个迭代器对象，其返回值是可以被用于迭代的。我们理解了他被foreach迭代时，其内部是如运作的之后更易于理解yield关键字的本质。

下面我们再做一个简单的测试，以便更直观的展示他的特性。

```php
function gen1(){
    yield 1;
    echo "i\n";
    yield 2;
    yield 3+1;
}
$gen = gen1();
foreach ($gen as $key => $value) {
    echo "{$key} - {$value}\n";
}
```

以上的代码输出

0 - 1

i

1 - 2

2 - 4

我们来分析一下输出的结果，首先当遍历开始时rewind被执行由于第一个yield之前无任何语句，无任何输出。

key的值为yield出现的次序为0,current为yield表达式后的值也就是1。

foreach开始，valid因为当前为第一个yield,所以返回true。正常输出0 - 1

此时next方法被执行,跳转到了第二个yield，第一个到第二个之间的代码被执行输出了i。

再次进入循环 执行vaild，由于当前在第二个yield上面，所以依然是true

由于next执行了，所以key的值也有刚刚的0变为了1，current的值为2，正常输出 1 - 2。

这时候继续执行next()，进入循环vaild()执行，由于此时到了第三个yield返回依然是true。key的值为2， yield为4。正常输出 2 - 4

再次执行next()，由于后续没有yield了vaild()返回为false， 所以循环到此便终止了。

下面我们用代码来验证一下

```php
$gen = gen1();
var_dump($gen->valid());
echo $gen->key().' - '.$gen->current()."\n";
$gen->next(); 
var_dump($gen->valid());
echo $gen->key().' - '.$gen->current()."\n";
$gen->next(); 
var_dump($gen->valid());
echo $gen->key().' - '.$gen->current()."\n";
$gen->next(); 
var_dump($gen->valid());
```

输出值如下

bool(true)

0 - 1

i

bool(true)

1 - 2

bool(true)

2 - 4

bool(false)

跟我们的分析完全一致，至此我们了解了Iterator接口在遍历时内部的运作方式,也了解了包含yield关键字的函数所生成的对象内部是如何实现Iterator接口的方法的。对于yild的特性了解一半了，但是如果我们仅仅将其用于生成可以被遍历的对象的话，yield目前对我们来说，似乎无太大的用处。当然我们可以利用他来生成一些集合对象，节约一些内存知道数据真正被用到的时候在生成。例如：

我们可以写一个方法

```php
function gen2(){
    yield getUserData();
    yield getBannerList();
    yield getContext();
}
#中间其他操作
#然后在view中获得数据
$data = gen2();
foreach ($data as $key => $value) {
    handleView($key, $value);
}
```

通过以上的代码，我们将几个获取数据的操作都延迟到了数据被渲染的时候执行。节省了中间进行其他操作时获取回来的数据占用的内存空间。然而实际开放项目的过程中，这些数据往往被多处使用。而且这样的结构让我们单独控制数据变得艰难，以此带来的性能提升相对于便利性来说，好处微乎其微。不过还好的是，我们对yield的了解才刚刚到一半，已经有这样的功效了。相信我们在了解完另外一半之后，它的功效将大大提升。

接下来我们来继续了解yield, 由于yield返回的是一个Generator类的对象，这个对象除了实现了Iterator接口之外，内部还有一个相当重要的方法就是send方法，即我们提到的第6点特性，通过send方法我们可以给yield发送一个值作为yield语句的值。

首先大家考虑一下下面的代码

```php
function gen3(){
    echo "test\n";
    echo (yield 1)."I\n";
    echo (yield 2)."II\n";
    echo (yield 3 + 1)."III\n";
}
$gen = gen3();
foreach ($gen as $key => $value) {
    echo "{$key} - {$value}\n";
}
```

执行以后输出

0 - 1

I

1 - 2

II

2 - 4

III

可能这段输出比较难理解，我们接下来，一步一步分析一下为什么得出这样的输入。由于我们知道了foreach的时候gen内部是如何操作的，那么我们便用代码来实现一次。

```php
$gen = gen3();
$gen->rewind();
echo $gen->key().' - '.$gen->current()."\n"; 
$gen->next(); 
```

执行后输出

0 - 1

I

通过这两句我们发现，当前的key为0，current则为1也就是yield后面表达式的值。因为yield 1被括号括起来了，所以yield后面表达式的值是1,如果没有括号则为1."I\n".当然因为1."I\n"是一个错误语法。如果想要测试的朋友需要给1加上双引号。

当执行next时，第1个yield到第二个yieldz之间的的语法被执行。也就是echo (yield 1)."I\n"被执行了,由于我们使用的是next(),所以yield当前是无值的。所以输出了I。需要注意的是在第一个yield之后的语法将不会被执行，而 echo (yield 2). "II\n";属于下一个yield块的语句，所以不会被执行。

到这里，是时候让我们今天最后的主角send方法来表现一下了。

public mixed Generator::send ( mixed $value )

这个是手册里send方法的描述，可以看出来他可以接受一个mixed类型的参数，也会返回一个mixed类型的值。

传入的参数会被做 yield 关键字在语句中的值，而他的返回值则是next之后，$gen->current()的值。

下面我们来尝试一下

```php
$gen = gen3(); 
$gen->rewind();
echo $gen->key().' - '.$gen->current()."\n"; 
echo $gen->send("send value - ");  
```

执行后输出

0 - 1

send value - I

2

这时候我们发现，我们通过send方法成功的将一个值传递给了一个函数的内部，并且当做yield关键字的值给输出了，由于下一个yield的值为2,所以我们调用send返回的值为2，同样被输出。

虽然我们知道了send可以完成内部对函数内部的yield表达式传值，也知道了可以通过$gen->current()获得当前yield表达式之后的值，但是这个有什么用呢。可以看一下这个函数

```php
function gen4(){
    $id = 2;
    $id = yield $id;
    echo $id;
}

$gen = gen4();
$gen->send($gen->current() + 3);
```

根据上面对yield代码的理解，我们不难发现这个函数会输出5,因为current()为2，而当我们send之后 yield的值为 2 + 3，也就是5.同时yield到函数结束之间的代码被执行。也就是$id = 5; echo $id;

通过这样一个简单的例子，我们发现。我们不但从函数内部获得了返回值，并且将他的返回值再次发送给了函数内部参与后续的计算。

关于yield的介绍就到此为止了，本文至此也告一段落。后续将会给大家带来，关于yield的下篇，实现一个调度器使得我们只需要将gen()函数返回的gen对象传递给调度器，其内部的代码就能自动的执行。并且让利用yield来实现并行(伪)，以及在多个$gen对象执行之间建立联系和控制其执行顺序，请大家多多关注。另外由于本人才疏学浅，yield特性较多也较为繁琐。文章内容难免有出错或者不周全的地方，如果大家发现有错误的地方，也希望大家留言告知， 祝大家周末愉快~


[1]: http://www.cnblogs.com/lynxcat/p/7954456.html
