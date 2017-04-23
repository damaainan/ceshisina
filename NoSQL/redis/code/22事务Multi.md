# Redis源码剖析--事务Multi

 时间 2017-01-02 18:27:35  ZeeCoder

_原文_[http://zcheng.ren/2017/01/02/TheAnnotatedRedisSourceMulti/][1]


数据库事务，是指作为单个逻辑工作单元执行的一系列操作，这些操作要么全部执行，要么全部不执行。事务处理可以确保除非事务性单元内的所有操作都成功完成，否则不会永久更新面向数据的资源，这样可以简化错误恢复并使应用程序更加可靠。事务包括ACID特性，分别是Atomic（原子性）、Consistency（一致性）、Isolation（隔离性）和Durablity（持久性）。Redis作为一个key-value数据库，当然也必须拥有事务处理功能，下面就一起去看看它是怎么实现的吧？

## 事务概述 

Redis事务允许用户将多个命令包裹起来，然后一次性地、按顺序地执行被包裹的所有命令。在事务的处理过程中，服务器不会中断事务而去执行其他的操作，只有在包裹的命令全部执行完毕后，服务器才会去处理其他命令请求。

Redis事务提供了一下五个命令，用于用户操作事务功能，其分别是：

命令 | 功能 
-|-
MULTI | 开始一个新的事务 
DISCARD | 放弃执行事务 
EXEC | 执行事务中的所有命令 
WATCH | 监视一个或多个key，如果至少有一个key在EXEC之前被修改，则放弃执行事务 
UNWATCH | 取消WATCH命令对所有键的监视 

为了更好的分析Redis事务功能，我们先来实验感受一下如何使用事务功能及它的功效！

    ~ redis-cli 
    127.0.0.1:6379> MULTI     // 开启一个事务
    OK
    127.0.0.1:6379> SET key value   // 添加命令
    QUEUED   // 将命令添加到命令队列
    127.0.0.1:6379> SADD key1 value1
    QUEUED 
    127.0.0.1:6379> EXEC  // 执行事务
    1) OK
    2) (integer) 1
    

如上，我们先开启了一个事务，然后添加了两条命令，最后执行此事务，两条命令全部执行并收到了回复。我们就从这个简单的事务处理过程，来一步一步分析Redis事务的具体实现过程。

## 事务实现 

由上述的小例子可以看出，Redis对于事务的实现由三个步骤：事务开始、事务队列和事务执行。下面就分别从这三个步骤入手，分析整个事务的实现过程。

## 事务开始 

当我们发送 MULTI 命令是，表示客户端需要执行一个事务。客户端定义了几个参数，来标记事务是否开始。 

    int flags; // 客户端当前事件标记
    #defineCLIENT_MULTI (1<<3) // 客户端事务标记
    

客户端通过 flags |= CLIENT_MULTI 语句来标记事务开启与否，然后服务器在执行命令的时候只需要检查flags参数，就能知道事务是否开启。下面是 MULTI 命令的源码实现： 

    voidmultiCommand(client *c){
        if (c->flags & CLIENT_MULTI) {  // 检查是否开启了事务
            addReplyError(c,"MULTI calls can not be nested");
            return;
        }
        c->flags |= CLIENT_MULTI;  // 标记事务已经开启
        addReply(c,shared.ok);  // 回复客户端
    }
    

## 事务队列 

既然事务中包含了一系列的操作，这些操作不能立即被执行，Redis必然会找个位置来存放这些命令。于是Redis定义了下面的结构体：

    /* 客户端结构体 */
    struct client {
        // ....
        multiState mstate;
        // ....
    }
    /* 事务状态 */
    typedef struct multiState {
        multiCmd *commands;     // 事务队列，存放命令，FIFO结构
        int count;              // 事务中所有命令的个数
        int minreplicas;        // 用于同步复制
        time_t minreplicas_timeout; // 超时时间
    } multiState;
    /* 命令队列 */
    typedef struct multiCmd {
        robj **argv; // 参数
        int argc; // 参数个数
        struct redisCommand *cmd; // 命令指针
    } multiCmd;
    

