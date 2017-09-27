# Redis源码剖析--通知Notify

 时间 2016-12-27 20:33:51  ZeeCoder

原文[http://zcheng.ren/2016/12/27/TheAnnotatedRedisSourceNotify/][1]



Redis在2.8版本以后，增加了键空间（Keyspace Notifications future）通知功能，此特性允许客户端可以以订阅/发布的模式，接收那些对数据库中的键和值有影响的操作事件。Redis关于通知的源代码均在notify.c文件中实现，源码中只有三个功能函数，相对较为简单，但是要想理解其功能，就需要配合server.c和pubsub.c里面的部分代码。

## Notify概述 

对于Redis服务器，它可以通过订阅发布功能来发送服务器中的键空间事件。所谓键空间事件，就是数据库中键的增加，修改和删除等操作，用于告知收听该类事件的客户端当前数据库中执行了哪些操作。由于通知功能会影响服务器的工作效率，Redis默认在启动的时候不开启键空间事件通知功能。

我们有两种方式开启键空间事件通知功能，或者只接受特定类型的通知，一是修改redis.conf中的指定参数，如下：

```
    /* 默认为空，表示不开启键空间事件通知功能 */
    notify-keyspace-events ""
```

第二种方法是通过CONFIG SET命令来设定notify-keyspace-events参数，其命令形式如下：

```c
    /* xx代表订阅的事件类型，后面会讲到 */
    CONFIG SET notify-keyspace-events KE
```

当服务器开启键空间事件通知功能时，需要指定事件的类型，即开启哪些特定类型的通知。Redis设定了一系列的宏定义，用来标识事件的类型。

```c
    #define NOTIFY_KEYSPACE (1<<0)    /* K */
    #define NOTIFY_KEYEVENT (1<<1)    /* E */
    #define NOTIFY_GENERIC (1<<2)     /* g */
    #define NOTIFY_STRING (1<<3)      /* $ */
    #define NOTIFY_LIST (1<<4)        /* l */
    #define NOTIFY_SET (1<<5)         /* s */
    #define NOTIFY_HASH (1<<6)        /* h */
    #define NOTIFY_ZSET (1<<7)        /* z */
    #define NOTIFY_EXPIRED (1<<8)     /* x */
    #define NOTIFY_EVICTED (1<<9)     /* e */
    #define NOTIFY_ALL (NOTIFY_GENERIC | NOTIFY_STRING | NOTIFY_LIST | NOTIFY_SET | NOTIFY_HASH | NOTIFY_ZSET | NOTIFY_EXPIRED | NOTIFY_EVICTED) /* A */
```

其中，每一个宏定义代表的事件类型如下表：

事件代号 | 事件类型 
-|-
K | 键空间通知，所有通知以_ _keyspace@\ \_ _为前缀 
E | 键事件通知，所有通知以_ _keyevent@\ \_ _为前缀 
g | DEL、EXPIRE、RENAME等类型无关的通用命令 
$ | 字符串命令的通知 
l | 列表命令的通知 
s | 集合命令的通知 
h | 哈希命令的通知 
z | 有序集合命令的通知 
x | 过期事件：每当有过期键被删除时发送 
e | 驱逐事件：每当有键因为maxmemory政策而被删除时发送 
A | 参数g$lshzxe的别名，代表全部上述全部命令 

关于notify-keyspace-events的设定，输入参数必须至少要有一个K或者E，用来标识该通知是键空间还是键事件；如果不包含，不管其余参数为什么，都将不会有任何通知被分发。例如：

```
    ~ redis-cli
    /* 开启所有的事件 */
    127.0.0.1:6379> CONFIG SET notify-keyspace-events KEA
    OK
    /* 开启所有的键空间命令 */
    127.0.0.1:6379> CONFIG SET notify-keyspace-events KA
    OK
    /* 开启列表命令的键事件通知 */
    127.0.0.1:6379> CONFIG SET notify-keyspace-events El
    OK
```

## Notify源码实现 

Notify的功能由三个函数实现，没错，就是三个，这充分体现了Redis模块划分明确的优点，使得代码的重用性很强。下面来看一下这三个函数吧。

```c
    /* 将Notify设置参数由字符串转换成标识量flag */
    int keyspaceEventsStringToFlags(char *classes);
    /* 将Notify设置参数由标识量flags转换成字符串 */
    sds keyspaceEventsFlagsToString(int flags);
    /* 通知功能的实现 */
    void notifyKeyspaceEvent(int type,char *event, robj *key,int dbid);
```

首先来看看第一个函数，其功能是将Notify设置参数由字符串转换成标识量flag

```c
    int keyspaceEventsStringToFlags(char *classes){
        char *p = classes;
        int c, flags = 0;
        // 遍历每一个字符
        while((c = *p++) != '\0') {
            switch(c) {
            case 'A': flags |= NOTIFY_ALL; break;
            case 'g': flags |= NOTIFY_GENERIC; break;
            case '$': flags |= NOTIFY_STRING; break;
            case 'l': flags |= NOTIFY_LIST; break;
            case 's': flags |= NOTIFY_SET; break;
            case 'h': flags |= NOTIFY_HASH; break;
            case 'z': flags |= NOTIFY_ZSET; break;
            case 'x': flags |= NOTIFY_EXPIRED; break;
            case 'e': flags |= NOTIFY_EVICTED; break;
            case 'K': flags |= NOTIFY_KEYSPACE; break;
            case 'E': flags |= NOTIFY_KEYEVENT; break;
            default: return -1;
            }
        }
        return flags;
    }
```

再来看看其逆向函数，如下：

```c
    sds keyspaceEventsFlagsToString(int flags){
        sds res;
    
        res = sdsempty();
        // 收听全部事件
        if ((flags & NOTIFY_ALL) == NOTIFY_ALL) {
            res = sdscatlen(res,"A",1);
        } else {
            // 检查每一个控制位
            if (flags & NOTIFY_GENERIC) res = sdscatlen(res,"g",1);
            if (flags & NOTIFY_STRING) res = sdscatlen(res,"$",1);
            if (flags & NOTIFY_LIST) res = sdscatlen(res,"l",1);
            if (flags & NOTIFY_SET) res = sdscatlen(res,"s",1);
            if (flags & NOTIFY_HASH) res = sdscatlen(res,"h",1);
            if (flags & NOTIFY_ZSET) res = sdscatlen(res,"z",1);
            if (flags & NOTIFY_EXPIRED) res = sdscatlen(res,"x",1);
            if (flags & NOTIFY_EVICTED) res = sdscatlen(res,"e",1);
        }
        // K，E参数的判断
        if (flags & NOTIFY_KEYSPACE) res = sdscatlen(res,"K",1);
        if (flags & NOTIFY_KEYEVENT) res = sdscatlen(res,"E",1);
        return res;
    }
```

接下来，主角登场了，利用Redis的订阅和发布功能来发送键空间事件通知。

```c
    void notify KeyspaceEvent(int type,char *event, robj *key,int dbid){
        sds chan;
        robj *chanobj, *eventobj;
        int len = -1;
        char buf[24];
    
        // 通知功能关闭，直接退出
        if (!(server.notify_keyspace_events & type)) return;
        // 创建事件对象
        eventobj = createStringObject(event,strlen(event));
        // 键空间通知，格式为__keyspace@<db>__:<key> <event>
        if (server.notify_keyspace_events & NOTIFY_KEYSPACE) {
            chan = sdsnewlen("__keyspace@",11);
            len = ll2string(buf,sizeof(buf),dbid);
            chan = sdscatlen(chan, buf, len);
            chan = sdscatlen(chan, "__:", 3);
            chan = sdscatsds(chan, key->ptr);
            chanobj = createObject(OBJ_STRING, chan);
            // 调用pub/sub命令
            pubsubPublishMessage(chanobj, eventobj);
            decrRefCount(chanobj);
        }
        // 键时间通知，格式为__keyevente@<db>__:<event> <key>
        if (server.notify_keyspace_events & NOTIFY_KEYEVENT) {
            chan = sdsnewlen("__keyevent@",11);
            if (len == -1) len = ll2string(buf,sizeof(buf),dbid);
            chan = sdscatlen(chan, buf, len);
            chan = sdscatlen(chan, "__:", 3);
            chan = sdscatsds(chan, eventobj->ptr);
            chanobj = createObject(OBJ_STRING, chan);
            // 调用pub/sub命令
            pubsubPublishMessage(chanobj, key);
            decrRefCount(chanobj);
        }
        decrRefCount(eventobj);
    }
```

整个通知的实现就是这么简单，通过pub/sub功能来发送事件通知，使得客户端能收到键空间事件。

## Notify实例 

为了验证上述的通知，是否按照预想发送了，我们可以做一个小的实验来验证一下。首先开启两个redis-cli客户端，每个客户端运行下述命令。

```
    /* 0号客户端 */
    127.0.0.1:6379> PSUBSCRIBE __keyevent*
    Reading messages... (press Ctrl-C to quit)
    1) "psubscribe"
    2) "__keyevent*"
    3) (integer) 1
    /* 1号客户端 */
    127.0.0.1:6379> CONFIG SET notify-keyspace-events KEA
    OK
    127.0.0.1:6379> set str value
    OK
```

0号客户端运行了PSUBSCRIBE命令后，就开始订阅了符合模式串__keyevent*的事件，1号客户端首先设置服务器开启键空间事件通知功能，然后运行SET命令，这个时间0号客户端就可以接收到这个事件，如下：

```
    /* 0号客户端 */
    127.0.0.1:6379> PSUBSCRIBE __key*
    Reading messages... (press Ctrl-C to quit)
    1) "psubscribe"
    2) "__key*"
    3) (integer) 1
    /* keyspace键空间通知 */
    1) "pmessage"
    2) "__key*"
    3) "__keyspace@0__:str"
    4) "set"
    /* keyevents键事件通知*/
    1) "pmessage"
    2) "__key*"
    3) "__keyevent@0__:set"
    4) "str"
```

## Notify小结 

本篇博客简要的分析了一下通知功能的实现，源码部分比较简单，当数据库中的键发生改变且服务器开启了相应的事件类型通知时，Redis就会发送键事件通知，通过pub/sub命令来告知客户端此刻数据库中的修改操作。pub/sub功能会在后续的博客中讲到，到时候可以回头来理解以下整个通知流程。

—-end—-

[1]: http://zcheng.ren/2016/12/27/TheAnnotatedRedisSourceNotify/