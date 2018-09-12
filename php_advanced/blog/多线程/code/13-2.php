<?php
// 初始化一个EventConfig（舰岛），虽然是个仅用于演示的空配置
$eventConfig = new EventConfig();
// 根据EventConfig初始化一个EventBase（辽宁舰，根据舰岛配置下辽宁舰）
$eventBase = new EventBase($eventConfig);
// 初始化一个定时器event（歼15，然后放到辽宁舰机库中）
$timer = new Event($eventBase, -1, Event::TIMEOUT | Event::PERSIST, function () {
    echo microtime(true) . " : 歼15，滑跃，起飞！" . PHP_EOL;
});
// tick间隔为0.05秒钟，我们还可以改成0.5秒钟甚至0.001秒，也就是毫秒级定时器
$tick = 0.05;
// 将定时器event添加（将歼15拖到甲板加上弹射器）
$timer->add($tick);
// eventBase进入loop状态（辽宁舰！走你！）
$eventBase->loop();