其中，所有在事务期间的命令都存放在事务队列中，也就是 commands 指针内。Redis在 processCommand 执行命令的函数里面判断此时是否开启了一个事务，如开启，则将命令压入命令队列，等待事务来处理。 

    /* Redis的命令处理函数 */
    intprocessCommand(client *c){
        // ...
        // 检查此时是否开启的事务，检查当前执行的命令不是EXEC、DISCARD、MULTI和WATCH
        if (c->flags & CLIENT_MULTI &&
            c->cmd->proc != execCommand && c->cmd->proc != discardCommand &&
            c->cmd->proc != multiCommand && c->cmd->proc != watchCommand)
        {
            // 执行命令入队
            queueMultiCommand(c);
            addReply(c,shared.queued);
        }
        // ...
    }
    

事务命令入队的功能由 queueMultiCommand 函数执行，其源码如下： 

    /* 添加命令到事务队列 */
    voidqueueMultiCommand(client *c){
        multiCmd *mc;
        int j;
        // 重新申请内存
        c->mstate.commands = zrealloc(c->mstate.commands,
                sizeof(multiCmd)*(c->mstate.count+1));
        // 找到新命令存放的位置
        mc = c->mstate.commands+c->mstate.count;
        // 存放命令
        mc->cmd = c->cmd;
        mc->argc = c->argc; // 存放参数个数
        mc->argv = zmalloc(sizeof(robj*)*c->argc);  // 存放参数
        memcpy(mc->argv,c->argv,sizeof(robj*)*c->argc); // 拷贝参数
        for (j = 0; j < c->argc; j++)  // 引用计数加1
            incrRefCount(mc->argv[j]);
        c->mstate.count++;  // 命令个数加1
    }
    

## 事务执行 

前面事务开始后的命令都存放在命令队列中，当客户端执行 EXEC 命令时，服务器会将事务队列中存放的命令以『先进先出』的方式一一执行，然后回复给客户端。 

    voidexecCommand(client *c){
        int j;
        robj **orig_argv;
        int orig_argc;
        struct redisCommand *orig_cmd;
        int must_propagate = 0; // 是否需要将MULTI/EXEC命令传播给slave节点或AOF
        // 如果客户端不处于事务状态，直接报错
        if (!(c->flags & CLIENT_MULTI)) {
            addReplyError(c,"EXEC without MULTI");
            return;
        }
        // 检查是否需要终止EXEC操作，因为：
        // (1) 有被监控的键被修改
        // (2) 命令入队时发生错误
        // 第一种情况会返回多个nil空对象，准确地说这不是一个错误而是一种特殊行为
        // 第二种情况会返回一个EXECABORT错误
        if (c->flags & (CLIENT_DIRTY_CAS|CLIENT_DIRTY_EXEC)) {
            addReply(c, c->flags & CLIENT_DIRTY_EXEC ? shared.execaborterr :
                                                      shared.nullmultibulk);
            // 取消事务，Redis不支持事务回滚
            discardTransaction(c);
            goto handle_monitor;
        }
    
        unwatchAllKeys(c); // 取消所有对键的监控
        // 先备份一次命令队列中的命令
        orig_argv = c->argv;
        orig_argc = c->argc;
        orig_cmd = c->cmd;
        addReplyMultiBulkLen(c,c->mstate.count);
        // 遍历事务中的命令，一一交给客户端处理
        for (j = 0; j < c->mstate.count; j++) {
            c->argc = c->mstate.commands[j].argc;
            c->argv = c->mstate.commands[j].argv;
            c->cmd = c->mstate.commands[j].cmd;
    
            // 当我们第一次遇到写命令的时候，传播MULTI命令
            // 这里我们MULTI/.../EXEC当做一个整体传输，保证服务器和AOF以及附属节点的一致性
            if (!must_propagate && !(c->cmd->flags & CMD_READONLY)) {
                execCommandPropagateMulti(c);
                must_propagate = 1;
            }
            // 执行命令
            call(c,CMD_CALL_FULL);
    
            // 命令执行后可能会被修改，需要更新操作
            c->mstate.commands[j].argc = c->argc;
            c->mstate.commands[j].argv = c->argv;
            c->mstate.commands[j].cmd = c->cmd;
        }
        // 回复原命令
        c->argv = orig_argv;
        c->argc = orig_argc;
        c->cmd = orig_cmd;
        // 消除事务状态
        scardTransaction(c);
        // 确保MULTI命令确实被传播了
        if (must_propagate) server.dirty++;
    
    handle_monitor:
        /* Send EXEC to clients waiting data from MONITOR. We do it here
         * since the natural order of commands execution is actually:
         * MUTLI, EXEC, ... commands inside transaction ...
         * Instead EXEC is flagged as CMD_SKIP_MONITOR in the command
         * table, and we do it here with correct ordering. */
        if (listLength(server.monitors) && !server.loading)
            replicationFeedMonitors(c,server.monitors,c->db->id,c->argv,c->argc);
    }
    

