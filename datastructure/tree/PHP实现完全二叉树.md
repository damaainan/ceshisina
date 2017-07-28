#  [PHP实现完全二叉树][0]

 标签： [PHP][1][二叉树][2][算法][3]

 2016-06-23 18:14  146人阅读  

 分类：

版权声明：本文为博主原创文章，未经博主允许不得转载。

若设二叉树的深度为h，除第 h 层外，其它各层 (1～h-1) 的结点数都达到最大个数，第 h 层所有的结点都连续集中在最左边，这就是完全二叉树。

![][8]

[PHP][9]代码实现（暂时实现添加节点、层次遍历节点，删除节点后续更新）

```php
<?php  
  
class Node{  
  
    public $value;  
    public $leftNode;  
    public $rightNode;  
  
}  
  
/* 找到空节点 */  
function findEmpytNode($node, $parent = null){  
    if(empty($node->value)){  
        return $node;  
    }else{  
        if(empty($node->leftNode->value)){  
            return $node->leftNode;  
        }else if(empty($node->rightNode->value)){  
            return $node->rightNode;  
        }else{  
            if(empty($parent) || $node->value == $parent->rightNode->value){  
                return findEmpytNode($node->leftNode, $node);  
            }else{  
                return findEmpytNode($parent->rightNode, $node);  
            }  
        }  
    }  
}  
  
/* 添加节点 */  
function addNode($node, $value){  
    $emptyNode = findEmpytNode($node);  
    setNode($emptyNode, $value);  
}  
  
/* 设置节点 */  
function setNode($node, $value){  
    $node->value = $value;  
    $node->leftNode = new Node();  
    $node->rightNode = new Node();  
}  
  
/* 打印 */  
function printTree($node, $parent = null){  
    if(empty($node->value)) return ;  
    echo $node->leftNode->value;  
    echo $node->rightNode->value;  
    if(empty($parent) || $node->value == $parent->rightNode->value){  
        printTree($node->leftNode, $node);  
    }else{  
        printTree($parent->rightNode, $node);  
    }  
}  
  
$head = new Node();  
setNode($head, 1);  
addNode($head, 2);  
addNode($head, 3);  
addNode($head, 4);  
addNode($head, 5);  
addNode($head, 6);  
  
printTree($head);  

```

[0]: http://blog.csdn.net/mxdzchallpp/article/details/51745780
[1]: http://www.csdn.net/tag/PHP
[2]: http://www.csdn.net/tag/%e4%ba%8c%e5%8f%89%e6%a0%91
[3]: http://www.csdn.net/tag/%e7%ae%97%e6%b3%95
[8]: ./img/20160623181324930.png
[9]: http://lib.csdn.net/base/php