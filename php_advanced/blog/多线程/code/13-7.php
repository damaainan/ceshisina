<?php
$base = new EventBase();
echo "特性：" . PHP_EOL;
$features = $base->getFeatures();
// 看不到这个判断条件的，请反思自己“位运算”相关欠缺
if ($features & EventConfig::FEATURE_ET) {
    echo "边缘触发" . PHP_EOL;
}
if ($features & EventConfig::FEATURE_O1) {
    echo "O1添加删除事件" . PHP_EOL;
}
if ($features & EventConfig::FEATURE_FDS) {
    echo "任意文件描述符，不光socket" . PHP_EOL;
}