## 事务取消 

Redis提供了 DISCARD 函数来取消当前客户端的事务状态，其主要操作是： 

* 清空命令队列
* 初始化命令队列
* 取消标记flag
* 取消所有被监视的键

它的实现很简单，源码如下：

    /* 取消事务 */
    voiddiscardCommand(client *c){
        // 如果当前不处在事务状态，则报错
        if (!(c->flags & CLIENT_MULTI)) {
            addReplyError(c,"DISCARD without MULTI");
            return;
        }
        discardTransaction(c);
        addReply(c,shared.ok);
    }
    /* 取消事务的底层实现 */
    voiddiscardTransaction(client *c){
        freeClientMultiState(c); // 释放事务队列
        initClientMultiState(c);  // 初始化事务队列
        // 取消所有有关事务的标记
        c->flags &= ~(CLIENT_MULTI|CLIENT_DIRTY_CAS|CLIENT_DIRTY_EXEC);
        // 取消所有被监视的键
        unwatchAllKeys(c);
    }
    

## WATCH实现 

事务功能中还提供了监视键的功能，当我们对某个键执行了监视之后，如果事务执行期间该键被修改，则不执行该事务。同样，先看个小例子来试试 WATCH 的功能。 

    /* 客户端一，执行监视和事务*/
    ~ redis-cli
    127.0.0.1:6379> WATCH key1  // 监视key1
    OK
    127.0.0.1:6379> MULTI
    OK
    127.0.0.1:6379> SET key1 value1
    QUEUED
    /* 客户端二，执行修改 */
    ~ redis-cli
    127.0.0.1:6379> SET key1 value2
    OK
    

当客户端一执行 EXEC 时，其返回结果如下： 

    127.0.0.1:6379> EXEC
    (nil)
    

表示该事务没有被执行，进一步验证了 WATCH 的功能。接下来，就去源码里真正理解它是如何工作的吧。为了实现 WATCH/UNWATCH 功能，Redis在服务器的数据库结构中定义了一个字典结构用来存放被监听的键及其相应的客户端。 

    /* redisDB数据库结构体 
     * | key1 | —— | client1 | -> | client2 |-> | client3 |
     * | key2 | —— | client4 |
     * | key3 | —— | client5 | -> | client6 |
     * 该字典结构的键为被监视的键，值为链表，保存监视该键的所有客户端
     */
    typedef struct redisDb {
        // ...
        dict *watched_keys;      // 保存所有被监视的键及相应客户端
        // ...
    } redisDb;
    

另外，在客户端结构中，也定义了一个链表结构，用来保存该客户端所有监视的键，该链表的每一个接待都是一个 watchedKey 结构。 

    /* 客户端结构 */
    typedef struct client {
        // ...
        list *watched_keys;  // 保存该客户端所有被监视的键
        // ...
    }
    /* 被监视的键结构体 */
    typedef struct watchedKey {
        robj *key;  // 保存键
        redisDb *db;  // 保存键所在的数据库
    } watchedKey;
    

