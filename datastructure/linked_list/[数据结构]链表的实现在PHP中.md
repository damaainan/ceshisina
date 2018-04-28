## [数据结构]链表的实现在PHP中

来源：[https://segmentfault.com/a/1190000014556511](https://segmentfault.com/a/1190000014556511)

开始对数据结构的学习


-----

今天写代码换了一个字体，以前一直用`console`很好看，今天发现一个更喜欢的风格`Source Code Pro`
上两张图，还是挺好看的！！！


![][0] 


![][1]


-----

步入正题，讲讲链表的操作
## 节点

* 首先得有一个节点类，用于存储数据

```php
<?php

namespace LinkedList;

class Node
{
    /**
     * @var $data integer
     */
    public $data;

    /**
     * 节点指向的下一个元素
     *
     * @var $next Node
     */
    public $next;

  
    public function __construct(int $data = null)
    {
            // 初始化赋值 data，也可通过 $node->data = X; 赋值
        $this->data = $data;
    }
}
```
## 链表管理类（用于操作节点数据）

* 操作类的代码由于太长，我们分部分解析

## 头插入（因为比较简单，所以先讲这个）


* 听名字，就知道是从头部插入一个节点
* 当链表为空，则初始化当前节点
* 当链表不为空，把新节点作为头结点


```php
public function insertHead(int $data) : bool
{
    ///////////////////////////////////////////////////////////////////////////
    // +-----------+    +--------+    +--------+
    // |           |    |        |    |        |
    // | head node | +> |  node  | +> |  node  | +>
    // |           | |  |        | |  |        | |
    // |           | |  |        | |  |        | |
    // |    next   | |  |  next  | |  |  next  | |
    // +------+----+ |  +----+---+ |  +----+---+ |
    //        |      |       |     |       |     |
    //        +------+       +-----+       +-----+
    ///////////////////////////////////////////////////////////////
    //                   +-----------+    +--------+    +--------+
    //                   |           |    |        |    |        |
    //             +---> | head node | +> |  node  | +> |  node  | +>
    //             |     |           | |  |        | |  |        | |
    //             |     |           | |  |        | |  |        | |
    //             |     |    next   | |  |  next  | |  |  next  | |
    //             |     +------+----+ |  +----+---+ |  +----+---+ |
    //             |            |      |       |     |       |     |
    //  +--------+ |            +------+       +-----+       +-----+
    //  |        | |
    //  |new node| |
    //  |        | |
    //  |        | |
    //  |  next  | |
    //  +----+---+ |
    //       |     |
    //       +-----+
    //
    // 1. 实例化一个数据节点
    // 2. 使当前节点的下一个等于现在的头结点
    //        即使当前头结点是 null，也可成立
    // 3. 使当前节点成为头结点
    //        即可完成头结点的插入
    $newNode = new Node($data);
    $newNode->next = $this->head;
    $this->head = $newNode;

    return true;
}
```
## 插入节点（index=0 是头结点，依次下去，超出位置返回 false）

```php
public function insert(int $index = 0, int $data) : bool
{
    // 头结点的插入, 当头部不存在，或者索引为0
    if (is_null($this->head) || $index === 0) {
        return $this->insertHead($data);
    }

    // 正常节点的插入, 索引从 0 开始计算
    // 跳过了头结点，从 1 开始计算
    $currNode = $this->head;
    $startIndex = 1;
    // 遍历整个链表，如果当前节点是 null，则代表到了尾部的下一个，退出循环
    for ($currIndex = $startIndex; ! is_null($currNode); ++ $currIndex) {

        ////////////////////////////////////////////////////////////////////////////
        ///
        //   +--------+    +--------+    +-------------+    +--------+
        //   |        |    |        |    |             |    |        |
        //   |  node  | +> |currNode| +> |currNode next| +> |  node  | +>
        //   |        | |  |        | |  |             | |  |        | |
        //   |        | |  |        | |  |             | |  |        | |
        //   |  next  | |  |  next  | |  |     next    | |  |  next  | |
        //   +----+---+ |  +----+---+ |  +------+------+ |  +----+---+ |
        //        |     |       |     |         |        |       |     |
        //        +-----+       +-----+         +--------+       +-----+
        ////////////////////////////////////////////////////////////////////////////
        //   +--------+    +--------+                +-------------+    +--------+
        //   |        |    |        |                |             |    |        |
        //   |  node  | +> |currNode|             +> |currNode next| +> |  node  | +>
        //   |        | |  |        |             |  |             | |  |        | |
        //   |        | |  |        |             |  |             | |  |        | |
        //   |  next  | |  |  next  |             |  |     next    | |  |  next  | |
        //   +----+---+ |  +--------+             |  +------+------+ |  +----+---+ |
        //        |     |              +--------+ |         |        |       |     |
        //        +-----+              |        | |         +--------+       +-----+
        //                             |new node| |
        //                             |        | |
        //                             |        | |
        //                             |  next  | |
        //                             +----+---+ |
        //                                  |     |
        //                                  +-----+
        ////////////////////////////////////////////////////////////////////////////
        //
        //   +--------+    +--------+                +-------------+    +--------+
        //   |        |    |        |                |             |    |        |
        //   |  node  | +> |currNode|             +> |currNode next| +> |  node  | +>
        //   |        | |  |        |             |  |             | |  |        | |
        //   |        | |  |        |             |  |             | |  |        | |
        //   |  next  | |  |  next  |             |  |     next    | |  |  next  | |
        //   +----+---+ |  +----+---+             |  +------+------+ |  +----+---+ |
        //        |     |       |      +--------+ |         |        |       |     |
        //        +-----+       |      |        | |         +--------+       +-----+
        //                      +----> |new node| |
        //                             |        | |
        //                             |        | |
        //                             |  next  | |
        //                             +----+---+ |
        //                                  |     |
        //                                  +-----+
        //
        // 1. 当前索引等于传入参数的索引
        // 2. 实例化新数据节点
        // 3. 新节点的下一个指向当前节点的下一个节点
        // 4. 当前节点的下一个节点指向新节点
        if ($currIndex === $index) {
            $newNode = new Node($data);
            $newNode->next = $currNode->next;
            $currNode->next = $newNode;

            return true;
        }
        // 移动到下一个节点
        $currNode = $currNode->next;
    }

    return false;
}
```


-----

以上两个这是插入的基本操作。看一下实例的代码。

```php
<?php
// 自动加载的代码就不贴了，直接在 github
require __DIR__.'/../vendor/bootstrap.php';

// 实例化一个链表管理对象
$manager = new \LinkedList\Manager();
// 8
$manager->insertHead(8);
// 5 8
$manager->insertHead(5);
// 1 5 8
$manager->insertHead(1);
// 1 2 5 8
$manager->insert(1, 2);
// false 节点元素不足 6 个
$manager->insert(5, 4);
// 1 2 5 8 9
$manager->insertEnd(9);

// 3
$manager->find(8);

// 1 2 8 9
$manager->delete(2);
```
## 查找

* 查找链表的值也是很简单的，只要遍历即可

```php
/**
* 查找链表的值中的索引
* 成功返回索引值，找不到返回 -1
*
* @param int $data
* @return int
*/
public function find(int $data) : int
{
    $currNode = $this->head;
    // 查找还是很简单的，只要遍历一次链表，然后再判断值是否相等就可以了
    for ($i = 0; ! is_null($currNode); ++ $i) {
        if ($currNode->data === $data) {
            return $i;
        }

        $currNode = $currNode->next;
    }

    return -1;
}
```

* 只需要遍历一次链表，找到相等的值，找到返回索引值，找不到返回 -1

## 删除

```php
/**
 * 删除链表的节点
 *
 * @param int $index
 * @return bool
 */
public function delete(int $index) : bool
{
    // 没有任何节点，直接跳过
    if (is_null($this->head)) {
       return false;
    } elseif ($index === 0) {
        // 头结点的删除
        $this->head = $this->head->next;
    }

    // 这里的开始的索引是 1
    // 但当前节点指向的确实 头结点
    // 因为删除的时候必须标记删除的前一个节点
    // for 的判断是判断下一个节点是否为 null
    // $currNode 是操作的节点
    //    $currNode->next 是要删除的节点
    $startIndex = 1;
    $currNode = $this->head;

    for ($i = $startIndex; ! is_null($currNode->next); ++ $i) {
    
        if ($index === $i) {
            // 使当前节点等于要删除节点的下一个
            // 即可完成删除
            $currNode->next = $currNode->next->next;
            break;
        }
        $currNode = $currNode->next;
    }

    return true;
}
```
## End


* 代码已托管在[github][2]

* 后续有时间继续学习数据结构，双链表，树之类的！！！


[2]: https://github.com/DavidNineRoc/data-structure
[0]: https://segmentfault.com/img/remote/1460000014556516
[1]: https://segmentfault.com/img/remote/1460000014556517