## PHP内存管理

来源：[https://segmentfault.com/a/1190000014764790](https://segmentfault.com/a/1190000014764790)


## 第一章 从操作系统内存管理说起

程序是代码和数据的集合，进程是运行着的程序；操作系统需要为进程分配内存；进程运行完毕需要释放内存；内存管理就是内存的分配和释放；
### 1. 分段管理

分段最早出现在8086系统中，当时只有16位地址总线，其能访问的最大地址是64k；当时的内存大小为1M；如何利用16位地址访问1M的内存空间呢？

于是提出了分段式内存管理；
CPU使用CS，DS，ES，SS等寄存器来保存程序的段首地址；当CPU需要访问内存时，只需要指定段偏移地址即可；将段首地址左移4位，再加上段偏移地址，即可得到实际内存地址；
即内存地址=段地址*16+段偏移地址。

后来的IA-32在内存中使用一张段表来记录各个段映射的物理内存地址，CPU只需要为这个段表提供一个记录其首地址的寄存器就可以了；如下图所示：

![][0]

进程包含多个段：代码段，数据段，链接库等；系统需要为每个段分配内存；
一种很自然地想法是，根据每个段实际需要的大小进行分配，并记录已经占用的空间和剩余空间：
当一个段请求内存时，如果有内存中有很多大小不一的空闲位置，那么选择哪个最合理？

```
a）首先适配：空闲链表中选择第一个位置(优点：查表速度快) 
b）最差适配：选择一个最大的空闲区域 
c）最佳适配：选择一个空闲位置大小和申请内存大小最接近的位置，比如申请一个40k内存，而恰巧内存中有一个50k的空闲位置；

```

内存分段管理具有以下优点：

```
a）内存共享： 对内存分段，可以很容易把其中的代码段或数据段共享给其他程序；
b）安全性： 将内存分为不同的段之后，因为不同段的内容类型不同，所以他们能进行的操作也不同，比如代码段的内容被加载后就不应该允许写的操作，因为这样会改变程序的行为
c）动态链接： 动态链接是指在作业运行之前，并不把几个目标程序段链接起来。要运行时，先将主程序所对应的目标程序装入内存并启动运行，当运行过程中又需要调用某段时，才将该段(目标程序)调入内存并进行链接。

```

尽管分段管理的方式解决了内存的分配与释放，但是会带来大量的内存碎片；即尽管我们内存中仍然存在很大空间，但全部都是一些零散的空间，当申请大块内存时会出现申请失败；为了不使这些零散的空间浪费，操作系统会做内存紧缩，即将内存中的段移动到另一位置。但明显移动进程是一个低效的操作。
### 2.分页管理

先说说虚拟内存的概念。CPU访问物理内存的速度要比磁盘快的多，物理内存可以认为是磁盘的缓存，但物理内存是有限的，于是人们想到利用磁盘空间虚拟出的一块逻辑内存
（这部分磁盘空间Windows下称之为虚拟内存，Linux下被称为交换空间（Swap Space））；

虚拟内存和真实的物理内存存在着映射关系；

为了解决分段管理带来的碎片问题，操作系统将虚拟内存分割为虚拟页，相应的物理内存被分割为物理页；而虚拟页和物理页的大小默认都是4K字节；

操作系统以页为单位分配内存：假设需要3k字节的内存，操作系统会直接分配一个页4K给进程，这就产生了内部碎片（浪费率优于分段管理）

在任意时刻，虚拟页面的集合都可以分为三种：

```
未分配的：系统还没有分配或创建的页；
缓存的：当前已缓存在物理内存的已分配页；
未缓存的：未缓存在物理内存中的已分配页；
```

同任何缓存系统一样；虚拟内存必须有某种方法判断一个虚拟页是否已分配；是否已缓存，缓存哪个物理页中；假如没有缓存，这个虚拟页存放在磁盘的哪个位置；

当访问没有缓存的虚拟页时，系统会在物理内存中选择一个牺牲页，并将虚拟页从磁盘赋值到物理内存，替换这个牺牲页；而如果这个牺牲页已经被修改，则还需要写回磁盘；这个过程就是所谓的缺页中断；

虚拟页的集合就称为页表（pageTable），页表就是一个页表条目（page table entry）的数组；每个页表条目都包含有效位标志，记录当前虚拟页是否分配，当前虚拟页的访问控制权限；同时包含物理页号或磁盘地址；

![][1]

进程所看到的地址都是虚拟地址；在访问虚拟地址时，操作系统需要将虚拟地址转化为实际的物理地址；而虚拟地址到物理地址的映射是存储在页表的；

将虚拟地址分为两部分：虚拟页号，记录虚拟页在页表中的偏移量（相当于数组索引）；页内偏移量；而页表的首地址是存储在寄存器中；

![][2]

对于32位系统，内存为4G，页大小为4K，假设每个页表项4字节；则页表包含1M个页表项，占用4M的存储空间，页表本身就需要分配1K个物理页；
页表条目太大时，页表本身需要占用更多的物理内存，而且其内存还必须是连续的；

目前有三种优化技术：

1）多级页表
一级页表中的每个PTE负责映射虚拟地址空间中一个4M的片（chunk），每一个片由1024个连续的页面组成；二级页表的每个PTE都映射一个4K的虚拟内存页面；

优点：节约内存（假如一级页表中的PTE为null，则其指向的二级页表就不存在了，而大多数进程4G的虚拟地址空间大部分都是未分配的；只有一级页表才总是需要在主存中，系统可以在需要的时候创建、调入、调出二级页表）
缺点：虚拟地址到物理地址的翻译更复杂了

![][3]

![][4]

2）TLB
多级页表可以节约内存，但是对于一次地址翻译，增加了内存访问次数，k级页表，需要访问k次内存才能完成地址的翻译；

由此出现了TLB：他是一个更小，访问速度更快的虚拟地址的缓存；当需要翻译虚拟地址时，先在TLB查找，命中的话就可以直接完成地址的翻译；没命中再页表中查找；

![][5]

3）hugePage

因为内存大小是固定的，为了减少映射表的条目，可采取的办法只有增加页的尺寸。hugePage便因此而来，使用大页面2m,4m,16m等等。如此一来映射条目则明显减少。
hugePage有以下优点：

```
无需交换。也就是不存在页面由于内存空间不足而存在换入换出的问题
减轻TLB的压力，也就是降低了cpu cache可缓存的地址映射压力。由于使用了huge page，相同的内存    大小情况下，管理的虚拟地址数量变少。TLB entry可以包含更多的地址空间，cpu的寻址能力相应的得到了增强。
降低page table负载，页表条目大大减少
降低page table查找负载
提高内存的整体性能

```
### 3.linux虚拟内存

linux为每个进程维护一个单独的虚拟地址空间，进程都以为自己独占了整个内存空间，如图所示：

![][6]

linux将内存组织为一些区域（段）的集合，如代码段，数据段，堆，共享库段，以及用户栈都是不同的区域。每个存在的虚拟页面都保存在某个区域中，不属于任何一个区域的虚拟页是不存在的，不能被进程使用；

![][7]

内核为系统中的每个进程维护一个单独的任务结构task_struct，任务中的一个字段指向mm_struct，他描述了虚拟内存的当前状态。其中包含两个字段：pgd指向第一级页表的基址（当内核运行这个进程时，就将pgd的内容存储在cr3控制寄存器中）；mmap指向一个vm_area_struct区域结构的链表；区域结构主要包括以下字段： 
vm_start：区域的起始地址； 
vm_end：区域的结束地址；
vm_port：指向这个区域所包含页的读写许可权限；
vm_flags：描述这个区域是与其他进程共享的，还是私有的等信息；

当我们访问虚拟地址时，内核会遍历vm_area_struct链表，根据vm_start和vm_end能够判断地址合法性；根据vm_por能够判断地址访问的合法性；
遍历链表时间性能较差，内核会将vm_area_struct区域组织成一棵树；

说到这里就不得不提一下系统调用mmap，其函数声明为

```c
void* mmap ( void * addr , size_t len , int prot , int flags , int fd , off_t offset )
```

函数mmap要求内核创建一个新的虚拟内存区域（注意是新的区域，其和堆是平级关系，即mmap函数并不是在堆上分配内存的，）；最好是从地址addr开始（一般传null），并将文件描述fd符指定的对象的一个连续的chunk（大小为len，从文件偏移offset开始）映射到这个新的区域；当fd传-1时，可用于申请分配内存；

参数port描述这个区域的访问控制权限，可以取以下值：

```c
PROT_EXEC //页内容可以被执行
PROT_READ //页内容可以被读取
PROT_WRITE //页可以被写入
PROT_NONE //页不可访问
```

参数flags由描述被映射对象类型的位组成，如MAP_SHARED 表示与其它所有映射这个对象的进程共享映射空间；MAP_PRIVATE 表示建立一个写入时拷贝的私有映射，内存区域的写入不会影响到原文件。

php在分配2M以上大内存时，就是直接使用mmap申请的；
## 第二章 说说内存分配器

malloc是c库函数，用于在堆上分配内存；操作系统给进程分配的堆空间肯定是若干个页，我们调用malloc向进程分配若干字节大小的内存；malloc就是一种内存分配器，其负责堆内存的分配与释放；

同样我们可以使用最低级的mmap和munmap来创建和删除虚拟内存区域，已达到内存的申请与释放；

观察第一章第三小节中的虚拟地址空间描述图，每个进程都有一个称为运行时堆的虚拟内存区域，操作系统内核维护着一个变量brk，指向了堆的顶部；并提供系统调用brk(void* addr)和sbrk(incr)来修改变量brk的值，从而实现堆内存的扩张与收缩；

brk函数将brk指针直接设置为某个地址，而sbrk函数将brk从当前位置移动incr所指定的增量；一个小技巧是，如果将incr设置为0，则可以获得当前brk指向的地址

因此我们也可以使用brk()或sbrk()来动态分配/释放内存块；

需要注意一点的是：系统对每一个进程所分配的资源不是无限的，包括可映射的内存空间，即堆内存并不是无限大的；所以当调用malloc将堆内存都分配完时，malloc会使用mmap函数额外再申请一个虚拟内存区域（由此发现，使用malloc申请的内存也并不一定是在堆上）
### 1.内存分配器设计思路

内存分配器用于处理堆上的内存分配或释放请求；

我们对分配器有以下要求：

``` 
1.处理任意的请求序列：一个应用可以由任意的请求和释放序列；分配器不能假设分配和释放请求的顺序；（将堆内存切割为多个未分配/已分配的离散的内存块）
2.立即响应请求：分配器必须立即响应分配请求，不允许为了提高性能重新排列或者缓冲请求；
3.内存对齐：为了保证分配的内存块可以存储任意数据类型；
4.不修改已分配的块：一单内存块被分配了，就不允许修改或移动；
5.两个目标：最大化吞吐率（单位时间内最大能完成的请求数）；最大化内存利用率；

```

要实现分配器必须考虑以下几个问题：

``` 
1.空闲块组织：如何记录空闲块；
2.分配：如何选择一个合适的空闲块来处理分配请求；
3.分割：空闲块肯定大于实际的分配请求，我们如何处理这个空闲块中的剩余部分；
4.回收：如何处理一个刚刚被释放的块；

```

下面讨论分配器的集中实现思路：
先说一点：任何分配器都需要一些数据结构，从而区分块边界，区分已分配块和空闲块；

1.1隐式空闲链表：

![][8]

![][9]

如何选择“合适”的空闲块呢？可以采用首次适配，下次适配，或最佳适配等算法；

分割空闲块：一旦匹配到合适的空闲块，就必须做出决策：分配整个空闲块（简单，但是会有大量内部碎片）；将空闲块分割为两部分，第一部分分配，第二部分称为新的空闲块；

如果分配器不能找到合适的空闲块来处理请求，可以调用sbrk函数，向内核请求额外的堆内存；

当分配器释放一个内存块时，可能有其他空闲块与这个新释放的空闲块相邻，这时候分配器需要合并相邻的空闲块；分配器可以选择释放时立即合并，也可以选择推后在某个时刻合并；

分配器如何实现合并呢？对于任意一个块，分配器很容易根据当前块位置，当前块大小，定位其下一个块，判断其是否空闲，问题是如何获得前一个块的空闲状态。

思路：在每个块的尾部，添加块大小和分配标记（边界标记）；缺点：浪费空间；

![][10]

1.2显式链表法：

![][11]

堆可以组织为一个空闲双向链表，每个空闲块都包含一个前驱和一个后继指针（不会浪费空间，因为空闲块主体是不需要存储内容的）；

使用双向链表而不是隐式空闲链表，使首次适配的分配时间从块总数的线性时间减少到空闲块总数的线性时间；

双向链表组织有两种思路：1）将新释放的块放置在链表的开始，这种情况下，释放一个内存块常数时间可以完成；如果使用了边界标记，那么合并也可以在常数时间完成；2）按照地址顺序来维护链表，链表中每个块的地址都小于它后继的地址；这种情况下，释放一个块需要线性时间的搜索，而按照地址排序的首次适配算法的内存利用率更高，接近于最佳适配；

我们已经知道，使用双向链表，首次适配的分配时间为空闲块总数的线性时间；

那么我们可以将空闲块分离存储，即维护多个链表，其中每个链表的块有大致相等的大小，这样在分配内存时，只需要遍历一个可以满足分配条件的链表即可，可以显著减少分配时间；

更进一步，伙伴系统是分离存储的特例，系统中的空闲块大小为（2^n）字节；同样按照块大小维护了多个链表；
## 第三章 内存池

C/C++下内存管理是让几乎每一个程序员头疼的问题，分配足够的内存、追踪内存的分配、在不需要的时候释放内存——这个任务相当复杂。而直接使用系统调用malloc/free、new/delete进行内存分配和释放，有以下弊端：

调用malloc/new,系统需要根据“最先匹配”、“最优匹配”或其他算法在内存空闲块表中查找一块空闲内存，调用free/delete,系统可能需要合并空闲内存块，这些会产生额外开销
频繁使用时会产生大量内存碎片，从而降低程序运行效率
容易造成内存泄漏

内存池（memory pool)是代替直接调用malloc/free、new/delete进行内存管理的常用方法，当我们申请内存空间时，首先到我们的内存池中查找合适的内存块，而不是直接向操作系统申请，优势在于：