这么做的原因是，当客户端添加监视键的时候，能快速判断该键是否已经被监视；而且，当客户端取消所有被监视键的时候，可以快速找到该键所在的数据库，从而在 redisDb->watched_keys 删除该被监视的键。下面来看看添加监视键和取消监视键的源码实现。 

    /* 监视一个或多个键 */
    voidwatchForKey(client *c, robj *key){
        list *clients = NULL;
        listIter li;
        listNode *ln;
        watchedKey *wk;
    
        // 检查该键是否已经被监视
        listRewind(c->watched_keys,&li);
        while((ln = listNext(&li))) {
            wk = listNodeValue(ln);
            if (wk->db == c->db && equalStringObjects(key,wk->key))
                return; // 已经被监视直接返回
        }
        // 该键没有被监视，添加该键
        clients = dictFetchValue(c->db->watched_keys,key);
        if (!clients) {
            clients = listCreate();
            dictAdd(c->db->watched_keys,key,clients);
            incrRefCount(key);
        }
        listAddNodeTail(clients,c);
        // 添加新键到客户端的watched_keys链表中
        wk = zmalloc(sizeof(*wk));
        wk->key = key;
        wk->db = c->db;
        incrRefCount(key);
        listAddNodeTail(c->watched_keys,wk);
    }
    /* 取消对所有键的监视 */
    voidunwatchAllKeys(client *c){
        listIter li;
        listNode *ln;
        // 当前没有监视的键，直接返回
        if (listLength(c->watched_keys) == 0) return;
        // 遍历所有监视的键，并一一取消监视
        listRewind(c->watched_keys,&li);
        while((ln = listNext(&li))) {
            list *clients;
            watchedKey *wk;
    
            // 在db->watched_keys中查找该键，并在客户端链表中删除该客户端
            wk = listNodeValue(ln);
            clients = dictFetchValue(wk->db->watched_keys, wk->key);
            serverAssertWithInfo(c,NULL,clients != NULL);
            listDelNode(clients,listSearchKey(clients,c));
            // 如果没有客户端监视该键了，直接删除键
            if (listLength(clients) == 0)
                dictDelete(wk->db->watched_keys, wk->key);
            // 从客户端的watched_keys链表中移除监视的键
            listDelNode(c->watched_keys,ln);
            decrRefCount(wk->key);
            zfree(wk);
        }
    }
    

以上源码就是对字典结构和链表结构的添加和删除操作，很好理解。那么服务器运行过程中，在哪里判断该键有没有被修改呢？我们找到了 touchWatchedKey 函数。 

    /* 标记被监视的键已被修改 */
    voidtouchWatchedKey(redisDb *db, robj *key){
        list *clients;
        listIter li;
        listNode *ln;
    
        if (dictSize(db->watched_keys) == 0) return;
        // 获取监视key的所有客户端
        clients = dictFetchValue(db->watched_keys, key);
        if (!clients) return;
    
        // 标记监视key的所有客户端为CLIENT_DIRTY_CAS
        listRewind(clients,&li);
        while((ln = listNext(&li))) {
            client *c = listNodeValue(ln);
            // 在flag变量中标记
            c->flags |= CLIENT_DIRTY_CAS;
        }
    }
    

当然，这只是对所有被修改键的客户端进行标记，还是没有弄清楚在什么时候标记这些客户端。于是，继续追溯，发现这个函数通常被 signalModifyKey() 函数进行封装，这下又见到了我们的『老朋友』了，这个总是在键被修改的函数里调用的函数。 

    /* 标记被修改的键 */
    voidsignalModifiedKey(redisDb *db, robj *key){
        touchWatchedKey(db,key);
    }
    

这么一来算是理清了 WATCH 命令的整个实现流程。 

## 事务小结 

本篇博客分析了事务MULTI/EXEC命令的实现以及WATCH监视命令的实现，从源码的角度剖析了其整个工作流程，涉及到multi.c、server.c和db.c文件，大家阅读的时候记得一定要理清整个流程。Redis的事务是具有ACID性质的，即原子性、一致性、隔离性和持久性，这个可以在源码中体现出来。另外，Redis的WATCH命令采用乐观锁的设计，只要被监视的键被修改，该事务就不执行。短短300多行代码就实现了这个实用强大的功能！值得学习！


[1]: http://zcheng.ren/2017/01/02/TheAnnotatedRedisSourceMulti/?utm_source=tuicool&utm_medium=referral
