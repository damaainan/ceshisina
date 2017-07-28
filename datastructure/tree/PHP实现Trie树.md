# [PHP实现Trie树（字典树）][0]

 标签： [PHP][1][算法][2][Trie][3]

 2016-06-15 17:56  1131人阅读  

 分类：

版权声明：本文为博主原创文章，未经博主允许不得转载。

Trie树的概念（百度的解释）：字典树又称单词查找树，Trie树，是一种树形结构，是一种哈希树的变种。典型应用是用于统计，排序和保存大量的字符串（但不仅限于字符串），所以经常被[搜索引擎][8]系统用于文本词频统计。它的优点是：利用字符串的公共前缀来减少查询时间，最大限度地减少无谓的字符串比较，查询效率比哈希树高。

我的理解是用来做字符串搜索的，每个节点只包含一个字符，比如录入单词"world"，则树的结构是：

![][9]

这时再录入单词"worab"，则树的结构为：

![][10]

所以每个节点必须还要一个字段is_end标识是否为结束单词。比如用户输入wor，搜索所有wor开头的单词，假设现在有一个单词就是wor，从"w"开始检索，当检索到"r"的时候需要判断"r"节点的is_end为true，则把wor加入到结果列表，然后继续往下面检索。

[PHP][11]实现代码：

```php
<?php  
  
class Node{  
  
    public $value;                 // 节点值  
    public $is_end = false;        // 是否为结束--是否为某个单词的结束节点  
    public $childNode = array();   // 子节点  
      
    /* 添加孩子节点--注意：可以不为引用函数，因为PHP对象赋值本身就是引用赋值 */  
    public function &addChildNode($value, $is_end = false){  
        $node = $this->searchChildNode($value);  
        if(empty($node)){  
            // 不存在节点，添加为子节点  
            $node = new Node();  
            $node->value = $value;  
            $this->childNode[] = $node;  
        }  
        $node->is_end = $is_end;  
        return $node;  
    }  
  
    /* 查询子节点 */  
    public function searchChildNode($value){  
        foreach ($this->childNode as $k => $v) {  
            if($v->value == $value){  
                // 存在节点，返回该节点  
                return $this->childNode[$k];  
            }  
        }  
        return false;  
    }  
}  
  
  
  
/* 添加字符串 */  
function addString(&$head, $str){  
    $node = null;  
    for ($i=0; $i < strlen($str); $i++) {  
        if($str[$i] != ' '){  
            $is_end = $i != (strlen($str) - 1) ? false : true;  
            if($i == 0){  
                $node = $head->addChildNode($str[$i], $is_end);  
            }else{  
                $node = $node->addChildNode($str[$i], $is_end);  
            }  
        }  
    }  
}  
  
/* 获取所有字符串--递归 */  
function getChildString($node, $str_array = array(), $str = ''){  
    if($node->is_end == true){  
        $str_array[] = $str;  
    }  
    if(empty($node->childNode)){  
        return $str_array;  
    }else{  
        foreach ($node->childNode as $k => $v) {  
            $str_array = getChildString($v, $str_array, $str . $v->value);  
        }  
        return $str_array;  
    }  
}  
  
/* 搜索 */  
function searchString($node, $str){  
    for ($i=0; $i < strlen($str); $i++) {  
        if($str[$i] != ' '){  
            $node = $node->searchChildNode($str[$i]);  
            // print_r($node);  
            if(empty($node)){  
                // 不存在返回空  
                return false;  
            }  
        }  
    }  
    return getChildString($node);  
}  
  
  
/* 调用测试开始 */  
$head = new Node;   // 树的head  
  
// 添加单词  
addString($head, 'hewol');  
addString($head, 'hemy');  
addString($head, 'heml');  
addString($head, 'you');  
addString($head, 'yo');  
  
// 获取所有单词  
$str_array = getChildString($head);  
  
// 搜索  
$search_array = searchString($head, 'hem');  
// 循环打印所有搜索结果  
foreach ($search_array as $key => $value) {  
    echo 'hem' . $value . '<br>';  // 输出带上搜索前缀  
}  

```

[0]: http://blog.csdn.net/mxdzchallpp/article/details/51684090
[1]: http://www.csdn.net/tag/PHP
[2]: http://www.csdn.net/tag/%e7%ae%97%e6%b3%95
[3]: http://www.csdn.net/tag/Trie
[8]: http://lib.csdn.net/base/searchengine
[9]: ./img/20160615174718741.png
[10]: ./img/20160615174854367.png
[11]: http://lib.csdn.net/base/php
[12]: #