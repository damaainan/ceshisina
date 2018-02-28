## 剑指offer试题（PHP篇四）

来源：[http://www.cnblogs.com/zlnevsto/p/8452810.html](http://www.cnblogs.com/zlnevsto/p/8452810.html)

2018-02-20 00:06

**`16.合并两个排序的链表`** 
## 题目描述


输入两个单调递增的链表，输出两个链表合成后的链表，当然我们需要合成后的链表满足单调不减规则。

时间限制：1秒    空间限制：32768K



```php
<?php
/*class ListNode{
    var $val;
    var $next = NULL;
    function __construct($x){
        $this->val = $x;
    }
}*/
function Merge($pHead1, $pHead2)
{
    // write code here
    $pHead = new ListNode(null);
    if($pHead1 == null){
        return $pHead2;
    }elseif($pHead2 == null){
        return $pHead1;
    }else{
        if($pHead1->val<$pHead2->val){
            $pHead=$pHead1;
            $pHead->next=Merge($pHead1->next,$pHead2);
        }else{
            $pHead=$pHead2;
            $pHead->next=Merge($pHead1,$pHead2->next);
        }
    }
    return $pHead;
}
```







运行时间：13ms   占用内存：2560k

感悟：

　　思路很简单，按顺序合并两个有序的链表，只要针对 每个链表的第一个值进行比较，再递归调用Merge函数即可。

 

**`17.树的子结构`** 
## 题目描述


输入两棵二叉树A，B，判断B是不是A的子结构。（ps：我们约定空树不是任意一个树的子结构）

时间限制：1秒    空间限制：32768K



```php
<?php

/*class TreeNode{
    var $val;
    var $left = NULL;
    var $right = NULL;
    function __construct($val){
        $this->val = $val;
    }
}*/
function HasSubtree($pRoot1, $pRoot2)
{
    // write code here
    if($pRoot1 == null || $pRoot2 == null){
        return false;
    }
    return isSubtree($pRoot1,$pRoot2) || isSubtree($pRoot1->left,$pRoot2) || isSubtree($pRoot1->right,$pRoot2);
}
function isSubtree($pRoot1,$pRoot2){
    if($pRoot2 == null){
        return true;
    }
    if($pRoot2 == null){
        return false;
    }
    return $pRoot1->val == $pRoot2->val && isSubtree($pRoot1->left,$pRoot2->left) && isSubtree($pRoot1->right,$pRoot2->right);
}
```






运行时间：11ms   占用内存：2432k

感悟：

　　首先判断两个链表是否为空，若空则返false，若不空，则递归调用isSubtree函数，这里要注意调用时要将链表1本身，其左子树，其右子树分别和链表2进行比较,若链表1的比较不成立，再进行其左子树和右子树的调用比较，若链表1本身就成立，则后面的两次调用就没有必要。再来说isSubtree函数，题目要求判断b是不是a的子结构，所以分别判断传入参数的两个链表是否为空，若1空，则返回false，若2空，则肯定是子结构，返回true，最后用与运算递归调用isSubtree函数进行子树的比较。

 

**`18.二叉树的镜像`** 
## 题目描述


操作给定的二叉树，将其变换为源二叉树的镜像。
## 输入描述:

```
二叉树的镜像定义：源二叉树 
    	    8
    	   /  \
    	  6   10
    	 / \  / \
    	5  7 9 11
    	镜像二叉树
    	    8
    	   /  \
    	  10   6
    	 / \  / \
    	11 9 7  5时间限制：1秒   空间限制：32768K
```


```php
<?php

/*class TreeNode{
    var $val;
    var $left = NULL;
    var $right = NULL;
    function __construct($val){
        $this->val = $val;
    }
}*/
function Mirror(&$root)
{
    // write code here
    if($root == null || ($root->left == null && $root->right == null)){
        return;
    }
    $tmp = $root->left;
    $root->left = $root->right;
    $root->right = $tmp;
    if($root->left){
        Mirror($root->left);
    }
    if($root->right){
        Mirror($root->right);
    }
    return $root;
}
```



运行时间：12ms   占用内存：2428k

感悟：

　　首先判断该二叉树是否为空树或者是否只有一个节点，若不是，则进行左右子节点的交换，最后将左右节点分别判断，再递归调用原函数Mirror()。

 

**`19.顺时针打印矩阵`** 
## 题目描述


输入一个矩阵，按照从外向里以顺时针的顺序依次打印出每一个数字，例如，如果输入如下矩阵： 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 则依次打印出数字1,2,3,4,8,12,16,15,14,13,9,5,6,7,11,10.

时间限制：1秒   空间限制：32768K

 

```php
function printMatrix($matrix)
{
    // write code here
    $output = [];
    $left = 0;
    $right = count($matrix[0]);
    $top = 0;
    $buttom = count($matrix);
    if ($right == 0 && $buttom == 0) {
        return $output;
    }
     
    $right--;
    $buttom--;
    while($left <= $right && $top <= $buttom) {
        for ($i = $left; $i <= $right; $i++) $output[] = $matrix[$top][$i];
         if ($top + 1 > $buttom) break;
        for ($i = $top + 1; $i <= $buttom; $i++) $output[] = $matrix[$i][$right];
         if ($right - 1 < $left) break;
        for ($i = $right - 1; $i >= $left; $i--) $output[] = $matrix[$buttom][$i];
         if ($buttom - 1 <= $top) break;
        for ($i = $buttom -1 ; $i > $top; $i--) $output[] = $matrix[$i][$left];
        $left++;
        $right--;
        $top++;
        $buttom--;
    }
    return $output;
}
```



运行时间：22ms   占用内存：7808k

感悟：

　　顺时针打印矩阵中的数值，把握好变量的取值，先判断给出的数组是否为空，之后根据top，buttom，left，right四个变量进行左到右，上到下，右到左，下到上的遍历赋值，进行一遍循环后，矩形缩小一圈，左加右减，上加下减，继续遍历，直到left大于等于right且top大于等于buttom，循环结束，输出output数组。

 

**`20.包含min函数的栈`** 
## 题目描述


定义栈的数据结构，请在该类型中实现一个能够得到栈最小元素的min函数。

时间限制：1秒   空间限制：32768K



```php
<?php
global $mystack;
$mystack = [];
function mypush($node)
{
    // write code here
    global $mystack;
    array_push($mystack,$node);
}
function mypop()
{
    // write code here
    global $mystack;
    array_pop($mystack);
}
function mytop()
{
    // write code here
    global $mystack;
    if(count($mystack) == 0){
        return null;
    }
    return $mystack[count($mystack)-1];
}
function mymin()
{
    // write code here
    global $mystack;
    $min = $mystack[0];
    for($i=0;$i<count($mystack);$i++){
        if($mystack[$i]<$min){
            $min = $mystack[$i];
        }
    }
    return $min;
}
```






运行时间：11ms   占用内存：2432k

感悟：

　　考察栈的操作，注意定义global变量，进栈出栈，返回栈顶元素，求栈中最小元素，按照一般思路求解即可。

 

 

 注：以上均为个人理解，如有错误，请提出，必改正。














