# [PHP实现单向链表解决约瑟夫环问题][0]

 标签： [算法][1][PHP][2][链表][3]

 2016-06-29 18:12  571人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

约瑟夫环问题：在罗马人占领乔塔帕特后，39 个犹太人与Josephus及他的朋友躲到一个洞中，39个犹太人决定宁愿死也不要被敌人抓到，于是决定了一个自杀方式，41个人排成一个圆圈，由第1个人开始报数，每报数到第3人该人就必须自杀，然后再由下一个重新报数，直到所有人都自杀身亡为止。然而Josephus 和他的朋友并不想遵从。首先从一个人开始，越过k-2个人（因为第一个人已经被越过），并杀掉第k个人。接着，再越过k-1个人，并杀掉第k个人。这个过程沿着圆圈一直进行，直到最终只剩下一个人留下，这个人就可以继续活着。问题是，给定了和，一开始要站在什么地方才能避免被处决？Josephus要他的朋友先假装遵从，他将朋友与自己安排在第16个与第31个位置，于是逃过了这场死亡游戏。

更多的类似问题是：n个人围成圈，依次编号为1,2,..,n，现在从1号开始依次报数，当报到m时，报m的人退出，下一个人重新从1报起，循环下去，问最后剩下那个人的编号是多少？

代码实现：
```php
<?php  
  
class Node{  
  
    public $value;      // 节点值  
    public $nextNode;   // 下一个节点  
  
}  
  
function create($node, $value){  
    $node->value = $value;  
}  
  
function addNode($node, $value){  
    $lastNode = findLastNode($node);  
    $nextNode = new Node();  
    $nextNode->value = $value;  
    $lastNode->nextNode = $nextNode;  
}  
  
/* 找到最后的节点 */  
function findLastNode($node){  
    if(empty($node->nextNode)){  
        return $node;  
    }else{  
        return findLastNode($node->nextNode);  
    }  
}  
  
/* 删除节点 必须head为引用传值 */  
function deleteNode(&$head, $node, $m, $k = 1){  
    if($k + 1 == $m){  
        if($node->nextNode == $head){  
            $node->nextNode = $node->nextNode->nextNode;  
            $head = $node->nextNode;  
            return $node->nextNode;  
        }else{  
            $node->nextNode = $node->nextNode->nextNode;  
            return $node->nextNode;  
        }  
    }else{  
        return deleteNode($head, $node->nextNode, $m, ++$k);  
    }  
}  
  
/* 节点数 */  
function countNode($head, $node, $count = 1){  
    if($node->nextNode == $head){  
        return $count;  
    }else{  
        return countNode($head, $node->nextNode, ++$count);  
    }  
}  
  
function printNode($head, $node){  
    echo $node->value . '  ';  
    if($node->nextNode == $head) return;  
    printNode($head, $node->nextNode);  
}  
  
function show($data){  
    echo '<pre>';  
    print_r($data);  
    echo '</pre>';  
}  
  
$head = new Node();  
create($head, 1);  
  
addNode($head, 2);  
addNode($head, 3);  
addNode($head, 4);  
addNode($head, 5);  
addNode($head, 6);  
addNode($head, 7);  
addNode($head, 8);  
addNode($head, 9);  
addNode($head, 10);  
addNode($head, 11);  
addNode($head, 12);  
$lastNode = findLastNode($head);  
$lastNode->nextNode = $head;  
  
$count = countNode($head, $head);  
$tmpHead = $head;  
while ($count > 2) {  
    $tmpHead = deleteNode($head, $tmpHead, 3, 1);  
    $count = countNode($head, $head);  
}  
printNode($head, $head);  

```

[0]: http://www.csdn.net/mxdzchallpp/article/details/51777371
[1]: http://www.csdn.net/tag/%e7%ae%97%e6%b3%95
[2]: http://www.csdn.net/tag/PHP
[3]: http://www.csdn.net/tag/%e9%93%be%e8%a1%a8
