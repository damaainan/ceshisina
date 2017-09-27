# Redis源码剖析--字符串t_string

Dec 16, 2016 | [Redis][0] | 212 Hits

文章目录

1. [1. 字符串概述][1]
1. [2. 字符串结构][2]
1. [3. 字符串命令][3]
1. [4. 例：SET命令实现流程][4]
1. [5. 字符串小结][5]

前面一直在分析Redis的底层数据结构，Redis利用这些底层结构设计了它面向用户可见的五种数据结构，字符串、哈希，链表，集合和有序集合，然后用redisObject对这五种结构进行了封装。从这篇博客开始，带你一点点分析五种数据类型常见命令对应的源码实现，慢慢地解开Redis的面纱。

# 字符串概述

字符串是Redis中最为常见的数据存储类型，其底层实现是[简单动态字符串sds][6]，因此，该字符串类型是二进制安全的，这就意味着它可以接受任何格式的数据。另外，Redis规定，字符串类型最多可以容纳的数据长度为512M。Redis提供了下列函数，来检测字符串键的大小。
   
```c
static int checkStringLength(client *c, long long size) {

    // 超出了512M，就直接报错

    if (size > 512*1024*1024) {

        addReplyError(c,"string exceeds maximum allowed size (512MB)");

        return C_ERR;

    }

    return C_OK;

}
```
# 字符串结构

在[RedisObject][7]中提到，Redis的底层编码类型有三种：OBJ_ENCODING_RAW，OBJ_ENCODING_INT和OBJ_ENCODING_EMBSTR，分别对应的底层数据结构为sds，int，sds。字符串的数据结构如下：
```c
typedef struct redisObject {

    unsigned type:4;  // 为OBJ_STRING

    unsigned encoding:4;  // 编码类型OBJ_ENCODING_RAW，OBJ_ENCODING_INT或OBJ_ENCODING_EMBSTR

    unsigned lru:LRU_BITS; // LRU_BITS为24位，最近一次的访问时间

    int refcount;

    void *ptr;

} robj;
```
其中，有必要讲解一下OBJ_ENCODING_EMBSTR编码，其实这就是一个小的技巧，我们在创建RedisObject和其存放的数据时，通常是分开创建，然后将ptr指向对应的数据，这样就有两次申请内存的过程。

而embstr的做法是，首先计算RedisObject和数据占用的字节数，然后只用一次申请内存，数据直接存放在RedisObject后面，如下：

```
|              stringObject              | data |

                   ↓                   →→→→→↑

                   ↓                  ↑

| type | encoding | lru | refcount | ptr | data |
```
当然，embstr只适合常见长度较小的字符串时才显得效率高。如果长度过长，申请大内存段比较费力。因此，Redis规定了小于规定字节才采用embstr编码。

```c
    /* 当长度小于44字节时，采用embstr编码 */
    
    #define OBJ_ENCODING_EMBSTR_SIZE_LIMIT 44
```
# 字符串命令

Redis为string提供了一系列的命令，用来操作和管理字符串，主要包括以下几个命令。

命令 | 命令描述 
-|-
SET key value [ex 秒数][px 毫秒数][nx/xx] |设置指定key的值 
GET key |获取指定key的值 
APPEND key value |将value追加到指定key的值末尾 
INCRBY key increment |将指定key的值加上增量increment 
DECRBY key decrement |将指定key的值减去增量decrement 
STRLEN key |返回指定key的值长度 
SETRANGE key offset value |将value覆写到指定key的值上，从offset位开始 
GETRANGE key start end |获取指定key中字符串的子串[start,end] 
MSET key value [key value …] |一次设定多个key的值 
MGET key1 [key2..] |一次获取多个key的值 

上述命令均为常用的字符串命令，其实现在t_string.c文件中，我们进而来查看一下它们的实现源码。
   