比malloc/free进行内存申请/释放的方式快
不会产生或很少产生堆碎片
可避免内存泄漏

一般内存池会组织成如下结构体：
结构中主要包含block、list 和pool这三个结构体，block结构包含指向实际内存空间的指针，前向和后向指针让block能够组成双向链表；list结构中free指针指向空闲 内存块组成的链表，used指针指向程序使用中的内存块组成的链表，size值为内存块的大小，list之间组成单向链表；pool结构记录list链表的头和尾。

当用户申请内存时，只需要根据所申请内存的大小，遍历list链表，查看是否存在相匹配的size；

![][12]
## 第四章 切入主题——PHP内存管理

PHP并没有直接使用现有的malloc/free来管理内存的分配和释放，而是重新实现了一套内存管理方案；

PHP采取“预分配方案”，提前向操作系统申请一个chunk（2M，利用到hugepage特性），并且将这2M内存切割为不同规格（大小）的若干内存块，当程序申请内存时，直接查找现有的空闲内存块即可；

PHP将内存分配请求分为3种情况：

huge内存：针对大于2M-4K的分配请求，直接调用mmap分配；

large内存：针对小于2M-4K，大于3K的分配请求，在chunk上查找满足条件的若干个连续page；

small内存：针对小于3K的分配请求；PHP拿出若干个页切割为8字节大小的内存块，拿出若干个页切割为16字节大小的内存块，24字节，32字节等等，将其组织成空闲链表；每当有分配请求时，只在对应的空闲链表获取一个内存块即可；
### 1.PHP内存管理器数据模型

