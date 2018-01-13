# [经典算法题每日演练——第七题 KMP算法][0]  
在大学的时候，应该在数据结构里面都看过kmp算法吧，不知道有多少老师对该算法是一笔带过的，至少我们以前是的，

确实kmp算法还是有点饶人的，如果说红黑树是变态级的，那么kmp算法比红黑树还要变态，很抱歉，每次打kmp的时候，输

入法总是提示“看毛片”三个字,嘿嘿，就叫“看毛片算法”吧。

一：BF算法

如果让你写字符串的模式匹配，你可能会很快的写出朴素的bf算法，至少问题是解决了，我想大家很清楚的知道它的时间复

杂度为O（MN），原因很简单，主串和模式串失配的时候，我们总是将模式串的第一位与主串的下一个字符进行比较，所以复杂

度高在主串每次失配的时候都要回溯，图我就省略了。

二：KMP算法

刚才我们也说了，主串每次都要回溯，从而提高了时间复杂度，那么能不能在“主串”和“模式串”失配的情况下，主串不回溯呢？

而是让”模式串“向右滑动一定的距离，对上号后继续进行下一轮的匹配，从而做到时间复杂度为O（M+N）呢？所以kmp算法就是

用来处理这件事情的，下面我们看下简单的例子。

![][1]

通过这张图，我们来讨论下它的一般推理，假设主串为S，模式串为P，在Si != Pj的时候，我们可以看到满足如下关系式

Si-jSi-j+1...Sn-1=P0P1..Pj-1。那么模式P应该向右滑动多少距离？也就是主串中的第i个字符应与模式串中的哪一个字符进行比较？

假设应该与模式串中的第k的位置相比较，假如模式串中存在最大的前缀真子串和后缀真子串，那么有P0P1..Pk-1=Pj-kPj-k+1...Pj-1.

这句话的意思也就是说，在模式P中，前k个字符与j个字符之前的k个字符相同，比如说：“abad”的最大前缀真子串为“aba"，最大

后缀真子串为“bad”，当然这里是不相等，这里的0< k< j，我们希望k接近于j，那么我们滑动的距离将会最小，好吧，现在我们用

next[j]来记录失配时模式串应该用哪一个字符于Si进行比较。

设 next[j]=k。根据公式我们有

-1 当j=0时

next[j] = max{k| 0< k< j 且 P0P1..Pk-1=Pj-kPj-k+1...Pj-1}

0 其他情况

好，接下来的问题就是如何求出next[j],这个也就是kmp思想的核心，对于next[j]的求法，我们采用递推法，现在我们知道了

next[j]=k，我们来求next[j+1]=？的问题？其实也就是两种情况：

①：Pk=Pj 时 则P0P1...Pk=Pj-kPj-k+1...Pj, 则我们知： 

next[j+1]=k+1。

又因为next[j]=k，则

next[j+1]=next[j]+1。

②：Pk!=Pj 时 则P0P1...Pk!=Pj-kPj-k+1...Pj,这种情况我们有点蛋疼，其实这里我们又将模式串的匹配问题转化为了上面我们提到

的”主串“和”模式串“中寻找next的问题，你可以理解成在模式串的前缀串和后缀串中寻找next[j]的问题。现在我们的思路就是一定

要找到这个k2，使得Pk2=Pj，然后将k2代入①就可以了。

设 k2=next[k]。 则有P0P1...Pk2-1=Pj-k2Pj-k2+1...Pj-1。

若 Pj=Pk2， 则 next[j+1]=k2+1=next[k]+1。

若 Pj!=Pk2, 则可以继续像上面递归的使用next，直到不存在k2为止。

好，下面我们上代码，可能有点绕，不管你懂没懂，反正我懂了。


```csharp
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace SupportCenter.Test
{
    public class Program
    {
        static void Main(string[] args)
        {
            string zstr = "ababcabababdc";

            string mstr = "babdc";

            var index = KMP(zstr, mstr);

            if (index == -1)
                Console.WriteLine("没有匹配的字符串！");
            else
                Console.WriteLine("哈哈，找到字符啦，位置为：" + index);

            Console.Read();
        }

        static int KMP(string bigstr, string smallstr)
        {
            int i = 0;
            int j = 0;

            //计算“前缀串”和“后缀串“的next
            int[] next = GetNextVal(smallstr);

            while (i < bigstr.Length && j < smallstr.Length)
            {
                if (j == -1 || bigstr[i] == smallstr[j])
                {
                    i++;
                    j++;
                }
                else
                {
                    j = next[j];
                }
            }

            if (j == smallstr.Length)
                return i - smallstr.Length;

            return -1;
        }

        /// <summary>
        /// p0,p1....pk-1         （前缀串）
        /// pj-k,pj-k+1....pj-1   （后缀串)
        /// </summary>
        /// <param name="match"></param>
        /// <returns></returns>
        static int[] GetNextVal(string smallstr)
        {
            //前缀串起始位置("-1"是方便计算）
            int k = -1;

            //后缀串起始位置（"-1"是方便计算）
            int j = 0;

            int[] next = new int[smallstr.Length];

            //根据公式： j=0时，next[j]=-1
            next[j] = -1;

            while (j < smallstr.Length - 1)
            {
                if (k == -1 || smallstr[k] == smallstr[j])
                {
                    //pk=pj的情况: next[j+1]=k+1 => next[j+1]=next[j]+1
                    next[++j] = ++k;
                }
                else
                {
                    //pk != pj 的情况:我们递推 k=next[k];
                    //要么找到，要么k=-1中止
                    k = next[k];
                }
            }

            return next;
        }
    }
}
```


![][2]

[0]: http://www.cnblogs.com/huangxincheng/archive/2012/12/01/2796993.html
[1]: ./img/2012120100515110.png
[2]: ./img/2012120101431897.png