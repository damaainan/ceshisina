<?php

/**
 * 拓扑排序(Topological Order)是指，将一个有向无环图(Directed Acyclic Graph简称DAG)进行排序进而得到一个有序的线性序列。

这样说，可能理解起来比较抽象。下面通过简单的例子进行说明！ 
例如，一个项目包括A、B、C、D四个子部分来完成，并且A依赖于B和D，C依赖于D。现在要制定一个计划，写出A、B、C、D的执行顺序。这时，就可以利用到拓扑排序，它就是用来确定事物发生的顺序的。

在拓扑排序中，如果存在一条从顶点A到顶点B的路径，那么在排序结果中B出现在A的后面。
 */

$graph = [
    [0, 0, 0, 0, 1],
    [1, 0, 0, 1, 0],
    [0, 1, 0, 1, 0],
    [0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0],
];

function topologicalSort(array $matrix): SplQueue
{
    $order    = new SplQueue;
    $queue    = new SplQueue;
    $size     = count($matrix);
    $incoming = array_fill(0, $size, 0);
    for ($i = 0; $i < $size; $i++) {
        for ($j = 0; $j < $size; $j++) {
            if ($matrix[$j][$i]) {
                $incoming[$i]++;
            }
        }
        if ($incoming[$i] == 0) {
            $queue->enqueue($i);
        }
    }
    while (!$queue->isEmpty()) {
        $node = $queue->dequeue();
        for ($i = 0; $i < $size; $i++) {
            if ($matrix[$node][$i] == 1) {
                $matrix[$node][$i] = 0;
                $incoming[$i]--;
                if ($incoming[$i] == 0) {
                    $queue->enqueue($i);
                }
            }
        }
        $order->enqueue($node);
    }
    if ($order->count() != $size) // cycle detected
    {
        return new SplQueue;
    }

    return $order;
}

$sorted = topologicalSort($graph);
while (!$sorted->isEmpty()) {
    echo $sorted->dequeue() . "\t";
}