```c
void setCommand(client *c); // SET命令，设定键值对
void setnxCommand(client *c); // SETNX命令，key不存在时才设置值
void setexCommand(client *c); // SETEX命令，key存在时才设置值，到期时间为秒
void psetexCommand(redisClient *c) // PSETEX命令，key存在时才设置值，到期时间为毫秒
void setrangeCommand(client *c); // SETRANGE命令，范围性的设置值
void msetCommand(client *c); // MSET命令，一次设定对个键值对
void msetnxCommand(client *c); // MSETNX命令，key不存在时才设置值
void getCommand(client *c); // GET命令，获取key对应的value
void mgetCommand(client *c); // MGET命令，获取多个key对应的value
void getrangeCommand(client *c); // GETRANGE命令，范围性的获取值
void getsetCommand(client *c); // 获取指定的键，如果存在则修改其值；反之不进行操作
void incrCommand(client *c); // 值递增1操作
void decrCommand(client *c); // 值递减1操作
void incrbyCommand(client *c); // 值增加操作
void decrbyCommand(client *c); // 值减少操作
void appendCommand(redisClient *c) // 追加key对应的值 
void strlenCommand(redisClient *c) // 获取key对应值得长度
```
接下来，我们以SET命令为例，来理解以下Redis处理字符串命令的过程。

# 例：SET命令实现流程

set命令用于设置指定的值，其具体命令格式如下：

    set key value [ex 秒数] [px 毫秒数] [nx/xx]

其中，各个选项的含义如下：

* ex 设置指定的到期时间，单位为秒
* px 设置指定的到期时间，单位为毫秒
* nx 只有在key不存在的时候，才设置key的值
* xx 只有key存在时，才对key进行设置操作

例如，我们在Redis的客户端中输入：

    127.0.0.1:6379> set zee 100 ex 1000 nx
    
    OK
    
    // 代表设定一组键值对[zee,100]，其中，到期时间为1000秒，如果zee不存在则创建key并设定值

SET 命令的源码由setcommod函数实现，调用set命令需要传入一个client的指针，client类型里面包含了很多Redis对于交互命令的处理参数，我们没必要去管一些目前还用不上的参数，先来看看set命令需要用到的参数。
   
```c
typedef struct client {
    redisDb *db;            // 当前数据库
    robj **argv;            // 命令参数
    // ....
} client;
```
很显然，db指向一个我们当前需要操作的数据库，argv指向待传入的命令参数。当我们执行set zee 100 ex 1000 nx命令时，argv中就包含六个RedisObject结构，其对应如下：
```c
argv[0] -- set
argv[1] -- zee
argv[3] -- 100
argv[4] -- ex
argv[5] -- 1000
argv[6] -- nx
```
我们规定了到期时间为1000秒，且只有在zee键不存在的时候才设定该键的值。Redis为SET命令的操作设定了下列三个宏定义，用来标记SET的操作类型。
   
```c
// 关于set命令的操作有三种宏定义

#define OBJ_SET_NO_FLAGS 0    // 没有设定参数

#define OBJ_SET_NX (1<<0)     // 只有键不存在时才设定其值

#define OBJ_SET_XX (1<<1)      // 只有键存在时才设定其值

#define OBJ_SET_EX (1<<2)       // ex属性，到期时间单位为秒

#define OBJ_SET_PX (1<<3)       // px属性，到期时间单位为毫秒
```
有了上述的理解之后，我们可以进入setCommand函数了。
  
