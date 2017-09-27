# Redis源码剖析--基数统计hyperloglog

 时间 2016-12-08 21:53:50  ZeeCoder

原文[http://zcheng.ren/2016/12/08/TheAnnotatedRedisSourceHyperloglog/][2]



Redis中hyperloglog是用来做基数统计的，其优点是：在输入元素的数量或者体积非常非常大的时候，计算基数所需的空间总是固定的，并且是很小的。在Redis里面，每个Hyperloglog键只需要12Kb的大小就能计算接近2^64个不同元素的基数，但是hyperloglog只会根据输入元素来计算基数，而不会存储元素本身，所以不能像集合那样返回各个元素本身。

## 基数统计 

什么是基数呢？基数是指一个集合中不同元素的个数。假设有一组数据{1，2，3，3，4，5，4，6}除去重复的数字之后，该组数据中不同的数有6个，则该组数据的基数为6。

那什么是基数统计呢？基数统计是指在误差允许的情况下估算出一组数据的误差。

从上述的概念中，我们可以很容易想到基数统计的用途，假设需要计算出某个网站一天中的独立ip访问量，相同ip访问多次的话值算作一次。这个问题即可转换成求一天内所有访问该网站的ip数组的基数。关键在于如何求这个基数？下面我就以最易懂的方法来给大家讲一下。

## 算法思路 

## 伯努利过程 

投掷一次硬币出现正、反两面的概率均为 1 / 2 1/2 。如果我们不断的投掷硬币，直到出现一次正面，在这样的一个过程中，投掷一次得到正面的概率为 1 / 2 1/2 ，投掷两次才得到正面的概率为 1 / 2 2 1/22 ….依次类推，投掷k次才得到一次正面的概率为 1 / 2 k 1/2k 。这个过程在统计学上称为伯努利问题。

有了以上的分析后，我们继续来思考下面两个问题：

* 进行n次伯努利过程，所有投掷次数都小于k的概率
* 进行n次伯努利过程，所有投掷次数都大于k的概率

针对第一个问题，在一次伯努利过程中，投掷次数大于k的概率为 1 / 2 k 1/2k ，也就是投了k次反面的概率。因此，在一次过程中投掷次数不大于k的概率为 1 − 1 / 2 k 1−1/2k 。因此n次伯努利过程所有投掷次数都不大于k的概率为

P ( x ≤ k ) = ( 1 − 1 / 2 k ) n P(x≤k)=(1−1/2k)n

很显然，第二个问题，n次伯努利过程，所有投掷次数都不小于k的概率为

P ( x ≥ k ) = ( 1 − 1 / 2 k − 1 ) n P(x≥k)=(1−1/2k−1)n

从上述公式中可得出结论：当n远小于 2 k 2k 时， P ( x ≥ k ) P(x≥k) 几乎为0，即所有投掷次数都小于k；当n远大于 2 k 2k 时， P ( x ≤ k ) P(x≤k) 几乎为0，即所有投掷次数都大于k。因此，当x=k的情况下，我们可以把 2 k 2k 当成n的一个粗糙估计。

## 基数统计 

将上述伯努利过程转换到比特位串上，假设我们有8位比特位串，每一位上出现0或者1的概率均为 1 / 2 1/2 ，投掷k次才得到一次正面的过程可以理解为第k位上出现第一个1的过程。

那么针对一个数据集来说，我们用某种变换将其转换成一个比特子串，就可以根据上述理论来估算出该数据集的技术。例如数据集转换成00001111，第一次出现1的位置为4，那么该数据集的基数为16。

于是现在的问题就是如何将数据集转换成一个比特位串？很明显，哈希变换可以帮助我们解决这个问题。

选取一个哈希函数，该函数满足一下条件：

* 具有很好的均匀性，无论原始数据集分布如何，其哈希值几乎服从均匀分布。这就保证了伯努利过程中的概率均为1/2
* 碰撞几乎忽略不计，也就是说，对于不同的原始值，其哈希结果相同的概率几乎为0
* 哈希得出的结果比特位数是固定的。

有了以上这些条件，就可以保证”伯努利过程“的随机性和均匀分布了。

接下来，对于某个数据集，其基数为n，将其中的每一个元素都进行上述的哈希变换，这样就得到了一组固定长度的比特位串，设 f ( i ) f(i) 为第i个元素比特位上第一次出现”1“的位置，简单的取其最大值 f m a x fmax 为 f ( i ) f(i) 的最大值，这样，我们就可以得出以下结论：

* 当n远小于 2 f m a x 2fmax 时， f m a x fmax 为当前值的概率为0
* 当n远大于 2 f m a x 2fmax 时， f m a x fmax 为当前值的概率为0

这样一来，我们就可以将 2 f m a x 2fmax 作为n的一个粗糙估计。当然，在实际应用中，由于数据存在偶然性，会导致估计量误差较大，这时候需要采用分组估计来消除误差，并且进行偏差修正。

所谓分组估计就是，每一个数据进行hash之后存放在不同的桶中，然后计算每一个桶的 f m a x fmax ，最后对这些值求一个平均favg，即可得到基数的粗糙估计 2 f a v g 2favg 。

## hyperloglog实现 

## hyperloglog数据结构 

每个hyperloglog键由一下结构体组成： 

```c
    struct hllhdr {
        char magic[4];      // 固定‘HYLL’，用于标识hyperloglog键
        uint8_t encoding;   // 编码模式，有密集标识Dence和稀疏模式sparse
        uint8_t notused[3]; // 未使用字段，留着日后用
        uint8_t card[8];    // 基数缓存，存储上一次计算的基数
        uint8_t registers[]; // 存放访客数据
    };
```

hyperloglog关于数据存放模式部分，本博客不对其进行剖析，因为博主也看不懂。有看的懂的大神在下面留言给我讲讲。

## 添加元素 

Redis提供一下命令来向hyperloglog键中添加数据。 

```
    PFADD key element [element ...]
```

其源码实现如下： 

```c
    void pfaddCommand(client *c){
        robj *o = lookupKeyWrite(c->db,c->argv[1]);
        struct hllhdr *hdr;
        int updated = 0, j;
        // 客户端交互部分，此处可以放着以后理解
        if (o == NULL) { 
            // 创建一个hyperloglog键
            o = createHLLObject();
            dbAdd(c->db,c->argv[1],o);
            updated++;
        } else {
            // 判断是否是一个hyperloglog键，判断前四个字节是否为'HYLL'
            if (isHLLObjectOrReply(c,o) != C_OK) return;
            o = dbUnshareStringValue(c->db,c->argv[1],o);
        }
        // 调用hllAdd函数来添加元素
        for (j = 2; j < c->argc; j++) {
            int retval = hllAdd(o, (unsigned char*)c->argv[j]->ptr,
                                   sdslen(c->argv[j]->ptr));
            switch(retval) {
            case 1:
                updated++;
                break;
            case -1:
                addReplySds(c,sdsnew(invalid_hll_err));
                return;
            }
        }
        hdr = o->ptr;
        if (updated) {
            signalModifiedKey(c->db,c->argv[1]);
            notifyKeyspaceEvent(NOTIFY_STRING,"pfadd",c->argv[1],c->db->id);
            server.dirty++;
            HLL_INVALIDATE_CACHE(hdr);
        }
        // 客户端交互部分，此处可以放着以后理解
        addReply(c, updated ? shared.cone : shared.czero);
    }
```

上述代码包含了很多与客户端交互的部分，此处可以先不看，添加元素主要由hllAdd函数实现。 

```c
    int hllAdd(robj *o,unsigned char *ele,size_t elesize){
        struct hllhdr *hdr = o->ptr;
        switch(hdr->encoding) {
        case HLL_DENSE: return hllDenseAdd(hdr->registers,ele,elesize);  // 密集模式添加元素
        case HLL_SPARSE: return hllSparseAdd(o,ele,elesize); // 稀疏模式添加元素
        default: return -1; // 非法模式
        }
    }
```

以hllDenseAdd为例，此函数计算添加元素哈希后第一个出现1的bit位置，然后添加到hll结构的registers部分。此处才是整个代码实现关于基数统计的关键地方。 

```c
    // 密集模式添加元素
    int hllDenseAdd(uint8_t *registers,unsigned char *ele,size_t elesize){
        uint8_t oldcount, count;
        long index;
    
        // 计算该元素第一个1出现的位置
        count = hllPatLen(ele,elesize,&index);
        // 计算现有的元素集合中，第一个1出现位置的最大值
        HLL_DENSE_GET_REGISTER(oldcount,registers,index);
        if (count > oldcount) {
            // 如果比现有的最大值还大，则添加该值到数据部分
            HLL_DENSE_SET_REGISTER(registers,index,count);
            return 1;
        } else {
            // 如果小于现有的最大值，则不做处理，因为不影响基数
            return 0;
        }
    }
    // 用于计算hash后的值中，第一个出现1的位置
    int hllPatLen(unsigned char *ele,size_t elesize,long *regp){
        uint64_t hash, bit, index;
        int count;
        // 利用MurmurHash64A哈希函数来计算该元素的hash值
        hash = MurmurHash64A(ele,elesize,0xadc83b19ULL);
        index = hash & HLL_P_MASK; /* Register index. */
        hash |= ((uint64_t)1<<63); /* Make sure the loop terminates. */
        bit = HLL_REGISTERS; /* First bit not used to address the register. */
        // 存储第一个1出现的位置
        count = 1; /* Initialized to 1 since we count the "00000...1" pattern. */
        // 计算count
        while((hash & bit) == 0) {
            count++;
            bit <<= 1;
        }
        *regp = (int) index;
        return count;
    }
```

## 计算基数 

Redis提供了下面的命令来计算数据集的基数。 

```
    PFCOUNT key [key ...]
```

如果只有一个key则计算其基数即可；如果存在多个键，则需要合并所有的键（求并集），然后计算其基数。

对于不同的编码格式，Redis提供了不同的函数对其进行基数计算，思路都是找数据集中第一次出现”1“的位置最大的元素的该位置值。源码部分实在不怎么理解其存储方式，所以此处不贴出来了，理解思路就好。

## 合并hyperloglog键 

Redis提供了下面的命令来合并多个hyperloglog键。（源码部分就省略了） 

```
    PFMERGE destkey sourcekey [sourcekey ...]
```


[2]: http://zcheng.ren/2016/12/08/TheAnnotatedRedisSourceHyperloglog/