1.1结构体

PHP需要记录申请的所有chunk，需要记录chunk中page的使用情况，要记录每种规格内存的空闲链表，要记录使用mmap分配的huge内存，等等…………

于是有了以下两个结构体：
_zend_mm_heap记录着内存管理器所需的所有数据：

```c
//省略了结构体中很多字段
struct _zend_mm_heap {
    //统计
    size_t             size;                    /* current memory usage */
    size_t             peak;                    /* peak memory usage */
    //由于“预分配”方案，实际使用内存和向操作系统申请的内存大小是不一样的；
    size_t             real_size;               /* current size of allocated pages */
    size_t             real_peak;               /* peak size of allocated pages */
 
    //small内存分为30种；free_slot数组长度为30；数组索引上挂着内存空闲链表
    zend_mm_free_slot *free_slot[ZEND_MM_BINS]; /* free lists for small sizes */
 
    //内存限制
    size_t             limit;                   /* memory limit */
    int                overflow;                /* memory overflow flag */
 
    //记录已分配的huge内存
    zend_mm_huge_list *huge_list;               /* list of huge allocated blocks */
 
    //PHP会分配若干chunk，记录当前主chunk首地址
    zend_mm_chunk     *main_chunk;
     
    //统计chunk数目
    int                chunks_count;            /* number of alocated chunks */
    int                peak_chunks_count;       /* peak number of allocated chunks for current request */ 
}
```

_zend_mm_chunk记录着当前chunk的所有数据

```c
struct _zend_mm_chunk {
    //指向heap
    zend_mm_heap      *heap;
    //chunk组织为双向链表
    zend_mm_chunk     *next;
    zend_mm_chunk     *prev;
    //当前chunk空闲page数目
    uint32_t           free_pages;              /* number of free pages */
    //当前chunk最后一个空闲的page位置
    uint32_t           free_tail;               /* number of free pages at the end of chunk */
    //每当申请一个新的chunk时，这个chunk的num会递增
    uint32_t           num;
    //预留
    char               reserve[64 - (sizeof(void*) * 3 + sizeof(uint32_t) * 3)];
    //指向heap，只有main_chunk使用
    zend_mm_heap       heap_slot;               /* used only in main chunk */
    //记录512个page的分配情况；0代表空闲，1代表已分配
    zend_mm_page_map   free_map;                /* 512 bits or 64 bytes */
    //记录每个page的详细信息，
    zend_mm_page_info  map[ZEND_MM_PAGES];      /* 2 KB = 512 * 4 */
};
```

1.2small内存

前面讲过small内存分为30种规格，每种规格的空闲内存都挂在_zend_mm_heap结构体的free_slot数组上；
30种规格内存如下：

