# PHP的引用，你知道多少？

 时间 2017-12-15 10:40:28  

原文[https://segmentfault.com/a/1190000012437512][2]


真的是变懒了，一个月一篇的节凑都很难保证了。

最近面试他人的过程中，问了一些关于PHP引用的知识，发现很多同学对这方面知之甚少，还有很多工作中基本没有使用过。甚至有人告诉我要少用引用，引用会带来一些诡异的问题。我心里默默说，避免诡异的问题是要去理解引用而不是少用引用。今天一起来解析解析。

## 场景假设

先从一个引用的所谓诡异问题开始。假设我们有这个场景：我们从数据库中读取了一组订单数据，需要把订单的每条数据单独做些处理。

    $list = [
        ['orderid' => '123', 'total_fee' => 10, 'name' => 'zhangsan'],
        ['orderid' => '456', 'total_fee' => 17, 'name' => 'lisi'],
        ['orderid' => '789', 'total_fee' => 14, 'name' => 'wangwu'],
    ];
    
    foreach ($orders as &$item) {
        // 对订单做了些什么处理
    }
    
    // 有了一些其它操作
    
    $result = [];// 需要返回的结果
    foreach ($orders as $item) {// 重新映射名字
        $result[] = [
            'order_id' => $item['orderid'],
            'amount' => $item['total_fee'],
        ];
    }

上面的程序会输出如下结果：

    var_dump($result);
    
    array(3) {
      [0]=>
      array(2) {
        ["order_id"]=>
        string(3) "123"
        ["total_fee"]=>
        int(10)
      }
      [1]=>
      array(2) {
        ["order_id"]=>
        string(3) "456"
        ["total_fee"]=>
        int(17)
      }
      [2]=>
      array(2) {
        ["order_id"]=>
        string(3) "456"
        ["total_fee"]=>
        int(17)
      }
    }

这就是经常遇到的一种所谓的诡异问题，先用引用循环处理数据，后面又用了与引用相同的临时变量继续处理数据。这里就是： $item 。很多同学说预防这种问题，就要少用引用。这种态度太消极了，引用在很多地方带来了代码书写的简洁，并且针对大数组使用引用能够节省大量的内存。 

## 诡异问题解析

现在我们来分析下上面问题出现的原因。先来看引用的定义

引用意味着用不同的名字访问同一个变量内容。

那么在这部分代码中

    foreach ($orders as &$item) {
        // 对订单做了些什么处理
    }

$item 最后跟 $orders[2] 指向了同一个变量内容。并且在 foreach 循环完后， $item 并没有被销毁，因此在后续如果同名的话，会继续生效。图示如下： 

![][4]

那么再接下来的的另一个循环中。

    foreach ($orders as $item) {// 重新映射名字
        $result[] = [
            'order_id' => $item['orderid'],
            'amount' => $item['total_fee'],
        ];
    }

每当 $orders 把变量赋值给 $item 的时候，都同时改变了 $orders[2] 的值。因此才会出现上面诡异的情况。我来逐步给大家演示下： 

* 第一次循环 $orders[0] ，$item 指向 orderid=123 的订单，由于 $item 是 $orders[2] 的引用，此时导致 $orders[2] 也指向了 orderid=123 的订单；
* 第二次循环 $orders[1] , $item 指向 orderid=456 的订单，因此 $orders[2] 也指向了 orderid=456 ；
* 第三次循环 $orders[2] 的时候，明显其值已经变成了 orderid=456 的订单。

通过上面的分析，我相信大家对引用所谓的诡异有了了解。那么又该如何避免这种情况出现呢？其实很简单，每次使用完引用后，记得 unset 调引用。在后面便可毫无顾忌的继续使用了。具体到本例子就是： 

    foreach ($orders as &$item) {
        // 对订单做了些什么处理
    }
    unset($item);
    
    // 有了一些其它操作
    
    foreach ($orders as $item) {// 重新映射名字
    }

## 引用的妙用

前面我说过，引用可以写出简洁的代码。无限级分类的使用便是一个使用场景。比如说我们有个分类的数据：

    $catList = [
        '1' => ['id' => 1, 'name' => '颜色', 'parent_id' => 0],
        '2' => ['id' => 2, 'name' => '规格', 'parent_id' => 0],
        '3' => ['id' => 3, 'name' => '白色', 'parent_id' => 1],
        '4' => ['id' => 4, 'name' => '黑色', 'parent_id' => 1],
        '5' => ['id' => 5, 'name' => '大', 'parent_id' => 2],
        '6' => ['id' => 6, 'name' => '小', 'parent_id' => 2],
        '7' => ['id' => 7, 'name' => '黄色', 'parent_id' => 1],
    ];

如果我想得到下面这种形式

    $result = [
        ['id' => 1, 'name' => '颜色', 'children' => [
            ['id' => 3, 'name' => '白色'],
            ['id' => 4, 'name' => '黑色'],
            ['id' => 7, 'name' => '黄色']
        ]],
        ['id' => 2, 'name' => '规格', 'children' => [
            ['id' => 5, 'name' => '大'],
            ['id' => 6, 'name' => '小']
        ]]
    ];

如果使用引用，可以非常简单的得出结果。

    $treeData = [];// 保存结果
    foreach ($catList as $item) {
        if (isset($catList[$item['parent_id']]) && ! empty($catList[$item['parent_id']])) {// 肯定是子分类
            $catList[$item['parent_id']]['children'][] = &$catList[$item['id']];
        } else {// 肯定是一级分类
            $treeData[] = &$catList[$item['id']];
        }
    }

大家可以试试不用引用的方式，把无限级实现出来试试，比较下代码。


[2]: https://segmentfault.com/a/1190000012437512

[4]: https://img1.tuicool.com/FRneiiB.jpg