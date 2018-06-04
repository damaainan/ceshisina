## php实现基本数据结构之链表

来源：[https://juejin.im/post/5af0f46c6fb9a07ace58ce98](https://juejin.im/post/5af0f46c6fb9a07ace58ce98)

时间 2018-05-08 16:18:03

 
链表（Linked list）是一种常见的基础数据结构，是一种线性表，但是并不会按线性的顺序存储数据，而是在每一个节点里存到下一个节点的指针(Pointer)。
 
使用链表结构可以克服数组链表需要预先知道数据大小的缺点，链表结构可以充分利用计算机内存空间，实现灵活的内存动态管理。但是链表失去了数组随机读取的优点，同时链表由于增加了结点的指针域，空间开销比较大。
 
链表有很多种不同的类型：单向链表，双向链表以及循环链表。
 
## 单向链表
 
链表中最简单的一种是单向链表，它包含两个域，一个信息域和一个指针域。这个链接指向列表中的下一个节点，而最后一个节点则指向一个空值。
 
 ![][0]
 
#### PHP实现简单的单向链表
 
```php
<?php

class Node
{
    private $Data;//节点数据
    private $Next;//存储下个点对象

    public function __construct($data, $next)
    {
        $this->Data = $data;
        $this->Next = $next;
    }

    public function __set($name, $value)
    {
        if (isset($this->$name))
            $this->$name = $value;
    }

    public function __get($name)
    {
        if (isset($this->$name))
            return $this->$name;
        else
            return NULL;
    }
}

class LinkList
{
    private $head;//头节点
    private $len;

    /**
     * 初始化头节点
     */
    public function __construct()
    {
        $this->init();
    }

    public function setHead(Node $val)
    {
        $this->head = $val;
    }

    public function getHead()
    {
        return $this->head;
    }

    public function getLen()
    {
        return $this->len;
    }

    public function init()
    {
        $this->setHead(new Node(NULL, NULL));
        $this->len = 0;
    }

    /**
     * 设置某位置节点的数据
     * @param int $index
     * @param $data
     * @return bool
     */
    public function set(int $index, $data)
    {
        $i = 1;
        $node = $this->getHead();
        while ($node->Next !== NULL && $i <= $index) {
            $node = $node->Next;
            $i++;
        }
        $node->Data = $data;
        return TRUE;
    }

    /**
     * 获取某位置节点的数据
     * @param int $index
     * @return mixed
     */
    public function get(int $index)
    {
        $i = 1;
        $node = $this->getHead();
        while ($node->Next !== NULL && $i <= $index) {
            $node = $node->Next;
            $i++;
        }
        return $node->Data;
    }

    /**
     * 在某位置处插入节点
     * @param $data
     * @param int $index
     * @return bool
     */
    public function insert($data, int $index = 0)
    {
        if ($index <= 0 || $index > $this->getLen())
            return FALSE;
        $i = 1;
        $node = $this->getHead();
        while ($node->Next !== NULL) {
            if ($index === $i) break;
            $node = $node->Next;
            $i++;
        }
        $node->Next = new Node($data, $node->Next);
        $this->len++;
        return TRUE;
    }

    /**
     * 删除某位置的节点
     * @param int $index
     * @return bool
     */
    public function delete(int $index)
    {
        if ($index <= 0 || $index > $this->getLen())
            return FALSE;
        $i = 1;
        $node = $this->getHead();
        while ($node->Next !== NULL) {
            if ($index === $i) break;
            $node = $node->Next;
            $i++;
        }
        $node->Next = $node->Next->Next;
        $this->len--;
        return TRUE;
    }
}
```
 
双向链表一种更复杂的链表是“双向链表”或“双面链表”。每个节点有两个连接：一个指向前一个节点，（当此“连接”为第一个“连接”时，指向空值或者空列表）；而另一个指向下一个节点，（当此“连接”为最后一个“连接”时，指向空值或者空列表）
 
 ![][1]
 
循环链表在一个 循环链表中,首节点和末节点被连接在一起。这种方式在单向和双向链表中皆可实现。要转换一个循环链表，你开始于任意一个节点然后沿着列表的任一方向直到返回开始的节点。再来看另一种方法，循环链表可以被视为“无头无尾”。这种列表很利于节约数据存储缓存，假定你在一个列表中有一个对象并且希望所有其他对象迭代在一个非特殊的排列下。 指向整个列表的指针可以被称作访问指针。
 
 ![][2]
 
基本思路都差不多有时间继续更新
 


[0]: ./img/6FVBniZ.png 
[1]: ./img/VV3aaiI.png 
[2]: ./img/MZFFJjA.png 