```c
//宏定义：第一列表示序号（称之为bin_num），第二列表示每个small内存的大小（字节数）；第四列表示每次获取多少个page；第三列表示将page分割为多少个大小为第一列的small内存；
#define ZEND_MM_BINS_INFO(_, x, y) \
    _( 0,    8,  512, 1, x, y) \
    _( 1,   16,  256, 1, x, y) \
    _( 2,   24,  170, 1, x, y) \
    _( 3,   32,  128, 1, x, y) \
    _( 4,   40,  102, 1, x, y) \
    _( 5,   48,   85, 1, x, y) \
    _( 6,   56,   73, 1, x, y) \
    _( 7,   64,   64, 1, x, y) \
    _( 8,   80,   51, 1, x, y) \
    _( 9,   96,   42, 1, x, y) \
    _(10,  112,   36, 1, x, y) \
    _(11,  128,   32, 1, x, y) \
    _(12,  160,   25, 1, x, y) \
    _(13,  192,   21, 1, x, y) \
    _(14,  224,   18, 1, x, y) \
    _(15,  256,   16, 1, x, y) \
    _(16,  320,   64, 5, x, y) \
    _(17,  384,   32, 3, x, y) \
    _(18,  448,    9, 1, x, y) \
    _(19,  512,    8, 1, x, y) \
    _(20,  640,   32, 5, x, y) \
    _(21,  768,   16, 3, x, y) \
    _(22,  896,    9, 2, x, y) \
    _(23, 1024,    8, 2, x, y) \
    _(24, 1280,   16, 5, x, y) \
    _(25, 1536,    8, 3, x, y) \
    _(26, 1792,   16, 7, x, y) \
    _(27, 2048,    8, 4, x, y) \
    _(28, 2560,    8, 5, x, y) \
    _(29, 3072,    4, 3, x, y)
 
#endif /* ZEND_ALLOC_SIZES_H */
```

只有这个宏定义有些功能不好用程序实现，比如bin_num=15时，获得此种small内存的字节数？分配此种small内存时需要多少page呢？
于是有了以下3个数组的定义：

```c
//bin_pages是一维数组，数组大小为30，数组索引为bin_num，数组元素为ZEND_MM_BINS_INFO宏中的第四列
#define _BIN_DATA_PAGES(num, size, elements, pages, x, y) pages,
static const uint32_t bin_pages[] = {
  ZEND_MM_BINS_INFO(_BIN_DATA_PAGES, x, y)
};
```

```c
//bin_elements是一维数组，数组大小为30，数组索引为bin_num，数组元素为ZEND_MM_BINS_INFO宏中的第三列
#define _BIN_DATA_ELEMENTS(num, size, elements, pages, x, y) elements,
static const uint32_t bin_elements[] = {
  ZEND_MM_BINS_INFO(_BIN_DATA_ELEMENTS, x, y)
};
```

```c
//bin_data_size是一维数组，数组大小为30，数组索引为bin_num，数组元素为ZEND_MM_BINS_INFO宏中的第二列
#define _BIN_DATA_SIZE(num, size, elements, pages, x, y) size,
static const uint32_t bin_data_size[] = {
  ZEND_MM_BINS_INFO(_BIN_DATA_SIZE, x, y)
};
```
### 2.PHP small内存分配方案

2.1设计思路

上一节提到PHP将small内存分为30种不同大小的规格；
每种大小规格的空闲内存会组织为链表，挂在数组_zend_mm_heap结构体的free_slot[bin_num]索引上；

![][13]

回顾下free_slot字段的定义：

```c
zend_mm_free_slot *free_slot[ZEND_MM_BINS];
 
struct zend_mm_free_slot {
    zend_mm_free_slot *next_free_slot;
};
```

可以看出空闲内存链表的每个节点都是一个zend_mm_free_slot结构体，其只有一个next指针字段；

思考：对于8字节大小的内存块，其next指针就需要占8字节的空间，那用户的数据存储在哪里呢？
答案：free_slot是small内存的空闲链表，空闲指的是未分配内存，此时是不需要存储其他数据的；当分配给用户时，此节点会从空闲链表删除，也就不需要维护next指针了；用户可以在8字节里存储任何数据；

思考：假设调用 void*ptr=emalloc(8)分配了一块内存；调用efree(ptr)释放内存时，PHP如何知道这块内存的字节数呢？
思考1：第二章指出，任何内存分配器都需要额外的数据结构来标志其管理的每一块内存：空闲/已分配，内存大小等；PHP也不例外；可是我们发现使用emalloc(8)分配内存时，其分配的就只是8字节的内存，并没有额外的空间来存储这块内存的任何属性；
思考2：观察small内存宏定义ZEND_MM_BINS_INFO；我们发现对于每一个page，其只可能被分配为同一种规格；不可能存在一部分分割为8字节大小，一部分分割为16字节大小；也就是说每一个page的所有small内存块属性是相同的；那么只需要记录每一个page的属性即可；
思考3：large内存是同样的思路；申请large内存时，可能需要占若干个page的空间；但是同一个page只会属于一个large内存，不可能将一个page的一部分分给某个large内存；
答案：不管page用于small内存还是large内存分配，只需要记录每一个page的属性即可，PHP将其记录在zend_mm_chunk结构体的zend_mm_page_info map[ZEND_MM_PAGES]字段；长度为512的int数组；

2.2入口API

```c
//内存分配对外统一入口API为_emalloc；函数内部直接调用zend_mm_alloc_heap，其第一个参数就是zend_mm_heap结构体（全局只有一个），第二个参数就是请求分配内存大小
void*  _emalloc(size_t size)
{
    return zend_mm_alloc_heap(AG(mm_heap), size);
}
```

