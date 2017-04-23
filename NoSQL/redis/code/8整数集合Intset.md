# Redis源码剖析--整数集合Intset

 时间 2016-12-09 17:26:37  ZeeCoder

_原文_[http://zcheng.ren/2016/12/09/TheAnnotatedRedisSourceIntset/][2]


本系列博客文章已经分析了Redis的大部分数据结构，包括动态字符串，双端链表，字典，跳跃表等，这些数据结构都非常强大实用，但是在内存消耗方面也非常“巨大”。Redis的数据都是存放在内存上面的，所以对内存的使用要求及其苛刻，Redis会想方设法的来节省内存。

假设有一组集合 { 1 , 2 , 3 , 6 , 5 } {1,2,3,6,5} ，如果采用上述的数据结构来存储的话，必然会付出昂贵的内存代价，因此，Redis在这种小数据量的条件下，会使用内存映射来代替内部数据结构。这就使得整数集合（intset）和压缩（ziplist）这两类节省内存的数据结构应运而生了。

## intset数据结构 

Intset是集合键的底层实现之一，如果一个集合满足只保存整数元素和元素数量不多这两个条件，那么Redis就会采用intset来保存这个数据集。intset的数据结构如下： 

    typedef struct intset {
        uint32_t encoding; // 编码模式
        uint32_t length;  // 长度
        int8_t contents[];  // 数据部分
    } intset;
    

其中，encoding字段表示该整数集合的编码模式，Redis提供三种模式的宏定义如下： 

    // 可以看出，虽然contents部分指明的类型是int8_t，但是数据并不以这个类型存放
    // 数据以int16_t类型存放，每个占2个字节，能存放-32768~32767范围内的整数
    #defineINTSET_ENC_INT16 (sizeof(int16_t))
    // 数据以int32_t类型存放，每个占4个字节，能存放-2^32-1~2^32范围内的整数
    #defineINTSET_ENC_INT32 (sizeof(int32_t))
    // 数据以int64_t类型存放，每个占8个字节，能存放-2^64-1~2^64范围内的整数
    #defineINTSET_ENC_INT64 (sizeof(int64_t))
    

length字段用来保存集合中元素的个数。

contents字段用于保存整数，数组中的元素要求不含有重复的整数且按照从小到大的顺序排列。在读取和写入的时候，均按照指定的encoding编码模式读取和写入。

## 升级 

inset中最值得一提的就是升级操作。当intset中添加的整数超过当前编码类型的时候，intset会自定升级到能容纳该整数类型的编码模式，如 { 1 , 2 , 3 , 4 } {1,2,3,4} ，创建该集合的时候，采用int16_t的类型存储，现在需要像集合中添加一个整数40000，超出了当前集合能存放的最大范围，这个时候就需要对该整数集合进行升级操作，将encoding字段改成int32_6类型，并对contents字段内的数据进行重排列。

Redis提供intsetUpgradeAndAdd函数来对整数集合进行升级然后添加数据。其升级过程可以参考如下图示：

![][5]

其源代码如下：

    // 升级整数集合并添加元素
    staticintset *intsetUpgradeAndAdd(intset *is,int64_tvalue){
        // 获取当前编码格式
        uint8_t curenc = intrev32ifbe(is->encoding);
        // 获取需要升级到的编码格式
        uint8_t newenc = _intsetValueEncoding(value);
        // 获取原整数集中的整数个数
        int length = intrev32ifbe(is->length);
        // 由于待添加的元素一定是大于或者小于整数集中所有元素，故此处需要判断添加到新数据集的头部或者尾部
        // 如果value为正，则添加到新数据集的尾部；反之则添加到首部
        int prepend = value < 0 ? 1 : 0;
    
        // 设定新的编码格式
        is->encoding = intrev32ifbe(newenc);
        // 对原数据集进行扩容
        is = intsetResize(is,intrev32ifbe(is->length)+1);
    
        // 采用从后往前的重编码顺序，这样就避免覆盖数据了。
        while(length--)
            // 将原数据集中的数据依次赋值到新数据集中
            // _intsetGetEncoded(is,length,curenc)获取数据集is的第length位上的数据，curenc为原数据集的编码格式
            // _intsetSet将数据集is的第length+prepend位上设定为上一函数返回的值
            _intsetSet(is,length+prepend,_intsetGetEncoded(is,length,curenc));
    
        // 将待添加的数据添加到首部或者尾部
        if (prepend)
            _intsetSet(is,0,value);
        else
            _intsetSet(is,intrev32ifbe(is->length),value);
        // 修改新数据集的长度
        is->length = intrev32ifbe(intrev32ifbe(is->length)+1);
        return is;
    }
    // 将value设定到整数集合is的第pos位
    static void _intsetSet(intset *is, int pos, int64_t value) {
        // 获取整数集合is的编码格式
        uint32_t encoding = intrev32ifbe(is->encoding);
        // 针对不同的编码格式做相应的处理
        if (encoding == INTSET_ENC_INT64) {
            // 将对应的pos位设置成value
            ((int64_t*)is->contents)[pos] = value;
            // 如果必要，对新值进行大小端转换
            memrev64ifbe(((int64_t*)is->contents)+pos);
        } else if (encoding == INTSET_ENC_INT32) {
            // 同上
            ((int32_t*)is->contents)[pos] = value;
            memrev32ifbe(((int32_t*)is->contents)+pos);
        } else {
            // 同上
            ((int16_t*)is->contents)[pos] = value;
            memrev16ifbe(((int16_t*)is->contents)+pos);
        }
    }
    // 获取整数集is中，按照enc编码格式的第pos位上的元素
    static int64_t _intsetGetEncoded(intset *is, int pos, uint8_t enc) {
        int64_t v64;
        int32_t v32;
        int16_t v16;
    
        // 针对不同的编码格式做相应的处理
        // (enc*)is->contents获取整数集中的数据部分
        // (enc*)is->contents+pos获取第pos位上的元素
        // memrevEncifbe(&vEnc)如有必要对拷贝出来的值进行大小端转换
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
    

Redis不提供降级操作，所以一旦对数组进行了升级，编码就会一直保持升级后的状态。

## inset基本操作 

## 创建intset 

Redis在创建intset集合时，默认采用int16_t编码格式。 

    intset *intsetNew(void){
        intset *is = zmalloc(sizeof(intset));
        is->encoding = intrev32ifbe(INTSET_ENC_INT16);
        is->length = 0;
        return is;
    }
    

## 添加元素 

intset在添加元素时需要判断新数据的大小，如果超出原编码格式能表示的范围，则调用上面的intsetUpgradeAndAdd函数进行添加，如果没有超出，则直接添加到指定位置。 

    // 向整数集合中添加元素
    intset *intsetAdd(intset *is,int64_tvalue,uint8_t*success){
        uint8_t valenc = _intsetValueEncoding(value);
        uint32_t pos;
        if (success) *success = 1;
    
        // 如果超出了当前编码格式所能表示的范围，则升级整数集合并添加元素
        if (valenc > intrev32ifbe(is->encoding)) {
            return intsetUpgradeAndAdd(is,value);
        } else {
            // 如果没有超出，则计算待添加整数需要应添加到整数集合中的位置
            if (intsetSearch(is,value,&pos)) {
                // intset中应不存在相同元素，如果待添加的整数已存在，则直接返回
                if (success) *success = 0;
                return is;
            }
            // 调整整数集合的大小
            is = intsetResize(is,intrev32ifbe(is->length)+1);
            // 将整数集合中pos~end的数据移动到pos+1~newend上
            if (pos < intrev32ifbe(is->length)) intsetMoveTail(is,pos,pos+1);
        }
        // 添加数据到第pos位
        _intsetSet(is,pos,value);
        // 更新length值
        is->length = intrev32ifbe(intrev32ifbe(is->length)+1);
        return is;
    }
    // 查找value在整数集is中该添加到的位置
    // 如果整数集中不存在value值，则返回0，并将插入位置存放在pos变量中
    // 反之，返回1，表示value已存在
    staticuint8_tintsetSearch(intset *is,int64_tvalue,uint32_t*pos){
        int min = 0, max = intrev32ifbe(is->length)-1, mid = -1;
        int64_t cur = -1;
    
        // 判断数据集是否为空
        if (intrev32ifbe(is->length) == 0) {
            if (pos) *pos = 0;
            return 0;
        } else {
            // 如果待查找的数超出了整数集中现有元素的最大和最小范围，则不需要查找
            if (value > _intsetGet(is,intrev32ifbe(is->length)-1)) {
                // value大于整数集中的最大值，则插入到整数集末尾
                if (pos) *pos = intrev32ifbe(is->length);
                return 0;
            } else if (value < _intsetGet(is,0)) {
                // value小于整数集中的最小值，则插入到整数集头部
                if (pos) *pos = 0;
                return 0;
            }
        }
        // 利用二分法进行查找，时间复杂度为O(logn)
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
    // 将整数集的from为开始的数据全部移动到to位以后
    staticvoidintsetMoveTail(intset *is,uint32_tfrom,uint32_tto){
        void *src, *dst;
        uint32_t bytes = intrev32ifbe(is->length)-from;
        uint32_t encoding = intrev32ifbe(is->encoding);
        // 根据编码格式做相应处理
        // src为待移动内存的初始位置
        // dst为需要移动到的内存块的初始位置
        // bytes为需要移动的字节数
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
    

## 移除数据 

    // 将整数集合中值为value的整数移除
    intset *intsetRemove(intset *is,int64_tvalue,int*success){
        uint8_t valenc = _intsetValueEncoding(value);
        uint32_t pos;
        if (success) *success = 0;
        // 符合删除条件的要求有两条：
        // -- 值不能超出当前编码格式能表示的范围
        // -- 整数集中能找到该值
        if (valenc <= intrev32ifbe(is->encoding) && intsetSearch(is,value,&pos)) {
            uint32_t len = intrev32ifbe(is->length);
    
            // 表示能删除
            if (success) *success = 1;
    
            // 移动内存，删除数据
            if (pos < (len-1)) intsetMoveTail(is,pos+1,pos);
            // 调整内存大小
            is = intsetResize(is,len-1);
            // 更新length值
            is->length = intrev32ifbe(len-1);
        }
        return is;
    }
    

## 其他操作函数 

* intsetFind 用二分法判断给定值是否存在于集合中
* intsetRandom 随机返回整数集合中的一个数
* intsetGet 取出底层属猪在给定索引上的元素
* intsetLen 返回整数集合中的元素个数
* intsetloblen 返回整数集合占用的内存字节数

## intset小结 

整数集合intset的底层实现为数组，该数组中的元素有序、无重复的存放，为了更好的节省内存，intset提供了升级操作，但是不支持降级操作。intset的源码实现比较简单，但功能上很实用。

[2]: http://zcheng.ren/2016/12/09/TheAnnotatedRedisSourceIntset/?utm_source=tuicool&utm_medium=referral
[5]: http://img1.tuicool.com/FRNVVj3.png!web