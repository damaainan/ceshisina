## 为什么鸟哥说 int 再怎么随机也申请不到奇数地址

来源：[https://segmentfault.com/a/1190000012576372](https://segmentfault.com/a/1190000012576372)

原文：我的个人博客 [https://mengkang.net/1046.html][4]
初中级 phper 有多久没给自己充电了呢，安利一波我的直播 [PHP 进阶之路][5]

## 鸟哥微博


![][0] 
## 为什么要字节对齐

需要字节对齐的根本原因在于CPU访问数据的效率问题。因为CPU每次都是从以 **`4字节（32位CPU）或是8字节（64位CPU）的整数倍`** 的内存地址中读进数据的。（ **`更深入的原因，谁告知下`** ），如果不对齐的话，很有可能一个4字节`int`需要分两次读取。具体演示看下面的实验。
## 数据类型自身的对齐值

按各数据类型自身大小进行对齐。 **`变量的内存地址正好位于它长度的整数倍`** 
## 实验

```c
#include <stdio.h>

int main(int argc, char const *argv[])
{   
    char a = 1; // 0x7fff5fbff77f,sizeof(a):1
    int  b = 1; // 0x7fff5fbff778,sizeof(b):4
    int  c = 1; // 0x7fff5fbff774,sizeof(c):4
    char d = 1; // 0x7fff5fbff773,sizeof(e):1
    int  e = 1; // 0x7fff5fbff76c,sizeof(f):4
    
    printf("%p,sizeof(a):%lu\n",&a,sizeof(a));
    printf("%p,sizeof(b):%lu\n",&b,sizeof(b));
    printf("%p,sizeof(c):%lu\n",&c,sizeof(c));
    printf("%p,sizeof(d):%lu\n",&d,sizeof(d));
    printf("%p,sizeof(e):%lu\n",&e,sizeof(e));

    return 0;
}
```

辅助以图片说明，该图左侧是上面代码的内存图，灰色部分表示该程序未使用的内存。右侧是在上面代码的基础上在`char a`后面声明了一个`short f`。

![][1] 

从上面的实验和图上我们可以找出以下规律：


* abcde 五个变量的内存地址从大到下依次分配的；
* 如果你细看，会发现它们的内存地址并不是紧密挨着的；
* 而且`int`类型的变量的内存地址都是偶数（ **`这也就是为什么鸟哥微博中说的不可能存在奇数的 int 变量的地址了`** ）；
* 再细看，发现 int 变量的地址都是可以被4整除，所以在栈上各变量是按各数据类型自身大小进行对齐的。
* 新增的`short f`地址也并没有紧挨着`a`，而是跟自身数据大小对齐，也就是偶数地址开始申请。
* 栈上各个变量申请的内存，返回的地址是这段连续内存的最小的地址。


反过来想，如果不对齐，比如上例中的 a,b,c 三个变量的内存地址紧挨着，而CPU每次只读取8个字节，也就是说变量 c 还有最后一个字节没有读取进来。访问数据效率就降低了。

**`栈上各个变量申请的内存，返回的地址是这段连续内存的最小的地址。这是怎么回事呢？`** 

我们还是通过实验来验证下我上面画的内存图，假如我有一个`int`变量，它的值占了满了4个字节，那么它的四个字节里是怎么存放数据的，我们用十六进制来演示`0x12345678`。


* 为什么用一个8位的十六进制来呢？因为int 4个字节，一个字节有8位，每位有0/1两个状态，那么就是2^8=256，也就是16^2。所以用了一个8位的16进制数正好可以填满一个 int 的内存。
* 为什么用12345678，纯属演示方便。


我先存了变量 b，然后以 char 指针 p 来依次访问 b 的四个字节的使用情况。

```c
#include <stdio.h>

int main(int argc, char const *argv[])
{
    char a = 1;             // 0x7fff5fbff777
    int  b = 0x12345678;    // 0x7fff5fbff770
    char c = 1;             // 0x7fff5fbff76f
    printf("%p\n",&a);
    printf("%p\n",&b);
    printf("%p\n",&c);

    char *p = (char *)&b;
    
    printf("%x %x %x %x\n", p[0],p[1],p[2],p[3]); // 78 56 34 12
    printf("%p %p %p %p\n", &p[0],&p[1],&p[2],&p[3]); // 0x7fff5fbff770 0x7fff5fbff771 0x7fff5fbff772 0x7fff5fbff773
        
    return 0;
}
```

变量 b`0x12345678`的最高位是`0x12`，最低位是`0x78`针对实验结果我又画了内存图，我们可以看到`0x12`存放在的内存地址要比`0x78`的大。


![][2] 

这里呢就必须说明下 [大小端模式][6]


* 小端法(Little-Endian)就是低位字节排放在内存的低地址端即该值的起始地址，高位字节排放在内存的高地址端。
* 大端法(Big-Endian)就是高位字节排放在内存的低地址端即该值的起始地址，低位字节排放在内存的高地址端。


所以，我当前的环境是小端序的形式。

为什么会有大端小端之分？
这个就得问硬件厂商了，都比较任性，所以历史就这样了。
## 结构体里的字节对齐

以成员中自身对齐值最大的那个值为标准。
## 实验

```c
int main(int argc, char const *argv[])
{
    struct str1{
        char a;
        short b;
        int c;
    };
    
    printf("sizeof(f):%lu\n",sizeof(struct str1));
    
    struct str2{
        char a;
        int c;
        short b;
    };
    
    printf("sizeof(g):%lu\n",sizeof(struct str2));
    
    struct str1 a;
    printf("a.a %p\n",&a.a);
    printf("a.b %p\n",&a.b);
    printf("a.c %p\n",&a.c);
    
    struct str2 b;
    printf("b.a %p\n",&b.a);
    printf("b.c %p\n",&b.c);
    printf("b.b %p\n",&b.b);

    
    return 0;
}
```

结果

``` 
sizeof(f):8
sizeof(g):12
a.a 0x7fff5fbff778
a.b 0x7fff5fbff77a
a.c 0x7fff5fbff77c
b.a 0x7fff5fbff768
b.c 0x7fff5fbff76c
b.b 0x7fff5fbff770
```
## 原理

灰色表填充用来对齐，保证最后结构体大小是最长的成员的大小的整数倍。


![][3] 
## 例外

实际工作中是否不按字节对齐的情况呢？有的，比如我们的 rpc 框架里面进行数据传输的时候，会选择设置为紧凑型，这样就可以轻松做到跨平台，跨语言了。
在网络程序中采用#pragma pack(1),即变量紧缩，不但可以减少网络流量，还可以兼容各种系统，不会因为系统对齐方式不同而导致解包错误。

实战举例 [yar_header 中使用 #pragma pack(1) 和 attribute ((packed)) 的意义][7]

[4]: https://mengkang.net/1046.html
[5]: https://segmentfault.com/ls/1650000011318558
[6]: https://baike.baidu.com/item/%E5%A4%A7%E5%B0%8F%E7%AB%AF%E6%A8%A1%E5%BC%8F/6750542
[7]: https://mengkang.net/586.html
[0]: ./img/1460000012576375.png
[1]: ./img/1460000012576376.png
[2]: ./img/1460000012576377.png
[3]: ./img/1460000012576378.png