# Redis源码分析—哈希t_hash

 时间 2016-12-23 16:19:53  ZeeCoder

_原文_[http://zcheng.ren/2016/12/23/TheAnnotatedRedisSourcet-hash/][1]



不知不觉，从第一篇写Redis源码分析开始，已经过了快一个月了，想想自己的进度，简直慢的吓人啊，这样下去不行，后面得加快脚步了。今天分析的是Redis的又一个数据类型—哈希，哈希键的底层编码形式有OBJ_ENCODING_ZIPLIST和OBJ_ENCODING_HT两种，其中，前者的底层数据结构为压缩列表，后者的底层数据结构为字典。如有对这两个结构不清楚的，可以点击跳转去温故复习一下。 

## Hash概述 

前面我们提到，Redis对于其五个对用户公开的数据类型统一采用RedisObject管理。Hash类型只需要修改encoding字段就能表示该对象为一个哈希对象。为了便于大家理解，我还是不厌其烦的先罗列出RedisObject的结构体定义。 

    typedef struct redisObject {
        unsigned type:4; // hash类型
        unsigned encoding:4;  // hash结构，此字段为OBJ_ENCODING_ZIPLIST或OBJ_ENCODING_HT
        unsigned lru:LRU_BITS;  // 上一次操作的时间
        int refcount; // 引用计数，便于内存管理
        void *ptr;  // 指向底层的数据结构
    } robj;
    

如果底层编码是ziplist的话，hash键按照如下方式排列，每一个key或value都作为ziplist的一个节点。

    |ziplistHeader|   entry1  |  entry2  |   entry3  |   entry4  | end |
                        ↓          ↓           ↓           ↓    
                  |    key1   |  value1  |    key2   |   value2  |
    

创建一个hash对象的时候，为了节省内存，会默认采用OBJ_ENCODING_ZIPLIST编码，其接口函数如下：

    robj *createHashObject(void){
        unsigned char *zl = ziplistNew();
        robj *o = createObject(OBJ_HASH, zl);  // 创建一个hash对象
        o->encoding = OBJ_ENCODING_ZIPLIST;  // 默认采用hash编码
        return o;
    }
    

一旦存放的整数或字符串长度超过一个阈值，或者ziplist的节点个数超过规定的阈值，就会将底层编码结构转换成OBJ_ENCODING_HT，此阈值在配置文件redis.conf中设定。Redis对于hash对象没有实现编码类型的反向转换功能，即一旦转换成OBJ_ENCODING_HT就不能转回去了。

    /* redis.conf文件中设定阈值 */
    hash-max-ziplist-value 64 // ziplist中最大能存放的值长度
    hash-max-ziplist-entries 512 // ziplist中最多能存放的节点数量
    

## Hash迭代器 

迭代器是每个数据类型都应该具备的数据结构，便于对该数据类型的每一个数据进行遍历操作，Hash的迭代器结构定义如下：

    typedef struct {
        robj *subject;  // 指向的hash对象
        int encoding;  // 编码类型
        // 用于迭代ziplist结构
        unsigned char *fptr, *vptr; // 域指针和值指针
        // 用于迭代dict结构
        dictIterator *di; // 字典迭代器
        dictEntry *de;  // 指向当前迭代字典节点的指针
    } hashTypeIterator;
    

迭代器提供了一系列的相关操作函数，初始化，指向下一个迭代器，以及释放迭代器，这些源码一并分析了。

    /* 初始化一个迭代器 */
    hashTypeIterator *hashTypeInitIterator(robj *subject){
        hashTypeIterator *hi = zmalloc(sizeof(hashTypeIterator));
        hi->subject = subject;
        hi->encoding = subject->encoding;
        // OBJ_ENCODING_ZIPLIST编码
        if (hi->encoding == OBJ_ENCODING_ZIPLIST) {
            hi->fptr = NULL;
            hi->vptr = NULL;
        // OBJ_ENCODING_HT编码
        } else if (hi->encoding == OBJ_ENCODING_HT) {
            hi->di = dictGetIterator(subject->ptr); // 字典结构有自己的迭代器
        } else {
            serverPanic("Unknown hash encoding");
        }
    
        return hi;
    }
    /* 释放一个迭代器 */
    voidhashTypeReleaseIterator(hashTypeIterator *hi){
        if (hi->encoding == OBJ_ENCODING_HT) {
            dictReleaseIterator(hi->di);
        }
        zfree(hi);
    }
    /* 迭代到下一个节点 */ 
    inthashTypeNext(hashTypeIterator *hi){
        if (hi->encoding == OBJ_ENCODING_ZIPLIST) {
            unsigned char *zl;
            unsigned char *fptr, *vptr;
    
            zl = hi->subject->ptr;
            fptr = hi->fptr;
            vptr = hi->vptr;
    
            if (fptr == NULL) {
                // 如果当前迭代器为空，则初始化指向ziplist的第一个节点
                serverAssert(vptr == NULL);
                fptr = ziplistIndex(zl, 0);
            } else {
                // 反之指向下一个key节点
                serverAssert(vptr != NULL);
                fptr = ziplistNext(zl, vptr);
            }
            if (fptr == NULL) return C_ERR;
    
            // fptr的下一个节点就是值节点
            vptr = ziplistNext(zl, fptr);
            serverAssert(vptr != NULL);
    
            // 更新参数
            hi->fptr = fptr;
            hi->vptr = vptr;
        } else if (hi->encoding == OBJ_ENCODING_HT) {
            // OBJ_ENCODING_HT编码的时候就直接调用哈希的迭代器即可
            if ((hi->de = dictNext(hi->di)) == NULL) return C_ERR;
        } else {
            serverPanic("Unknown hash encoding");
        }
        return C_OK;
    }
    

## Hash基本接口 

和其他数据类型一样，Redis为hash数据类型提供了丰富的接口函数。为了方便学习，我把函数声明罗列出来如下。

    /* 检查ziplist存放的数长度是否超过，如超过，则将编码类型转换成字典编码*/
    voidhashTypeTryConversion(robj *o, robj **argv,intstart,intend);
    /* 当hash采用OBJ_ENCODING_HT编码的时候，需要将键值对转换成字符串编码 */
    voidhashTypeTryObjectEncoding(robj *subject, robj **o1, robj **o2);
    /* 当hash采用OBJ_ENCODING_ZIPLIST编码的时候，根据域field获取值*/
    inthashTypeGetFromZiplist(robj *o, robj *field,unsignedchar**vstr,
                               unsigned int *vlen, long long *vll);
    /* 当hash采用OBJ_ENCODING_HT编码的时候，根据域field获取它的值*/
    inthashTypeGetFromHashTable(robj *o, robj *field, robj **value);
    /* 根据键获取值得泛型实现 
     * 当底层编码为OBJ_ENCODING_HT时，调用上述hashTypeGetFromHashTable函数
     * 当底层编码为OBJ_ENCODING_ZIPLIST时，调用上述hashTypeGetFromZiplist函数
    */
    robj *hashTypeGetObject(robj *o, robj *field);
    /* 获取hash对象中域field所指向值的长度*/
    size_t hashTypeGetValueLength(robj *o, robj *field);
    /* 判断域field是否存在于hash对象中*/
    inthashTypeExists(robj *o, robj *field);
    /* 向hash对象中添加键值对数据
     * 如果该键存在，则更新它的值；反之则添加新键值对
     */
    inthashTypeSet(robj *o, robj *field, robj *value);
    /* 删除hash对象中域field及其对应的值*/
    inthashTypeDelete(robj *o, robj *field);
    /* 返回hash对象中所有数据项的数量*/
    unsignedlonghashTypeLength(robj *o);
    /* 根据当前迭代器指向的位置，获取ziplist结构中当前位置的key或value
     * 至于是key或者value，由what参数执行，其取值为OBJ_HASH_KEY或OBJ_HASH_VALUE
     */
    voidhashTypeCurrentFromZiplist(hashTypeIterator *hi,intwhat,
                                    unsigned char **vstr,
                                    unsigned int *vlen,
                                    long long *vll);
    /* 同上，根据当前迭代器指向的位置，获取字典结构中当前位置上的key或者value*/
    voidhashTypeCurrentFromHashTable(hashTypeIterator *hi,intwhat, robj **dst);
    /* 获取迭代器当前位置上的key或value的泛型实现*/
    robj *hashTypeCurrentObject(hashTypeIterator *hi,intwhat);
    /* 在当前数据库中查找指定key是否存在，如果不存在就创建*/
    robj *hashTypeLookupWriteOrCreate(client *c, robj *key);
    /* 将当前hash对象的编码类型由OBJ_ENCODING_HT转换成OBJ_ENCODING_ZIPLIST*/
    voidhashTypeConvertZiplist(robj *o,intenc);
    /* hash对象的底层编码转换的泛型实现*/
    voidhashTypeConvert(robj *o,intenc);
    

还是以最重要的添加元素函数hashTypeSet为例，来剖析一下它的源码。添加元素的操作需要注意一下几点。

* 如果当前键field存在，则更新它的值；反之不存在就添加该键值对
* 当ziplist中存放的数据项个数超过512时，会将底层编码转换为OBJ_ENCODING_HT
```
    inthashTypeSet(robj *o, robj *field, robj *value){
        int update = 0;
        // 底层编码为OBJ_ENCODING_ZIPLIST
        if (o->encoding == OBJ_ENCODING_ZIPLIST) {
            unsigned char *zl, *fptr, *vptr;
            // 从robj中解码出字符串或者数字
            field = getDecodedObject(field);
            value = getDecodedObject(value);
            // 获得hash对象中的数据部分
            zl = o->ptr;
            // 得到ziplist的头指针
            fptr = ziplistIndex(zl, ZIPLIST_HEAD);
            if (fptr != NULL) {
                // 定位到域field
                fptr = ziplistFind(fptr, field->ptr, sdslen(field->ptr), 1);
                if (fptr != NULL) {
                    // 定位到域对应的值
                    vptr = ziplistNext(zl, fptr);
                    serverAssert(vptr != NULL);
                    // 标识这次为更新操作
                    update = 1;
    
                    // 删除旧的键值对
                    zl = ziplistDelete(zl, &vptr);
    
                    // 添加新的键值对
                    zl = ziplistInsert(zl, vptr, value->ptr, sdslen(value->ptr));
                }
            }
            // 如果不是一个更新操作，则该field不存在，需要添加新键值对
            if (!update) {
                // 将新的键值对添加到ziplist结构中
                zl = ziplistPush(zl, field->ptr, sdslen(field->ptr), ZIPLIST_TAIL);
                zl = ziplistPush(zl, value->ptr, sdslen(value->ptr), ZIPLIST_TAIL);
            }
            o->ptr = zl;
            // 引用计数减1，用于释放临时对象
            decrRefCount(field);
            decrRefCount(value);
    
            // 检查ziplist中存放的节点个数，如果超过512(默认值)则转换成OBJ_ENCODING_HT编码
            if (hashTypeLength(o) > server.hash_max_ziplist_entries)
                hashTypeConvert(o, OBJ_ENCODING_HT);
        // 底层编码为OBJ_ENCODING_HT时
        } else if (o->encoding == OBJ_ENCODING_HT) {
            // 添加或者替换键值对到字典
            // 如果添加则返回1；如果是替换则返回0
            if (dictReplace(o->ptr, field, value)) {
                incrRefCount(field);
            } else { 
                // 更新操作
                update = 1;
            }
            incrRefCount(value);
        } else {
            serverPanic("Unknown hash encoding");
        }
        // 返回是添加还是替换操作
        return update;
    }
    
```
## Hash命令 

对于一个hash对象，Redis为其与客户端交互提供了一系列的操作命令，例如，执行如下命令：

    // 添加键值对[key value]到hash对象中
    127.0.0.1:6379> HSET hash key value
    (integer) 1
    // 获取hash对象中key对应的值
    127.0.0.1:6379> HGET hash key
    "value"
    

这两个命令分别可以往hash对象中添加元素和获取键对应的值，其实现由hsetCommand和hgetCommand实现。下面来一起看看他们的具体实现步骤。

    /* 向哈希对象中添加元素*/
    voidhsetCommand(client *c){
        int update;
        robj *o;
        // 查找数据库中是否存在该哈希对象，如果不存在则创建并添加到数据库
        if ((o = hashTypeLookupWriteOrCreate(c,c->argv[1])) == NULL) return;
        // 检查待添加元素的长度，如果超过规定的阈值
        // 则将hash对象的编码由OBJ_ENCODING_ZIPLIST转换成OBJ_ENCODING_HT
        hashTypeTryConversion(o,c->argv,2,3);
        // 如果hash对象采用OBJ_ENCODING_HT编码时，将待添加的键和值转换成字符串编码
        hashTypeTryObjectEncoding(o,&c->argv[2], &c->argv[3]);
        // 向hash对象中添加键值对，返回操作类型：添加或者替换
        update = hashTypeSet(o,c->argv[2],c->argv[3]);
        // 返回状态
        addReply(c, update ? shared.czero : shared.cone);
        // 发送键修改信号
        signalModifiedKey(c->db,c->argv[1]);
        // 发送事件通知
        notifyKeyspaceEvent(NOTIFY_HASH,"hset",c->argv[1],c->db->id);
        // 服务器的脏数据增加1
        server.dirty++;
    }
    /* 获取哈希对象中指定键对应的值 */
    voidhgetCommand(client *c){
        robj *o;
        // 检查是否存在该对象且编码类型为HASH
        if ((o = lookupKeyReadOrReply(c,c->argv[1],shared.nullbulk)) == NULL ||
            checkType(c,o,OBJ_HASH)) return;
        // 取出并返回域field的值
        addHashFieldToReply(c, o, c->argv[2]);
    }
    /* 获取值的底层实现函数
     * 将哈希对象域yield的值添加到回复中
     */
    staticvoidaddHashFieldToReply(redisClient *c, robj *o, robj *field){
        int ret;
        // 对象不存在
        if (o == NULL) {
            addReply(c, shared.nullbulk);
            return;
        }
        // OBJ_ENCODING_ZIPLIST编码
        if (o->encoding == REDIS_ENCODING_ZIPLIST) {
            unsigned char *vstr = NULL;
            unsigned int vlen = UINT_MAX;
            long long vll = LLONG_MAX;
            // 从ziplist中取出值
            ret = hashTypeGetFromZiplist(o, field, &vstr, &vlen, &vll);
            if (ret < 0) {
                addReply(c, shared.nullbulk);
            } else {
                if (vstr) {
                    addReplyBulkCBuffer(c, vstr, vlen);
                } else {
                    addReplyBulkLongLong(c, vll);
                }
            }
    
        // OBJ_ENCODING_HT编码
        } else if (o->encoding == REDIS_ENCODING_HT) {
            robj *value;
    
            // 从字典结构中取出域field对应的值
            ret = hashTypeGetFromHashTable(o, field, &value);
            if (ret < 0) {
                addReply(c, shared.nullbulk);
            } else {
                addReplyBulk(c, value);
            }
    
        } else {
            redisPanic("Unknown hash encoding");
        }
    }
    

其他的命令函数，我一一列出函数声明，有兴趣的同学可以深入到t_set.c文件中剖析它们。

    /* 添加函数操作，如果域field存在则不做处理，反之则添加*/
    voidhsetnxCommand(client *c);
    /* 添加一个或多个键值对到hash对象中*/
    voidhmsetCommand(client *c);
    /* 给指定hash对象中的域field对应的值执行增加某个增量操作，值必须是整数*/
    voidhincrbyCommand(client *c);
    /* 同上，只不过增量是float类型*/
    voidhincrbyfloatCommand(client *c);
    /* 获取一个或多个域field对应的值*/
    voidhmgetCommand(client *c);
    /* 删除hash对象中的指定域field*/
    voidhdelCommand(client *c);
    /* 获取hash对象中所有键值对的总个数*/
    voidhlenCommand(client *c);
    /* 获取hash对象中指定域field对应的值的长度*/
    voidhstrlenCommand(client *c);
    /* 通过当前迭代器指向的位置获取键值对并回复，genericHgetallCommand的底层实现函数*/
    staticvoidaddHashIteratorCursorToReply(client *c, hashTypeIterator *hi,intwhat);
    /* 获取哈希对象中所有的键值对的泛型实现*/
    voidgenericHgetallCommand(client *c,intflags);
    /* 获取哈希对象中所有的域*/
    voidhkeysCommand(client *c);
    /* 获取哈希对象中所有的值*/
    voidhvalsCommand(client *c);
    /* 获取哈希对象中所有的域和值*/ 
    voidhgetallCommand(client *c);
    /* 判断哈希对象中是否存在该域field*/
    voidhexistsCommand(client *c);
    /* 客户端扫描操作 */
    voidhscanCommand(client *c);
    

## Hash小结 

Hash是Redis的一个重要数据类型，其提供了HSET，HGET，HINCRBY，HLEN等丰富的操作命令。每一个hash键中都包含了多个键值对数据，键值对数据的长度较小或者键值对个数较少时采用OBJ_ENCODING_ZIPLIST编码，即用ziplist结构存储；当数据量超过阈值，或者数据长度超过阈值时，将采用OBJ_ENCODING_HT编码，即用字典结构来存放键值对。

整个Hash类型的源码剖析就到此，各位读者如果有疑惑的话，可以在下方留言，期待结交更多学习Redis的同学或前辈，一起交流学习。

[1]: http://zcheng.ren/2016/12/23/TheAnnotatedRedisSourcet-hash/?utm_source=tuicool&utm_medium=referral
