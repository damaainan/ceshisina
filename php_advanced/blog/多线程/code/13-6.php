<?php
// 查看当前系统平台支持的IO多路复用的方法都有哪些？
$method = Event::getSupportedMethods();
print_r($method);
// 查看当前用的方法是哪一个？
$eventBase = new EventBase();
echo "当前event的方法是：" . $eventBase->getMethod() . PHP_EOL;
// 跑了许久龙套的config这次也得真的露露手脚了
$eventConfig = new EventConfig;
// 避免使用方法kqueue
$eventConfig->avoidMethod('kqueue');
// 利用config初始化event base
$eventBase = new EventBase($eventConfig);
echo "当前event的方法是：" . $eventBase->getMethod() . PHP_EOL;