```c
//可以看出其根据请求内存大小size判断分配small内存还是large内存，还是huge内存
static void *zend_mm_alloc_heap(zend_mm_heap *heap, size_t size)
{
    void *ptr;
 
    if (size <= ZEND_MM_MAX_SMALL_SIZE) {
        ptr = zend_mm_alloc_small(heap, size, ZEND_MM_SMALL_SIZE_TO_BIN(size));   //注意ZEND_MM_SMALL_SIZE_TO_BIN这个宏定义
        return ptr;
    } else if (size <= ZEND_MM_MAX_LARGE_SIZE) {
        ptr = zend_mm_alloc_large(heap, size);
        return ptr;
    } else {
        return zend_mm_alloc_huge(heap, size);
    }
}
 
 
#define ZEND_MM_CHUNK_SIZE (2 * 1024 * 1024)               /* 2 MB  */
#define ZEND_MM_PAGE_SIZE  (4 * 1024)                      /* 4 KB  */
#define ZEND_MM_PAGES      (ZEND_MM_CHUNK_SIZE / ZEND_MM_PAGE_SIZE)  /* 512 */
#define ZEND_MM_FIRST_PAGE (1)
#define ZEND_MM_MAX_SMALL_SIZE      3072
#define ZEND_MM_MAX_LARGE_SIZE      (ZEND_MM_CHUNK_SIZE - (ZEND_MM_PAGE_SIZE * ZEND_MM_FIRST_PAGE))

```

2.3计算规格（bin_num）

我们发现在调用zend_mm_alloc_small时，使用到了ZEND_MM_SMALL_SIZE_TO_BIN，其定义了一个函数，用于将size转换为bin_num；即请求7字节时，实际需要分配8字节，bin_num=1；请求37字节时，实际需要分配40字节，bin_num=4；即根据请求的size计算满足条件的最小small内存规格的bin_num；

```c
#define ZEND_MM_SMALL_SIZE_TO_BIN(size)  zend_mm_small_size_to_bin(size)
 
static zend_always_inline int zend_mm_small_size_to_bin(size_t size)
{
 
    unsigned int t1, t2;
 
    if (size <= 64) {
        /* we need to support size == 0 ... */
        return (size - !!size) >> 3;
    } else {
        t1 = size - 1;
        t2 = zend_mm_small_size_to_bit(t1) - 3;
        t1 = t1 >> t2;
        t2 = t2 - 3;
        t2 = t2 << 2;
        return (int)(t1 + t2);
        //看到这一堆t1，t2，脑子里只有一个问题：我是谁，我在哪，这是啥；
    }
}
```

1）先分析size小于64情况：看看small内存前8组大小定义，8，16，24，32，48，56，64；很简单，就是等差数列，递增8；所以对于每个size只要除以8就可以了（右移3位）；但是对于size=8，16，24，32，40，48，56，64这些值，需要size-1然后除以8才满足；考虑到size=0的情况，于是有了(size - !!size) >> 3这个表达式；

2）当size大于64时，情况就复杂了：small内存的字节数变化为，64，80，96，112，128，160，192，224，256，320，384，448，512……；等16，递增32，递增64……；

还是先看看二进制吧：

![][14]

我们将size每4个分为一组，第一组比特序列长度为7，第二组比特序列长度为8，……；

那我们可以这么算：1）计算出size属于第几组；2）计算size在组内的偏移量；3）计算组开始位置。思路就是这样，但是计算方法并不统一，只要找规律计算出来即可。

```c
//计算当前size属于哪一组；也就是计算比特序列长度；也就是计算最高位是1的位置；
 
//从低到高位查找也行，O(n)复杂度；使用二分查号，复杂度log（n）
 
//size最大为3072（不知道的回去看small内存宏定义）；将size的二进制看成16比特的序列；先按照8二分，再按照4或12二分，再按照2/6/10/16二分……
 
//思路：size与255比较（0xff）比较，如果小于，说明高8位全是0，只需要在低8位查找即可；
 
/* higher set bit number (0->N/A, 1->1, 2->2, 4->3, 8->4, 127->7, 128->8 etc) */
static zend_always_inline int zend_mm_small_size_to_bit(int size)
{
    int n = 16;
    if (size <= 0x00ff) {n -= 8; size = size << 8;}
    if (size <= 0x0fff) {n -= 4; size = size << 4;}
    if (size <= 0x3fff) {n -= 2; size = size << 2;}
    if (size <= 0x7fff) {n -= 1;}
    return n;
}
```

2.4开始分配了

前面说过small空闲内存会形成链表，挂在zen_mm_heap字段free_slot[bin_num]上；

最初请求分配时，free_slot[bin_num]可能还没有初始化，指向null；此时需要向chunk分配若干页，将页分割为大小相同的内存块，形成链表，挂在free_slot[bin_num]

```c
static zend_always_inline void *zend_mm_alloc_small(zend_mm_heap *heap, size_t size, int bin_num)
{
    //空闲链表不为null，直接分配
    if (EXPECTED(heap->free_slot[bin_num] != NULL)) {
        zend_mm_free_slot *p = heap->free_slot[bin_num];
        heap->free_slot[bin_num] = p->next_free_slot;
        return (void*)p;
    } else {
    //先分配页
        return zend_mm_alloc_small_slow(heap, bin_num);
    }
}
```

```c
//分配页；切割；形成链表
static zend_never_inline void *zend_mm_alloc_small_slow(zend_mm_heap *heap, uint32_t bin_num)
{
    zend_mm_chunk *chunk;
    int page_num;
    zend_mm_bin *bin;
    zend_mm_free_slot *p, *end;
 
    //分配页（页数目是small内存宏定义第四列）；放在下一节large内存分配讲解
    bin = (zend_mm_bin*)zend_mm_alloc_pages(heap, bin_pages[bin_num]);
 
    if (UNEXPECTED(bin == NULL)) {
        /* insufficient memory */
        return NULL;
    }
     
    //之前提过任何内存分配器都需要额外的数据结构记录每块内存的属性；分析发现PHP每个page的属性都是相同的；且存储在zend_mm_chunk结构体的map字段（512个int）
    //bin即页的首地址；需要计算bin是当前chunk的第几页：1）得到chunk首地址；2）得到bin相对chunk首地址偏移量；3）除以页大小
    chunk = (zend_mm_chunk*)ZEND_MM_ALIGNED_BASE(bin, ZEND_MM_CHUNK_SIZE);
    page_num = ZEND_MM_ALIGNED_OFFSET(bin, ZEND_MM_CHUNK_SIZE) / ZEND_MM_PAGE_SIZE;
     
    //记录页属性；后面分析
    chunk->map[page_num] = ZEND_MM_SRUN(bin_num);
    if (bin_pages[bin_num] > 1) {
        uint32_t i = 1;
 
        do {
            chunk->map[page_num+i] = ZEND_MM_NRUN(bin_num, i);
            i++;
        } while (i < bin_pages[bin_num]);
    }
 
    //切割内存；形成链表
    end = (zend_mm_free_slot*)((char*)bin + (bin_data_size[bin_num] * (bin_elements[bin_num] - 1)));
    heap->free_slot[bin_num] = p = (zend_mm_free_slot*)((char*)bin + bin_data_size[bin_num]);
    do {
        p->next_free_slot = (zend_mm_free_slot*)((char*)p + bin_data_size[bin_num]);
        p = (zend_mm_free_slot*)((char*)p + bin_data_size[bin_num]);
    } while (p != end);
 
    /* terminate list using NULL */
    p->next_free_slot = NULL;
 
    /* return first element */
    return (char*)bin;
}
```

