# Redis源码剖析--发布与订阅Pubsub

 时间 2016-12-29 17:59:59  ZeeCoder

_原文_[http://zcheng.ren/2016/12/29/TheAnnotatedRedisSourcePubsub/][1]



在分析Notify通知功能的时候讲到，Notify是用过订阅和发布功能来发送通知的。本来按计划是要分析持久化的代码的，可是对这个pubsub实在是有点感兴趣，所以先分析这方面的代码。订阅和发布，顾名思义，就是客户端可以订阅某个频道，也可以向某个频道发布消息，有点像收音机的功能一样。

## Pubsub概述 

Redis的发布和订阅功能由PUBLISH、SUBSCRIBE和PSUBSCRIBE等命令组成，要想理解源码，必须首先熟悉这些命令的形式和功能。

首先我们打开三个redis-cli的客户端，其中，每个客户端的职责如下：

* 0号客户端：打开redis-server，开启服务器程序
* 1号客户端：向频道发送消息
* 2号客户端：订阅channle0和channle2频道
* 3号客户端：订阅channle1和channle2频道

初始化如下图所示：

![][5]

接下来，1号客户端分别向channle0，channle1和channle2发送消息，查看2、3号客户端是否接收到。

![][6]

接下来，测试多个收听channle2的客户端能否同时受到1号客户端向channle2频道发布的消息。

![][7]

如此一来，简单的订阅和发布功能就基本上了解了。下面，一起来看看Redis的底层是怎么实现这个功能的。

## Pubsub数据结构 

阅读源码最好是从数据结构开始，这样能尽可能的理解功能函数。Redis服务器结构体中定义了如下数据结构用来记录某个频道有哪些客户端订阅。

    struct redisServer {
      // ...
      dict *pubsub_channels;  // 字典结构，用来记录频道和客户端的对应关系
      // ...
    }
    

例如，上一节中的简单示例里面，其服务器的dict结构布局如下：

![][8]

当客户端向某频道发送消息的时候，就检查这个字典下该频道对应的客户端，然后一一发送消息。

同样，在客户端结构体也用一个字典结构记录了该客户端订阅了哪些频道。

    struct client {
      // ...
      dict *pubsub_channels; // 记录了该客户端订阅了哪些频道
      // ...
    }
    

在上一届的示例中，2号客户端的 pubsub_channels 字典结构的结构布局如下： 

![][9]

该字典结构的键为收听的频道，值全为NULL，这样做的目的是快速判断该客户端是否收听了该频道。

另外，Redis还支持订阅特定模式的频道，其命令是PSUBSCRIBE，例如运行如下命令，就代表我可以订阅所有以chann开头的频道。

    PSUBSCRIBE chann*
    

关于订阅指定模式的频道，Redis定义了 pubsub_patterns 链表结构，在服务器结构体重，该链表的每一个节点都是一个 pubsubPattern 结构，具体定义如下： 

    /* 服务器结构 */
    struct redisServer {
      // ...
      list *pubsub_patterns;  // 记录了客户端和模式串的对应关系
      // ...
    }
    /* pubsub模式串结构体 */
    typedef struct pubsubPattern {
        client *client; // 指向客户端
        robj *pattern;  // 指向该客户端收听的模式串
    } pubsubPattern;
    /* 客户端结构 
     * 注意：在客户端结构中，该链表的每一个节点就是一个模式串
     * 而不是一个结构体。(这里为啥不采取字典结构，有待考虑)
     */
    struct client {
      // ...
      list *pubsub_patterns; // 记录了该客户端订阅了哪些模式串
      // ...
    }
    

假设客户端订阅了某个模式串，其会向上述两个链表中添加相关信息，之后发布消息的时候，会检查模式串是否符合要求，如符合就向客户端发送消息。

## 订阅 

## 订阅频道 

当客户端执行订阅频道命令的时候，客户端和服务器需要执行两个步骤：

* 向客户端的 pubsub_channels 字典中添加该频道
* 向服务器的 pubsub_channels 字典中添加该频道及其对应的客户端

上述两个步骤由subscribeCommand函数完成，其源码如下：

    /* 订阅频道命令的实现 */
    voidsubscribeCommand(client *c){
        int j;
        // 遍历指令中的所有频道
        for (j = 1; j < c->argc; j++)
            pubsubSubscribeChannel(c,c->argv[j]);
        c->flags |= CLIENT_PUBSUB;
    }
    /* 订阅频道的底层实现代码 */
    intpubsubSubscribeChannel(client *c, robj *channel){
        dictEntry *de;
        list *clients = NULL;
        int retval = 0;
    
        // 添加频道到client->pubsub_channels字典中
        if (dictAdd(c->pubsub_channels,channel,NULL) == DICT_OK) {
            retval = 1;
            incrRefCount(channel);
            // 查找server.pubsub_channels字典中是否存在该频道
            de = dictFind(server.pubsub_channels,channel);
            if (de == NULL) {
                // 如不存在就创建，客户端是以链表形式连接
                clients = listCreate();
                // 添加频道和收听该频道的客户端链表到pubsub_channels字典中
                dictAdd(server.pubsub_channels,channel,clients);
                incrRefCount(channel);
            } else {
                // 如果存在，获取客户端链表
                clients = dictGetVal(de);
            }
            // 将该客户端添加到客户端链表的尾部
            listAddNodeTail(clients,c);
        }
        // 通知客户端
        addReply(c,shared.mbulkhdr[3]);
        addReply(c,shared.subscribebulk);
        addReplyBulk(c,channel);
        addReplyLongLong(c,clientSubscriptionsCount(c));
        return retval;
    }
    

## 订阅模式 

当客户端执行订阅模式的指令时，同样需要对服务器和客户端的pubsub_patterns链表进行操作。其源码如下：

    /* 订阅模式命令的实现 */
    voidpsubscribeCommand(client *c){
        int j;
        // 遍历模式串
        for (j = 1; j < c->argc; j++)
            pubsubSubscribePattern(c,c->argv[j]);
        c->flags |= CLIENT_PUBSUB;
    }
    /* 订阅模式的底层实现 */
    intpubsubSubscribePattern(client *c, robj *pattern){
        int retval = 0;
        // 查看链表中该模式是否存在，如存在不做处理，反之则添加
        if (listSearchKey(c->pubsub_patterns,pattern) == NULL) {
            retval = 1;
            pubsubPattern *pat;
            // 添加模式串到client->pubsub_patterns链表的尾部
            listAddNodeTail(c->pubsub_patterns,pattern);
            incrRefCount(pattern);
            // 构造pubsubPattern结构体并赋值
            pat = zmalloc(sizeof(*pat));
            pat->pattern = getDecodedObject(pattern);
            pat->client = c;
            // 添加pubsubPattern结构体到链表尾部
            listAddNodeTail(server.pubsub_patterns,pat);
        }
        // 回复客户端
        addReply(c,shared.mbulkhdr[3]);
        addReply(c,shared.psubscribebulk);
        addReplyBulk(c,pattern);
        addReplyLongLong(c,clientSubscriptionsCount(c));
        return retval;
    }
    

## 退订 

退订的操作就放在一节里面讲了，无非就是从结构体中删除一些节点，事实就是如此，以退订频道为例：

    /* 退订频道的命令实现 */
    voidunsubscribeCommand(client *c){
        if (c->argc == 1) {
            // 退订所有频道
            pubsubUnsubscribeAllChannels(c,1);
        } else {
            int j;
            // 遍历频道，一一退订
            for (j = 1; j < c->argc; j++)
                // 退订频道
                pubsubUnsubscribeChannel(c,c->argv[j],1);
        }
        if (clientSubscriptionsCount(c) == 0) c->flags &= ~CLIENT_PUBSUB;
    }
    /* 退订频道的底层实现 */
    intpubsubUnsubscribeChannel(client *c, robj *channel,intnotify){
        dictEntry *de;
        list *clients;
        listNode *ln;
        int retval = 0;
        // 该指针可能指向字典结构中的同一个对象，此处需要保护它
        incrRefCount(channel); 
        // 在客户端的pubsub_channels字典中删除
        if (dictDelete(c->pubsub_channels,channel) == DICT_OK) {
            retval = 1;
            // 在服务器的pubsub_channels中删除
            de = dictFind(server.pubsub_channels,channel);
            serverAssertWithInfo(c,NULL,de != NULL);
            clients = dictGetVal(de); // 获取客户端链表
            ln = listSearchKey(clients,c); // 找到该客户端对应的节点
            serverAssertWithInfo(c,NULL,ln != NULL);
            listDelNode(clients,ln); // 删除节点
            if (listLength(clients) == 0) {
                // 如果该频道下没有客户端了，就删除字典中的该频道节点
                dictDelete(server.pubsub_channels,channel);
            }
        }
        // 通知客户端
        if (notify) {
            addReply(c,shared.mbulkhdr[3]);
            addReply(c,shared.unsubscribebulk);
            addReplyBulk(c,channel);
            addReplyLongLong(c,dictSize(c->pubsub_channels)+
                           listLength(c->pubsub_patterns));
    
        }
        // 到了这里可以安全的删除了
        decrRefCount(channel);
        return retval;
    }
    

其他的退订操作也是如此，下面仅罗列出它们的函数声明和功能，有兴趣的可以去源码中查看。

    /* 退订所有频道 */
    pubsubUnsubscribeAllChannels(client *c, int notify);
    /* 退订所有模式 */
    pubsubUnsubscribeAllPatterns(client *c, int notify);
    /* 退订一个或多个频道 */
    pubsubUnsubscribeChannel(client *c, robj *channel, int notify);
    /* 退订一个或多个模式 */
    pubsubUnsubscribePattern(client *c, robj *pattern, int notify);
    /* 退订模式的命令实现 */
    punsubscribeCommand(client *c);
    /* 退订频道的命令实现 */
    subscribeCommand(client *c);
    

## 发布消息 

当客户端调用发布消息的命令时，需要进行如下两个操作：

* 查找服务器的pubsub_channels字典下该频道对应的客户端链表，然后遍历，一一发送
* 查找服务器的pubsub_patterns链表，遍历模式串，如果匹配就发送，反之不作处理

发布消息的命令由publishCommand函数实现，其源码如下：

    /* 发布消息命令的实现 */
    voidpublishCommand(client *c){
        int receivers = pubsubPublishMessage(c->argv[1],c->argv[2]);
        // 如果开启了集群，需要向集群中的客户端发送消息
        // 现阶段不讨论集群
        if (server.cluster_enabled)
            clusterPropagatePublish(c->argv[1],c->argv[2]);
        else
            forceCommandPropagation(c,PROPAGATE_REPL);
        addReplyLongLong(c,receivers);
    }
    /* 发布消息的底层实现 */
    intpubsubPublishMessage(robj *channel, robj *message){
        int receivers = 0;
        dictEntry *de;
        listNode *ln;
        listIter li;
    
        // 发送到订阅该频道的所有客户端
        de = dictFind(server.pubsub_channels,channel);
        if (de) {
            // 如果存在该频道，则获取客户端链表
            list *list = dictGetVal(de);
            listNode *ln;
            listIter li;
            // 获取迭代器
            listRewind(list,&li);
            // 遍历，发送消息
            while ((ln = listNext(&li)) != NULL) {
                client *c = ln->value;
                // 发送消息
                addReply(c,shared.mbulkhdr[3]);
                addReply(c,shared.messagebulk);
                addReplyBulk(c,channel);
                addReplyBulk(c,message);
                receivers++;
            }
        }
        // 发送到所有模式能与该频道匹配上的客户端
        if (listLength(server.pubsub_patterns)) {
            // 获取迭代器
            listRewind(server.pubsub_patterns,&li);
            // 解码频道
            channel = getDecodedObject(channel);
            // 遍历该链表
            while ((ln = listNext(&li)) != NULL) {
                pubsubPattern *pat = ln->value;
                // 判断是否能匹配上
                if (stringmatchlen((char*)pat->pattern->ptr,
                                    sdslen(pat->pattern->ptr),
                                    (char*)channel->ptr,
                                    sdslen(channel->ptr),0)) {
                    // 能匹配上，发送消息
                    addReply(pat->client,shared.mbulkhdr[4]);
                    addReply(pat->client,shared.pmessagebulk);
                    addReplyBulk(pat->client,pat->pattern);
                    addReplyBulk(pat->client,channel);
                    addReplyBulk(pat->client,message);
                    receivers++;
                }
            }
            // 执行完之后，引用计数减1
            decrRefCount(channel);
        }
        // 返回收到消息的客户端个数
        return receivers;
    }
    

本来感觉到此就没有什么功能了，没想到还有一个函数给漏掉了。那就是PUBSUB命令的实现函数，一开始不怎么理解它，于是查看了一下源码。有意思，这是个含有子命令的命令。

    /* 后面的参数是模式串，子命令channels的功能是返回所有符合该模式串的频道 */
    PUBSUB CHANNELS [<pattern1>]
    /* 后面的参数是频道，子命令NUMSUB的功能是返回收听该频道的客户端个数 */
    PUBSUB NUMSUB [channel1 ... channeln]
    /* 子命令NUMPAT的功能是返回服务器中所有模式串频道的个数，即pubsub_patterns链表的长度*/
    PUBSUB NUMPAT
    

其源码实现也很简单，这里列出来大家一起看看。

    /* PUBSUB命令源码实现 */
    voidpubsubCommand(client *c){
        if (!strcasecmp(c->argv[1]->ptr,"channels") &&
            (c->argc == 2 || c->argc ==3))
        {
            // 子命令 PUBSUB CHANNELS [<pattern>]
            sds pat = (c->argc == 2) ? NULL : c->argv[2]->ptr;
            // 获取迭代器
            dictIterator *di = dictGetIterator(server.pubsub_channels);
            dictEntry *de;
            long mblen = 0;
            void *replylen;
    
            replylen = addDeferredMultiBulkLength(c);
            // 遍历并检查与模式串是否匹配
            while((de = dictNext(di)) != NULL) {
                robj *cobj = dictGetKey(de);
                sds channel = cobj->ptr;
                if (!pat || stringmatchlen(pat, sdslen(pat),
                                           channel, sdslen(channel),0))
                {
                    // 如匹配，就返回该频道的名称
                    addReplyBulk(c,cobj);
                    mblen++;
                }
            }
            dictReleaseIterator(di);
            setDeferredMultiBulkLength(c,replylen,mblen);
        } else if (!strcasecmp(c->argv[1]->ptr,"numsub") && c->argc >= 2) {
            // 子命令PUBSUB NUMSUB [Channel_1 ... Channel_N]
            int j;
    
            addReplyMultiBulkLen(c,(c->argc-2)*2);
            for (j = 2; j < c->argc; j++) {
                list *l = dictFetchValue(server.pubsub_channels,c->argv[j]);
    
                addReplyBulk(c,c->argv[j]);
                addReplyLongLong(c,l ? listLength(l) : 0);
            }
        } else if (!strcasecmp(c->argv[1]->ptr,"numpat") && c->argc == 2) {
            // 子命令PUBSUB NUMPAT
            addReplyLongLong(c,listLength(server.pubsub_patterns));
        } else {
            // 其他不能识别的命令 直接报错
            addReplyErrorFormat(c,
                "Unknown PUBSUB subcommand or wrong number of arguments for '%s'",
                (char*)c->argv[1]->ptr);
        }
    }
    

## Pubsub小结 

至此，发布和订阅这个有意思的功能就全部剖析完了，是不是感觉超级简单但很实用？其中，还是不太理解为什么模式串频道要用list，难道是因为模式串频道数据量比较小？要用list来节省内存？而且效率方面也不会影响多少？姑且就这么认为吧，哈哈。大家有什么疑惑和问题请在下方留言区留言，期待和志同道合的你一起讨论Redis！共同学习，共同进步！

欢迎转载本篇博客，不过请注明博客原地址： [http://zcheng.ren/2016/12/29/TheAnnotatedRedisSourcePubsub][10]


[1]: http://zcheng.ren/2016/12/29/TheAnnotatedRedisSourcePubsub/?utm_source=tuicool&utm_medium=referral

[5]: http://img0.tuicool.com/A3Mr2mJ.png!web
[6]: http://img1.tuicool.com/riQFVjr.jpg!web
[7]: http://img0.tuicool.com/M3q2yyi.jpg!web
[8]: http://img2.tuicool.com/fuqqEjA.png!web
[9]: http://img1.tuicool.com/j2AVNnu.png!web
[10]: http://zcheng.ren/2016/12/29/TheAnnotatedRedisSourcePubsub