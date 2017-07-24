# [经典算法题每日演练——第十一题 Bitmap算法][0] 


在所有具有性能优化的数据结构中，我想大家使用最多的就是hash表，是的，在具有定位查找上具有O(1)的常量时间，多么的简洁优美，

但是在特定的场合下：

①：对10亿个不重复的整数进行排序。

②：找出10亿个数字中重复的数字。

当然我只有普通的服务器，就算2G的内存吧，在这种场景下，我们该如何更好的挑选数据结构和算法呢？

一：问题分析

这年头，大牛们写的排序算法也就那么几个，首先我们算下放在内存中要多少G: (10亿 * 32)/(1024*1024*1024*8)=3.6G，可怜

的2G内存直接爆掉，所以各种神马的数据结构都玩不起来了，当然使用外排序还是可以解决问题的，由于要走IO所以暂时剔除，因为我们

要玩高性能，无望后我们想想可不可以在二进制位上做些手脚？ 

比如我要对{1,5,7,2}这四个byte类型的数字做排序，该怎么做呢？我们知道byte是占8个bit位，其实我们可以将数组中的值作为bit位的

key，value用”0，1“来标识该key是否出现过？下面看图：

![][1]

从图中我们精彩的看到，我们的数组值都已经作为byte中的key了，最后我只要遍历对应的bit位是否为1就可以了，那么自然就成有序数组了。

可能有人说，我增加一个13怎么办？很简单，一个字节可以存放8个数，那我只要两个byte就可以解决问题了。

![][2]

可以看出我将一个线性的数组变成了一个bit位的二维矩阵，最终我们需要的空间仅仅是:3.6G/32=0.1G即可，要注意的是bitmap排序不

是N的，而是取决于待排序数组中的最大值，在实际应用上关系也不大，比如我开10个线程去读byte数组，那么复杂度为:O(Max/10)。

二：代码

我想bitmap的思想大家都清楚了，这一次又让我们见证了二进制的魅力，当然这些移位都是位运算的工作了，熟悉了你就玩转了。

1:Clear方法（将数组的所有bit位置0）

比如要将当前4对应的bit位置0的话，只需要1左移4位取反与B[0] & 即可。

![][3]


```csharp
#region 初始化所用的bit位为0
/// <summary>
/// 初始化所用的bit位为0
/// </summary>
/// <param name="i"></param>
static void Clear(byte i)
{
    //相当于 i%8 的功能
    var shift = i & 0x07;

    //计算应该放数组的下标
    var arrindex = i >> 3;

    //则将当前byte中的指定bit位取0，&后其他对方数组bit位必然不变，这就是 1 的妙用
    var bitPos = ~(1 << shift);

    //将数组中的指定bit位置一  “& 操作”
    a[arrindex] &= (byte)(bitPos);
}
#endregion
```

2:Add方法（将bit置1操作）

同样也很简单，要将当前4对应的bit位置1的话，只需要1左移4位与B[0] | 即可。

![][4]

```csharp
#region 设置相应bit位上为1
/// <summary>
/// 设置相应bit位上为1
/// </summary>
/// <param name="i"></param>
static void Add(byte i)
{
    //相当于 i%8 的功能
    var shift = i & 0x07;

    //计算应该放数组的下标
    var arrindex = i >> 3;

    //将byte中的 1 移动到i位
    var bitPos = 1 << shift;

    //将数组中的指定bit位置一  “| 操作”
    a[arrindex] |= (byte)bitPos;
}
#endregion
```

2:Contain方法（判断当前bit位是否是1）

如果看懂了Clear和Add，我相信最后一个方法已经不成问题了。

```csharp
#region 判断当前的x在数组的位中是否存在
/// <summary>
///判断当前的x在数组的位中是否存在
/// </summary>
/// <param name="i"></param>
/// <returns></returns>
static bool Contain(byte i)
{
    var j = a[i >> 3] & (1 << (i & 0x07));

    if (j == 0)
        return false;
    return true;
}
#endregion
```

最后上总的代码：

```csharp
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Diagnostics;
using System.Threading;
using System.IO;

namespace ConsoleApplication2
{
    public class Program
    {
        static byte n = 7;

        static byte[] a;

        public static void Main()
        {
            //节省空间的做法
            a = new byte[(n >> 3) + 1];

            for (byte i = 0; i < n; i++)
                Clear(i);

            Add(4);
            Console.WriteLine("插入4成功！");

            var s = Contain(4);

            Console.WriteLine("当前是否包含4:{0}", s);

            s = Contain(5);

            Console.WriteLine("当前是否包含5:{0}", s);

            Console.Read();
        }

        #region 初始化所用的bit位为0
        /// <summary>
        /// 初始化所用的bit位为0
        /// </summary>
        /// <param name="i"></param>
        static void Clear(byte i)
        {
            //相当于 i%8 的功能
            var shift = i & 0x07;

            //计算应该放数组的下标
            var arrindex = i >> 3;

            //则将当前byte中的指定bit位取0，&后其他对方数组bit位必然不变，这就是 1 的妙用
            var bitPos = ~(1 << shift);

            //将数组中的指定bit位置一  “& 操作”
            a[arrindex] &= (byte)(bitPos);
        }
        #endregion

        #region 设置相应bit位上为1
        /// <summary>
        /// 设置相应bit位上为1
        /// </summary>
        /// <param name="i"></param>
        static void Add(byte i)
        {
            //相当于 i%8 的功能
            var shift = i & 0x07;

            //计算应该放数组的下标
            var arrindex = i >> 3;

            //将byte中的 1 移动到i位
            var bitPos = 1 << shift;

            //将数组中的指定bit位置一  “| 操作”
            a[arrindex] |= (byte)bitPos;
        }
        #endregion

        #region 判断当前的x在数组的位中是否存在
        /// <summary>
        ///判断当前的x在数组的位中是否存在
        /// </summary>
        /// <param name="i"></param>
        /// <returns></returns>
        static bool Contain(byte i)
        {
            var j = a[i >> 3] & (1 << (i & 0x07));

            if (j == 0)
                return false;
            return true;
        }
        #endregion
    }
}
```
![][7]

[0]: http://www.cnblogs.com/huangxincheng/archive/2012/12/06/2804756.html
[1]: ./img/2012120613274913.png
[2]: ./img/2012120613280963.png
[3]: ./img/2012120612430411.png
[4]: ./img/2012120714304050.jpg
[7]: ./img/2012120612514888.png