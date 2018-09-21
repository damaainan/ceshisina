## Redis的数据类型一探究竟

来源：[https://helei112g.github.io/2018/07/22/Redis的数据类型一探究竟/](https://helei112g.github.io/2018/07/22/Redis的数据类型一探究竟/)

时间 2018-07-26 10:57:27

 
第二篇来了，Redis常用5种类型大揭秘。长文预警！
 
接上篇 [为什么要用Redis][7] ，今天来聊聊具体的Redis数据类型与命令。本篇是深入理解Redis的一个重要基础，请坐稳，前方 长文预警。
 
本系列内容基于：redis-3.2.12
 
文中不会介绍所有命令，主要是工作中经常遇到的。
 
平时我们看的大部分资料，都是简单粗暴的告诉我们这个命令干嘛，那个命令需要几个参数。这种方式只会知其然不知其所以然，本文从命令的时间复杂度到用途，再到对应类型在Redis低层采用何种结构保存数据，希望让大家认识的更深刻，使用时心里更有底。

 
* 这里在阅读中请注意：虽然很多命令的时间复杂度都是O(n)，但要注意其n所代表的具体含义。
  
* 文中会用到 OBJECT ENCODING xxx 来检查Redis的内部编码，它其实是读取的 redisObject 结构体中 encoding 所代表的值。redisObject 对不同类型的数据提供了统一的表现形式。

 
## String类型 
 
应该讲这是Redis中使用的最广泛的数据类型。该类型中的一些命令使用场景非常广泛。比如：

 
* 缓存，这是使用非常多的地方； 
* 计数器/限速器技术； 
* 共享Session服务器也是基于该数据类型 
 
 
![][0]
 
注：表格中仅仅说明了String中的12个命令，使用场景也仅列举了部分。
 
我们时常被人说教 MSET/MGET 这类命令少用，因为他们的时间复杂度是O(n)，但其实这里注意，n表示的是本次设置或读取的key个数，所以如果你批量读取的key并不是很多，每个key的内容也不是很大，那么使用批量操作命令反而能够节省网络请求、传输的时间。
 
## 内部结构 
 
String类型的数据最终是如何在Redis中保存的呢？如果要细究的话，得先从`SDS`这个结构说起，不过今天先按下不表这源码部分的细节，只谈其内部保存的数据结构。最终我们设置的字符串都会以三种形式中的一种被存储下来。

 
* Int，8个字节的长整型，最大值是：0x7fffffffffffffffL 
* Embstr，小于等于44个字节的字符串 
* Raw 
 
 
结合代码来看看Redis对这三种数据结构是如何决策的。当我们在客户端使用命令`SET test hello,redis`时，客户端会把命令保存到一个buf中，然后按照收到的命令先后顺序依次执行。这其中有一个函数是：`processMultibulkBuffer()`，它内部调用了`createStringObject()`函数：

```c
#define OBJ_ENCODING_EMBSTR_SIZE_LIMIT 44
robj *createStringObject(const char *ptr, size_t len) {
	  // 检查保存的字符串长度，选择对应类型
    if (len <= OBJ_ENCODING_EMBSTR_SIZE_LIMIT)
        return createEmbeddedStringObject(ptr,len);
    else
        return createRawStringObject(ptr,len);
}


```

 
不懂C语言不要紧，这里就是检查我们输入的字符串`hello,redis`长度是否超过了 44 ，如果超过了用类型`raw`，没有则选用`embstr`。实验看看：

```
127.0.0.1:6379> SET test 12345678901234567890123456789012345678901234 // len=44
OK
127.0.0.1:6379> OBJECT encoding test
"embstr"
127.0.0.1:6379> SET test 123456789012345678901234567890123456789012345 // len=45
OK
127.0.0.1:6379> OBJECT encoding test
"raw"


```

 
可以看到，一旦超过44，底层类型就变成了：`raw`。等等，上面我们不是还提到有一个`int`类型吗？从函数里边完全看不到它的踪迹啊？不急，当我们输入的这条命令真的要开始执行时，也就是调用函数`setCommand()`时，会触发一个`tryObjectEncoding()`函数，这个函数的作用是试图对输入的字符串进行压缩，继续看看代码：

```c
robj *tryObjectEncoding(robj *o) {
    ... ...
    len = sdslen(s);
	  // 长度小于等于20，并且能够转成长整形
    if(len <= 20 && string2l(s,len,&value)) {
        o->encoding = OBJ_ENCODING_INT;
    }
    ... ...
}


```

 
这个函数被我大幅缩水了，但是简单我们能够看到它判断长度是否小于等于20，并且尝试转化成整型，看看例子。
 
9223372036854775807是8位字节可表示的最大整数，它的16进制形式是： **`0x7fffffffffffffffL`** 

```
127.0.0.1:6379> SET test 9223372036854775807
OK
127.0.0.1:6379> OBJECT encoding test
"int"
127.0.0.1:6379> SET test 9223372036854775808 // 比上面大1
OK
127.0.0.1:6379> OBJECT encoding test
"embstr"


```

 
至此，关于String的类型选择流程完毕了。这对我们的参考价值是，我们在使用String类型保存数据时，要考虑到底层对应不同的类型，不同的类型在Redis内部会执行不同的流程，其所对应的执行效率、内存消耗都是不同的。
 
## Hash类型 
 
我们经常用它来保存一个结构化的数据，比如与一个用户相关的缓存信息。如果使用普通的String类型，需要对字符串进行序列化与反序列化，无疑增加额外开销，并且每次读取都只能全部读取出来。

 
* 缓存结构化的数据，如：文章信息，可灵活修改其某一个字段，如阅读量。 
 
 
![][1]
 
Hash类型保存的结构话数据，非常像MySQL中的一条记录，我们可以方便修改某一个字段，但是它更具灵活性，每个记录能够含有不同的字段。
 
## 内部结构 
 
在内部Hash类型数据可能存在两种类型的数据结构：

 
* ZipList，更加节省空间，限制：key与field长度不超过64，key中field的个数不超过512个 
* HashTable 
 
 
对于Hash，Redis 首先默认给它设置使用`ZipList`数据结构，后续根据条件进行判断是否需要改变。

```c


void hsetCommand(client *c) {
    int update;
    robj *o;

    if ((o = hashTypeLookupWriteOrCreate(c,c->argv[1])) == NULL) return;
    hashTypeTryConversion(o,c->argv,2,3);// 根据长度决策
    ... ...
    update = hashTypeSet(o,c->argv[2],c->argv[3]);// 根据元素个数决策
    addReply(c, update ? shared.czero : shared.cone);
    ... ...
}


```

 `hashTypeLookupWriteOrCreate()`内部会调用`createHashObject()`创建Hash对象。

```c
robj *createHashObject(void) {
    unsigned char *zl = ziplistNew();
    robj *o = createObject(OBJ_HASH, zl);
    o->encoding = OBJ_ENCODING_ZIPLIST;// 设置编码 ziplist
    return o;
}


```

 `hashTypeTryConversion()`函数内部根据是否超过`hash_max_ziplist_value`限制的长度（64），来决定低层的数据结构。

```c
void hashTypeTryConversion(robj *o, robj **argv, int start, int end) {
    int i;

    if (o->encoding != OBJ_ENCODING_ZIPLIST) return;

    for (i = start; i <= end; i++) {
		  // 检查 field 与 value 长度是否超长
        if (sdsEncodedObject(argv[i]) &&
            sdslen(argv[i]->ptr) > server.hash_max_ziplist_value)
        {
            hashTypeConvert(o, OBJ_ENCODING_HT);
            break;
        }
    }
}


```

 
然后在函数`hashTypeSet()`中检查field个数是否超过了`hash_max_ziplist_entries`的限制（512个）。

```c
int hashTypeSet(robj *o, robj *field, robj *value) {
    int update = 0;

    if (o->encoding == OBJ_ENCODING_ZIPLIST) {
		  ... ...
        // 检查field个数是否超过512
        if (hashTypeLength(o) > server.hash_max_ziplist_entries)
            hashTypeConvert(o, OBJ_ENCODING_HT);
    } else if (o->encoding == OBJ_ENCODING_HT) {
		  ... ...
    }
    ... ...
    return update;
}


```

 
来验证一下上面的逻辑：

```
127.0.0.1:6379> HSET test name qweqweqwkejkksdjfslfldsjfkldjslkfqweqweqwkejkksdjfslfldsjfkldjsl
(integer) 1
127.0.0.1:6379> HSTRLEN test name
(integer) 64
127.0.0.1:6379> OBJECT encoding test
"ziplist"
127.0.0.1:6379> HSET test name qweqweqwkejkksdjfslfldsjfkldjslkfqweqweqwkejkksdjfslfldsjfkldjslq
(integer) 0
127.0.0.1:6379> HSTRLEN test name
(integer) 65
127.0.0.1:6379> OBJECT encoding test
"hashtable"


```

 
关于key设置超过64，以及field个数超过512的限制情况，大家可自行测试。
 
## List类型 
 
List类型的用途也是非常广泛，主要概括下常用场景:

 
* 消息队列：LPUSH + BRPOP（阻塞特征） 
* 缓存：用户记录各种记录，最大特点是可支持分页 
* 栈：LPUSH + LPOP 
* 队列：LPUSH + RPOP 
* 有限队列：LPUSH + LTRIM，可以维持队列中数据的数量 
 
 
![][2]
 
## 内部结构 
 
List 的数据类型在低层实现有以下几种：

 
* QuickList：它是以ZipList为节点的LinkedList 
* ZipList（省内存）， **`在3.2.12版本中发现有地方使用`**   
* LinkedList， **`在3.2.12版本中发现有地方使用`**   
 
 
网络上有些文章说`LinkedList`在`Redis 4.0`之后的版本没有再被使用，实际上我发现`Redis 3.2.12`版本中也没有再使用该结构（不直接做为数据存储结构），包括`ZipList`在`3.2.12`版本中都没有再被直接用来存储数据了。
 
我们做个实验来验证下，我们设置一个List中有 **`1000`**  个元素，每个元素value长度都超过 **`64`**  个字符。

```
127.0.0.1:6379> LLEN test
(integer) 1000
127.0.0.1:6379> OBJECT encoding test
"quicklist"
127.0.0.1:6379> LINDEX test 0
"qweqweqwkejkksdjfslfldsjfkldjslkfqweqweqwkejkksdjfslfldsjfkldjslq" // 65个字符


```

 
无论我们是改变列表元素的个数以及元素值的长度，其结构都是`QuickList`。还不信的话，我们来看看代码：

```c
void pushGenericCommand(client *c, int where) {
    int j, waiting = 0, pushed = 0;
    robj *lobj = lookupKeyWrite(c->db,c->argv[1]);
	  ... ...
    for (j = 2; j < c->argc; j++) {
        c->argv[j] = tryObjectEncoding(c->argv[j]);
        if (!lobj) {
			  // 创建 quick list
            lobj = createQuicklistObject();
            quicklistSetOptions(lobj->ptr, server.list_max_ziplist_size,
                                server.list_compress_depth);
            dbAdd(c->db,c->argv[1],lobj);
        }
        listTypePush(lobj,c->argv[j],where);
        pushed++;
    }
    ... ...
}


```

 
初始话时，调用`createQuicklistObject()`设置其低层数据结构是：`quick list`。后续流程中没有地方再对该结构进行转化。
 
## Set类型 
 
Set 类型的重要特性之一是可以去重、无序。它集合的性质在社交上可以有广泛的使用。

 
* 共同关注 
* 共同喜好 
* 数据去重 
 
 
![][3]
 
## 内部结构 
 
Set低层实现采用了两种数据结构：

 
* IntSet，集合成员都是整数（不能超过最大整数）并且集合成员个数少于512时使用。 
* HashTable 
 
 
该命令的代码如下，其中重要的两个关于决定类型的调用是：`setTypeCreate()`和`setTypeAdd()`。

```c
void saddCommand(client *c) {
    robj *set;
    ... ...
    if (set == NULL) {
		  // 初始化
        set = setTypeCreate(c->argv[2]);
    } else {
        ... ...
    }

    for (j = 2; j < c->argc; j++) {
		  // 内部会检查元素个数是否扩充到需要改变低层结构
        if (setTypeAdd(set,c->argv[j])) added++;
    }
    ... ...
}


```

 
来看下 Set 结构对象的初始创建代码：

```c
robj *setTypeCreate(robj *value) {
    if (isObjectRepresentableAsLongLong(value,NULL) == C_OK)
        return createIntsetObject(); // 使用IntSet
    return createSetObject(); // 使用HashTable
}


```

 `isObjectRepresentableAsLongLong()`内部判断其整数范围，如果是整数且没有超过最大整数就会使用`IntSet`来保存。否则使用`HashTable`。接着会检查元素的个数。

```c
int setTypeAdd(robj *subject, robj *value) {
    long long llval;
    if (subject->encoding == OBJ_ENCODING_HT) {
		 ... ...
    } else if (subject->encoding == OBJ_ENCODING_INTSET) {
        if (isObjectRepresentableAsLongLong(value,&llval) == C_OK) {
            uint8_t success = 0;
            subject->ptr = intsetAdd(subject->ptr,llval,&success);
            if (success) {
                /* Convert to regular set when the intset contains
                 * too many entries. */
                if (intsetLen(subject->ptr) > server.set_max_intset_entries)
                    setTypeConvert(subject,OBJ_ENCODING_HT);
                return 1;
            }
        } else {
            /* Failed to get integer from object, convert to regular set. */
            setTypeConvert(subject,OBJ_ENCODING_HT);
			  ... ...
            return 1;
        }
    }
	  ... ...
    return 0;
}


```

 
看看例子，这里以最大整数临界值为例：

```
127.0.0.1:6379> SADD test 9223372036854775807
(integer) 1
127.0.0.1:6379> OBJECT encoding test
"intset"
127.0.0.1:6379> SADD test 9223372036854775808
(integer) 1
127.0.0.1:6379> OBJECT encoding test
"hashtable"


```

 
关于集合个数的测试，请自行完成观察。
 
## SortSet类型 
 
现在的应用，都有一些排行榜之类的功能，比如投资网站显示投资金额排行，购物网站显示消费排行等。SortSet非常适合做这件事。常用来解决以下问题：

 
* 各类排行榜 
* 设置执行任务权重，后台脚本根据其排序顺序执行相关操作 
* 范围查找，查找某个值在集合的哪个范围 
 
 
![][4]
 
## 内部结构 
 
虽然有序集合也是集合，但是低层的数据结构却与Set不一样，它也有两种数据结构，分别是：

 
* ZipList，当有序集合的元素个少于等于128或 member 的长度小于等于64的时候使用该结构 
* SkipList 
 
 
这个转变成过程如下：

```c
void zaddGenericCommand(client *c, int flags) {
if (zobj == NULL) {
        if (xx) goto reply_to_client; /* No key + XX option: nothing to do. */
        if (server.zset_max_ziplist_entries == 0 ||
            server.zset_max_ziplist_value < sdslen(c->argv[scoreidx+1]->ptr))
        {
            zobj = createZsetObject();// skip list
        } else {
            zobj = createZsetZiplistObject();// zip list
        }
        dbAdd(c->db,key,zobj);
    } else {
        ... ...
    }
	  ... ...
    if (zobj->encoding == OBJ_ENCODING_ZIPLIST) {
        if (zzlLength(zobj->ptr) > server.zset_max_ziplist_entries)
            zsetConvert(zobj,OBJ_ENCODING_SKIPLIST);// 根据个数转化编码
        if (sdslen(ele->ptr) > server.zset_max_ziplist_value)
            zsetConvert(zobj,OBJ_ENCODING_SKIPLIST);// 根据长度转化编码
    }
}


```

 
这里以member长度超过64举例：

```
127.0.0.1:6379> ZADD test 77 qwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwer // member长度是 64
(integer) 1
127.0.0.1:6379> OBJECT encoding test
"ziplist"
127.0.0.1:6379> ZADD test 77 qwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwertyuiopqwerq // member长度是65
(integer) 1
127.0.0.1:6379> OBJECT encoding test
"skiplist"


```

 
当我们member 超过64位长度时，低层的数据结构由`ZipList`转变成了`SkipList`。剩下的元素个数的测试，动动手试试看。
 
## 全局常用命令 
 
![][5]
 
对于全局命令，不管对应的key是什么类型的数据，都是可以进行操作的。其中需要注意 **`KEYS`**  这个命令，不能用于线上，因为Redis单线程机制，如果内存中数据太多，会操作严重的阻塞，导致整个Redis服务都无法响应。
 
## 总结 

 
* Redis每种类型的命令时间复杂度不同，有的跟对应元素的个数有关系；有的跟请求个数有关系； 
* 合理安排元素相关个数以及长度，争取Redis底层采用最简单的数据结构； 
* 关注时间复杂度，了解自己的Redis内部元素情况，避免阻塞； 
* 越简单的数据，越能获得更好的性能； 
* Redis每种数据类型低层都对应多种数据结构，修改与扩展对上层无感知。 
 
 
第一篇讲了为什么要用Redis，本文又讲了绝大部分命令吧，以及Redis源码中对它们的一些实现，后续开始关注具体实践中的一些操作。希望对大家有帮助， **`期待任何形式的批评与鼓励`**  。
 
公众号：`dayuTalk`


[7]: https://mp.weixin.qq.com/s/cKIaRPGKywrxfs_s7wiaQg
[0]: ./img/AzQf2qv.png
[1]: ./img/nEvmeuy.png
[2]: ./img/NVZ3YjQ.png
[3]: ./img/VRbM3y7.png
[4]: ./img/qUVjMzi.png
[5]: ./img/URviamr.png
