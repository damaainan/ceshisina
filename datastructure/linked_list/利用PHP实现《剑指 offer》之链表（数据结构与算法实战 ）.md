## 利用PHP实现《剑指 offer》之链表（数据结构与算法实战 ）

来源：[https://segmentfault.com/a/1190000014531580](https://segmentfault.com/a/1190000014531580)

一定要认真看 分析 | 注释 | 题目要求
## Question 1
 **`题目描述：`** 
输入一个链表，从尾到头打印链表每个节点的值。
 **`分析：`** 
因为链表只有知道当前结点才能知道下一结点，所以不可能直接从后往前打印。这种 **`逆序`** 的算法（策略）我们常用 **`栈`** 这种数据结构来解决，所以我们的基本思路为，先将链表压入栈，再弹出，但是这样做我们需要遍历两次才能得出答案，更奇妙的解决方案为一次将结点值 **`插入数组头部`** ，一次便可以满足题目要求，代码如下：

```php
<?php

/*class ListNode{
    var $val;
    var $next = NULL;
    function __construct($x){
        $this->val = $x;
    }
}*/

function printListFromTailToHead($head)
{
    $stack = [];
    if(!$head){
        return $stack;
    }
    while($head){
        array_unshift($stack,$head->val);   #array_unshift返回头插后的数组单元数目
        $head = $head->next;
    }
    return $stack;
}

```

测试地址：[https://www.nowcoder.com/prac...][0]
## Question 2
 **`题目描述`** ：
输入一个链表，输出该链表中倒数第k个结点。
 **`分析：`** 
前提：链表是动态分配的，事先不能知道链表的总长度
一般思路：遍历链表，得出长度，输出结点
面试思路：准备 **`两个指针`** ，假如第一个指针走到n-1（即链表末尾），第二个指针走到倒数k的位置， **`两者之间相差`**  **`(n-1)-(n-k) = k-1`** ，先让一个指针走到k-1，第二个指针原地等待，然后让第二个指针和第一个指针同时走，走到末尾，找到k，代码如下：

```php
<?php


    /**
     * Question1:输出倒数第K个节点
     * @param $head object 链表
     * @param $k     int    序号
     * Think: 动态分配不属于固定空间 不知道链表实际有
     */
    function FindKthToTail($head, $k){
        /*    如果链表为空或者k不合法 返回null    */
        if($head == null || $k<=0){
            return null;
        }
        
        /*    这里采用了复杂度为O(n)的算法，需要准备两个节点    */
        $behind = $head;    #指向链表的第一个节点
       
        /*    算法思路：准备两个指针，假如第一个指针走到n-1（即链表末尾），第二个指针走到倒数k的位置，两者之间相差(n-1)-(n-k) = k-1 */
        for($i=0;$i<$k-1;$i++){
            /*    让第一个指针先走k-1个单位，如果不为空，则指针向后移动    */
            if($head->next != null){
                $head = $head->next;
            }else{
                 /*    注意：这里有一个隐藏的条件，就是链表的长度有可能小于k，当我们走到k-1时链表就已经遍历结束，这种情况同样非法    */
                return null;
            }
        }
        /*    当第一个指针走到k-1且还不为空，这时让第二个指针开始走，当第一个指针走到n-1的时候，第二个指针也走到了倒数第k的位置，即所求    */
        while($head->next != null){
            $head = $head->next;
            $behind = $behind->next;
        }
        return $behind;
    }
```
 **`测试地址：`** [https://www.nowcoder.com/prac...][1]
## Question 3
 **`题目描述`** ：
输入一个链表，反转链表后，输出链表的所有元素。
 **`分析：`** 
画图最佳 主要就是运用临时节点 **`看注释`** 

```php
<?php


    /**
     * Question:输入一个链表，反转链表后，输出链表的所有元素
     * @param $pHead object 链表
     * Think: 画图最佳 主要就是运用临时节点
     */
    function ReverseList($pHead){

        if($pHead == NULL){
            return NULL;
        }

        $cur = null;

        while($pHead){
            $tmp = $pHead->next;    #首先将链上第二个位置的值放在临时容器中
            $pHead->next = $cur;    #将第二个位置赋一个空值 保持链的关系

            $cur = $pHead;          #将第一个位置的值赋给第二个位置
            $pHead = $tmp;          #将原第二个位置的值赋给头结点
        }
        return $cur;
    }
```
 **`测试地址：`** [https://www.nowcoder.com/prac...][2]
## Question 4
 **`题目描述`** ：
输入两个单调递增的链表，输出两个链表合成后的链表，当然我们需要合成后的链表满足 **`单调不减`** 规则。
 **`分析：`** 
画图最佳 先用头结点比较大小，小的压入数组，大的和小的的后一位继续比较

```php
<?php
/*class ListNode{
    var $val;
    var $next = NULL;
    function __construct($x){
        $this->val = $x;
    }
}*/
function MergeList($pHead1, $pHead2)
{
    // write code here
    /*
         先用头结点比较大小，小的压入数组，大的和小的的后一位继续比较
    */
    if($pHead1 == null && $pHead2 == null){
        return null;
    }
    if($pHead1 == null){
        return $pHead2;
    }
    if($pHead2 == null){
        return $pHead1;
    }

    $target = array();

    if($pHead1 != null && $pHead2 != null){     #这里作判断的目的主要是在递归过程中可能会有一方先遍历结束
        if($pHead1->val >= $pHead2->val){
            $target = $pHead2;
            $target->next = MergeList($pHead1,$pHead2->next);
        }else{
            $target = $pHead1;
            $target->next = MergeList($pHead1->next,$pHead2);
        }
    }
    return $target;
    
}
```
 **`测试地址：`** [https://www.nowcoder.com/prac...][3]
## Question 5
 **`题目描述`** ：
给定一个链表

```php
1.判断链表是否有环
2.链表起始结点（入口结点）

```
 **`分析：`** 
 最近segment插图好像出了点问题，我给个链接吧[环形链表][4]

```php
function EntryNodeOfLoop($pHead)
{
    // write code here
    if($pHead == null){
        return null;
    }
    $fast = $pHead;
    $slow = $pHead;
    
    while($fast != null && $fast->next != null){
        $fast = $fast->next->next;
        $slow = $slow->next;
        if($fast == $slow){  #存在环
            $slow = $pHead;
            while($fast != $slow){ #此时slow为头结点
                $fast = $fast->next;
                $slow = $slow->next;
            }
            if($fast == $slow){
                    return $fast;
                }
   
        }
    }
   
    return null;
}
```
## Question 6
 **`题目描述`** ：
“约瑟夫环”是一个数学的应用问题：一群猴子排成一圈，按1,2,…,n依次编号。然后从第1只开始数，数到第m只,把它踢出圈， **`从它后面再开始数`** ， 再数到第m只，在把它踢出去…，如此不停的进行下去， 直到最后只剩下一只猴子为止，那只猴子就叫做大王。要求编程模拟此过程，输入m、n, 输出最后那个大王的编号。
 **`分析：`** 
 第一个出列的猴子肯定是a[1]=m(mod)n，设此时某个猴子的新编号是i，他原来的编号就是(i+a[1])%n

```php
/**
* @param  $monkeys Array 预设数组
* @param  $m.    Int。   预设剔除序号
* @param  $cur.  Int。   标记
*/
function JosephCircle($monkeys , $m , $current = 0){
    $number = count($monkeys);
    $num = 1;
    if(count($monkeys) == 1){
        echo $monkeys[0];
        return;
    }
    else{
        while($num++ < $m){
            $current++ ;
            $current = $current%$number;
        }
        echo $monkeys[$current];
        array_splice($monkeys , $current , 1);  #剔除
        JosephCircle($monkeys , $m , $current);
    }
}
```

[0]: https://www.nowcoder.com/practice/d0267f7f55b3412ba93bd35cfa8e8035?tpId=13&tqId=11156&tPage=1&rp=1&ru=%2Fta%2Fcoding-interviews&qru=%2Fta%2Fcoding-interviews%2Fquestion-ranking
[1]: https://www.nowcoder.com/practice/529d3ae5a407492994ad2a246518148a?tpId=13&tqId=11167&tPage=1&rp=1&ru=%2Fta%2Fcoding-interviews&qru=%2Fta%2Fcoding-interviews%2Fquestion-ranking
[2]: https://www.nowcoder.com/practice/75e878df47f24fdc9dc3e400ec6058ca?tpId=13&tqId=11168&tPage=1&rp=1&ru=%2Fta%2Fcoding-interviews&qru=%2Fta%2Fcoding-interviews%2Fquestion-ranking
[3]: https://www.nowcoder.com/practice/d8b6b4358f774294a89de2a6ac4d9337?tpId=13&tqId=11169&tPage=1&rp=1&ru=%2Fta%2Fcoding-interviews&qru=%2Fta%2Fcoding-interviews%2Fquestion-ranking
[4]: https://blog.csdn.net/happywq2009/article/details/44313155