2.5说说记录页属性的map

1）对任意地址p，如何计算页号？

地址p减去chunk首地址获得偏移量；偏移量除4K即可；问题是如何获得chunk首地址？我们看看源码：

```c
chunk = (zend_mm_chunk*)ZEND_MM_ALIGNED_BASE(bin, ZEND_MM_CHUNK_SIZE);
page_num = ZEND_MM_ALIGNED_OFFSET(bin, ZEND_MM_CHUNK_SIZE) / ZEND_MM_PAGE_SIZE;
 
#define ZEND_MM_ALIGNED_OFFSET(size, alignment) \
    (((size_t)(size)) & ((alignment) - 1))
#define ZEND_MM_ALIGNED_BASE(size, alignment) \
    (((size_t)(size)) & ~((alignment) - 1))
#define ZEND_MM_SIZE_TO_NUM(size, alignment) \
    (((size_t)(size) + ((alignment) - 1)) / (alignment))
```

我们发现计算偏移量或chunk首地址时，需要两个参数：size，地址p；alignment，调用时传的是ZEND_MM_CHUNK_SIZE（2M）；

其实PHP在申请chunk时，额外添加了一个条件：chunk首地址2M字节对齐；

2M字节对齐时，chunk首地址的低21位全是0；给定任意地址p，p的低21位即地址p相对于chunk首地址的偏移量；

那如何保证chunk首地址2M字节对齐呢？分析源码：

```c
//chunk大小为size 2M；chunk首地址对齐方式 2M
static void *zend_mm_chunk_alloc_int(size_t size, size_t alignment)
{
    void *ptr = zend_mm_mmap(size);
 
    if (ptr == NULL) {
        return NULL;
    } else if (ZEND_MM_ALIGNED_OFFSET(ptr, alignment) == 0) { //2M对齐，直接返回
        return ptr;
    } else {
        size_t offset;
 
        //没有2M对齐，先释放，再重新分配2M+2M-4K空间
        //重新分配大小为2M+2M也是可以的（减4K是因为操作系统分配内存按页分配的，页大小4k）
        //此时总能定位一段2M的内存空间，且首地址2M对齐
        zend_mm_munmap(ptr, size);
        ptr = zend_mm_mmap(size + alignment - REAL_PAGE_SIZE);
 
        //分配了2M+2M-4K空间，需要释放前面、后面部分空间。只保留中间按2M字节对齐的chunk即可
        offset = ZEND_MM_ALIGNED_OFFSET(ptr, alignment);
        if (offset != 0) {
            offset = alignment - offset;
            zend_mm_munmap(ptr, offset);
            ptr = (char*)ptr + offset;
            alignment -= offset;
        }
        if (alignment > REAL_PAGE_SIZE) {
            zend_mm_munmap((char*)ptr + size, alignment - REAL_PAGE_SIZE);
        }
        return ptr;
    }
}
//理论分析，申请2M空间，能直接2M字节对齐的概率很低；但是实验发现，概率还是蛮高的，这可能与内核分配内存有关；
```

2）每个页都需要记录哪些属性？

chunk里的某个页，可以分配为large内存，large内存连续占多少个页；可以分配为small内存，对应的是哪种规格的small内存（bin_num）

```c
//29-31比特表示当前页分配为small还是large
//当前页用于large内存分配
#define ZEND_MM_IS_LRUN                  0x40000000
//当前页用于small内存分配
#define ZEND_MM_IS_SRUN                  0x80000000
 
//对于large内存，0-9比特表示分配的页数目
#define ZEND_MM_LRUN_PAGES_MASK          0x000003ff
#define ZEND_MM_LRUN_PAGES_OFFSET        0
 
//对于small内存，0-4比特表示bin_num
#define ZEND_MM_SRUN_BIN_NUM_MASK        0x0000001f
#define ZEND_MM_SRUN_BIN_NUM_OFFSET      0
 
//count即large内存占了多少个页
#define ZEND_MM_LRUN(count)              (ZEND_MM_IS_LRUN | ((count) << ZEND_MM_LRUN_PAGES_OFFSET))
#define ZEND_MM_SRUN(bin_num)            (ZEND_MM_IS_SRUN | ((bin_num) << ZEND_MM_SRUN_BIN_NUM_OFFSET))
```

再回顾一下small内存30种规格的宏定义，bin_num=16、17、20-29时，需要分配大于1个页；此时不仅需要记录bin_num，还需要记录其对应的页数目

```c
#define ZEND_MM_SRUN_BIN_NUM_MASK        0x0000001f
#define ZEND_MM_SRUN_BIN_NUM_OFFSET      0
 
#define ZEND_MM_SRUN_FREE_COUNTER_MASK   0x01ff0000
#define ZEND_MM_SRUN_FREE_COUNTER_OFFSET 16
 
#define ZEND_MM_NRUN_OFFSET_MASK         0x01ff0000
#define ZEND_MM_NRUN_OFFSET_OFFSET       16
 
//当前页分配为small内存；0-4比特存储bin_num；16-25存储当前规格需要分配的页数目；
#define ZEND_MM_SRUN_EX(bin_num, count)  (ZEND_MM_IS_SRUN | ((bin_num) << ZEND_MM_SRUN_BIN_NUM_OFFSET) |
        ((count) << ZEND_MM_SRUN_FREE_COUNTER_OFFSET))
 
//29-31比特表示同时属于small内存和large内存；0-4比特存储bin_num；16-25存储偏移量
//对于bin_num=29，需要分配3个页，假设为10，11，12号页
//map[10]=ZEND_MM_SRUN_EX(29,3);map[11]=ZEND_MM_NRUN(29,1);map[12]=ZEND_MM_NRUN(29,2);
#define ZEND_MM_NRUN(bin_num, offset)    (ZEND_MM_IS_SRUN | ZEND_MM_IS_LRUN | ((bin_num) << ZEND_MM_SRUN_BIN_NUM_OFFSET) |
        ((offset) << ZEND_MM_NRUN_OFFSET_OFFSET))
```
### 3.large内存分配：

