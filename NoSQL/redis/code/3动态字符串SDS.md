# Redis源码剖析--动态字符串SDS

 时间 2016-12-02 19:12:52  ZeeCoder

_原文_[http://zcheng.ren/2016/12/02/TheAnnotatedRedisSouceSDS/][1]



Redis没有使用C语言的字符串结构，而是自己设计了一个简单的动态字符串结构sds。它的特点是：可动态扩展内存、二进制安全和与传统的C语言字符串类型兼容。下面就从源码的角度来分析一下Redis中sds的实现。（sds的源码实现主要在sds.c和sds.h两个文件中）

## sds数据结构定义 

在sds.h文件中，我们可以找到sds的数据结构定义如下： 

    typedef char *sds;
    

看到这里可能大家都疑惑了，这不就是char*嘛？的确，Redis采用一整段连续的内存来存储sds结构，char*类型正好可以和传统的C语言字符串类型兼容。但是，sds和char*并不等同，sds是二进制安全的，它可以存储任意二进制数据，不能像C语言字符串那样以‘\0’来标识字符串结束，因此它必然存在一个长度字段，那么这个字段在哪呢？请看下面的代码： 

    /* Note: sdshdr5 is never used, we just access the flags byte directly.
     * However is here to document the layout of type 5 SDS strings. */
    struct __attribute__ ((__packed__)) sdshdr5 {
        unsigned char flags; /* 3 lsb of type, and 5 msb of string length */
        char buf[];
    };
    struct __attribute__ ((__packed__)) sdshdr8 {
        uint8_t len; /* used */
        uint8_t alloc; /* excluding the header and null terminator */
        unsigned char flags; /* 3 lsb of type, 5 unused bits */
        char buf[];
    };
    struct __attribute__ ((__packed__)) sdshdr16 {
        uint16_t len; /* used */
        uint16_t alloc; /* excluding the header and null terminator */
        unsigned char flags; /* 3 lsb of type, 5 unused bits */
        char buf[];
    };
    struct __attribute__ ((__packed__)) sdshdr32 {
        uint32_t len; /* used */
        uint32_t alloc; /* excluding the header and null terminator */
        unsigned char flags; /* 3 lsb of type, 5 unused bits */
        char buf[];
    };
    struct __attribute__ ((__packed__)) sdshdr64 {
        uint64_t len; /* used */
        uint64_t alloc; /* excluding the header and null terminator */
        unsigned char flags; /* 3 lsb of type, 5 unused bits */
        char buf[];
    };
    

sds结构一共有五种Header定义，其目的是为了满足不同长度的字符串可以使用不同大小的Header，从而节省内存。

Header部分主要包含以下几个部分：

* len：表示字符串真正的长度，不包含空终止字符
* alloc：表示字符串的最大容量，不包含Header和最后的空终止字符
* flags：表示header的类型 

    // 五种header类型，flags取值为0~4
    #defineSDS_TYPE_5 0
    #defineSDS_TYPE_8 1
    #defineSDS_TYPE_16 2
    #defineSDS_TYPE_32 3
    #defineSDS_TYPE_64 4

由于sds是采用一段连续的内存空间来存储动态字符串，那么，我们进一步来分析一下sds在内存中的布局。下图是字符串”redis”在内存的布局示例图，

![][4]

由于sds的header共有五种，要想得到sds的header属性，就必须先知道header的类型，flags字段存储了header的类型。假如我们定义了sds* s，那么获取flags字段仅仅需要将s向前移动一个字节，即unsigned char flags = s[-1]。

在这里解释一下 **attribute** (( **packed** ))的用意：加上此字段是为了让编译器以紧凑模式来分配内存。如果没有这个字段，编译器会按照struct中的字段进行内存对齐，这样的话就不能保证header和sds的数据部分紧紧的相邻了，也不能按照固定的偏移来获取flags字段。 

获取了header的类型之后，我们就可以依照每个类型header的定义来获取sds的长度，最大容量等属性了。Redis定义了如下几个宏定义来操作header

    #defineSDS_TYPE_MASK 7// 类型掩码
    #defineSDS_TYPE_BITS 3
    #defineSDS_HDR_VAR(T,s) struct sdshdr##T *sh = (void*)((s)-(sizeof(struct sdshdr##T)));// 获取header头指针
    #defineSDS_HDR(T,s) ((struct sdshdr##T *)((s)-(sizeof(struct sdshdr##T))))// 获取header头指针
    #defineSDS_TYPE_5_LEN(f) ((f)>>SDS_TYPE_BITS)// 获取sdshdr5的长度
    

这里需要注意宏定义中的##是将两个符号连接成一个，如sdshdr和8（T为8）合成sdshdr8

其中SDS_HDR是为了获取header的头指针，即s指针按照header的结构大小向前偏移sizeof(struct sdshdr##T)位，找到了header的头指针，就很容易获取len和alloc的大小了。

到这里，sds的数据结构定义就基本清楚了，下面来看看sds的一些基本操作函数。

## sds基本操作函数 

## sds创建函数 

Redis在创建sds时，会为其申请一段连续的内存空间，其中包含sds的header和数据部分buf[]。其创建函数如下： 

    sds sdsnewlen(constvoid*init,size_tinitlen){
        void *sh;
        sds s;
        char type = sdsReqType(initlen);
        // 空的字符串通常被创建成type 8，因为type 5已经不实用了。
        if (type == SDS_TYPE_5 && initlen == 0) type = SDS_TYPE_8;
        // 得到sds的header的大小
        int hdrlen = sdsHdrSize(type);
        unsigned char *fp; // flags字段的指针
        // s_malloc等同于zmalloc，+1代表字符串结束符
        sh = s_malloc(hdrlen+initlen+1);
        if (!init)
            memset(sh, 0, hdrlen+initlen+1);
        if (sh == NULL) return NULL;
        // s为数据部分的起始指针
        s = (char*)sh+hdrlen;
        fp = ((unsigned char*)s)-1; // 得到flags的指针
        // 根据字符串类型来设定header中的字段
        switch(type) {
            case SDS_TYPE_5: {
                *fp = type | (initlen << SDS_TYPE_BITS);
                break;
            }
            case SDS_TYPE_8: {
                SDS_HDR_VAR(8,s); 
                sh->len = initlen; // 设定字符串长度
                sh->alloc = initlen; // 设定字符串的最大容量
                *fp = type;
                break;
            }
            case SDS_TYPE_16: {
                SDS_HDR_VAR(16,s);
                sh->len = initlen;
                sh->alloc = initlen;
                *fp = type;
                break;
            }
            case SDS_TYPE_32: {
                SDS_HDR_VAR(32,s);
                sh->len = initlen;
                sh->alloc = initlen;
                *fp = type;
                break;
            }
            case SDS_TYPE_64: {
                SDS_HDR_VAR(64,s);
                sh->len = initlen;
                sh->alloc = initlen;
                *fp = type;
                break;
            }
        }
        if (initlen && init)
            memcpy(s, init, initlen); // 拷贝数据部分
        s[initlen] = '\0'; // 与C字符串兼容
        return s; // 返回创建的sds字符串指针
    }
    

## sds释放函数 

sds的释放采用zfree来释放内存。其实现代码如下： 

    voidsdsfree(sds s){
        if (s == NULL) return; 
        // 得到内存的真正其实位置，然后释放内存
        s_free((char*)s-sdsHdrSize(s[-1]));
    }
    

## sds动态调整函数 

sds最重要的性能就是动态调整，Redis提供了扩展sds容量的函数。 

    // 在原有的字符串中取得更大的空间，并返回扩展空间后的字符串
    sds sdsMakeRoomFor(sds s,size_taddlen){
        void *sh, *newsh;
        size_t avail = sdsavail(s); // 获取sds的剩余空间
        size_t len, newlen;
        char type, oldtype = s[-1] & SDS_TYPE_MASK;
        int hdrlen;
    
        // 如果剩余空间足够，则直接返回
        if (avail >= addlen) return s;
    
        len = sdslen(s);
        sh = (char*)s-sdsHdrSize(oldtype);
        newlen = (len+addlen);
        // sds规定：如果扩展后的字符串总长度小于1M则新字符串长度为扩展后的两倍
        // 如果大于1M，则新的总长度为扩展后的总长度加上1M
        // 这样做的目的是减少Redis内存分配的次数，同时尽量节省空间
        if (newlen < SDS_MAX_PREALLOC) // SDS_MAX_PREALLOC = 1024*1024
            newlen *= 2;
        else
            newlen += SDS_MAX_PREALLOC;
        // 根据sds的长度来调整类型
        type = sdsReqType(newlen);
    
        // 不使用SDS_TYPE_5，一律按SDS_TYPE_8处理
        if (type == SDS_TYPE_5) type = SDS_TYPE_8;
        // 获取新类型的头长度
        hdrlen = sdsHdrSize(type);
        if (oldtype==type) {
            // 如果与原类型相同，直接调用realloc函数扩充内存
            newsh = s_realloc(sh, hdrlen+newlen+1);
            if (newsh == NULL) return NULL;
            s = (char*)newsh+hdrlen;
        } else {
            // 如果类型调整了，header的大小就需要调整
            // 这时就需要移动buf[]部分，所以不能使用realloc
            newsh = s_malloc(hdrlen+newlen+1);
            if (newsh == NULL) return NULL;
            memcpy((char*)newsh+hdrlen, s, len+1);
            s_free(sh);
            s = (char*)newsh+hdrlen; // 更新s
            s[-1] = type; // 设定新的flags参数
            sdssetlen(s, len); // 更新len
        }
        sdssetalloc(s, newlen); // 更新sds的容量
        return s;
    }
    

另外，Redis还提供了回收sds空余空间的函数。 

    // 用来回收sds空余空间，压缩内存，函数调用后，s会无效
    // 实际上，就是重新分配一块内存，将原有数据拷贝到新内存上，并释放原有空间
    // 新内存的大小比原来小了alloc-len大小
    sds sdsRemoveFreeSpace(sds s){
        void *sh, *newsh;
        char type, oldtype = s[-1] & SDS_TYPE_MASK;
        int hdrlen;
        size_t len = sdslen(s); // 获取字符串的实际大小
        sh = (char*)s-sdsHdrSize(oldtype);
    
        type = sdsReqType(len);
        hdrlen = sdsHdrSize(type);
        if (oldtype==type) {
            newsh = s_realloc(sh, hdrlen+len+1); // 申请的内存大小为hdrlen+len，原有的空余空间不算
            if (newsh == NULL) return NULL;
            s = (char*)newsh+hdrlen;
        } else {
            newsh = s_malloc(hdrlen+len+1); // 如上
            if (newsh == NULL) return NULL;
            memcpy((char*)newsh+hdrlen, s, len+1);
            s_free(sh);
            s = (char*)newsh+hdrlen;
            s[-1] = type;
            sdssetlen(s, len);
        }
        sdssetalloc(s, len);
        return s;
    }
    

## sds连接操作函数 

sds提供了字符串的连接函数，用来连接两个字符串 

    sds sdscatlen(sds s,constvoid*t,size_tlen){
        size_t curlen = sdslen(s); // 获取当前字符串的长度
    
        s = sdsMakeRoomFor(s,len); // 扩展空间
        if (s == NULL) return NULL; 
        memcpy(s+curlen, t, len); // 连接新字符串
        sdssetlen(s, curlen+len); // 设定连接后字符串长度
        s[curlen+len] = '\0'; 
        return s;
    }
    

## sds其他操作函数 

sds还提供了一系列的操作函数，这里就不列出源码，只说明其用途。 

    sds sdsempty(void); // 清空sds
    sds sdsdup(constsds s); // 复制字符串
    sds sdsgrowzero(sds s,size_tlen); // 扩展字符串到指定长度
    sds sdscpylen(sds s,constchar*t,size_tlen); // 字符串的复制
    sds sdscpy(sds s,constchar*t); // 字符串的复制
    sds sdscatfmt(sds s,charconst*fmt, ...);   //字符串格式化输出
    sds sdstrim(sds s,constchar*cset);       //字符串缩减
    voidsdsrange(sds s,intstart,intend);   //字符串截取函数
    voidsdsupdatelen(sds s);   //更新字符串最新的长度
    voidsdsclear(sds s);   //字符串清空操作
    voidsdstolower(sds s);    //sds字符转小写表示
    voidsdstoupper(sds s);    //sds字符统一转大写
    sds sdsjoin(char**argv,intargc,char*sep);   //以分隔符连接字符串子数组构成新的字符串
    

## sds小结 

sds是Redis中最基本的数据结构，使用一整段连续的内存来存储sds头信息和数据信息。其中，字符串的header包括了sds的字符串长度，字符串的最大容量以及sds的类型这三大信息。这样做的好处有很多，能让很多操作的复杂度降低，比如获取sds中字符串长度的操作，只需要O(1)即可，比strlen的O(N)好很多。

另外，sds还提供了很多操作函数，使其在拥有原生字符串的特性外，还能动态扩展内存和符合二进制安全等。

[1]: http://zcheng.ren/2016/12/02/TheAnnotatedRedisSouceSDS/?utm_source=tuicool&utm_medium=referral
[4]: http://img2.tuicool.com/6Fzuyq2.png!web