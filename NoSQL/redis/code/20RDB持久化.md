# Redis源码剖析--RDB持久化

 时间 2016-12-30 22:30:01  ZeeCoder

_原文_[http://zcheng.ren/2016/12/30/TheAnnotatedRedisSourceRdb/][1]



众所周知，Reids是一个高效的内存数据库，所有的数据都存放在内存中。这种模式的缺点就是一旦服务器关闭后会立刻丢失所有存储的数据，Redis当然要避免这种情况的发生，于是其提供了两种持久化机制：RDB和AOF。它们的功能都是将内存中存放的数据保存到磁盘文件上，等到服务器下次开启时能重载数据，以免数据丢失。今天，我们先来剖析一下RDB持久化机制。

## RDB概述 

看过我系列博客的应该知道我分析源码的方式是，先学会使用它，再来一步一步的深入它。我先演示一个小例子来感受一下RDB持久化。

    /* 开启一个redis-cli，执行添加数据操作如下 */
    127.0.0.1:6379> flushdb
    OK
    127.0.0.1:6379> set hello world
    OK
    127.0.0.1:6379> SAVE
    OK
    

如下，我开启了一个Redis客户端，先清空了里面的数据，然后依次添加了一个键值对到数据库，最后通过SAVE文件将数据库中的数据保存到rdb文件中，实现数据的持久化。运行完SAVE命令之后，服务器会显示数据已经存放在磁盘文件上。

    78415:M 30 Dec 10:58:11.445 * DB saved on disk
    

接着，我们来看看这个文件中存放着什么数据，保存到磁盘的文件名为 dump.rdb ，利用od命令就能查看里面的数据。 

    ~ od -c dump.rdb
    0000000    R   E   D   I   S   0   0   0   7 372  \t   r   e   d   i   s
    0000020    -   v   e   r 005   3   .   2   .   3 372  \n   r   e   d   i
    0000040    s   -   b   i   t   s 300   @ 372 005   c   t   i   m   e 164
    0000060    t 317   e   X 372  \b   u   s   e   d   -   m   e   m 302    
    0000100    _ 017  \0 376  \0 373 001  \0  \0 005   h   e   l   l   o 005
    0000120    w   o   r   l   d 377   l 320   E   e  \b   E  \a   @
    

看二进制文档实在有点费力，不过大致可以看到里面有如下信息：

* RDB文件标识和版本号：REDIS0007
* Redis版本：redis-ver 3.2.3
* Redis系统位数（32位或64位）：redis-bits
* 系统时间：ctime
* 内存使用量：used-mem
* 一组键值对：hello-word

其他看不出来的信息，我们待会去源码中一一剖析出来，在源码面前，这些都不是秘密！

## RDB文件结构 

上面打印出来的二进制文件只能看出部分信息，Redis的RDB文件中具体包含了哪些信息，我们需要从源码中挖掘出来，不然在理解上可能会出问题，下面我画了一个表格来表示RDB的文件结构。

    ————————————————————————————————————————————
    | 文件标识 | 辅助信息 | 数据库 | 结束符 | 校验和 | 
    ————————————————————————————————————————————
    

## 文件标识 

Redis在每一个RDB文件的首部都写入了如下字符，用来标识这是一个Redis的RDB文件。

    —————————————————————
    | REDIS  | 文件版本号 |
    —————————————————————
    

例如：示例中的文件以『REDIS0007』开头，0007代表RDB文件的版本号。

## 辅助信息 

Redis在新的RDB文件版本上加入了辅助信息，其格式如下：

    ————————————————————————————————————————————
    | redis版本 | 系统位数 | 系统时间 | 已使用的内存 |
    ————————————————————————————————————————————
    

例如：在上述的示例中，这些信息对应着：

* 372 表示是一个辅助信息
* \t redis-ver 表示后面的数据代表Redis的版本号
* 005 3.2.3 表示当前Redis版本为3.2.3，005代表长度为5
* \n redis-bits 表示后面的数据为当前Redis服务器的位数
* 300 @ 乱码，应该是代表系统为64位
* 005 ctime 表示后面跟着的数据为系统当前时间
* 164 t 317 e X 当前系统时间
* \b used-mem 表示后面的数据为已使用的内存数
* 302 _ 017 \0 已使用的内存数

示例中每一个信息的都是以372开头，表示这是一个辅助信息。其中，该类信息的宏定义如下：

    // 辅助信息标识量为250+信息长度，底层二进制就为372
    #defineRDB_OPCODE_AUX 250
    

## 数据库 

Redis服务器默认有16个数据库，每个数据的信息是一次写入rdb文件中，每个数据库的信息的存放格式如下：

    ————————————————————————————————————————————————————
    | select | dbnum | db_size | expires_size | 键值数据 |
    ————————————————————————————————————————————————————
    

其中，select标识当前进行切换数据库操作，后面的dnum表示当前存放的是第dbnum号数据库的数据。示例中的二进制码对应的信息如下：

* 376 \0 表示切换到第0号数据库；
* 373 \1 表示当前数据库中只有一个数据；
* \0 表示当前没有过期键

其中，切换，数据库大小，过期键个数这些都属于一个操作信息，Redis用宏定义来表示这些数据。

    // 初始化数据库字典的大小
    #defineRDB_OPCODE_RESIZEDB 251
    // 选择数据库
    #defineRDB_OPCODE_SELECTDB 254
    

### 键值数据 

键值数据的存放格式如下：

    ———————————————————————————————————————————————————————
    | 过期键标识 | 时间戳 | 键值对类型 | 键长度 | 键 | 值长度 | 值 |
    ———————————————————————————————————————————————————————
    

其中，过期键标识和时间戳是可选项，如果该键设置了过期时间就需要在数据前面加上这些信息。过期键标识由以下两个宏定义给出：

    /* 以ms为单位的过期时间 */
    #defineRDB_OPCODE_EXPIRETIME_MS 252
    /* 以s为单位的过期时间 */
    #defineRDB_OPCODE_EXPIRETIME 253
    

键值对类型为Redis的五个数据类型，其宏定义如下：

    #defineRDB_TYPE_STRING 0// 字符串标识
    #defineRDB_TYPE_LIST 1// 链表标识
    #defineRDB_TYPE_SET 2// 集合标识
    #defineRDB_TYPE_ZSET 3// 有序集合标识
    #defineRDB_TYPE_HASH 4// 哈希标识
    

在示例中，各二进制位代表的含义如下：

* \0 标识后面是一个字符串键
* 005 hello 长度为5的字符串hello
* 005 world 长度为5的字符串world

## 结束符 

每个RDB文件都以EOF结束符结尾。上述示例中对应EOF的是：

* 377 标识EOF标志位

其宏定义如下：

    #defineRDB_OPCODE_EOF 255
    

## 校验和 

Redis在每一个RDB文件的末尾加上了采用CRC校验的校验和，二进制中最后一串乱码标识的就是校验和，如果我们用 od -cx dump.rdb 就可以更直观的看到检验和为多少。 

    ~ od -cx dump.rdb 
    0000000    R   E   D   I   S   0   0   0   7 372  \t   r   e   d   i   s
                 4552    4944    3053    3030    fa37    7209    6465    7369
    0000020    -   v   e   r 005   3   .   2   .   3 372  \n   r   e   d   i
                 762d    7265    3305    322e    332e    0afa    6572    6964
    0000040    s   -   b   i   t   s 300   @ 372 005   c   t   i   m   e 164
                 2d73    6962    7374    40c0    05fa    7463    6d69    c265
    0000060    t 317   e   X 372  \b   u   s   e   d   -   m   e   m 302    
                 cf74    5865    08fa    7375    6465    6d2d    6d65    20c2
    0000100    _ 017  \0 376  \0 373 001  \0  \0 005   h   e   l   l   o 005
                 0f5f    fe00    fb00    0001    0500    6568    6c6c    056f
    0000120    w   o   r   l   d 377   l 320   E   e  \b   E  \a   @        
                 6f77    6c72    ff64    d06c    6545    4508    4007
    

最后的 0x 4007 4508 6545 d06c 就代表的是该RDB文件的校验和（校验和以小端模式存储）。 

## RDB编码格式 

对于Redis的数据存放结构，上述分析已经很明了了。接下来，我们要具体到Redis对于每种数据结构的编码方式。

## 长度编码 

在之前的压缩列表和整数集合中就多次见识到Redis为了节省内存做的各种措施，由于C语言中对于指针指向的内存无法计算长度，所以必须将该段内存的大小标识出来。在Redis中，有很多长度信息需要保存，如字符串的长度，链表的长度，数据库的大小等，针对不同大小的长度数据，Redis会使用不同的编码格式来节省内存。我们先来看看这些宏定义。

    #defineRDB_6BITLEN 0// 6位长度，最大表示的长度为64
    #defineRDB_14BITLEN 1// 14位长度
    #defineRDB_32BITLEN 2// 32位长度
    #defineRDB_ENCVAL 3// 特殊编码
    

其具体的编码格式如下：

    00|000000 // 6位长度值
    01|000000 00000000 // 14位长度值
    10|000000 [32位]  // 后续32位表示一个32位的长度值，所以其需要5个字节来表示
    11|000000 表示一个特殊编码
    

该编码方式对应的源代码函数如下，各位可以对着代码理解以下：

    // 长度编码
    intrdbSaveLen(rio *rdb,uint32_tlen){
        unsigned char buf[2];
        size_t nwritten;
    
        if (len < (1<<6)) {
            /* Save a 6 bit len */
            buf[0] = (len&0xFF)|(RDB_6BITLEN<<6);
            if (rdbWriteRaw(rdb,buf,1) == -1) return -1;
            nwritten = 1;
        } else if (len < (1<<14)) {
            /* Save a 14 bit len */
            buf[0] = ((len>>8)&0xFF)|(RDB_14BITLEN<<6);
            buf[1] = len&0xFF;
            if (rdbWriteRaw(rdb,buf,2) == -1) return -1;
            nwritten = 2;
        } else {
            /* Save a 32 bit len */
            buf[0] = (RDB_32BITLEN<<6);
            if (rdbWriteRaw(rdb,buf,1) == -1) return -1;
            len = htonl(len);
            if (rdbWriteRaw(rdb,&len,4) == -1) return -1;
            nwritten = 1+4;
        }
        return nwritten;
    }
    

## 特殊编码 

特殊编码主要是将一些用字符串表示的小整数转换成整数编码，以节省内存，比如”12”，”-1”等。Redis对于这些小整数类型的字符串有以下几种不同的编码格式，用宏定义指出。

    #defineRDB_ENC_INT8 0/* 8 bit signed integer */
    #defineRDB_ENC_INT16 1/* 16 bit signed integer */
    #defineRDB_ENC_INT32 2/* 32 bit signed integer */
    #defineRDB_ENC_LZF 3/* string compressed with FASTLZ */
    

因此，其编码对应的内存布局如下：

    11|0000|00 00000000  // 后面八字节表示该整数
    11|0000|01 00000000 00000000 // 后面16字节表示该整数
    11|0000|10 [32 bits]  // 后面32位表示该整数
    11|0000|11 // 表示LZF压缩后的数据
    

所以，存储一个能用八字节表示字符串有符整数需要2位；存储一个能用16字节表示的有符整数需要3字节；存储一个能用32字节表示的有符整数需要5个字节。

特殊编码的实现由rdbTryIntegerEncoding和rdbEncodeInteger函数完成。

    /* 判断能不能编码成小有符整数 */
    intrdbTryIntegerEncoding(char*s,size_tlen,unsignedchar*enc){
        long long value;
        char *endptr, buf[32];
      
        // 检查该值能不能编码成一个数字
        value = strtoll(s, &endptr, 10);
        // 转换失败，返回
        if (endptr[0] != '\0') return 0;
        // 将数字转换成字符串
        ll2string(buf,32,value);
    
        // 如果转换后的数字不能还原成原来的字符，则表示转换失败，返回0
        if (strlen(buf) != len || memcmp(buf,s,len)) return 0;
        // 可以转换成整数，进行编码操作
        return rdbEncodeInteger(value,enc);
    }
    /* 小整数编码底层实现 */
    intrdbEncodeInteger(longlongvalue,unsignedchar*enc){
        if (value >= -(1<<7) && value <= (1<<7)-1) {
            enc[0] = (RDB_ENCVAL<<6)|RDB_ENC_INT8;
            enc[1] = value&0xFF;
            return 2;
        } else if (value >= -(1<<15) && value <= (1<<15)-1) {
            enc[0] = (RDB_ENCVAL<<6)|RDB_ENC_INT16;
            enc[1] = value&0xFF;
            enc[2] = (value>>8)&0xFF;
            return 3;
        } else if (value >= -((long long)1<<31) && value <= ((long long)1<<31)-1) {
            enc[0] = (RDB_ENCVAL<<6)|RDB_ENC_INT32;
            enc[1] = value&0xFF;
            enc[2] = (value>>8)&0xFF;
            enc[3] = (value>>16)&0xFF;
            enc[4] = (value>>24)&0xFF;
            return 5;
        } else {
            return 0;
        }
    }
    

## LZF编码 

当Redis开启了字符串压缩功能且字符串长度大于20bytes时，会采用LZF编码对其进行压缩，开启字符串压缩功能的变量为：

    rdbcompression yes  // redis.conf中设定的，默认为开启状态
    

当字符串写入RDB文件时，判断上述条件成立与否，进而选择编码格式，其源码片段如下：

    // 在rdbSaveRawString函数内，判断是否开启字符串压缩功能，且字符串长度大于20bytes
    if (server.rdb_compression && len > 20) {
      n = rdbSaveLzfStringObject(rdb,s,len);
      if (n == -1) return -1;
      if (n > 0) return n;
      /* Return value of 0 means data can't be compressed, save the old way */
    }
    

真正执行编码操作的函数是rdbSaveLzfStringObject，其按照上述的编码格式对数据进行压缩。

    /* 将字符串进行lzf压缩 */
    ssize_t rdbSaveLzfStringObject(rio *rdb, unsigned char *s, size_t len) {
        size_t comprlen, outlen;
        void *out;
    
        // 至少要求长度为4个字节以上，不然不值得用压缩算法
        if (len <= 4) return 0;
        outlen = len-4;
        // 内存不足，返回0
        if ((out = zmalloc(outlen+1)) == NULL) return 0;
        // LZF算法对其进行压缩，这里不讨论具体的算法
        comprlen = lzf_compress(s, len, out, outlen);
        // 压缩失败，返回0
        if (comprlen == 0) {
            zfree(out);
            return 0;
        }
        // 执行写入操作
        ssize_t nwritten = rdbSaveLzfBlob(rdb, out, comprlen, len);
        zfree(out);
        return nwritten;
    }
    /* 写入LZF压缩的字符串 */
    ssize_t rdbSaveLzfBlob(rio *rdb, void *data, size_t compress_len,
                           size_t original_len) {
        unsigned char byte;
        ssize_t n, nwritten = 0;
    
        // 数据已经被压缩了，写入RDB文件中
        byte = (RDB_ENCVAL<<6)|RDB_ENC_LZF;
        // 写入类型，表示这是一个LZF压缩的数据
        if ((n = rdbWriteRaw(rdb,&byte,1)) == -1) goto writeerr;
        nwritten += n;
        // 写入压缩后的长度
        if ((n = rdbSaveLen(rdb,compress_len)) == -1) goto writeerr;
        nwritten += n;
        // 写入压缩前的长度
        if ((n = rdbSaveLen(rdb,original_len)) == -1) goto writeerr;
        nwritten += n;
        // 写入压缩的数据
        if ((n = rdbWriteRaw(rdb,data,compress_len)) == -1) goto writeerr;
        nwritten += n;
    
        return nwritten;
    
    writeerr:
        return -1;
    }
    

从源码中可以看出，经过LZF算法压缩的字符串在内存中的布局如下：

    ——————————————————————————————————————————————————————
    | LZF标识(11000011) | 压缩后的长度 | 原长度 | 压缩后的数据 |
    ——————————————————————————————————————————————————————
    

## String对象编码 

前面在 [Redis源码剖析–字符串t_string][4] 一文中，有介绍到string对象的底层编码有三种，分别是 OBJ_ENCODING_INT 、 OBJ_ENCODING_RAW 和 OBJ_ENCODING_EMBSTR ，这三种编码的不同之处各位可以跳转复习一下。在写入RDB文件时，会判断String对象的编码类型，从而选择以何种编码方式写入到RDB文件中。字符串是按照如下三种格式存放在RDB文件中的。 

    /* 按照字符串编码的形式 */
    ————————————————
    |  len  | data |
    ————————————————
    /* 按照INT编码的形式 */
    ——————————————————
    | Encoding | int |
    ——————————————————
    /* 按照LZF压缩后的形式 */
    ————————————————————————————————————————————
    | LZF标识 | 压缩后的长度 | 原长度 | 压缩后的数据 |
    ————————————————————————————————————————————
    

其实现源码如下：

    /* String对象编码 */
    intrdbSaveStringObject(rio *rdb, robj *obj){
        /* Avoid to decode the object, then encode it again, if the
         * object is already integer encoded. */
        if (obj->encoding == OBJ_ENCODING_INT) {
            // 如果是整数编码，则试图以字符串的形式写入
            return rdbSaveLongLongAsStringObject(rdb,(long)obj->ptr);
        } else {
            // 反之，直接以字符串的形式写入
            serverAssertWithInfo(NULL,obj,sdsEncodedObject(obj));
            return rdbSaveRawString(rdb,obj->ptr,sdslen(obj->ptr));
        }
    }
    /* 将整型数以字符串的形式写入 */
    ssize_t rdbSaveLongLongAsStringObject(rio *rdb, long long value) {
        unsigned char buf[32];
        ssize_t n, nwritten = 0;
        // 判断该整数是否能以整型数编码，此函数为上述为小整数准备的编码方式
        int enclen = rdbEncodeInteger(value,buf);
        // 如果可以，直接写入
        if (enclen > 0) {
            return rdbWriteRaw(rdb,buf,enclen);
        } else {
            // 非小整数，以字符串的形式写入
            enclen = ll2string((char*)buf,32,value);
            serverAssert(enclen < 32);
            // 写入长度
            if ((n = rdbSaveLen(rdb,enclen)) == -1) return -1;
            nwritten += n;
            // 写入数据
            if ((n = rdbWriteRaw(rdb,buf,enclen)) == -1) return -1;
            nwritten += n;
        }
        return nwritten;
    }
    /* 将字符串对象以[len][data]的形式写入RDB文件 */
    ssize_t rdbSaveRawString(rio *rdb, unsigned char *s, size_t len) {
        int enclen;
        ssize_t n, nwritten = 0;
    
        // 试图编码为小整数
        if (len <= 11) {
            unsigned char buf[5];
            if ((enclen = rdbTryIntegerEncoding((char*)s,len,buf)) > 0) {
                if (rdbWriteRaw(rdb,buf,enclen) == -1) return -1;
                return enclen;
            }
        }
    
        // 检查能不能用LZF压缩后存储
        if (server.rdb_compression && len > 20) {
            n = rdbSaveLzfStringObject(rdb,s,len);
            if (n == -1) return -1;
            if (n > 0) return n;
        }
        // 按照[len][data]的形式存放
        if ((n = rdbSaveLen(rdb,len)) == -1) return -1;
        nwritten += n;
        if (len > 0) {
            if (rdbWriteRaw(rdb,s,len) == -1) return -1;
            nwritten += len;
        }
        return nwritten;
    }
    

## List对象编码 

在 [Redis源码剖析–列表t_list][5] 一文中，解释到List的底层编码只有quicklist。其存放格式如下： 

    ————————————————————————————————————————————————————————————————————————
    | listLength | len1| data1 | len2 | CompressLength| OriginLength | data2 |
    ————————————————————————————————————————————————————————————————————————
    

其中，第一个节点直接按照字符串的形式存放；第二个节点采用LZF压缩后存放，其源码如下：

    /* 存储对象 */
    ssize_t rdbSaveObject(rio *rdb, robj *o) {
        ssize_t n = 0, nwritten = 0;
    
        if (o->type == OBJ_STRING) {
            // 存放字符串对象
            if ((n = rdbSaveStringObject(rdb,o)) == -1) return -1;
            nwritten += n;
        } else if (o->type == OBJ_LIST) {
            // 存放链表对象
            if (o->encoding == OBJ_ENCODING_QUICKLIST) {
                quicklist *ql = o->ptr;
                quicklistNode *node = ql->head;
                // 存放长度
                if ((n = rdbSaveLen(rdb,ql->len)) == -1) return -1;
                nwritten += n;
    
                do {
                    // 判断节点是否能压缩
                    if (quicklistNodeIsCompressed(node)) {
                        // 如能，采用yzf压缩后写入
                        void *data;
                        size_t compress_len = quicklistGetLzf(node, &data);
                        if ((n = rdbSaveLzfBlob(rdb,data,compress_len,node->sz)) == -1) return -1;
                        nwritten += n;
                    } else {
                        // 如果不能则直接写入
                        if ((n = rdbSaveRawString(rdb,node->zl,node->sz)) == -1) return -1;
                        nwritten += n;
                    }
                } while ((node = node->next));
            } else {
                serverPanic("Unknown list encoding");
            }
        }
        // ....
    }
    

## Set对象编码 

在 [Redis源码剖析–集合t_set][6] 一文中讲到Set的实现原理和数据存储形式，set的底层采用字典或者整数集合的编码形式。Set对象在RDB文件中的存储形式为： 

    —————————————————————————————————————————
    | setSize | elem1 | elem2 | ... | elemN |
    —————————————————————————————————————————
    /* 集合存储示例 */
    ————————————————————————————————————————————
    | 3 | 3 | "zee" | 5 | "coder" | 5 | "cheng" |
    ————————————————————————————————————————————
    

集合中的每一个值都按照其值选取不同的编码存放，如字符串就按字符串，LZF压缩就压缩存储，小整数就按照小整数存储…..

    /* 存储对象 */
    ssize_t rdbSaveObject(rio *rdb, robj *o) {
        ssize_t n = 0, nwritten = 0;
        // ...截取部分代码
        if (o->type == OBJ_SET) {
            // 存放集合对象
            if (o->encoding == OBJ_ENCODING_HT) {
                // 字典编码的时候，存放长度和键值
                dict *set = o->ptr;
                dictIterator *di = dictGetIterator(set);
                dictEntry *de;
    
                if ((n = rdbSaveLen(rdb,dictSize(set))) == -1) return -1;
                nwritten += n;
    
                while((de = dictNext(di)) != NULL) {
                    // 遍历每一个键，并存放在RDB中
                    robj *eleobj = dictGetKey(de);
                    if ((n = rdbSaveStringObject(rdb,eleobj)) == -1) return -1;
                    nwritten += n;
                }
                dictReleaseIterator(di);
            } else if (o->encoding == OBJ_ENCODING_INTSET) {
                // 直接将INTSET转换成字符串对象存放
                size_t l = intsetBlobLen((intset*)o->ptr);
    
                if ((n = rdbSaveRawString(rdb,o->ptr,l)) == -1) return -1;
                nwritten += n;
            } else {
                serverPanic("Unknown set encoding");
            }
        }
    }
    

## Zset对象编码 

在 [Redis源码剖析–有序集合t_zset][7] 一文中提到，zset采用zskiplist或者ziplist编码，不过这两种编码不影响它在RDB文件中的存放格式。 

    ———————————————————————————————————————————————————————————————————————
    | zset_length | elem1 | score1 | elem2 | score2 | ... | elem3 | score3 |
    ———————————————————————————————————————————————————————————————————————
    

其源码实现如下，没什么特别的，不懂的看注释吧。

    /* 存储对象 */
    ssize_t rdbSaveObject(rio *rdb, robj *o) {
        ssize_t n = 0, nwritten = 0;
        // ...截取部分代码
        if (o->type == OBJ_ZSET) {
            // 保存有序集合对象
            if (o->encoding == OBJ_ENCODING_ZIPLIST) {
                // 采用ziplist编码的情况，直接按照字符串形式存储
                size_t l = ziplistBlobLen((unsigned char*)o->ptr);
    
                if ((n = rdbSaveRawString(rdb,o->ptr,l)) == -1) return -1;
                nwritten += n;
            } else if (o->encoding == OBJ_ENCODING_SKIPLIST) {
                // 采用skiplist的情况，遍历所有节点，一次存放
                zset *zs = o->ptr;
                dictIterator *di = dictGetIterator(zs->dict);
                dictEntry *de;
    
                if ((n = rdbSaveLen(rdb,dictSize(zs->dict))) == -1) return -1;
                nwritten += n;
                // zset如果是skiplist编码的话，内部有一个dict结构，存放所有的对象和分值
                while((de = dictNext(di)) != NULL) {
                    robj *eleobj = dictGetKey(de);
                    double *score = dictGetVal(de);
    
                    if ((n = rdbSaveStringObject(rdb,eleobj)) == -1) return -1;
                    nwritten += n;
                    if ((n = rdbSaveDoubleValue(rdb,*score)) == -1) return -1;
                    nwritten += n;
                }
                dictReleaseIterator(di);
            } else {
                serverPanic("Unknown sorted set encoding");
            }
        }
    }
    

## Hash对象编码 

在 [Redis源码剖析–哈希t_hash][8] 一文中，hash底层的数据结构有两种，ziplist和字典，同样在写入RDB文件的时候，需要判断编码类型，然后采用不同的形式存放。 

    —————————————————————————————————————————————————
    | hashSize | key1 | value1| .... | key2 | value2 |
    —————————————————————————————————————————————————
    

其源码实现如下：

    /* 存储对象 */
    ssize_t rdbSaveObject(rio *rdb, robj *o) {
        ssize_t n = 0, nwritten = 0;
        // ...截取部分代码
        if (o->type == OBJ_HASH) {
            // 存放hash对象
            if (o->encoding == OBJ_ENCODING_ZIPLIST) {
                // 采用ziplist编码的情况，直接按照字符串形式存储
                size_t l = ziplistBlobLen((unsigned char*)o->ptr);
    
                if ((n = rdbSaveRawString(rdb,o->ptr,l)) == -1) return -1;
                nwritten += n;
    
            } else if (o->encoding == OBJ_ENCODING_HT) {
                // 字典采用字典方式，遍历，存放
                dictIterator *di = dictGetIterator(o->ptr);
                dictEntry *de;
    
                if ((n = rdbSaveLen(rdb,dictSize((dict*)o->ptr))) == -1) return -1;
                nwritten += n;
    
                while((de = dictNext(di)) != NULL) {
                    robj *key = dictGetKey(de);
                    robj *val = dictGetVal(de);
    
                    if ((n = rdbSaveStringObject(rdb,key)) == -1) return -1;
                    nwritten += n;
                    if ((n = rdbSaveStringObject(rdb,val)) == -1) return -1;
                    nwritten += n;
                }
                dictReleaseIterator(di);
    
            } else {
                serverPanic("Unknown hash encoding");
            }
    
        } 
    }
    

## RDB命令 

RDB有两种命令，一种是SAVE，另一种是BGSAVE。我们一起来看看他们的实现源码。

## SAVE命令 

按照Redis的命令定义，可以知道SAVE命令的底层代码实现是由saveCommand实现，于是去源码中找到了它。

    /* SAVE命令的底层实现代码 */
    voidsaveCommand(client *c){
        if (server.rdb_child_pid != -1) {
            // 检查BGSAVE命令正在执行(BGSAVE是开一个进程来指定存储命令)
            addReplyError(c,"Background save already in progress");
            return;
        }
        // 开始执行rdb持久化操作
        if (rdbSave(server.rdb_filename) == C_OK) {
            addReply(c,shared.ok);
        } else {
            addReply(c,shared.err);
        }
    }
    

上述代码中，真正进行写rdb文件的函数是rdbSave函数，于是我们进一步跟踪到了它。

    /* 在磁盘上保存rdb文件，如果出错返回C_ERR，反之C_OK */
    intrdbSave(char*filename){
        char tmpfile[256];
        char cwd[MAXPATHLEN]; // 当前工作目录
        FILE *fp;
        rio rdb;
        int error = 0;
        // 创建临时文件
        snprintf(tmpfile,256,"temp-%d.rdb", (int) getpid());
        fp = fopen(tmpfile,"w");
        if (!fp) {
            char *cwdp = getcwd(cwd,MAXPATHLEN);
            serverLog(LL_WARNING,
                "Failed opening the RDB file %s (in server root dir %s) "
                "for saving: %s",
                filename,
                cwdp ? cwdp : "unknown",
                strerror(errno));
            return C_ERR;
        }
        // 初始化I/0，便于后续写入文件
        rioInitWithFile(&rdb,fp);
        // 利用RIO来执行写入操作
        if (rdbSaveRio(&rdb,&error) == C_ERR) {
            errno = error;
            goto werr;
        }
    
        // 确保输出缓存中没有数据
        if (fflush(fp) == EOF) goto werr;
        if (fsync(fileno(fp)) == -1) goto werr;
        if (fclose(fp) == EOF) goto werr;
    
        // 使用RENAME，原子性的对临时文件进行改名，覆盖原来的RDB文件
        if (rename(tmpfile,filename) == -1) {
            char *cwdp = getcwd(cwd,MAXPATHLEN);
            serverLog(LL_WARNING,
                "Error moving temp DB file %s on the final "
                "destination %s (in server root dir %s): %s",
                tmpfile,
                filename,
                cwdp ? cwdp : "unknown",
                strerror(errno));
            unlink(tmpfile);
            return C_ERR;
        }
        // 写入完成，打印日志
        serverLog(LL_NOTICE,"DB saved on disk");
        // 清零脏数据
        server.dirty = 0;
        // 记录最后一次完成SAVE的时间
        server.lastsave = time(NULL);
        // 记录最后一次执行SAVE的状态
        server.lastbgsave_status = C_OK;
        return C_OK;
    
    werr:
        // 报错
        serverLog(LL_WARNING,"Write error saving DB on disk: %s", strerror(errno));
        // 关闭文件
        fclose(fp);
        // 删除文件
        unlink(tmpfile);
        // 返回错误
        return C_ERR;
    }
    

到这一步，还是没有看出来rdb的结构。不过可以知道在写RDB文件时，是先创建一个临时文件，向临时文件中写入数据，如果成功则改名，反之则删除。我们注意到调用了底层函数rdbSaveRio来执行的写操作。接着我们继续吧。

    /* 利用RIO进行写数据操作 */
    intrdbSaveRio(rio *rdb,int*error){
        dictIterator *di = NULL;
        dictEntry *de;
        char magic[10];
        int j;
        long long now = mstime();
        uint64_t cksum;
        // 设置校验和
        if (server.rdb_checksum)
            rdb->update_cksum = rioGenericUpdateChecksum;
        // 写入REDIS文件标识和版本号
        snprintf(magic,sizeof(magic),"REDIS%04d",RDB_VERSION);
        if (rdbWriteRaw(rdb,magic,9) == -1) goto werr;
        // 写入此时系统相关信息
        if (rdbSaveInfoAuxFields(rdb) == -1) goto werr;
        // 遍历所有数据库
        for (j = 0; j < server.dbnum; j++) {
            redisDb *db = server.db+j;
            dict *d = db->dict;
            if (dictSize(d) == 0) continue;
            di = dictGetSafeIterator(d);
            if (!di) return C_ERR;
    
            // 写入当前数据类型
            if (rdbSaveType(rdb,RDB_OPCODE_SELECTDB) == -1) goto werr;
            // 写入数据库编号
            if (rdbSaveLen(rdb,j) == -1) goto werr;
    
            // 获取数据库字典的大小和过期键字典的大小
            // 为了编码方便这些大小最大为UINT32_MAX，但是并不影响数据库和过期键的实际大小
            // 因为此大小只是在加载rdb数据的时候申请哈希表的初始大小
            uint32_t db_size, expires_size;
            db_size = (dictSize(db->dict) <= UINT32_MAX) ?
                                    dictSize(db->dict) :
                                    UINT32_MAX;
            expires_size = (dictSize(db->expires) <= UINT32_MAX) ?
                                    dictSize(db->expires) :
                                    UINT32_MAX;
            // 写入当前待写入数据的类型，此处为RDB_OPCODE_RESIZEDB
            if (rdbSaveType(rdb,RDB_OPCODE_RESIZEDB) == -1) goto werr;
            // 写入数据库大小
            if (rdbSaveLen(rdb,db_size) == -1) goto werr;
            // 写入过期键的个数
            if (rdbSaveLen(rdb,expires_size) == -1) goto werr;
    
            // 迭代当前数据库中的每一个节点，并将键值对写入rdb文件
            while((de = dictNext(di)) != NULL) {
                sds keystr = dictGetKey(de);
                robj key, *o = dictGetVal(de);
                long long expire;
    
                initStaticStringObject(key,keystr);
                expire = getExpire(db,&key);
                // 写入键值对数据
                if (rdbSaveKeyValuePair(rdb,&key,o,expire,now) == -1) goto werr;
            }
            dictReleaseIterator(di);
        }
        di = NULL; // 不释放，留下一次迭代用
    
        // 写入结束符
        if (rdbSaveType(rdb,RDB_OPCODE_EOF) == -1) goto werr;
    
        // 写入CRC64校验和
        cksum = rdb->cksum;
        memrev64ifbe(&cksum);
        if (rioWrite(rdb,&cksum,8) == 0) goto werr;
        return C_OK;
    
    werr:
        // 出错的处理代码
        if (error) *error = errno;
        if (di) dictReleaseIterator(di);
        return C_ERR;
    }
    

## BGSAVE命令 

BGSAVE命令是开一个进程，然后存储RDB文件在该进程中执行，属于后台存储。该存储方式需要注意一下几种情况。

* 如果后台正在进行RDB存储，则返回错误
* 如果后台正在进行AOF存储，则将rdb_bgsave_scheduled参数置1，等到系统函数 serverCron 定期执行的时候，检查参数，并执行BGSAVE命令

其源码如下：

    /* 后台SAVE命令实现 */
    voidbgsaveCommand(client *c){
        int schedule = 0;
    
        // schedule参数是为了避免服务器在执行AOF持久化的时候影响RDB持久化
        // 于是向系统添加一个日程计划，使得服务器在定期事件中检查该参数和AOF持久化结束没
        // 然后执行BGSAVE命令
        if (c->argc > 1) {
            if (c->argc == 2 && !strcasecmp(c->argv[1]->ptr,"schedule")) {
                schedule = 1;
            } else {
                addReply(c,shared.syntaxerr);
                return;
            }
        }
        // 后台正在运行BGSAVE，直接退出
        if (server.rdb_child_pid != -1) {
            addReplyError(c,"Background save already in progress");
        } else if (server.aof_child_pid != -1) {
            // AOF正在后台执行，增加schedule，提醒客户端增加schedule参数
            if (schedule) {
                server.rdb_bgsave_scheduled = 1;
                addReplyStatus(c,"Background saving scheduled");
            } else {
                addReplyError(c,
                    "An AOF log rewriting in progress: can't BGSAVE right now. "
                    "Use BGSAVE SCHEDULE in order to schedule a BGSAVE whenver "
                    "possible.");
            }
        } else if (rdbSaveBackground(server.rdb_filename) == C_OK) {
            // 执行BGSAVE命令
            addReplyStatus(c,"Background saving started");
        } else {
            addReply(c,shared.err);
        }
    }
    /* 真正执行BGSAVE的代码 */
    intrdbSaveBackground(char*filename){
        pid_t childpid;
        long long start;
        // 检查后台是否在执行AOF或RDB持久化操作
        if (server.aof_child_pid != -1 || server.rdb_child_pid != -1) return C_ERR;
        // 取出脏数据
        server.dirty_before_bgsave = server.dirty;
        server.lastbgsave_try = time(NULL);
    
        start = ustime();
        // fork出一个子进程
        if ((childpid = fork()) == 0) {
            int retval;
            // 子进程执行存储操作
            closeListeningSockets(0);
            redisSetProcTitle("redis-rdb-bgsave");
            // 进程中执行rdbsave函数
            retval = rdbSave(filename);
            if (retval == C_OK) {
                size_t private_dirty = zmalloc_get_private_dirty();
    
                if (private_dirty) {
                    serverLog(LL_NOTICE,
                        "RDB: %zu MB of memory used by copy-on-write",
                        private_dirty/(1024*1024));
                }
            }
            exitFromChild((retval == C_OK) ? 0 : 1);
        } else {
            // 父进程执行操作
            server.stat_fork_time = ustime()-start;
            server.stat_fork_rate = (double) zmalloc_used_memory() * 1000000 / server.stat_fork_time / (1024*1024*1024); /* GB per second. */
            latencyAddSampleIfNeeded("fork",server.stat_fork_time/1000);
            if (childpid == -1) {
                // 创建子进程失败
                server.lastbgsave_status = C_ERR;
                serverLog(LL_WARNING,"Can't save in background: fork: %s",
                    strerror(errno));
                return C_ERR;
            }
            // 通知客户端进程号
            serverLog(LL_NOTICE,"Background saving started by pid %d",childpid);
            // 保存rdb持久化开始时间
            server.rdb_save_time_start = time(NULL);
            // 保存子进程号
            server.rdb_child_pid = childpid;
            server.rdb_child_type = RDB_CHILD_TYPE_DISK;
            updateDictResizePolicy();
            return C_OK;
        }
        return C_OK; /* unreached */
    }
    

以上代码中需要注意的是，fork的特性，在子进程中childPid为0，在父进程中childPid为父进程的ID号，所以子进程在执行SAVE操作，父进程在检查操作是否执行成功并存储相关变量。

## 自动保存 

在Redis.conf文件中，可以配置服务器定期执行SAVE命令，该参数如下：

    save 900 1
    save 300 10
    save 60 10000
    

其含义依次如下：

* 服务器在900秒之内，对数据库至少进行了一次修改
* 服务器在300秒之内，对数据库至少进行了10次修改
* 服务器在60秒之内，对数据库至少进行了10000次修改

所以，服务器只要满足这三个条件之一，就会自动执行SAVE操作。那么这一功能是如何实现的呢？我们先来看一个数据结构，在server.h文件夹中。

    struct saveparam {
        time_t seconds;  // 保存配置文件中的秒数要求
        int changes;  // 保存配置文件中的修改次数要求
    }
    

另外，在redisServer结构体中有如下参数，用来记录上述要求。

    struct redisServer {
        // ...
        struct saveparam * saveparam ;
        // ...
    }
    

有了配置文件中的参数，那么服务器中对数据的修改次数和事件存放在哪呢？其实，之前的源码分析中都见到过，每次修改数据的时候，都需要将系统的脏数据个数加1，而且还要保存修改时间，没错就是它俩。

    struct redisServer {
        // ...
        long long dirty; // 服务器的脏数据，表示没有进行持久化的数据
        time_t lastsave; // 上一次执行保存的时间
        // ...
    }
    

了解了这些参数和结构体在哪之后，就可以通过判断自动保存的条件是否符合来执行BGSAVE命令了，其源码如下：

    /* serverCron函数中的代码片段 */
    // 判断后台没有执行rdb或者aof持久化操作
    if (server.rdb_child_pid != -1 || server.aof_child_pid != -1 ||
        ldbPendingChildren())
    {
      // ...
    } else {
      // 执行到此，说明后台没有进行rdb或aof操作
      // 对每一条时间和修改次数要求进行检查
      for (j = 0; j < server.saveparamslen; j++) {
        struct saveparam *sp = server.saveparams+j;
    
        // 如脏数据个数大于规定的修改次数且距离上一次保存的时间也大于规定的时间
        // 且上一次试图后台SAVE的间隔超过规定时间或者上一次BGSAVE命令执行成功
        // 才执行BGSAVE操作
        if (server.dirty >= sp->changes &&
            server.unixtime-server.lastsave > sp->seconds &&
            (server.unixtime-server.lastbgsave_try >
             CONFIG_BGSAVE_RETRY_DELAY ||
             server.lastbgsave_status == C_OK))
        {
          serverLog(LL_NOTICE,"%d changes in %d seconds. Saving...",
                    sp->changes, (int)sp->seconds);
          rdbSaveBackground(server.rdb_filename);
          break;
        }
      }
      // ...
    }
    

## RDB小结 

本文简要分析了RDB结构中数据的存放格式，而后分析了SAVE和BGSAVE命令的执行步骤和源码，最后分析了自动保存功能的实现原理，基本上整个RDB持久化操作的过程以及了然于心了。我们现在可以放心的保证，Redis的RDB全过程已GET！由于本人也是边学边写博客，其中难免有错误的地方，希望大家在阅读的时候能及时指出！期待和大家一起学习交流Redis！

欢迎转载本篇博客，不过请注明博客原地址： [http://zcheng.ren/2016/12/29/TheAnnotatedRedisSourcePubsub][9]


[1]: http://zcheng.ren/2016/12/30/TheAnnotatedRedisSourceRdb/?utm_source=tuicool&utm_medium=referral

[4]: http://zcheng.ren/2016/12/16/TheAnnotatedRedisSourcetstring/
[5]: http://zcheng.ren/2016/12/19/TheAnnotatedRedisSourcet-list/
[6]: http://zcheng.ren/2016/12/22/TheAnnotatedRedisSourcet-set/
[7]: http://zcheng.ren/2016/12/24/TheAnnotatedRedisSourcet-zset/
[8]: http://zcheng.ren/2016/12/23/TheAnnotatedRedisSourcet-hash/
[9]: http://zcheng.ren/2016/12/29/TheAnnotatedRedisSourcePubsub