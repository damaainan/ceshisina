<?php
function generateData($max)
{
    $arr = [];
    for ($i = 0; $i <= $max; $i++) {
        $arr[] = $i;
    }
}

echo '开始前内存占用：' . memory_get_usage() . PHP_EOL;
$data = generateData(1000000);
echo '生成完数组后内存占用：' . memory_get_usage() . PHP_EOL;
unset($data);
echo '释放后的内存占用：' . memory_get_usage() . PHP_EOL;