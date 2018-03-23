<?php 
header("Content-type:text/html; Charset=utf-8");
$arr=[
"cnblogsIntroduction-Stack-and-Queue.md***01浅谈算法和数据结构: 一 栈和队列",
"cnblogsIntroduction-Insertion-and-Selection-and-Shell-Sort.md***02浅谈算法和数据结构: 二 基本排序算法",
"cnblogsIntroduce-Merge-Sort.md***03浅谈算法和数据结构: 三 合并排序",
"cnblogsIntroduce-Quick-Sort.md***04浅谈算法和数据结构: 四 快速排序",
"cnblogsIntroduce-Priority-Queue-And-Heap-Sort.md***05浅谈算法和数据结构: 五 优先级队列与堆排序",
"cnblogsIntroduce-Symbol-Table-and-Elementary-Implementations.md***06浅谈算法和数据结构: 六 符号表及其基本实现",
"cnblogsIntroduce-Binary-Search-Tree.md***07浅谈算法和数据结构: 七 二叉查找树",
"cnblogsIntroduce-2-3-Search-Tree.md***08浅谈算法和数据结构: 八 平衡查找树之2-3树",
"cnblogsIntroduce-Red-Black-Tree.md***09浅谈算法和数据结构: 九 平衡查找树之红黑树",
"cnblogsIntroduce-B-Tree-and-B-Plus-Tree.md***10浅谈算法和数据结构: 十 平衡查找树之B树",
"cnblogsIntroduce-Hashtable.md***11浅谈算法和数据结构: 十一 哈希表",
"cnblogsIntroduce-Undirected-Graphs.md***12浅谈算法和数据结构: 十二 无向图相关算法基础"
];

foreach ($arr as $value) {
    $ar = explode("***",$value);
    $old = $ar[0];
    $new = $ar[1];
    echo $old,"\n",$new,"\n";
    $new = str_replace(':', '', $new);
    $ret =  rename('./'.$old, "./".$new.".md"); // 英文冒号 导致重命名错误
    // var_dump($ret);
}