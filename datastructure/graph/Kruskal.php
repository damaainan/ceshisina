<?php

/**
 * 克鲁斯卡尔(Kruskal)算法，是用来求加权连通图的最小生成树的算法。

基本思想：按照权值从小到大的顺序选择n-1条边，并保证这n-1条边不构成回路。 
具体做法：首先构造一个只含n个顶点的森林，然后依权值从小到大从连通网中选择边加入到森林中，并使森林中不产生回路，直至森林变成一棵树为止。
 */


function Kruskal(array $graph): array
{
    $len  = count($graph);
    $tree = [];
    $set  = [];
    foreach ($graph as $k => $adj) {
        $set[$k] = [$k];
    }
    $edges = [];
    for ($i = 0; $i < $len; $i++) {
        for ($j = 0; $j < $i; $j++) {
            if ($graph[$i][$j]) {
                $edges[$i . ',' . $j] = $graph[$i][$j];
            }
        }}
    asort($edges);
    foreach ($edges as $k => $w) {
        list($i, $j) = explode(',', $k);
        $iSet        = findSet($set, $i);
        $jSet        = findSet($set, $j);
        if ($iSet != $jSet) {
            $tree[] = ["from" => $i, "to" => $j,
                "cost"            => $graph[$i][$j]];
            unionSet($set, $iSet, $jSet);
        }}
    return $tree;
}
function findSet(array &$set, int $index)
{
    foreach ($set as $k => $v) {
        if (in_array($index, $v)) {
            return $k;
        }
    }
    return false;
}
function unionSet(array &$set, int $i, int $j)
{
    $a = $set[$i];
    $b = $set[$j];
    unset($set[$i], $set[$j]);
    $set[] = array_merge($a, $b);
}

$graph = [
    [0, 3, 1, 6, 0, 0],
    [3, 0, 5, 0, 3, 0],
    [1, 5, 0, 5, 6, 4],
    [6, 0, 5, 0, 0, 2],
    [0, 3, 6, 0, 0, 6],
    [0, 0, 4, 2, 6, 0],
];
$mst         = Kruskal($graph);
$minimumCost = 0;
foreach ($mst as $v) {
    echo "From {$v['from']} to {$v['to']} cost is {$v['cost']} \r\n";
    $minimumCost += $v['cost'];
}
echo "Minimum cost: $minimumCost \r\n";
