## 剑指offer试题（PHP篇一）

来源：[http://www.cnblogs.com/zlnevsto/p/8448732.html](http://www.cnblogs.com/zlnevsto/p/8448732.html)

2018-02-14 19:17

**`1.二维数组中的查找`** 
## 题目描述


在一个二维数组中，每一行都按照从左到右递增的顺序排序，每一列都按照从上到下递增的顺序排序。请完成一个函数，输入这样的一个二维数组和一个整数，判断数组中是否含有该整数。

时间限制：1秒   空间限制：32768K



```php
<?php

function Find($target, $array)
{
    // write code here
    foreach($array as $k=>$v){
        if(in_array($target,$v)){
            return "true";
        }
    }　　return "false";
}    
    

```



运行时间：281ms   占用内存：4252k

感悟：

　　这道题用PHP写起来比较简单，只要懂得二维数组的遍历，以及in_array()函数的使用，不要搞错参数位置，参数一为要查找的字符串，参数二为数组。

 

**`2.替换空格`** 
## 题目描述


请实现一个函数，将一个字符串中的空格替换成“%20”。例如，当字符串为We Are Happy.则经过替换之后的字符串为We%20Are%20Happy。

时间限制：1秒   空间限制：32768K



```php
<?php
function replaceSpace($str)
{
    // write code here
    return str_replace(" ","%20",$str);
}　
```



运行时间：9ms   占用内存：2428k

感悟：

　　要熟悉str_replace()函数，参数一为要查找的值，参数二为替换的值，参数三为被搜索的字符串。

　　如果搜索的字符串是数组，那么它将返回数组，对数组中的每个元素进行查找和替换。注意，在数组替换中，如果需要执行替换的元素少于查找到的元素的数量，那么多余元素将用空字符串进行替换。

 

**`3.从尾到头打印链表`** 
## 题目描述


输入一个链表，从尾到头打印链表每个节点的值。

时间限制：1秒   空间限制：32768K



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
    // write code here
    $list = array();
    while($head!=null){
        $list[]=$head->val;
        $head=$head->next;
    }
    return array_reverse($list);
}　
```



运行时间：18ms   占用内存：2432k

感悟：

　　本题的思路为先将链表的值顺序装入一个空数组中，然后使用array_reverse()函数以相反的元素顺序返回数组。

 

**`4.重建二叉树`** 
## 题目描述


输入某二叉树的前序遍历和中序遍历的结果，请重建出该二叉树。假设输入的前序遍历和中序遍历的结果中都不含重复的数字。例如输入前序遍历序列{1,2,4,7,3,5,6,8}和中序遍历序列{4,7,2,1,5,3,8,6}，则重建二叉树并返回。

时间限制：1秒   空间限制：32768K



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
function reConstructBinaryTree($pre, $vin)
{
    // write code here
	if($pre && $vin){
		$treeRoot = new TreeNode($pre[0]);
		$index = array_search($pre[0],$vin);
		$treeRoot->left = reConstructBinaryTree(array_slice($pre,1,$index),array_slice($vin,0,$index));
		$treeRoot->right = reConstructBinaryTree(array_slice($pre,$index+1),array_slice($vin,$index+1));
		return $treeRoot;
	}
}
```



运行时间：35ms   占用内存：4204k

感悟：

　　本题的思路为递归调用reConstructBinaryTree方法来分别执行左子树和右子树的遍历查找，每次都查找出根节点并进行保存。

　　题中使用到的函数：array_search()，在数组（参数二）中搜索某个键值（参数一），并返回对应的键名，若没有找到，则返回false。

　　　　　　　　　　　array_slice()，在数组中根据条件取出一段值，并返回，参数一为数组，参数二为取值的起始位置（相当于数组下标），参数三为选取的长度（可选，若不填，则取到数组结尾）。

 

**`5.用两个栈实现队列`** 
## 题目描述


用两个栈来实现一个队列，完成队列的Push和Pop操作。 队列中的元素为int类型。

时间限制：1秒    空间限制：32768K



```php
<?php
$stack = array();
function mypush($node)
{
    // write code here
    global $stack;
    return $stack[]=$node;
}
function mypop()
{
    global $stack;
    if($stack){
        return array_shift($stack);
    }
    // write code here
    return $stack;
}
```



运行时间：14ms   占用内存：3688k

感悟：

　　首先要清楚队列的Push和Pop操作的含义：push(x)：将x压入队列的末端，pop()：弹出队列的第一个元素。

　　本题要点为定义全局数组$stack，其次为array_shift()函数：删除数组中的第一个元素，并返回被删除元素的值。

 

 

注：以上均为个人理解，如有错误，请提出，必改正。




















