## 【Nginx源码分析】Nginx中的锁与原子操作

来源：[https://segmentfault.com/a/1190000017005117](https://segmentfault.com/a/1190000017005117)

李乐
## 问题引入

多线程或者多进程程序访问同一个变量时，需要加锁才能实现变量的互斥访问，否则结果可能是无法预期的，即存在并发问题。解决并发问题通常有两种方案：
1）加锁：访问变量之前加锁，只有加锁成功才能访问变量，访问变量之后需要释放锁；这种通常称为悲观锁，即认为每次变量访问都会导致并发问题，因此每次访问变量之前都加锁。
2）原子操作：只要访问变量的操作是原子的，就不会导致并发问题。那表达式么i++是不是原子操作呢？
nginx通常会有多个worker处理请求，多个worker之间需要通过抢锁的方式来实现监听事件的互斥处理，由函数ngx_shmtx_trylock实现抢锁逻辑，代码如下：

```LANG
ngx_uint_t ngx_shmtx_trylock(ngx_shmtx_t *mtx)
{
    return (*mtx->lock == 0 && ngx_atomic_cmp_set(mtx->lock, 0, ngx_pid));
}
```

变量mtx->lock指向的是一块共享内存地址（所有worker都可以访问）；worker进程会尝试设置变量mtx->lock的值为当前进程号，如果设置成功，则说明抢锁成功，否则认为抢锁失败。
注意ngx_atomic_cmp_set设置变量mtx->lock的值为当前进程号并不是无任何条件的，而是只有当变量mtx->lock值为0时才设置，否则不予设置。ngx_atomic_cmp_set是典型的比较-交换操作，且必须加锁或者是原子操作才行，函数实现方式下节分析。
nginx有一些全局统计变量，比如说变量ngx_connection_counter，此类变量由所有worker进程共享，并发执行累加操作，由函数ngx_atomic_fetch_add实现；而该累加操作需要加锁或者时原子操作才行，函数实现方式下节分析。
上面说的mtx->lock和ngx_connection_counter都是共享变量，所有worker进程都可以访问，这些变量在ngx_event_core_module模块的ngx_event_module_init函数创建，且该函数在fork worker进程之前执行。

```LANG
/* cl should be equal to or greater than cache line size */
cl = 128;
size = cl            /* ngx_accept_mutex */
       + cl          /*ngx_connection_counter */
       + cl;         /* ngx_temp_number */
           
if (ngx_shm_alloc(&shm) != NGX_OK) {
     return NGX_ERROR;
}
shared = shm.addr;
if (ngx_shmtx_create(&ngx_accept_mutex, (ngx_shmtx_sh_t *) shared,cycle->lock_file.data)!= NGX_OK)
    {
        return NGX_ERROR;
    }
    
ngx_connection_counter = (ngx_atomic_t *) (shared + 1 * cl);
```

这里需要重点思考这么几个问题：
1）cache_line_size是什么？我们都知道CPU与主存之间还存在着高速缓存，高速缓存的访问速率高于主存访问速率，因此主存中部分数据会被缓存在高速缓存中，CPU访问数据时会先从高速缓存中查找，如果没有命中才会访问主从。需要注意的是，主存中的数据并不是一字节一字节加载到高速缓存中的，而是每次加载一个数据块，该数据块的大小就称为cache_line_size，高速缓存中的这块存储空间称为一个缓存行。cache_line_size32字节，64字节不等，通常为64字节。
2）此处cl取值128字节，可是cl为什么一定要大于等于cache_line_size？待下一节分析了原子操作函数实现方式后自然会明白的。
3）函数ngx_shm_alloc是通过系统调用mmap分配的内存空间，首地址为shared；
4）这里创建了三个共享变量ngx_accept_mutex、ngx_connection_counter和ngx_temp_number；函数ngx_shmtx_create使得ngx_accept_mutex->lock变量指向shared；ngx_connection_counter指向shared+128字节位置处，ngx_temp_number指向shared+256字节位置处。
## 原子操作函数实现方式

据说gcc某版本以后内置了一些原子性操作函数（没有验证），如：

```LANG
//原子加
type __sync_fetch_and_add (type *ptr, type value);
//原子减
type __sync_fetch_and_sub (type *ptr, type value);
//原子比较-交换，返回true
bool __sync_bool_compare_and_swap(type* ptr, type oldValue, type newValue, ....);
//原子比较交换，返回之前的值
type __sync_val_compare_and_swap(type* ptr, type oldValue, type newValue, ....);
```

通过这些函数很容易解决上面说的多个worker抢锁，统计变量并发累计问题。nginx会检测系统是否支持上述方法，如果不支持会自己实现类似的原子性操作函数。
源码目录下src/os/unix/ngx_gcc_atomic_amd64.h、src/os/unix/ngx_gcc_atomic_x86.h等文件针对不同操作系统实现了若干原子性操作函数。
## 内联汇编

可通过内联汇编向C代码中嵌入汇编语言。原子操作函数内部都使用到了内联汇编，因此这里需要做简要介绍；
内联汇编格式如下，需要了解以下6个概念：

```LANG
asm ( 
汇编指令
: 输出操作数（可选）
: 输入操作数（可选）
: 寄存器列表（表明哪些寄存器被修改，可选）
);
```

1）寄存器通常有一些简称；

* r：表示使用一个通用寄存器，由GCC在%eax/%ax/%al, %ebx/%bx/%bl, %ecx/%cx/%cl, %edx/%dx/%dl中选取一个GCC认为合适的。
* a：表示使用%eax / %ax / %al
* b：表示使用%ebx / %bx / %bl
* c：表示使用%ecx / %cx / %cl
* d：表示使用%edx / %dx / %dl
* m: 表示内存地址
* 等


2）汇编指令；

```LANG
" popl %0 "
" movl %1, %%esi "
" movl %2, %%edi "
```

3）输入操作数，通常格式为——"寄存器简称/内存简称"(值)；这种称为寄存器约束或者内存约束，表明输入或者输出需要借助寄存器或者内存实现。

```LANG
: "m" (*lock), "a" (old), "r" (set)
```

4）输出操作数；

```LANG
//+号表示既是输入参数又是输出参数
:"+r" (add)
//将寄存器%eax / %ax / %al存储到变量res中
:"=a" (res)
```

5）寄存器列表，如

```LANG
: "cc", "memory"
```

cc表示会修改标志寄存器中的条件标志，memory表示会修改内存。
6）占位符与volatile关键字

```LANG
__asm__ volatile (
    "    xaddl  %0, %1;   "
    : "+r" (add) : "m" (*value) : "cc", "memory");
```

volatile表明禁止编译器优化；%0和%1顺序对应后面的输出或输入操作数，如%0对应"+r" (add)，%1对应"m" (*value)。
## 比较-交换原子实现

现代处理器都提供了比较-交换汇编指令cmpxchgl  r, [m]，且是原子操作。其含义如下为，如果eax寄存器的内容与[m]内存地址内容相等，则设置[m]内存地址内容为r寄存器的值。伪代码如下（标志寄存器zf位）：

```LANG
if (eax == [m]) {
    zf = 1;
    [m] = r;
} else {
    zf = 0;
    eax = [m];
}
```

因此利用指令cmpxchgl可以很容易实现原子性的比较-交换功能。
但是想想这样有什么问题呢？对于单核CPU来说没任何问题，多核CPU则无法保证。（参考深入理解计算机系统第六章）以Intel Core i7处理器为例，其有四个核，且每个核都有自己的L1和L2高速缓存。

![][0] 
前面提到，主存中部分数据会被缓存在高速缓存中，CPU访问数据时会先从高速缓存中查找；那假如同一块内存地址同时被缓存在核0与核1的L2级高速缓存呢？此时如果核0与核1同时修改该地址内容，则会造成冲突。
目前处理器都提供有lock指令；其可以锁住总线，其他CPU对内存的读写请求都会被阻塞，直到锁释放；不过目前处理器都采用锁缓存替代锁总线（锁总线的开销比较大），即lock指令会锁定一个缓存行。当某个CPU发出lock信号锁定某个缓存行时，其他CPU会使它们的高速缓存该缓存行失效，同时检测是对该缓存行中数据进行了修改，如果是则会写所有已修改的数据；当某个高速缓存行被锁定时，其他CPU都无法读写该缓存行；lock后的写操作会及时会写到内存中。
以文件src/os/unix/ngx_gcc_atomic_x86.h为例。
查看ngx_atomic_cmp_set函数实现如下：

```LANG
#define NGX_SMP_LOCK  "lock;"
static ngx_inline ngx_atomic_uint_t
ngx_atomic_cmp_set(ngx_atomic_t *lock, ngx_atomic_uint_t old,
    ngx_atomic_uint_t set)
{
    u_char  res;
    __asm__ volatile (
         NGX_SMP_LOCK
    "    cmpxchgl  %3, %1;   "
    "    sete      %0;       "
    : "=a" (res) : "m" (*lock), "a" (old), "r" (set) : "cc", "memory");
    return res;
}
```

cmpxchgl即为上面说的原子比较-交换指令；sete取标志寄存器中ZF位的值，并存储在%0对应的操作数。函数最后返回标志寄存器zf位。
累加指令格式为xaddl r [m]，含义如下：

```LANG
temp = [m];
[m] += r;
r = temp;
```

查看ngx_atomic_fetch_add函数实现：

```LANG
static ngx_inline ngx_atomic_int_t
ngx_atomic_fetch_add(ngx_atomic_t *value, ngx_atomic_int_t add)
{
    __asm__ volatile (
         NGX_SMP_LOCK
    "    xaddl  %0, %1;   "
    : "+r" (add) : "m" (*value) : "cc", "memory");
    return add;
}
```

指令xaddl实现了加法功能，其将%0对应操作数加到%1对应操作数，函数最后返回累加之前的旧值。
这里再回到第一小节，cl取值128字节，且注释表明cl一定要大于等于cache_line_size。cl是什么？三个共享变量之间的偏移量。那假如去掉这个限制，由于每个变量只占8字节，所以三个变量总共占24字节，假设cache_line_size即缓存行大小为64字节，即这三个共享变量可能属于同一个缓存行。
那么当使用lock指令锁定ngx_accept_mutex->lock变量时，会锁定该变量所在的缓存行，从而导致对共享变量ngx_connection_counter和ngx_temp_number同样执行了锁定，此时其他CPU是无法访问这两个共享变量的。因此这里会限制cl大于等于缓存行大小。
## 总结

本文简要介绍了nginx中锁的实现原理，多核高速缓存冲突问题，内联汇编简单语法，以及原子比较-交换操作和原子累加操作的实现。
才疏学浅，如有错误或者不足，请指出。

[0]: ./img/1460000017005120.png