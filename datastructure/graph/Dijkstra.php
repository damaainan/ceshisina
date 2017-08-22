<?php

/**
 * 迪杰斯特拉(Dijkstra)算法是典型最短路径算法，用于计算一个节点到其他节点的最短路径。 
它的主要特点是以起始点为中心向外层层扩展(广度优先搜索思想)，直到扩展到终点为止。


基本思想

     通过Dijkstra计算图G中的最短路径时，需要指定起点s(即从顶点s开始计算)。

     此外，引进两个集合S和U。S的作用是记录已求出最短路径的顶点(以及相应的最短路径长度)，而U则是记录还未求出最短路径的顶点(以及该顶点到起点s的距离)。

     初始时，S中只有起点s；U中是除s之外的顶点，并且U中顶点的路径是"起点s到该顶点的路径"。然后，从U中找出路径最短的顶点，并将其加入到S中；接着，更新U中的顶点和顶点对应的路径。 然后，再从U中找出路径最短的顶点，并将其加入到S中；接着，更新U中的顶点和顶点对应的路径。 ... 重复该操作，直到遍历完所有顶点。


操作步骤

(1) 初始时，S只包含起点s；U包含除s外的其他顶点，且U中顶点的距离为"起点s到该顶点的距离"[例如，U中顶点v的距离为(s,v)的长度，然后s和v不相邻，则v的距离为∞]。

(2) 从U中选出"距离最短的顶点k"，并将顶点k加入到S中；同时，从U中移除顶点k。

(3) 更新U中各个顶点到起点s的距离。之所以更新U中顶点的距离，是由于上一步中确定了k是求出最短路径的顶点，从而可以利用k来更新其它顶点的距离；例如，(s,v)的距离可能大于(s,k)+(k,v)的距离。

(4) 重复步骤(2)和(3)，直到遍历完所有顶点。

单纯的看上面的理论可能比较难以理解，下面通过实例来对该算法进行说明。
 */


$graph = [
    'A' => ['B' => 3, 'C' => 5, 'D' => 9],
    'B' => ['A' => 3, 'C' => 3, 'D' => 4, 'E' => 7],
    'C' => ['A' => 5, 'B' => 3, 'D' => 2, 'E' => 6, 'F' => 3],
    'D' => ['A' => 9, 'B' => 4, 'C' => 2, 'E' => 2, 'F' => 2],
    'E' => ['B' => 7, 'C' => 6, 'D' => 2, 'F' => 5],
    'F' => ['C' => 3, 'D' => 2, 'E' => 5],
];

function Dijkstra(array $graph, string $source, string $target): array
{
    $dist  = [];
    $pred  = [];
    $Queue = new SplPriorityQueue();
    foreach ($graph as $v => $adj) {
        $dist[$v] = PHP_INT_MAX;
        $pred[$v] = null;
        $Queue->insert($v, min($adj));
    }
    $dist[$source] = 0;
    while (!$Queue->isEmpty()) {
        $u = $Queue->extract();
        if (!empty($graph[$u])) {
            foreach ($graph[$u] as $v => $cost) {
                if ($dist[$u] + $cost < $dist[$v]) {
                    $dist[$v] = $dist[$u] + $cost;
                    $pred[$v] = $u;
                }
            }
        }
    }
    $S        = new SplStack();
    $u        = $target;
    $distance = 0;
    while (isset($pred[$u]) && $pred[$u]) {
        $S->push($u);
        $distance += $graph[$u][$pred[$u]];
        $u = $pred[$u];
    }
    if ($S->isEmpty()) {
        return ["distance" => 0, "path" => $S];
    } else {
        $S->push($source);
        return ["distance" => $distance, "path" => $S];
    }
}

$source = "A";
$target = "F";
$result = Dijkstra($graph, $source, $target);
extract($result);
echo "Distance from $source to $target is $distance \r\n";
echo "Path to follow : ";
while (!$path->isEmpty()) {
    echo $path->pop() . "\t";
}
