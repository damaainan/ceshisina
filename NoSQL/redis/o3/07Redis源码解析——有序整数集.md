# Redis源码解析——有序整数集

 时间 2016-12-13 00:11:03  方亮的专栏

原文[http://blog.csdn.net/breaksoftware/article/details/53576492][1]


## 大小尾（Big Endian/Little Endian）

第一次接触这个概念还是在大学时上“计算机原理”时，当时只是简单的知道它的特点，但是丝毫没有深究它们产生的原因和特点。这次借着解析Redis源码，重新学习了一下大小尾。

如果进行过逆向的同学一般都会知道ESP指针的作用，它指向当前函数栈Frame的栈顶。相应的，EBP寄存器指向当前函数栈Frame的栈底。在逆向的汇编代码中，我们会发现一般使用EBP结合偏移的方法去表示栈上的一个变量，如：

    mov    eax, dword ptr  [ebp+0ch]
    mov    ebx, dword ptr  [ebp+08h]

但是为什么没有使用ESP表示变量呢？这是因为ESP指针在当前调用堆栈中也不是稳定的，比如我们进行函数调用，会将一些信息进行入栈处理，这个时候栈顶指针就会减小以扩大有效栈的区域。为什么是栈顶值减小以扩大栈区域呢？这是因为栈结构的特点：栈底地址比栈顶地址大。

![][3]

我们再看下大小尾数据在栈空间的布局。 

大尾结构将数据的高位放在地址低处，而小尾结构将数据的高位放在地址的高位。于是我们看下0x00123456在不同结构中的布局

![][4]

如果我们直接在内存中查看，则是这样展现的

![][5]

可以发现大尾的结构比较正常，它展现的数据的形式和人类的认知方式是相同的——高位在前，低位在后。但是小尾的展现形式则比较反人类——需要倒着看。但是存在即合理，那么小尾结构那么反人类为什么它还存在呢？

这就要从CPU的历史讲起来。历史上关于选择大尾还是小尾有着很多争论，各派都有自己的理论依据。我在网上找到一篇关于这段历史的文档—— [《ON HOLY WARS AND A PLEA FOR PEACE》][6] ，鉴于文档稍长，我没有仔细阅读，有兴趣的读者可以去了解下。我在这儿简单讲一下我个人的认识：大尾结构便于人类理解，小尾结构便于计算机计算。 

大尾结构便于人类理解，这点我们在上面的图中已经发现了。现行的网络传输协议也是采用大尾结构，很明显作为协议，其重点是协议的可被理解性，而非其参与计算的能力。

小尾结构便于计算机计算怎么理解呢？举个例子，比如我们要对上面数据（假设为a）执行加法操作，操作数是0x12efcdab。那么计算机取到a的地址是0xFF000000，然后用操作数中的0xab与0xFF000000地址的数据进行add操作，操作结果还保存在0xFF000000中；如果有进位，则参与到操作数0xcd与0xFF000001地址的数据相加中。CPU只要对操作数和被操作数的地址向后移动取值相加就可以了。我们再想像下大尾数据的处理方法，如果也是从地址低位开始计算——即是数据高位，则可能产生回溯的问题——数据低位计算有进位则要求改之前计算的值——甚至还要改之前的之前计算的值。如果从地址高位开始计算——即数据低位，则有一次通过a地址（地址低位）跳转到地址高位的过程。可能有人会说你为什么不拿减法例子呢？人类的减法操作都是从高位向低位进行的。但是计算机没有减法器——它是通过加法操作进行减法运算的。

大小尾虽然是一个非常古老的问题，但是我们在进行数据跨网络交互时要考虑，因为网络字节序和本机字节序可能不一样。跨语言传输数据时也要考虑，像Java的数据就是大尾结构的。

Redis在源码的endianconv.c提供了一系列小尾结构向大尾结构转换的方法。我们看一下64位数据的处理函数：

    /* Toggle the 64 bit unsigned integer pointed by *p from little endian to
     * big endian */
    void memrev64(void *p) {
        unsigned char *x = p, t;
    
        t = x[0];
        x[0] = x[7];
        x[7] = t;
        t = x[1];
        x[1] = x[6];
        x[6] = t;
        t = x[2];
        x[2] = x[5];
        x[5] = t;
        t = x[3];
        x[3] = x[4];
        x[4] = t;
    }
    
    uint64_t intrev64(uint64_t v) {
        memrev64(&v);
        return v;
    }

Redis使用大尾也印证了我之前的观点，因为Redis是重要的功能是存储和网络交互，而非进行数值计算。

接下来我们看看Redis的有序整数集的保存结构。

## 基础结构

    typedef struct intset {
        uint32_t encoding;
        uint32_t length;
        int8_t contents[];
    } intset;

encoding是表示该结构保存的数据类型。它可以是下列类型值：

    #define INTSET_ENC_INT16 (sizeof(int16_t))
    #define INTSET_ENC_INT32 (sizeof(int32_t))
    #define INTSET_ENC_INT64 (sizeof(int64_t))

至于结构中encoding最终设置为何种类型，则要视其存储的最大数据的类型来决定。比如该结构最初始的内容只有0x08，则类型是INTSET_ENC_INT16。而如果加入了0x12345678，则类型将变成INTSET_ENC_INT32。具体的判断方法是看数值的范围：

    static uint8_t _intsetValueEncoding(int64_t v) {
        if (v < INT32_MIN || v > INT32_MAX)
            return INTSET_ENC_INT64;
        else if (v < INT16_MIN || v > INT16_MAX)
            return INTSET_ENC_INT32;
        else
            return INTSET_ENC_INT16;
    }

不要被上面的区间名给欺骗了，它们的定义如下。

    #define INT8_MIN     ((int8_t)_I8_MIN)
    #define INT8_MAX     _I8_MAX
    #define INT16_MIN    ((int16_t)_I16_MIN)
    #define INT16_MAX    _I16_MAX
    #define INT32_MIN    ((int32_t)_I32_MIN)
    #define INT32_MAX    _I32_MAX

_IXX_MIN都是负数，其最高位为1，这样使用无符号类型强转之后便是更高区间的最大值。

intset结构中length字段表示contents数组元素的个数；contents则是整型数数组的首地址，但是不要被它类型int8_t欺骗了，它实际存储的类型可能是int32_t或者int64_t。

## 创建集合

集合创建通过下面方法实现

    intset *intsetNew(void) {
        intset *is = zmalloc(sizeof(intset));
        is->encoding = intrev32ifbe(INTSET_ENC_INT16);
        is->length = 0;
        return is;
    }

可见集合结构是在堆上分配的，初始类型是INTSET_ENC_INT16，元素个数是0。此时contents指针还是无效的，这说明该结构可能没有采用预分配空间的设计，而是实时分配。之后的代码也印证了这点。

## 重分配集合空间

因为inset结构是个可变长度结构，其可变部分就是contents数组的长度，所以重分配集合空间主要是根据集合保存的数据类型和数组元素个数重新分配空间。

    static intset *intsetResize(intset *is, uint32_t len) {
        uint32_t size = len*intrev32ifbe(is->encoding);
        is = zrealloc(is,sizeof(intset)+size);
        return is;
    }

## 获取集合长度

即返回intset的length字段，它表示其保存的数字个数

    /* Return intset length */
    uint32_t intsetLen(intset *is) {
        return intrev32ifbe(is->length);
    }

## 获取集合占用空间大小

集合结构的设计说明其是一个可变长结构，所以计算空间大小要把结构的头大小和可变长度数组长度相加 

    /* Return intset blob size in bytes. */
    size_t intsetBlobLen(intset *is) {
        return sizeof(intset)+intrev32ifbe(is->length)*intrev32ifbe(is->encoding);
    }

## 通过位置设置值

因为contents保存的数值长度要视intset的encoding类型决定，所以通过位置定位元素时，需要将contents强转为相应类型的指针。这样通过加法操作，可以让指针步进的长度为元素类型的长度

    static void _intsetSet(intset *is, int pos, int64_t value) {
        uint32_t encoding = intrev32ifbe(is->encoding);
    
        if (encoding == INTSET_ENC_INT64) {
            ((int64_t*)is->contents)[pos] = value;
            memrev64ifbe(((int64_t*)is->contents)+pos);
        } else if (encoding == INTSET_ENC_INT32) {
            ((int32_t*)is->contents)[pos] = value;
            memrev32ifbe(((int32_t*)is->contents)+pos);
        } else {
            ((int16_t*)is->contents)[pos] = value;
            memrev16ifbe(((int16_t*)is->contents)+pos);
        }
    }

## 通过位置获取值

获取值时同样要根据intset保存的数据类型决定对contents进行加法操作时步进的长度 

    /* Return the value at pos, given an encoding. */
    static int64_t _intsetGetEncoded(intset *is, int pos, uint8_t enc) {
        int64_t v64;
        int32_t v32;
        int16_t v16;
    
        if (enc == INTSET_ENC_INT64) {
            memcpy(&v64,((int64_t*)is->contents)+pos,sizeof(v64));
            memrev64ifbe(&v64);
            return v64;
        } else if (enc == INTSET_ENC_INT32) {
            memcpy(&v32,((int32_t*)is->contents)+pos,sizeof(v32));
            memrev32ifbe(&v32);
            return v32;
        } else {
            memcpy(&v16,((int16_t*)is->contents)+pos,sizeof(v16));
            memrev16ifbe(&v16);
            return v16;
        }
    }
    
    /* Return the value at pos, using the configured encoding. */
    static int64_t _intsetGet(intset *is, int pos) {
        return _intsetGetEncoded(is,pos,intrev32ifbe(is->encoding));
    }

## 查找元素

查找元素时，先看待查找的元素数值是否在该集合可以表达的数值空间之内。如果不在则直接认为找不到元素，这样可以免去查找操作

    static uint8_t intsetSearch(intset *is, int64_t value, uint32_t *pos) {
        int min = 0, max = intrev32ifbe(is->length)-1, mid = -1;
        int64_t cur = -1;
    
        /* The value can never be found when the set is empty */
        if (intrev32ifbe(is->length) == 0) {
            if (pos) *pos = 0;
            return 0;
        }

由于intset保存的是有序数字，且数字从小到大排列。这样如果元素数值比第一个元素小，或者比最后一个元素大，则说明待查元素也不在数组中。

    else {
            /* Check for the case where we know we cannot find the value,
             * but do know the insert position. */
            if (value > _intsetGet(is,intrev32ifbe(is->length)-1)) {
                if (pos) *pos = intrev32ifbe(is->length);
                return 0;
            } else if (value < _intsetGet(is,0)) {
                if (pos) *pos = 0;
                return 0;
            }
        }

其他情况则说明待查元素，这个时候就采用二分查找的方式进行

    while(max >= min) {
            mid = ((unsigned int)min + (unsigned int)max) >> 1;
            cur = _intsetGet(is,mid);
            if (value > cur) {
                min = mid+1;
            } else if (value < cur) {
                max = mid-1;
            } else {
                break;
            }
        }
    
        if (value == cur) {
            if (pos) *pos = mid;
            return 1;
        } else {
            if (pos) *pos = min;
            return 0;
        }
    }

## 检测元素是否在集合中

检测操作非常简单，只是简单的调用intsetSearch方法

    /* Determine whether a value belongs to this set */
    uint8_t intsetFind(intset *is, int64_t value) {
        uint8_t valenc = _intsetValueEncoding(value);
        return valenc <= intrev32ifbe(is->encoding) && intsetSearch(is,value,NULL);
    }

## 新增一个更大数值类型元素

如果一开始时，集合中保存的元素只有0x01，那么集合的类型是INTSET_ENC_INT16。contents数组的长度也是INTSET_ENC_INT16的长度。现在要往集合中新增一个元素0x12345678，这个时候INTSET_ENC_INT16类型长度的空间已经不能保存该数据了。于是需要对整个结构进行升级

    static intset *intsetUpgradeAndAdd(intset *is, int64_t value) {
        uint8_t curenc = intrev32ifbe(is->encoding);
        uint8_t newenc = _intsetValueEncoding(value);
        int length = intrev32ifbe(is->length);

先要获取以前集合类型和长度，还要计算新集合为何种类型

    int prepend = value < 0 ? 1 : 0;

然后检查新增的值是否为负数。因为该数值的绝对值比之前数组中所有元素都要大，所以如果该数如果是负数，则它比之前任何元素都小，这样它就要插在头部。相反，如果它是正数，则可能是插在尾部。

    /* First set new encoding and resize */
        is->encoding = intrev32ifbe(newenc);
        is = intsetResize(is,intrev32ifbe(is->length)+1);
    
        /* Upgrade back-to-front so we don't overwrite values.
         * Note that the "prepend" variable is used to make sure we have an empty
         * space at either the beginning or the end of the intset. */
        while(length--)
            _intsetSet(is,length+prepend,_intsetGetEncoded(is,length,curenc));

使用新的类型更新集合encoding字段，再通过intsetResize重新分配集合的内存。因为当前内存数据分布和之前的一致（除了变长了），所以还要通过之后的while循环将之前的值转移到其现在应该在的位置。

    /* Set the value at the beginning or the end. */
        if (prepend)
            _intsetSet(is,0,value);
        else
            _intsetSet(is,intrev32ifbe(is->length),value);
        is->length = intrev32ifbe(intrev32ifbe(is->length)+1);
        return is;
    }

最后视新增数据的正负情况插入到新结构的不同位置。

## 数组尾部空间平移

这步操作在要往数组中间插入或者删除元素时发生。如果插入元素，则需要将插入位置的元素及之后的元素一起向后平移。如果删除元素，则要将被删除元素之后的元素向前平移

    static void intsetMoveTail(intset *is, uint32_t from, uint32_t to) {
        void *src, *dst;
        uint32_t bytes = intrev32ifbe(is->length)-from;
        uint32_t encoding = intrev32ifbe(is->encoding);
    
        if (encoding == INTSET_ENC_INT64) {
            src = (int64_t*)is->contents+from;
            dst = (int64_t*)is->contents+to;
            bytes *= sizeof(int64_t);
        } else if (encoding == INTSET_ENC_INT32) {
            src = (int32_t*)is->contents+from;
            dst = (int32_t*)is->contents+to;
            bytes *= sizeof(int32_t);
        } else {
            src = (int16_t*)is->contents+from;
            dst = (int16_t*)is->contents+to;
            bytes *= sizeof(int16_t);
        }
        memmove(dst,src,bytes);
    }

## 增加元素

增加元素时，要先判断待添加的元素是否比现在的集合类型大。如果是，则要重新分配和更新整个集合内存空间

    intset *intsetAdd(intset *is, int64_t value, uint8_t *success) {
        uint8_t valenc = _intsetValueEncoding(value);
        uint32_t pos;
        if (success) *success = 1;
    
        /* Upgrade encoding if necessary. If we need to upgrade, we know that
         * this value should be either appended (if > 0) or prepended (if < 0),
         * because it lies outside the range of existing values. */
        if (valenc > intrev32ifbe(is->encoding)) {
            /* This always succeeds, so we don't need to curry *success. */
            return intsetUpgradeAndAdd(is,value);
        }

如果之前集合的类型可以承载待添加的元素，则先去检查元素是否已经在数组中。如果已经存在，则不再进行添加操作，直接认为操作成功

    else {
            /* Abort if the value is already present in the set.
             * This call will populate "pos" with the right position to insert
             * the value when it cannot be found. */
            if (intsetSearch(is,value,&pos)) {
                if (success) *success = 0;
                return is;
            }

如果不在数组中，则上面的intsetSearch方法将计算出待添加的数据需要被插入到数组中的的位置。这个时候就需要重新分配集合长度，并将要插入的位置及之后的数据向后平移，并把待添加数据设置到数组的相应位置。

    is = intsetResize(is,intrev32ifbe(is->length)+1);
            if (pos < intrev32ifbe(is->length)) intsetMoveTail(is,pos,pos+1);
        }
    
        _intsetSet(is,pos,value);
        is->length = intrev32ifbe(intrev32ifbe(is->length)+1);
        return is;
    }

## 删除元素

删除元素比较简单，只要通过intsetSearch找到元素的位置，将该位置之后的元素向前平移就行了

    /* Delete integer from intset */
    intset *intsetRemove(intset *is, int64_t value, int *success) {
        uint8_t valenc = _intsetValueEncoding(value);
        uint32_t pos;
        if (success) *success = 0;
    
        if (valenc <= intrev32ifbe(is->encoding) && intsetSearch(is,value,&pos)) {
            uint32_t len = intrev32ifbe(is->length);
    
            /* We know we can delete */
            if (success) *success = 1;
    
            /* Overwrite value with tail and update length */
            if (pos < (len-1)) intsetMoveTail(is,pos+1,pos);
            is = intsetResize(is,len-1);
            is->length = intrev32ifbe(len-1);
        }
        return is;
    }


[1]: http://blog.csdn.net/breaksoftware/article/details/53576492
[3]: ./img/qMvEz2a.png
[4]: ./img/JjQb2i6.png
[5]: ./img/NBFreyY.png
[6]: https://www.ietf.org/rfc/ien/ien137.txt