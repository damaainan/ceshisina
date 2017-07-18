# 【redis专题(7)】命令语法介绍之Pub/Sub

Redis- - -

Redis 发布订阅(pub/sub)是一种消息通信模式：发送者(pub)发送消息，订阅者(sub)接收消息。主要的目的是解耦消息发布者和消息订阅者之间的耦合，这点和设计模式中的观察者模式比较相似。pub /sub不仅仅解决发布者和订阅者直接代码级别耦合也解决两者在物理部署上的耦合。   
redis作为一个pub/sub server，在订阅者和发布者之间起到了消息路由的功能。订阅者可以通过subscribe和psubscribe命令向redis server订阅自己感兴趣的消息类型，redis将消息类型称为通道(channel)。当发布者通过publish命令向redis server发送特定类型的消息时。订阅该消息类型的全部client都会收到此消息。这里消息的传递是多对多的。一个client可以订阅多个channel,也可以向多个channel发送消息。

下图展示了频道 channel1 ， 以及订阅这个频道的三个客户端 —— client2 、 client5 和 client1 之间的关系：

![pubsub1.png-7kB][0]

当有新消息通过 PUBLISH 命令发送给频道 channel1 时， 这个消息就会被发送给订阅它的三个客户端：

![pubsub2.png-10.4kB][1]

最明显的用法就是构建实时消息系统，比如普通的即时聊天，群聊等功能。这时每个人都是订阅者与发布者。

## 命令简述

    SUBSCRIBE channel [channel2 ...] 
    

订阅给定的一个或多个频道的信息。

    PSUBSCRIBE pattern [pattern ...] 
    

订阅一个或多个符合给定模式的频道。每个模式以 * 作为匹配符，比如 it* 匹配所有以 it 开头的频道( it.news 、 it.blog 、 it.tweets 等等)。 news.* 匹配所有以 news. 开头的频道( news.it 、 news.global.today 等等)，诸如此类。

    pubsub channels [pattern]
    

列出活跃频道(正在被subscribe监听的频道,注意不包括psubscribe监听的)

    pubsub numsub [channel-1 ... channel-n] 
    

返回给定频道的订阅者数量，订阅模式的客户端不计算在内

    PUBSUB NUMPAT 
    

返回订阅模式的数量。

    UNSUBSCRIBE [channel [channel ...]] 
    

指退订给定的频道。

    PUNSUBSCRIBE [pattern [pattern ...]] 
    

退订所有给定模式的频道。

    PUBLISH channel message 
    

将信息发送到指定的频道。

## Example

开两个redis-cli 一个作为发布者，一个作为订阅者；

    

    # 订阅者
    127.0.0.1:6379> subscribe news #订阅news频道，这个时候就是一个监听状态了，只要发布者一发布消息，订阅者就会收到
    Reading messages... (press Ctrl-C to quit)
    1) "subscribe"
    2) "news"
    3) (integer) 1
    # 发布者
    redis 127.0.0.1:6379> publish news 'good good study'
    (integer) 1 #这里反馈的是有多少个subscribe客户端接收到这条news;
    redis 127.0.0.1:6379> publish news 'day day up'
    (integer) 1

[0]: ./pubsub1.png
[1]: ./pubsub2.png