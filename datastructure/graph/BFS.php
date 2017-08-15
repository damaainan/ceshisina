<?php

$graph       = [];
$visited     = [];
$vertexCount = 6;
for ($i = 1; $i <= $vertexCount; $i++) {
    $graph[$i]   = array_fill(1, $vertexCount, 0);
    $visited[$i] = 0;
}

$graph[1][2] = $graph[2][1] = 1;
$graph[1][5] = $graph[5][1] = 1;
$graph[5][2] = $graph[2][5] = 1;
$graph[5][4] = $graph[4][5] = 1;
$graph[4][3] = $graph[3][4] = 1;
$graph[3][2] = $graph[2][3] = 1;
$graph[6][4] = $graph[4][6] = 1;

function BFS(array &$graph, int $start, array $visited): SplQueue
{
    $queue = new SplQueue;
    $path  = new SplQueue;
    $queue->enqueue($start);
    $visited[$start] = 1;
    while (!$queue->isEmpty()) {
        $node = $queue->dequeue();
        $path->enqueue($node);
        foreach ($graph[$node] as $key => $vertex) {
            if (!$visited[$key] && $vertex == 1) {
                $visited[$key] = 1;
                $queue->enqueue($key);
            }
        }
    }
    return $path;
}

$path = BFS($graph, 5, $visited); // 改变第二个参数 会改变结果
while (!$path->isEmpty()) {
    echo $path->dequeue() . "\t";
}

