<?php

// 允许使用不被限制的内存
ini_set('memory_limit', -1);

require 'contracts/FileInterface.php';
require 'contracts/SortingAlgorithmInterface.php';
require 'File.php';
require 'ModifiedMergeSort.php';

echo "starting...\n";

$startTime = microtime(true);

// $argv是php自带的变量。$argv[1]是脚本名后面跟的第一个参数
$filePath = $argv[1];

//断言文件大小限制如果需要，默认为1mb。
//如果文件大于这个值，它将被分割成指定大小的块。
$memoryLimit = isset($argv[2]) ? $argv[2] : 1;

// 选择输出目录
// 确保输出目录存在
$output = isset($argv[3]) ? $argv[3] : "output";

$file = new File($filePath);

$sortingAlgorithm = new ModifiedMergeSort($file, $memoryLimit);

$sortingAlgorithm->sort($output);

$executionTime = round( (microtime(true) - $startTime) * 1000, 1);

echo "Execution Time: " . $executionTime . " Milliseconds\n";

echo "Memory usage: " . memory_get_usage(true) / 1024 . " KiB\n";

echo "Memory peak usage: " . memory_get_peak_usage(true) / 1024 . " KiB\n";