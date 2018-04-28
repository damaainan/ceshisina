## PHP标准库SPL学习之数据结构、常用迭代器、基础接口

来源：[https://segmentfault.com/a/1190000014479581](https://segmentfault.com/a/1190000014479581)


## 一、SPL简介

     什么是SPL

* PHP的标准库SPL：`S`tandard`P`HP`L`ibrary

     SPL: 用于解决常见普遍问题的一组接口与类的集合

     Common Problem：


* 数学建模/数据结构

* 解决数据怎么存储的问题


* 元素遍历

* 数据怎么查看问题


* 常用方法的统一调用


* 通用方法（数组、集合的大小）
* 自定义遍历

* 类定义的自动装载

* 让PHP程序适应大型项目的管理要求，把功能的实现分散到不同的文件中

     SPL的基本框架

![][0]
## 二、SPL的常用数据结构

![][1]
## 2.1 双向链表
### 2.1.1 双向链表简介

![][2]

**`Bottom：`** 最先添加到链表中的节点叫做`Bottom`(底部)，也称为头部(`head`)
**`Top：`** 最后添加到链表中得节点叫做`top`顶部，也称为尾部
**`链表指针:`** 是一个当前关注的节点的标识，可以指向任意节点
**`当前指针：`** 链表指针指向的节点称为当前节点
**`节点名称：`** 可以在链表中唯一标识一个节点的名称，我们通常又称为节点的`key`或`offset`
**`节点数据：`** 存放在链表中的应用数据，通常称为`value`### 2.1.2 双向链表代码实践

```php
/**
 * 双向链表
 */
$obj = new SplDoublyLinkedList();
$obj->push(4);
$obj->push(6);
$obj->unshift(66);
print_r($obj);
```

```
SplDoublyLinkedList Object
(
    [flags:SplDoublyLinkedList:private] => 0
    [dllist:SplDoublyLinkedList:private] => Array
        (
            [0] => 66
            [1] => 4
            [2] => 6
        )

)
```

**`双向链表常用方法：`** 
**`Bottom:`**  获得链表底部（头部）元素，当前指针位置不变
**`Top:`**  获取链表顶部（尾部）元素，当前指针位置不变
**`Push:`**  往链表顶部(`Top`)中追加节点
**`Pop:`**  把`top`位置的节点从链表中删除，操作不改变当前指针的位置
**`Unshif:`**  往链表底部追加节点(`Bottom`)
**`Shif:`**  删除链表底部的节点
**`Rewind:`**  把节点指针指向`Bottom`所在的节点
**`Current:`**  指向链表当前节点的指针， **``必须``**  **`在调用之前先调用`rewind``** 。当指向的节点被删除之后，会指向一个空节点
**`Next:`**  指针指向下一个节点，`current`的返回值随之改变
**`Prev:`**  指针指向上一个节点，`current`的返回值随之改变

     双向链表判断当前节点是否有效节点方法：

```
if(双向链表对象.current())
    有效
else
    无效
```

**`或`** 

```
//用$obj->current()判断当前是否有迭代元素不好，因为当元素值是false,0,或者空字符时
//他们效果和null一样，区分不了，所以严谨的话要使用valid方法判断
if(双向链表对象.valid())
    有效
  else
    无效
```
## 2.2 堆栈
### 2.2.1 堆栈简介

     继承自SplDoublyLinkedList类的SplStack类
     操作：

```
- `push`:压入堆栈（存入）
- `pop`:退出堆栈（取出）
```

     堆栈：单端出入，先进后出 Fist In Last Out（`FILO`）
### 2.2.2 堆栈代码实践

```php
/**
 * 堆栈
 */
$obj = new SplStack();
$obj->push(2);
$obj->push('test');
$obj->push(6);
print_r($obj);

```

```
SplStack Object
(
    [flags:SplDoublyLinkedList:private] => 6
    [dllist:SplDoublyLinkedList:private] => Array
        (
            [0] => 2
            [1] => test
            [2] => 6
        )

)
```

**`常用操作：`** 
**`Bottom()：`**  最先进入的元素；
**`Top():`**  最后进入的元素；
**`offSet(0):`**  **`是`top`的位置`** 
**`rewind():`**  把`top`的元素置为`current()`的位置

**`注意：`** 

```
- 堆栈的`rewind()`指向`top`，双向链表的`rewind()`指向`bottom`
- 堆栈和双向链表都有`next`方法，方向相反

```
## 2.3 队列

     队列和堆栈刚好相反，最先进入队列的元素会最先走出队列
     继承自`SplDoublyLinkedList`类的`SqlQueue`类
**`操作：`** 

```
- `enqueue`:进入队列
- `dequeue`:退出队列
```

```php
/**
 * 队列
 */
$obj = new SplQueue();
$obj->enqueue('a');
$obj->enqueue('b');
$obj->enqueue('c');
print_r($obj);
```

```
SplQueue Object
(
    [flags:SplDoublyLinkedList:private] => 4
    [dllist:SplDoublyLinkedList:private] => Array
        (
            [0] => a
            [1] => b
            [2] => c
        )

)
```

**`常用操作：`**     
**`enqueue：`**  插入一个节点到队列里面的`top`位置
**`dequeue:`**  操作从队列中提取`Bottom`位置的节点,同时从队列里面删除该元素
**`offSet(0):`**  是`Bottom`所在的位置
**`rewind:`**  操作使得指针指向`Bottom`所在的位置的节点
**`next:`**  操作使得当前指针指向`Top`方向的下一个节点
## 三、SPL的常用迭代器
## 3.1 迭代器概述

通过某种 **``统一的方式``**  遍历链表或则数组中的元素的过程叫做迭代遍历，这种统一的遍历工具叫迭代器
     `PHP`中迭代器是通过`Iterator`接口定义的

![][3]
## 3.2 ArrayIterator迭代器

     `ArrayIterator`迭代器用于遍历数组


* `seek()`，指针定位到某个位置，很实用，跳过前面`n-1`的元素
* `ksort()`，对`key`进行字典序排序
* `asort()`，对`值`进行字典序排序


```php
$arr=array(
    'apple' => 'apple value', // position = 0
    'orange' => 'orange value', // position = 1
    'grape' => 'grape value',
    'plum' => 'plum value'
);
$obj=new ArrayObject($arr);
$it =$obj->getIterator();//生成数组的迭代器。
foreach ($it as $key => $value){
    echo $key . ":". $value .'<br />';
}

echo '<br />';
//实现和foreach同样功能
$it->rewind();// 调用current之前一定要调用rewind
While($it->valid()){//判断当前是否为有效数据
    echo $it->key().' : '.$it->current().'<br />';
    $it->next();//千万不能少
}

//实现更复杂功能，跳过某些元素进行打印
$it->rewind();
if ($it->valid()){
    $it->seek(1);//position，跳过前面 n-1的元素
    While($it->valid()){//判断当前是否为有效数据
        echo $it->key().' : '.$it->current().'<br />';
        $it->next();//千万不能少
    }
}

$it->ksort();//对key进行字典序排序
//$it->asort();//对值进行字典序排序
foreach ($it as $key => $value){
    echo $key . ":". $value .'<br />';
}
```

     `foreach`本质会自动生成一个迭代器，只是使用了迭代器的最长用功能，如果要实现复杂需求，`foreach`实现不了，就需要手动生成迭代器对象来使用了
     比如，要从一个大数组中取出一部分数据，`foreach`比较困难，除非他知道数据的样子。将数组或者集合中的全部或者一部数据取出来，用迭代器比较方便
## 3.3 AppendIterator迭代器

     `AppendIterator`能陆续遍历几个迭代器

* 按顺序迭代访问几个不同的迭代器。例如，希望在一次循环中迭代访问两个或者更多的组合

```php
$arr_a = new ArrayIterator(array('a'=> array('a','b'=>234),'b','c'));
$arr_b = new ArrayIterator(array('d','e','f'));
$it = new AppendIterator();
$it->append($arr_a);//追加数组
$it->append($arr_b);//追加数组，然后遍历$it
foreach ($it as $key => $value){
    print_r($value);
}
```
## 3.4 MultipleIterator迭代器

     用于把多个`Iterator`里面的数据组合成为 **`一个整体`** 来访问


* `Multipleiterator`将多个`arrayiterator` **`拼凑起来`** 
* `Appenditerator`将多个`arrayiteratorr` **`连接起来`** 


```php
$idIter = new ArrayIterator(array('01','02','03'));
$nameIter = new ArrayIterator(array('张三','李四','王五'));
$ageIter = new ArrayIterator(array('22','23','25'));
$mit = new MultipleIterator(MultipleIterator::MIT_KEYS_ASSOC);//按照key关联
$mit->attachIterator($idIter,"ID");
$mit->attachIterator($nameIter,"NAME");
$mit->attachIterator($ageIter,"AGE");
foreach ($mit as $value){
    print_r($value);
}
```

```

Array
(
    [ID] => 01
    [NAME] => 张三
    [AGE] => 22
)
Array
(
    [ID] => 02
    [NAME] => 李四
    [AGE] => 23
)
Array
(
    [ID] => 03
    [NAME] => 王五
    [AGE] => 25
)
```
## 四、SPL的基础接口
## 4.1 最常用的接口


* **`Countable：`** 继承了该接口的类可以直接调用 **`count()`** ，得到元素个数
* **`OuterIterator：`** ，如果想对迭代器进行一定的处理之后再返回，可以用这个接口，相当于进行了一次封装，对原来的进行一定的处理
* **`RecursiveIterator：`** ，可以对多层结构的迭代器进行迭代，比如遍历一棵树，类似于`filesystemIterator`
* **`SeekableIterator：`** ,可以通过`seek`方法定位到集合里面的某个特定元素


## 4.2 Countable

     在代码里面经常可以直接用`count($obj)`方法获取对象里面的元素个数

```php
count(array('name'=>'Peter','id'=>'5'));
```

     对于我们定义的类，也能这样访问吗？


* 如果对象本身也有`count`函数，但是没有继承`countable`接口，直接用`count`函数时，不会调用对象自定义的`count`
* 如果对象本身也有`count`函数，同时对象也继承了`countable`接口，直接用`count`函数时，会调用对象自身的`count`函数，效果相当与:`对象->count()`

* `count()`是`Countable`必须实现的接口
* `count(Countable $obj)`返回是类内部的`count()`返回的结果，其会被强制转成`int`

```php
$arr = array(
    array('name' => 'name value', 'id' => 2),
    array('name' => 'Peter', 'id' => 4, 'age' => 22),
);
echo count($arr);
echo count($arr[1]);

class CountMe implements Countable
{
    protected $myCount = 6;
    protected $myCount2 = 3;
    protected $myCount3 = 2;
    public function count()
    {
        // TODO: Implement count() method.
        return $this->myCount;
    }
}
$obj = new CountMe();
echo count($obj); //6
```
## 4.3 OuterIterator

     OuterIterator接口


* 如果想对迭代器进行一定得处理湖再返回，可以用这个接口
* `IteratorIterator`类是`OuterIterator`的实现，扩展的时候，可以直接继承`IteratorIterator`


```php
$array = ['Value1','Value2','Value3','Value4'];
$outerObj = new OuterImpl(new ArrayIterator($array));
foreach ($outerObj as $key => $value){
    echo "++".$key.'-'.$value."\n";
}

class OuterImpl extends IteratorIterator
{
    public function current()
    {
        return parent::current()."_tail";
    }

    public function key()
    {
        return "Pre_".parent::key();
    }
}
```

```
++Pre_0-Value1_tail
++Pre_1-Value2_tail
++Pre_2-Value3_tail
++Pre_3-Value4_tail
```
## 4.4 RecursiveIterator和SeekableIterator

![][4]

![][5]
 **`完！`** 

[参考教程：站在巨人的肩膀上写代码—SPL][6]

[6]: https://www.imooc.com/learn/150
[0]: https://segmentfault.com/img/bV8K4R
[1]: https://segmentfault.com/img/bV8UTE
[2]: https://segmentfault.com/img/bV8Oz3
[3]: https://segmentfault.com/img/bV8SqG
[4]: https://segmentfault.com/img/bV8Umq
[5]: https://segmentfault.com/img/bV8UpN