需要从chunk中查找连续pages_count个空闲的页；zend_mm_chunk结构体的free_map为512个比特，记录着每个页空闲还是已分配；

以64位机器为例，free_map又被分为8组；每组64比特，看作uint32_t类型；

```c
#define ZEND_MM_CHUNK_SIZE (2 * 1024 * 1024)               /* 2 MB  */
#define ZEND_MM_PAGE_SIZE  (4 * 1024)                      /* 4 KB  */
#define ZEND_MM_PAGES      (ZEND_MM_CHUNK_SIZE / ZEND_MM_PAGE_SIZE)  /* 512 */
 
typedef zend_ulong zend_mm_bitset;    /* 4-byte or 8-byte integer */
 
#define ZEND_MM_BITSET_LEN      (sizeof(zend_mm_bitset) * 8)       /* 32 or 64 */
#define ZEND_MM_PAGE_MAP_LEN    (ZEND_MM_PAGES / ZEND_MM_BITSET_LEN) /* 16 or 8 */
```

```
static void *zend_mm_alloc_pages(zend_mm_heap *heap, uint32_t pages_count)
{
    //获取main_chunk
    zend_mm_chunk *chunk = heap->main_chunk;
    uint32_t page_num, len;
    int steps = 0;
 
    //其实就是最佳适配算法
    while (1) {
        //free_pages记录当前chunk的空闲页数目
        if (UNEXPECTED(chunk->free_pages < pages_count)) {
            goto not_found;
        } else {
            /* Best-Fit Search */
            int best = -1;
            uint32_t best_len = ZEND_MM_PAGES;
 
            //从free_tail位置开始，后面得页都是空闲的
            uint32_t free_tail = chunk->free_tail;
            zend_mm_bitset *bitset = chunk->free_map;
            zend_mm_bitset tmp = *(bitset++);
            uint32_t i = 0;
            //从第一组开始遍历；查找若干连续空闲页；i实际每次递增64；
            //最佳适配算法;查找到满足条件的间隙，空闲页数目大于pages_count；
            //best记录间隙首位置；best_len记录间隙空闲页数目
            while (1) {
                //注意：(zend_mm_bitset)-1，表示将-1强制类型转换为64位无符号整数，即64位全1（表示当前组的页全被分配了）
                while (tmp == (zend_mm_bitset)-1) {
                    i += ZEND_MM_BITSET_LEN;
                    if (i == ZEND_MM_PAGES) {
                        if (best > 0) {
                            page_num = best;
                            goto found;
                        } else {
                            goto not_found;
                        }
                    }
                    tmp = *(bitset++); //当前组的所有页都分配了，递增到下一组
                }
                //每一个空闲间隙，肯定有若干个比特0，查找第一个比特0的位置：
                //假设当前tmp=01111111（低7位全1，高位全0）；则zend_mm_bitset_nts函数返回8
                page_num = i + zend_mm_bitset_nts(tmp); 函数实现后面分析
                 
                //tmp+1->10000000;  tmp&(tmp+1)  其实就是把tmp的低8位全部置0，只保留高位
                tmp &= tmp + 1;
                 
                //如果此时tmp == 0，说明从第个页page_num到当前组最后一个页，都是未分配的；
                //否则，需要找出这个空闲间隙另外一个0的位置，相减才可以得出空闲间隙页数目
                while (tmp == 0) {
                    i += ZEND_MM_BITSET_LEN; //i+64,如果超出free_tail或者512，说明从page_num开始后面所有页都是空闲的；否则遍历下一组
                    if (i >= free_tail || i == ZEND_MM_PAGES) {
                        len = ZEND_MM_PAGES - page_num;
                        if (len >= pages_count && len < best_len) {   //从page_num处开始后面页都空闲，且剩余页数目小于已经查找到的连续空闲页数目，直接分配
                            chunk->free_tail = page_num + pages_count;
                            goto found;
                        } else {  //当前空闲间隙页不满足条件
                              
                            chunk->free_tail = page_num;
                            if (best > 0) { //之前有查找到空闲间隙符合分配条件
                                page_num = best;
                                goto found;
                            } else {  //之前没有查找到空闲页满足条件，说明失败
                                goto not_found;
                            }
                        }
                    }
                    tmp = *(bitset++); //遍历下一组
                }
                 
                //假设最初tmp=1111000001111000111111，tmp&=tmp+1后，tmp=1111000001111000 000000
                //上面while循环进不去；且page_num=7+i；
                //此时需从低到高位查找第一个1比特位置，为11，11+i-（7+i）=4，即是连续空闲页数目
                len = i + zend_ulong_ntz(tmp) - page_num;
                if (len >= pages_count) { //满足分配条件，记录
                    if (len == pages_count) {
                        goto found;
                    } else if (len < best_len) {
                        best_len = len;
                        best = page_num;
                    }
                }
                 
                //上面计算后tmp=1111000001111000 000000；发现这一组还有一个空闲间隙，拥有5个空闲页，下一个循环肯定需要查找出来；
                //而目前低10比特其实已经查找过了，那么需要将低10比特全部置1，以防再次查找到；
                //tmp-1:1111000001110111 111111; tmp |= tmp - 1:1111000001111111 111111
                tmp |= tmp - 1;
            }
        }
 
not_found:
        ………………
found:
     
    //查找到满足条件的连续页，设置从page_num开始pages_count个页为已分配
    chunk->free_pages -= pages_count;
    zend_mm_bitset_set_range(chunk->free_map, page_num, pages_count);
    //标志当前页用于large内存分配，分配数目为pages_count
    chunk->map[page_num] = ZEND_MM_LRUN(pages_count);
    //更新free_tail
    if (page_num == chunk->free_tail) {
        chunk->free_tail = page_num + pages_count;
    }
    //返回当前第一个page的首地址
    return ZEND_MM_PAGE_ADDR(chunk, page_num);
}
 
//4K大小的字节数组
struct zend_mm_page {
    char               bytes[ZEND_MM_PAGE_SIZE];
};
 
//偏移page_num*4K
#define ZEND_MM_PAGE_ADDR(chunk, page_num) \
    ((void*)(((zend_mm_page*)(chunk)) + (page_num)))
```

