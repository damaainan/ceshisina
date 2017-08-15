<?php

/**
 * SplHeap  SplMaxHeap   SplMinHeap  自动实现堆排序
 */ 


$numbers = [37, 44, 34, 65, 26, 86, 143, 129, 9];
$heap    = new SplMaxHeap;
foreach ($numbers as $number) {
    $heap->insert($number);
}
while (!$heap->isEmpty()) {
    echo $heap->extract() . "\t";
}

echo "\r\n";

$numbers = [37, 44, 34, 65, 26, 86, 143, 129, 9];
$heap    = new SplMinHeap;
foreach ($numbers as $number) {
    $heap->insert($number);
}
while (!$heap->isEmpty()) {
    echo $heap->extract() . "\t";
}
