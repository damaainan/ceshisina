<?php

/**
 * 普里姆(Prim)算法，和克鲁斯卡尔算法一样，是用来求加权连通图的最小生成树的算法。

基本思想 
对于图G而言，V是所有顶点的集合；现在，设置两个新的集合U和T，其中U用于存放G的最小生成树中的顶点，T存放G的最小生成树中的边。 从所有uЄU，vЄ(V-U) (V-U表示出去U的所有顶点)的边中选取权值最小的边(u, v)，将顶点v加入集合U中，将边(u, v)加入集合T中，如此不断重复，直到U=V为止，最小生成树构造完毕，这时集合T中包含了最小生成树中的所有边。
 */


$G = [
    [0, 3, 1, 6, 0, 0],
    [3, 0, 5, 0, 3, 0],
    [1, 5, 0, 5, 6, 4],
    [6, 0, 5, 0, 0, 2],
    [0, 3, 6, 0, 0, 6],
    [0, 0, 4, 2, 6, 0],
];

function primMST(array $graph)
{
    $parent  = []; // Array to store the MST
    $key     = []; // used to pick minimum weight edge
    $visited = []; // set of vertices not yet included in MST
    $len     = count($graph);
    // Initialize all keys as MAX
    for ($i = 0; $i < $len; $i++) {
        $key[$i]     = PHP_INT_MAX;
        $visited[$i] = false;
    }
    $key[0]    = 0;
    $parent[0] = -1;
    // The MST will have V vertices
    for ($count = 0; $count < $len - 1; $count++) {
        // Pick the minimum key vertex
        $minValue = PHP_INT_MAX;
        $minIndex = -1;
        foreach (array_keys($graph) as $v) {
            if ($visited[$v] == false && $key[$v] < $minValue) {
                $minValue = $key[$v];
                $minIndex = $v;
            }
        }
        $u = $minIndex;
    // Add the picked vertex to the MST Set
        $visited[$u] = true;
        for ($v = 0; $v < $len; $v++) {
            if ($graph[$u][$v] != 0 && $visited[$v] == false &&
                $graph[$u][$v] < $key[$v]) {
                $parent[$v] = $u;
                $key[$v]    = $graph[$u][$v];
            }
        }
    }
    // Print MST
    echo "Edge\tWeight\n";
    $minimumCost = 0;
    for ($i = 1; $i < $len; $i++) {
        echo $parent[$i] . " - " . $i . "\t" . $graph[$i][$parent[$i]] . "\r\n";
        $minimumCost += $graph[$i][$parent[$i]];
    }
    echo "Minimum cost: $minimumCost \n";
}


primMST($G);