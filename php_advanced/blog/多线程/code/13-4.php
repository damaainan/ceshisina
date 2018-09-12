<?php
// 依然是照例行事，尽管暂时没什么实际意义上的配置
$eventConfig = new EventConfig();
// 初始化eventBase
$eventBase = new EventBase($eventConfig);
// 初始化event
$event = new Event($eventBase, SIGTERM, Event::SIGNAL, function () {
    echo "signal term." . PHP_EOL;
});
// 挂起event对象
$event->add();
// 进入循环
echo "进入循环" . PHP_EOL;
$eventBase->loop();
