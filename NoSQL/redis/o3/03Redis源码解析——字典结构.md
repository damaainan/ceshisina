# Redis源码解析——字典结构

 时间 2016-12-08 20:25:11  方亮的专栏

原文[http://blog.csdn.net/breaksoftware/article/details/53485416][2]



一般情况下，我们谈到字典，难免要谈到红黑树。但是Redis这套字典库并没有使用该方案去实现，而是使用的是链表，且整个代码行数在1000行以内。所以这块逻辑还是非常好分析的。

我们可以想象下，如果使用普通的链表去实现字典，那么是不是整个数据都在一条链表结构上呢？如果是这么设计，插入和删除操作是非常方便的，但是查找操作可能就非常耗时——需要从前向后一个个遍历对比。很显然不能采用这种方案。于是有一种替代性的方案，就是使用数组去存储，然后通过下标去访问。因为下标操作就是指针的移动，所以查找元素变得非常快。相应的问题便是如何将数据的Key转换成数组下标？

一种比较容易想到的就是使用Key对应的二进制码作为下标。比如我们要保存pair(1,"String1")，则使用其Key的值1对应的二进值1作为下标；再比如pair('A',"stringA")，则使用A字符对应的编码十进制值65作为下标。这种设计方法固然简单，但是有个非常现实的问题——到底要分配多大的数组？上面两个例子还比较简单，我们看个稍微复杂的例子，比如要保存pair("AAAA","StringAAA")，则AAAA的二进制编码对应的十进制是65656565，难道我们要分配那么大的数组？想想也不可能，因为我们往往需要保存的数据比上面这些例子还要复杂很多。如果这么设计，我们的内存可能是否不够分配的，且其使用率也非常低。那怎么解决呢？于是我们就要提到Hash算法了。

我们看下Hash中文定义：Hash，一般翻译做“散列”，也有直接音译为“哈希”的，就是把任意长度的输入（又叫做预映射， pre-image），通过散列算法，变换成固定长度的输出，该输出就是散列值。这种转换是一种 **压缩映射** ，也就是， **散列值的空间通常远小于输入的空间** ， **不同的输入可能会散列成相同的输出** ，所以不可能从散列值来唯一的确定输入值。 简单的说就是一种将任意长度的消息压缩到某一固定长度的消息摘要的函数。 （源自百度百科） 

上面的加粗文字，说明Hash算法可以解决我们之前的问题。但是可想想下，将无限的数据归于有限的空间之内，必然会出现碰撞的问题。对于碰撞问题的解决，也有很多方法。下面将介绍Redis的Dict库中Hash碰撞解决方案，只有弄明白这个方案，才能理解该库的设计思想。

## Hash算法碰撞解决方案——拉链法

为了让我们的例子说明比较简单，我杜撰出一种Hash算法和限定使用范围，这样将复杂的问题简单化，从而让我们一窥问题究竟。

我们将Key的使用范围限定于0~4，Hash算法的定义是hash_value = key%5。则我们可以构建一个数组保存key为0~4的数据

![][5]

但是，当我们认知范围从0~4扩展到0~9，则通过我们上面的Hash算法将产生大量的碰撞。在碰撞无法避免的情况下，只有改变我们的存储结构，但是我们还想使用数组，那怎么办呢？那我们就对Hash的值再Hash，再Hash的方法是hash_value%3。于是有

![][6]

上面就是拉链解决Hash碰撞的思路。它将碰撞的数据通过链表的形式连接在一块，而通过数组的形式找到该链表的起始元素。这种方案可以解决碰撞问题，但是相应的效率也会有所下降，但是下降的幅度要视链表的长度来决定。因为通过Hash值寻找数组元素是非常快速的，通过数组元素定位到链表的时间消耗也是快速的，因为它们都是寻址运算。所以可以想象真正消耗时间的是链表中数据的查找。

对上面的问题，我们该如何优化呢？我们可以想到的最简单的方法就是适度的扩大数组的长度。比如我们将数组长度扩大到5个，则链表长度将缩小，其查找效率会明显提升：

![][7]

现在再考虑一个情况，如果我们随机的去掉大部分元素，仅仅留下元素1和4，那么我们上面的结构变为

![][8]

上图可以看出该结构显得非常松散，也浪费内存。这个时候我们可以重新定义再Hash算法，比如让hash_value%2，则

![][9]

上面这两种再Hash是针对链表过长或者空间过于零散的场景设计的。如果把这些看明白了，那么Redis的Dict的实现思想也就大致清楚了。

## Dict的基础结构

Redis的Dict中最基础的元素结构是

    typedef struct dictEntry {
        void *key;
        union {
            void *val;
            uint64_t u64;
            int64_t s64;
            double d;
        } v;
        struct dictEntry *next;
    } dictEntry;

该结构自身内部有一个指向下一个该结构对象的指针，可以见得这是链表元素的结构。key字段是一个无类型指针，我们可以让该key指向任意类型，从而支撑Dict的key是任意类型的能力。联合体v则是key对应的value，它可以是uint_64_t、int64_t、double和void*型，void*型是无类型指针，它使得Dict可以承载任意类型的value值。 

一般一个dict只能承载一种类型的（key,value）对，而key和value的类型则可以是自定义的。这种开放的能力需要优良架构设计的支持。因为对类型没有约束，而框架自身无法得知这些类型的一些信息。但是流程上却需要得知一些必要信息，比如key字段如何进行Hash？key和value如何复制和析构？key字段如何进行等值对比？这些框架无法提前预知的能力只能让数据类型提供者去提供。Redis的Dict中通过下面的结构来指定这些信息

    typedef struct dictType {
        unsigned int (*hashFunction)(const void *key);
        void *(*keyDup)(void *privdata, const void *key);
        void *(*valDup)(void *privdata, const void *obj);
        int (*keyCompare)(void *privdata, const void *key1, const void *key2);
        void (*keyDestructor)(void *privdata, void *key);
        void (*valDestructor)(void *privdata, void *obj);
    } dictType;

承载dictEntry的是下面这个结构，它就是我们之前讨论Hash碰撞时拉链算法的体现 

    typedef struct dictht {
        dictEntry **table;
        unsigned long size;
        unsigned long sizemask;
        unsigned long used;
    } dictht;

table是一个保存dicEntry指针的数组；size是数组的长度；sizemask是用于进行hash再归类的桶，它的值是size-1；used是元素个数，我们通过一个图来解释 

![][10]

似乎我们可以用这个结构已经可以实现字典了。但是Redis在这个基础上做了一些优化，我们看下它定义的字典结构：

    typedef struct dict {
        dictType *type;
        void *privdata;
        dictht ht[2];
        long rehashidx; /* rehashing not in progress if rehashidx == -1 */
        int iterators; /* number of iterators currently running */
    } dict;

type字段定义了字典处理key和value的相应方法，通过这个字段该框架开放了处理自定义类型数据的能力。privdata是私有数据，但是一般都传NULL。ht是个数组，它有两个元素，都是可以用于存储数据的。这儿有个问题，就是为什么要两个dictht对象？我们在讲解拉链法时抛出过两个问题，即数据链过长时或数据松散时如何进行优化？我们采用的是扩大数组个数和缩小数组个数，即再Hash（rehash）的方案。其实Redis就是这样的方案去做的，只是它处理的比较精细。ht[0]作为主要的数据存储区域，ht[1]则是用于rehash操作的结果，但是一旦rehash完成，就将ht[1]中的数据赋值给ht[0]。那么为什么不让ht[1]作为rehash操作中一个栈上临时变量，而要保存在字典结构中呢？这是因为如果我们将rehash操作当成一个原子操作在一个函数中去做，此时如果有数据插入或者删除，则需要等到rehash操作完成才可以执行。而当数据量很大时，rehash操作会比较慢，这样势必影响其他操作的速度。于是Redis在设计时，采用的是一种渐进式的rehash方法。因为渐进式非原子性，所以中间状态也要保存在字典结构中以保证数据完整性。这就是为什么有两个dictht的原因。rehashidx是rehash操作时ht[0]中正在被rehash操作的数组下标，如果它是-1则代表没有在进行rehash操作。iterators是迭代器，我们会在之后讲解。 

![][11]


[2]: http://blog.csdn.net/breaksoftware/article/details/53485416
[5]: ./img/ZJbQ7fv.png
[6]: ./img/fEjE7vV.png
[7]: ./img/MNJjqam.png
[8]: ./img/raAVbu3.png
[9]: ./img/nqaQzi7.png
[10]: ./img/yQBzeaj.png
[11]: ./img/3YnAVv3.png