```c
/* set命令实现函数 */
void setCommand(client *c) {
    int j;
    robj *expire = NULL;
    int unit = UNIT_SECONDS;
    // 用于标记ex/px和nx/xx命令参数
    int flags = OBJ_SET_NO_FLAGS;
    // 从命令串的第四个参数开始，查看其是否设定了ex/px和nx/xx
    for (j = 3; j < c->argc; j++) {
        char *a = c->argv[j]->ptr;
        robj *next = (j == c->argc-1) ? NULL : c->argv[j+1];
        if ((a[0] == 'n' || a[0] == 'N') &&
            (a[1] == 'x' || a[1] == 'X') && a[2] == '\0' &&
            !(flags & OBJ_SET_XX)) // 标记
        {
            flags |= OBJ_SET_NX;
        } else if ((a[0] == 'x' || a[0] == 'X') &&
                   (a[1] == 'x' || a[1] == 'X') && a[2] == '\0' &&
                   !(flags & OBJ_SET_NX))
        {
            flags |= OBJ_SET_XX;
        } else if ((a[0] == 'e' || a[0] == 'E') &&
                   (a[1] == 'x' || a[1] == 'X') && a[2] == '\0' &&
                   !(flags & OBJ_SET_PX) && next)
        {
            flags |= OBJ_SET_EX;
            unit = UNIT_SECONDS;
            expire = next;
            j++;
        } else if ((a[0] == 'p' || a[0] == 'P') &&
                   (a[1] == 'x' || a[1] == 'X') && a[2] == '\0' &&
                   !(flags & OBJ_SET_EX) && next)
        {
            flags |= OBJ_SET_PX;
            unit = UNIT_MILLISECONDS;
            expire = next;
            j++;
        } else {
            // 如果不是上述参数，则需要报错，命令错误
            addReply(c,shared.syntaxerr);
            return;
        }
    }
    // 判断value是否可以编码成整数，如果能则编码；反之不做处理
    c->argv[2] = tryObjectEncoding(c->argv[2]);
    // 调用底层函数进行键值对设定
    setGenericCommand(c,flags,c->argv[1],c->argv[2],expire,unit,NULL,NULL);
}
/* 真正的set底层实现函数 */
void setGenericCommand(client *c, int flags, robj *key, robj *val, robj *expire, int unit, robj *ok_reply, robj *abort_reply) {
    long long milliseconds = 0; /* initialized to avoid any harmness warning */
    // 设定过期时间
    if (expire) {
        if (getLongLongFromObjectOrReply(c, expire, &milliseconds, NULL) != C_OK)
            return;
        if (milliseconds <= 0) {
            addReplyErrorFormat(c,"invalid expire time in %s",c->cmd->name);
            return;
        }
        if (unit == UNIT_SECONDS) milliseconds *= 1000;
    }
    // 判断key是否存在，并根据nx和xx命令来决定是否set命令是否执行
    if ((flags & OBJ_SET_NX && lookupKeyWrite(c->db,key) != NULL) ||
        (flags & OBJ_SET_XX && lookupKeyWrite(c->db,key) == NULL))
    {
        addReply(c, abort_reply ? abort_reply : shared.nullbulk);
        return;
    }
    // 将键值对关联到数据库
    setKey(c->db,key,val);
    // 设定该数据库为脏
    server.dirty++;
    // 设定过期时间
    if (expire) setExpire(c->db,key,mstime()+milliseconds);
    // 发送事件通知
    notifyKeyspaceEvent(NOTIFY_STRING,"set",key,c->db->id);
    // 发送定期事件通知
    if (expire) notifyKeyspaceEvent(NOTIFY_GENERIC,
        "expire",key,c->db->id);
    // 向客户端发送命令处理结果
    addReply(c, ok_reply ? ok_reply : shared.ok);
}
```
从SET命令中，衍生除了SETNX，SETEX，PSETEX等命令，其底层均是调用setGenericCommand来实现。
   
```c
// key不存在时，才设定值，flag为REDIS_SET_NX；如果key存在则不做处理
// 命令形式为：setnx key value
void setnxCommand(client *c) {
    c->argv[2] = tryObjectEncoding(c->argv[2]);
    setGenericCommand(c,OBJ_SET_NX,c->argv[1],c->argv[2],NULL,0,shared.cone,shared.czero);
}
// key存在时才设置值，flag为REDIS_SET_NO_FLAGS，过期时间单位为秒，如果key不存在则不做处理
// 命令形式为：setex key seconds value (seconds为键过期时间，单位秒)
void setexCommand(client *c) {
    // 这里为argv[3]，因为value存放在此
    c->argv[3] = tryObjectEncoding(c->argv[3]);
    setGenericCommand(c,OBJ_SET_NO_FLAGS,c->argv[1],c->argv[3],c->argv[2],UNIT_SECONDS,NULL,NULL);
}
// key存在时才设置值，flag为REDIS_SET_NO_FLAGS，过期时间单位为毫秒
// PSETEX key milliseconds value(milliseconds为键过期时间，单位毫秒)
void psetexCommand(client *c) {
    c->argv[3] = tryObjectEncoding(c->argv[3]);
    setGenericCommand(c,OBJ_SET_NO_FLAGS,c->argv[1],c->argv[3],c->argv[2],UNIT_MILLISECONDS,NULL,NULL);
}
```
# 字符串小结
在字符串的处理命令中，涉及到很多数据库和事件的相关处理函数，现阶段我们可以忽略。本博客只是列举了set命令的源码处理过程，这些命令的处理大多是涉及到命令解析的过程，比较繁琐，但是很好理解。有兴趣的可以在深入到每个命令的源码中，一窥实现步骤。源码面前，了无秘密。

[0]: /categories/Redis/
[1]: #字符串概述
[2]: #字符串结构
[3]: #字符串命令
[4]: #例：SET命令实现流程
[5]: #字符串小结
[6]: http://zcheng.ren/2016/12/02/TheAnnotatedRedisSourceSDS/
[7]: http://zcheng.ren/2016/12/14/TheAnnotatedRedisSourceObject/