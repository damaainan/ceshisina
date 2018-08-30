<?php
function generateData($max)
{
  for ($i = 0; $i <= $max; $i++) {
      yield $i;
  }
}

echo '开始前内存占用：' . memory_get_usage() . PHP_EOL;
$data = generateData(1000000);// 这里实际上得到的是一个迭代器
echo '生成完数组后内存占用：' . memory_get_usage() . PHP_EOL;
unset($data);
echo '释放后的内存占用：' . memory_get_usage() . PHP_EOL;