看看PHP是如何高效查找0比特位置的：依然是二分查找

```c
static zend_always_inline int zend_mm_bitset_nts(zend_mm_bitset bitset)
{
    int n=0;
//64位机器才会执行
#if SIZEOF_ZEND_LONG == 8
    if (sizeof(zend_mm_bitset) == 8) {
        if ((bitset & 0xffffffff) == 0xffffffff) {n += 32; bitset = bitset >> Z_UL(32);}
    }
#endif
    if ((bitset & 0x0000ffff) == 0x0000ffff) {n += 16; bitset = bitset >> 16;}
    if ((bitset & 0x000000ff) == 0x000000ff) {n +=  8; bitset = bitset >>  8;}
    if ((bitset & 0x0000000f) == 0x0000000f) {n +=  4; bitset = bitset >>  4;}
    if ((bitset & 0x00000003) == 0x00000003) {n +=  2; bitset = bitset >>  2;}
    return n + (bitset & 1);
}
```
### 4.huge内存分配：

```c
#define ZEND_MM_ALIGNED_SIZE_EX(size, alignment) \
    (((size) + ((alignment) - Z_L(1))) & ~((alignment) - Z_L(1)))
 
//会将size扩展为2M字节的整数倍；直接调用分配chunk的函数申请内存
//huge内存以n*2M字节对齐的
static void *zend_mm_alloc_huge(zend_mm_heap *heap, size_t size)
{
    size_t new_size = ZEND_MM_ALIGNED_SIZE_EX(size, REAL_PAGE_SIZE);
     
    void *ptr = zend_mm_chunk_alloc(heap, new_size, ZEND_MM_CHUNK_SIZE);
    return ptr;
}
```
### 5.内存释放

```c
ZEND_API void ZEND_FASTCALL _efree(void *ptr)
{
    zend_mm_free_heap(AG(mm_heap), ptr);
}
 
static zend_always_inline void zend_mm_free_heap(zend_mm_heap *heap, void *ptr)
{
    //计算当前地址ptr相对于chunk的偏移
    size_t page_offset = ZEND_MM_ALIGNED_OFFSET(ptr, ZEND_MM_CHUNK_SIZE);
 
    //偏移为0，说明是huge内存，直接释放
    if (UNEXPECTED(page_offset == 0)) {
        if (ptr != NULL) {
            zend_mm_free_huge(heap, ptr);
        }
    } else {
        //计算chunk首地址
        zend_mm_chunk *chunk = (zend_mm_chunk*)ZEND_MM_ALIGNED_BASE(ptr, ZEND_MM_CHUNK_SIZE);
        //计算页号
        int page_num = (int)(page_offset / ZEND_MM_PAGE_SIZE);
        //获得页属性信息
        zend_mm_page_info info = chunk->map[page_num];
 
        //small内存
        if (EXPECTED(info & ZEND_MM_IS_SRUN)) {
            zend_mm_free_small(heap, ptr, ZEND_MM_SRUN_BIN_NUM(info));
        }
        //large内存
        else /* if (info & ZEND_MM_IS_LRUN) */ {
            int pages_count = ZEND_MM_LRUN_PAGES(info);
 
            zend_mm_free_large(heap, chunk, page_num, pages_count);
        }
    }
}
```
### 6.zend_mm_heap和zend_mm_chunk

PHP有一个全局唯一的zend_mm_heap，其是zend_mm_chunk一个字段；

zend_mm_chunk至少需要空间2k+；和zend_mm_chunk存储在哪里？

这两个结构体其实是存储在chunk的第一个页，即chunk的第一个页始终是分配的，且用户不能申请的；

申请的多个chunk之间是形成双向链表的；如下图所示：

![][15]

```c
static zend_mm_heap *zend_mm_init(void)
{
    //将分配的2M空间，强制转换为zend_mm_chunk*；并初始化zend_mm_chunk结构体
    zend_mm_chunk *chunk = (zend_mm_chunk*)zend_mm_chunk_alloc_int(ZEND_MM_CHUNK_SIZE, ZEND_MM_CHUNK_SIZE);
    zend_mm_heap *heap;
 
    heap = &chunk->heap_slot;
    chunk->heap = heap;
    chunk->next = chunk;
    chunk->prev = chunk;
    chunk->free_pages = ZEND_MM_PAGES - ZEND_MM_FIRST_PAGE;
    chunk->free_tail = ZEND_MM_FIRST_PAGE;
    chunk->num = 0;
    chunk->free_map[0] = (Z_L(1) << ZEND_MM_FIRST_PAGE) - 1;
    chunk->map[0] = ZEND_MM_LRUN(ZEND_MM_FIRST_PAGE);
    heap->main_chunk = chunk;
    heap->cached_chunks = NULL;
    heap->chunks_count = 1;
    heap->peak_chunks_count = 1;
    heap->cached_chunks_count = 0;
    heap->avg_chunks_count = 1.0;
    heap->last_chunks_delete_boundary = 0;
    heap->last_chunks_delete_count = 0;
    heap->huge_list = NULL;
    return heap;
}
```
### 7. PHP内存管理器初始化流程：

php_module_startup——>zend_startup——>start_memory_manager——>alloc_globals_ctor——>zend_mm_init——>zend_mm_chunk_alloc_int

[0]: ./img/bV960b.png
[1]: ./img/bV9611.png
[2]: ./img/bV9614.png
[3]: ./img/bV962b.png
[4]: ./img/bV962c.png
[5]: ./img/bV962i.png
[6]: ./img/bV9621.png
[7]: ./img/bV9623.png
[8]: ./img/bV963o.png
[9]: ./img/bV963v.png
[10]: ./img/bV963H.png
[11]: ./img/bV963T.png
[12]: ./img/bV964S.png
[13]: ./img/bV966d.png
[14]: ./img/bV9664.png
[15]: ./img/bV969o.png