# Redis源码剖析--列表t_list

 时间 2016-12-20 22:49:37  ZeeCoder

_原文_[http://zcheng.ren/2016/12/19/TheAnnotatedRedisSourcet-list/][1]


上一篇博客 [Redis源码剖析–快速列表][4] 带大家一起剖析了quicklist这个底层数据结构的实现原理。Redis对外开放的列表list结构就是采用quicklist作为底层实现（在新版本的Redis源码中，不再采用ziplist和sdlist两种结构，而是统一采用quicklist）。有关列表键的实现源码在t_list.c文件中，大家可以边看源码边看这篇博客，一起来理解。 

## List概述 

其实在[Redis源码剖析—对象Object]一文中有一个错误，list数据类型的底层编码并没有采用ziplist和sdlist，而是统一采用quicklist作为底层数据结构，这点需要提前说明一下。Redis的新版本中，list的底层编码类型只有OBJ_ENCODING_QUICKLIST，那么原先关于合适进行编码类型转换的代码都省略了。

列表没有其特有的数据结构，而是采用RedisObject作为其泛型数据结构，当RedisObject的编码类型为OBJ_LIST时，该对象被认为是一个列表。

Redis为列表提供了迭代器结构，本质就是quicklist迭代器的基本上做了一层封装。

    typedef struct {
        robj *subject;  // 迭代器指向的对象
        unsigned char encoding;  // 编码类型
        unsigned char direction; // 迭代器方向
        quicklistIter *iter; // quicklist的迭代器
    } listTypeIterator;
    // 代表list中的某个数据项
    typedef struct {
        listTypeIterator *li;  // list迭代器指针
        quicklistEntry entry; // quicklist的数据项节点结构
    } listTypeEntry;
    

## List主要接口 

列表定义了基本的接口函数，包括push，pop，insert，find等等，基本上都是在quicklist上做了一次封装。我们先来看看主要有哪些接口。

    // list的push操作
    voidlistTypePush(robj *subject, robj *value,intwhere);
    // list的pop操作
    robj *listTypePop(robj *subject,intwhere);
    // 返回list的数据项个数总和
    unsignedlonglistTypeLength(robj *subject);
    // 初始化一个list迭代器
    listTypeIterator *listTypeInitIterator(robj *subject,longindex,
                                           unsigned char direction);
    // 释放一个list迭代器
    voidlistTypeReleaseIterator(listTypeIterator *li);
    // 指向下一个数据项的list迭代器
    intlistTypeNext(listTypeIterator *li, listTypeEntry *entry);
    // 返回entry指向的list中的数据项的值
    robj *listTypeGet(listTypeEntry *entry);
    // 在entry指向的list数据项前面或者后面插入value
    voidlistTypeInsert(listTypeEntry *entry, robj *value,intwhere);
    // 比较entry指向的list中的数据项与o的大小
    intlistTypeEqual(listTypeEntry *entry, robj *o);
    // 删除entry指向的list中的数据项
    voidlistTypeDelete(listTypeIterator *iter, listTypeEntry *entry);
    // 将OBJ_ENCODING_ZIPLIST类型编码的列表转换成OBJ_ENCODING_QUICKLIST编码的列表
    voidlistTypeConvert(robj *subject,intenc);
    

其中，我们以push和pop操作来简要看看这些函数的实现源码。

    // 向list中压入数据
    voidlistTypePush(robj *subject, robj *value,intwhere){
        // 仅仅当编码类型为OBJ_ENCODING_QUICKLIST时才进行操作
        if (subject->encoding == OBJ_ENCODING_QUICKLIST) {
            // 判断压入位置
            int pos = (where == LIST_HEAD) ? QUICKLIST_HEAD : QUICKLIST_TAIL;
            // 从value中解码出数据项
            value = getDecodedObject(value);
            // 得到数据项的长度
            size_t len = sdslen(value->ptr);
            // 调用quicklistPush插入数据
            quicklistPush(subject->ptr, value->ptr, len, pos);
            // 将数据项对象的引用次数减1，也就是释放value
            decrRefCount(value);
        } else {
            serverPanic("Unknown list encoding");
        }
    }
    // 向list中弹出数据
    robj *listTypePop(robj *subject,intwhere){
        long long vlong;
        robj *value = NULL;
        // 判断弹出位置
        int ql_where = where == LIST_HEAD ? QUICKLIST_HEAD : QUICKLIST_TAIL;
        // 仅仅当编码类型为OBJ_ENCODING_QUICKLIST时才进行操作
        if (subject->encoding == OBJ_ENCODING_QUICKLIST) {
            // 调用quicklistPopCustom函数弹出数据
            if (quicklistPopCustom(subject->ptr, ql_where, (unsigned char **)&value,
                                   NULL, &vlong, listPopSaver)) {
                if (!value)
                    // 将数据项编码成string类型的RedisObject
                    value = createStringObjectFromLongLong(vlong);
            }
        } else {
            serverPanic("Unknown list encoding");
        }
        // 返回string类型编码的数据项对象
        return value;
    }
    

其他的一些接口函数均是调用quicklist提供的底层接口函数来实现，大家有空可以对照源码来看看。

## List命令 

与string一样，list也提供了很多命令以供用户使用。按照惯例，先列一张表给大家（包含部分重要指令）。

命令形式 命令描述 LPUSH key value1 [value2….] 将一个或多个值插入到列表头部 LPOP key 移除并获取列表的头部第一个元素 RPUSH key value1 [value2….] 将一个或者多个值插入到列表尾部 RPOP key 移除并获取列表的尾部第一个元素 LPUSHX key value 为已存在的列表头部添加值 RPUSHX key value 为已存在的列表尾部添加值 BLPOP key1 [key2…] timeout 移出并获取列表的第一个元素（阻塞模式） BRPOP key1 [key2…] timeout 移出并获取列表的最后一个元素（阻塞模式） BRPOPLPUSH source destination timeout 从列表中弹出一个值，将弹出的元素插入到另外一个列表中并返回它（阻塞模式） LLEN key 获取列表长度 LINDEX 通过索引获取列表中的元素 

同样，博主仅仅贴出部分源码来供大家理解这些命令的简要实现过程，我们来看看LPUSH和RPUSH命令的实现。

    // lpush操作
    voidlpushCommand(client *c){
        c->argv[2] = tryObjectEncoding(c->argv[2]);
        pushxGenericCommand(c,NULL,c->argv[2],LIST_HEAD);
    }
    // rpush操作
    voidrpushCommand(client *c){
        c->argv[2] = tryObjectEncoding(c->argv[2]);
        pushxGenericCommand(c,NULL,c->argv[2],LIST_TAIL);
    }
    // 真正的push操作函数，where指定位置
    voidpushGenericCommand(client *c,intwhere){
        int j, waiting = 0, pushed = 0;
        // 在数据库中查找是否存在该键，如果存在则返回该键，反之返回NULL
        robj *lobj = lookupKeyWrite(c->db,c->argv[1]);
        // 如果该键并非list，属于类型错误，交由服务器处理
        if (lobj && lobj->type != OBJ_LIST) {
            addReply(c,shared.wrongtypeerr);
            return;
        }
        // 添加数据元素
        for (j = 2; j < c->argc; j++) {
            // 试图将该元素编码成字符串类型以节省空间
            c->argv[j] = tryObjectEncoding(c->argv[j]);
            // 如果该列表不存在
            if (!lobj) {
                // 创建一个编码类型为OBJ_ENCODING_QUICKLIST的列表
                lobj = createQuicklistObject();
                // 设定列表的属性
                quicklistSetOptions(lobj->ptr, server.list_max_ziplist_size,
                                    server.list_compress_depth);
                // 将键和新的列表作为键值对添加到数据库
                dbAdd(c->db,c->argv[1],lobj);
            }
            // 将元素添加到列表中
            listTypePush(lobj,c->argv[j],where);
            // 记录添加的元素个数
            pushed++;
        }
        // 返回添加的节点数量
        addReplyLongLong(c, waiting + (lobj ? listTypeLength(lobj) : 0));
        // 至少有一个添加成功则进行操作
        if (pushed) {
            char *event = (where == LIST_HEAD) ? "lpush" : "rpush";
            // 发送键修改信号
            signalModifiedKey(c->db,c->argv[1]);
            // 发送事件通知
            notifyKeyspaceEvent(NOTIFY_LIST,event,c->argv[1],c->db->id);
        }
        // 服务器的脏数据个数增加
        server.dirty += pushed;
    }
    

这些命令的源码实现基本上大同小异，不过相对于其他数据类型，list提供了带有阻塞的命令，包括BLPOP，BRPOP，BLPOPRPUSH，这些命令可能会造成客户端被阻塞。这属于list的一大特色，也是需要着重理解的地方。

## 阻塞命令 

前面提到，list为用户提供了三个带有阻塞模式的命令，分别是BLPOP，BRPOP，BLPOPRPUSH。那么，到底这些命令是如何执行，如何进行阻塞和解阻塞的呢？首先，我们来看看BLPOP，BRPOP的源码。

    // BLPOP命令
    voidblpopCommand(client *c){
        blockingPopGenericCommand(c,LIST_HEAD);
    }
    // BRPOP命令
    voidbrpopCommand(client *c){
        blockingPopGenericCommand(c,LIST_TAIL);
    }
    // 带有阻塞的pop命令实现函数
    voidblockingPopGenericCommand(client *c,intwhere){
        robj *o;
        mstime_t timeout;
        int j;
        // 取出timeout参数
        if (getTimeoutFromObjectOrReply(c,c->argv[c->argc-1],&timeout,UNIT_SECONDS)
            != C_OK) return;
        // 遍历所有输入键
        for (j = 1; j < c->argc-1; j++) {
            // 在当前数据库中查找list键
            o = lookupKeyWrite(c->db,c->argv[j]);
            if (o != NULL) {
                // 执行到此处，说明数据库中存在此键
                // 检查类型
                if (o->type != OBJ_LIST) {
                    addReply(c,shared.wrongtypeerr);
                    return;
                } else {
                    // list不为空的话，则转换为普通的pop操作
                    if (listTypeLength(o) != 0) {
                        // 当前list不为空，转换为普通的pop进行处理
                        char *event = (where == LIST_HEAD) ? "lpop" : "rpop";
                        robj *value = listTypePop(o,where);
                        serverAssert(value != NULL);
    
                        addReplyMultiBulkLen(c,2);
                        addReplyBulk(c,c->argv[j]);
                        addReplyBulk(c,value);
                        decrRefCount(value);
                        notifyKeyspaceEvent(NOTIFY_LIST,event,
                                            c->argv[j],c->db->id);
                        // 如果弹出后list为空，则删除
                        if (listTypeLength(o) == 0) {
                            dbDelete(c->db,c->argv[j]);
                            notifyKeyspaceEvent(NOTIFY_GENERIC,"del",
                                                c->argv[j],c->db->id);
                        }
                        signalModifiedKey(c->db,c->argv[j]);
                        server.dirty++;
    
                        /* Replicate it as an [LR]POP instead of B[LR]POP. */
                        rewriteClientCommandVector(c,2,
                            (where == LIST_HEAD) ? shared.lpop : shared.rpop,
                            c->argv[j]);
                        return;
                    }
                }
            }
        }
    
        /* If we are inside a MULTI/EXEC and the list is empty the only thing
         * we can do is treating it as a timeout (even with timeout 0). */
        // 如果用户在一个事务中执行阻塞命令，则返回一个空回复。这样做为了避免客户端死等
        if (c->flags & CLIENT_MULTI) {
            addReply(c,shared.nullmultibulk);
            return;
        }
    
        // 执行到此处，说明列表为空，或者当前键并不存在
        // 执行阻塞
        blockForKeys(c, c->argv + 1, c->argc - 2, timeout, NULL);
    }
    

从这段代码中，我们可以看出，当执行带有阻塞的pop命令时，有如下两种情况。

* 如果指定的list存在于当前数据库中且list不为空，则转而执行普通的pop操作
* 如果指定的list键不存在，或者该list为空，则执行阻塞操作

那么阻塞的过程是如下进行的呢？别急，我们去查看以下blockForKeys函数，看看它干了些什么。

    // 设置客户端对指定键的阻塞状态
    // 参数keys可以指定任意数量的键，timeout指定超时时间，target代表目标listType对象
    voidblockForKeys(client *c, robj **keys,intnumkeys,mstime_ttimeout, robj *target){
        dictEntry *de;
        list *l;
        int j;
        // 设定阻塞超时时间
        c->bpop.timeout = timeout;
        // 设置目标选项，target在执行RPOPLPUSH命令时使用
        c->bpop.target = target;
     
        if (target != NULL) incrRefCount(target);
        // 添加阻塞客户端和键的映射关系
        for (j = 0; j < numkeys; j++) {
            // 如果当前键存在，则忽略；反之则添加该键
            // bpop.keys记录所有造成客户端阻塞的键
            if (dictAdd(c->bpop.keys,keys[j],NULL) != DICT_OK) continue;
            incrRefCount(keys[j]);
    
            // blocking_keys是一个字典，其键为造成阻塞的键，值是一个链表，记录所有被该键阻塞的客户端
            // 查找当前造成阻塞的键
            de = dictFind(c->db->blocking_keys,keys[j]);
            if (de == NULL) {
                // 键不存在，则新创建一个，并将它关联到字典中
                int retval;
                // 创建新的list
                l = listCreate();
                // 将键和值加入到c->db->blocking_keys中
                retval = dictAdd(c->db->blocking_keys,keys[j],l);
                incrRefCount(keys[j]);
                serverAssertWithInfo(c,keys[j],retval == DICT_OK);
            } else {
                // 如果键存在，则直接获取该键的值
                l = dictGetVal(de);
            }
            // 将客户端加入到链表中
            listAddNodeTail(l,c);
        }
        // 阻塞该客户端
        blockClient(c,BLOCKED_LIST);
    }
    

在上述代码中，设计到server.c中的一些数据结构。这里我简要的罗列一下。

    typedef struct client {
        redisDb *db;   // 指向当前数据库
        blockingState bpop;  // 记录阻塞状态
        // ...其他的参数省略
    }
    // 阻塞状态结构体
    typedef struct blockingState {
        mstime_t timeout;      // 阻塞超时时间
    
        dict *keys;           // 记录所有造成客户端阻塞的键
        robj *target;         // 目标选项，target在执行RPOPLPUSH命令时使用，
    
        /* BLOCKED_WAIT */
        int numreplicas;        /* Number of replicas we are waiting for ACK. */
        long long reploffset;   /* Replication offset to reach. */
    } blockingState;
    typedef struct redisDb {
        dict *blocking_keys;        // 记录所有造成阻塞的键，及其相应的客户端
        // ...其他参数省略
    } redisDb;
    

Redis采用了一个字典结构blocking_keys，其将所有造成阻塞的键，以及阻塞于该键的所有客户端的信息存放起来。执行完这些以后，就调用blockClient函数，真正的对该客户端进行阻塞。

那么，接下来要考虑的问题就是如何解阻塞，客户端不可能一直阻塞在那吧，是不是？由我们之前设定的参数，可以推测出来，有两种情况会对客户端进行解阻塞操作。

* 执行阻塞的时候，设置了超时参数，如果阻塞时长超过了该参数设定的时间，则自动对该客户端进行解阻塞
* 执行阻塞的时候，记录了所有造成客户端阻塞的键，那么如果有其他客户端执行命令，往造成阻塞的键里面添加了新值，这个时候Redis检查到该键中有值了，就会处理pop命令，也就是说，Redis采用先阻塞，后执行的策略来执行阻塞命令。

有了这些推测之后，我们就去push命令中找关于解阻塞的操作，一番查找之后，锁定了signalListAsReady函数，该函数在dbadd函数中执行。于是，跳转到signalListAsReady函数的源码。

    // 如果有客户端因为等待给定key 被push阻塞，那么将此key加入到server.ready_keys中
    // 这个列表最终会被 handleClientsBlockedOnLists() 函数处理。
    voidsignalListAsReady(redisDb *db, robj *key){
        readyList *rl;
    
        // 如果在所有造成客户端阻塞的键中找不到此键，则不作处理
        if (dictFind(db->blocking_keys,key) == NULL) return;
    
        // 这个键已经存在于ready_keys中了，则不做处理
        if (dictFind(db->ready_keys,key) != NULL) return;
    
        // 执行到此，说明有客户端因为此键被阻塞，且此键不存在于db->ready_keys中
        // 创建一个新的readylists结构，保存键和数据库
        // 然后将该结构添加到server.ready_keys中
        rl = zmalloc(sizeof(*rl));
        rl->key = key;
        rl->db = db;
        incrRefCount(key);
        listAddNodeTail(server.ready_keys,rl);
    
        // 同样，将key添加到db->ready_keys中
        incrRefCount(key);
        serverAssert(dictAdd(db->ready_keys,key,NULL) == DICT_OK);
    }
    

此代码中有一点小小的疑惑， db->ready_keys 和 server.ready_keys 这不重复了吗？为什么要设计这两个同样的结构。于是我们来查看以下它们的定义。 

    typedef struct redisDb {
        dict *ready_keys;           // 存放push操作添加的造成阻塞的键，字典结构
        // 省略了其他参数
    } redisDb;
    
    struct redisServer {
        list *ready_keys;    // 存在push操作添加的造成阻塞的键，链表结构
        // 省略了不必要的参数
    }
    // ready_keys链表结构中存放的节点数据结构
    typedef struct readyList {
        redisDb *db;  // key所在的数据库
        robj *key;  //造成阻塞的键
    } readyList;
    

Redis采用了一个链表和一个字典结构存放同一个key，想了想，这似乎也有道理。假设我们往一个key中添加多个新值时，Redis只需要在 server.ready_keys 中为该key保存一个readyList节点即可，这样可以避免在一个事务或者脚本中将同一个key一次又一次的添加到 server.ready_keys 中，为了避免不重复添加，Redis又采用一个链表结构 db->ready_keys 来快速判断 server.ready_keys 中是否存在该键。这样一来，既保证了不重复添加，又保证了哈希结构带来的查找效率。 

好了，理解了这一点，我们继续往下剖析，在push操作的时候，只是回收了push进来的造成阻塞的键，如何利用这些信息对已经阻塞的客户端进行解阻塞呢？Redis在运行的过程中，会一直查看 server.ready_keys 里是否有值，如果有则需要对存放的值对应的客户端进行接阻塞，此操作由handleClientsBlockedOnLists函数执行。 

    // 遍历server.ready_keys中所有已经准备好的key，同时在c->db->blocking_keys中
    // 遍历所有由此键造成阻塞的客户端，如果key不为空的话，就从key中弹出一个元素返回给客户端并
    // 接触该客户端的阻塞状态，直到server.ready_keys为空，或没有因该key而阻塞的客户端为止
    voidhandleClientsBlockedOnLists(void){
        while(listLength(server.ready_keys) != 0) {
            list *l;
    
            // 备份server.ready_keys，然后初始化server.ready_keys
            l = server.ready_keys;
            server.ready_keys = listCreate();
            // 不为空
            while(listLength(l) != 0) {
                // 取出server.ready_keys中的第一个节点
                listNode *ln = listFirst(l);
                // 指向redislist结构
                readyList *rl = ln->value;
    
                /* First of all remove this key from db->ready_keys so that
                 * we can safely call signalListAsReady() against this key. */
                // 从ready_keys中移除就绪的key
                dictDelete(rl->db->ready_keys,rl->key);
    
                /* If the key exists and it's a list, serve blocked clients
                 * with data. */
                // 获取键对象，此对象非空且为list结构
                robj *o = lookupKeyWrite(rl->db,rl->key);
                if (o != NULL && o->type == OBJ_LIST) {
                    dictEntry *de;
    
                    /* We serve clients in the same order they blocked for
                     * this key, from the first blocked to the last. */
                    // 取出没有被这个key阻塞的客户端
                    de = dictFind(rl->db->blocking_keys,rl->key);
                    if (de) {
                        list *clients = dictGetVal(de);
                        int numclients = listLength(clients);
    
                        while(numclients--) {
                            // 取出客户端
                            listNode *clientnode = listFirst(clients);
                            client *receiver = clientnode->value;
                            // 设置弹出的目标对象（只在 BRPOPLPUSH 时使用）
                            robj *dstkey = receiver->bpop.target;
                            // 从列表中弹出元素
                            // 弹出的位置取决于是执行 BLPOP 还是 BRPOP 或者 BRPOPLPUSH
                            int where = (receiver->lastcmd &&
                                         receiver->lastcmd->proc == blpopCommand) ?
                                        LIST_HEAD : LIST_TAIL;
                            robj *value = listTypePop(o,where);
                            // 还有元素可弹出，非NULL
                            if (value) {
                                /* Protect receiver->bpop.target, that will be
                                 * freed by the next unblockClient()
                                 * call. */
                                if (dstkey) incrRefCount(dstkey);
                                // 取消客户端的阻塞状态
                                unblockClient(receiver);
    
                                if (serveClientBlockedOnList(receiver,
                                    rl->key,dstkey,rl->db,value,
                                    where) == C_ERR)
                                {
                                    /* If we failed serving the client we need
                                     * to also undo the POP operation. */
                                        listTypePush(o,value,where);
                                }
    
                                if (dstkey) decrRefCount(dstkey);
                                decrRefCount(value);
                            } else {
                                // 执行到此处，表示还有至少一个客户端被该key阻塞
                                // 这些客户端需要下一次push才能被解阻塞
                                break;
                            }
                        }
                    }
                    // 如果列表元素已经为空，那么从数据库中将它删除
                    if (listTypeLength(o) == 0) {
                        dbDelete(rl->db,rl->key);
                    }
                    /* We don't call signalModifiedKey() as it was already called
                     * when an element was pushed on the list. */
                }
    
                /* Free this item. */
                // 释放
                decrRefCount(rl->key);
                zfree(rl);
                listDelNode(l,ln);
            }
            listRelease(l); /* We have the new list on place at this point. */
        }
    }
    

剖析到此，整个阻塞操作的流程就都清晰明了了。如有疑惑，可以在留言区留言，咋们继续讨论。

## List小结 

本篇博客剖析list的主要接口，以及所有命令的实现，值得大家注意的是带阻塞的pop命令，这个在上文中有详细的实现过程，分析源码的过程就向探索迷宫一样，一步一步的把它藏在深处的秘密挖出来，坚持下去总会有收获。keep moving！明天继续按照预定的步骤分析！

[1]: http://zcheng.ren/2016/12/19/TheAnnotatedRedisSourcet-list/?utm_source=tuicool&utm_medium=referral
[4]: http://zcheng.ren/2016/12/19/TheAnnotatedRedisSourceQuicklist/