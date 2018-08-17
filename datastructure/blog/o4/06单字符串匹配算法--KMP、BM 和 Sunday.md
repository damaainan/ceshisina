# 单字符串匹配算法--KMP、BM 和 Sunday

<font face=微软雅黑>

 时间 2016-11-13 19:19:51  是非之地

原文[http://vinoit.me/2016/11/13/single-string-match-KMP-BM-Sunday/][1]


字符串匹配(查找)算法是一类重要的字符串算法，给定一个长度为n的字符串text（文本串），要求找出text中是否存在另一个长度为m的字符串pattern（模式串）。如果使用蛮力法，那么时间复杂度为O(m*n)，只有在长度较短的场景下才能勉强使用。

本文涉及的3种算法，KMP、BM、Sunday，都是目前比较主流的单字符串匹配算法。所谓单字符串匹配，就是在一个文本串中找一个模式串，多字符串匹配便是一个文本串中找多个模式串，多字符串匹配算法中主流的有ac自动机。

这3种单字符串匹配算法的具体说明在这里就不详细展开了，网络上有很多图文并茂的教程，在这里只提一下每个算法中最核心的几点，并分别给出java实现的算法。

#### 一些名称

* text:文本串
* pattern:模式串
* i为文本串中当前比较字符的下标
* j为模式串中当前比较字符的下标

#### KMP 

KMP算法是对蛮力法的改进，在蛮力法中，i和j都是一步一步往后移的，这就会做大量无用的循环判断。在KMP中，通过提前计算出一个next数组。从左到右匹配时，当模式串中的某个字符匹配失败了，取出next数组中对应下标的值，这个值就是j的下一个值。考虑以下情况：

![][4]

此时pattern中的最后一个字符匹配失败，如果是蛮力法，那么pattern会向后移动一位。但是仔细观察pattern，就会发现有更好的移动方案：

![][5]

在pattern移动到此位置之前的所有移动都属于无用功，这正是KMP中的正确做法。

#### KMP中的一些概念

* 前缀：指除了最后一个字符以外，一个字符串的全部头部组合；
* 后缀：指除了第一个字符以外，一个字符串的全部尾部组合；
* 最大前后缀匹配：”前缀”和”后缀”的最长的共有元素的长度；

“ABCDAB”的前缀为[A, AB, ABC, ABCD, ABCDA]，后缀为[BCDAB, CDAB, DAB, AB, B]，共有元素为”AB”，长度为2；

“ABCDABD”的前缀为[A, AB, ABC, ABCD, ABCDA, ABCDAB]，后缀为[BCDABD, CDABD, DABD, ABD, BD, D]，共有元素的长度为0。

KMP中的next数组便是用来存放这个最大前后缀匹配的情况。对于给定的模式串：ABCDABD，它的最大长度表及next 数组分别如下：

![][6]

#### java实现

```java
package singleStrMatch;
/**
 * Created by vino on 2016/11/13.
 */
public class KMP {
    public static int[] getNext(String pattern) {
        int[] next = new int[pattern.length()];
        int j = 0;//模式串下标
        int k = -1;//最大前后缀匹配中前缀的下一个下标（最大前后缀匹配的长度）
        int len = pattern.length();
        next[0] = -1;
        while (j < len - 1) {//j为计算当前next值的前一个下标
            //k == -1表示0～j的字符串中没有前后缀匹配
            //如果pattern.charAt(k) == pattern.charAt(j)，最大前后缀匹配长度加1
            if (k == -1 || pattern.charAt(k) == pattern.charAt(j)) {
                j++;
                k++;
                next[j] = k;
            }
            else {//此时不存在前后缀匹配。若k>1,则p[0 ～ k-1]字符串和p[j-k ～ j-1]字符串相等。
                k = next[k];//（前缀中最大前后缀匹配）的前缀的下一个下标
            }
        }
        return next;
    }
    public static int kmp(String text, String pattern) {
        int i = 0;
        int j = 0;
        int slen = text.length();
        int plen = pattern.length();
        int[] next = getNext(pattern);
        while (i < slen && j < plen) {
            if (text.charAt(i) == pattern.charAt(j)) {
                i++;
                j++;
            }
            else {
                if (next[j] == -1) {
                    i++;
                    j = 0;
                } else {
                    j = next[j];
                }
            }
            if (j == plen) {
                return i - j;
            }
        }
        return -1;
    }
    public static void main(String[] args) {
        String text = "HERE IS A SIMPLE EXAMPLE";
        String pattern = "EXAMPLE";
        System.out.println("字符串匹配的位置为:" + kmp(text, pattern));
    }
}

```

#### BM 

KMP算法其实并不是效率最高的字符串匹配算法，实际应用的并不多，各种文本编辑器的“查找(CTRL F)”功能大多采用的是BM算法(Boyer Moore)。BM算法效率更高，更容易理解,BM算法往往比KMP算法快上3－5倍。

BM算法从尾部开始比较，如果匹配失败，此时存在2种规则：

1. 坏字符规则
    * 后移位数 = 坏字符的位置 - 模式串中的上一次出现位置值

假如在模式串中未出现，则为-1

假定文本串为”HERE IS A SIMPLE EXAMPLE”，模式串为”EXAMPLE”。

首先，”字符串”与”搜索词”头部对齐，从 尾部 开始比较。 

![][7]

S就是坏字符,移动 6-(-1) 。 

依然从尾部开始比较，发现”P”与”E”不匹配，所以”P”是”坏字符”。但是，”P”包含在搜索词”EXAMPLE”之中。所以，将搜索词后移两位，两个”P”对齐。

![][8]

移动 6-4 。 

![][9]

1. 好后缀规则
    * 后移位数 = 好后缀的位置 - 搜索词中的上一次出现位置

同样，未出现则为-1

![][10]

这个规则有三个注意点： 

* “好后缀”的位置以最后一个字符为准。假定”ABCDEF”的”EF”是好后缀，则它的位置以”F”为准，即5（从0开始计算）。
* 如果”好后缀”在搜索词中只出现一次，则它的上一次出现位置为 -1。比如，”EF”在”ABCDEF”之中只出现一次，则它的上一次出现位置为-1（即未出现）。
* 如果”好后缀”有多个，则除了最长的那个”好后缀”，其他”好后缀”的上一次出现位置必须在头部。比如，假定”BABCDAB”的”好后缀”是”DAB”、”AB”、”B”，请问这时”好后缀”的上一次出现位置是什么？回答是，此时采用的好后缀是”B”，它的上一次出现位置是头部，即第0位。这个规则也可以这样表达：如果最长的那个”好后缀”只出现一次，则可以把搜索词改写成如下形式进行位置计算”(DA)BABCDAB”，即虚拟加入最前面的”DA”。

回到上图，此时，所有的”好后缀”（MPLE、PLE、LE、E）之中，只有”E”在”EXAMPLE”还出现在头部，所以后移 6 - 0 = 6位。

![][11]

那什么时候用坏字符规则，什么时候用好后缀规则呢？Boyer-Moore算法的基本思想是，每次后移这两个规则之中的较大值。因为模式串已经确定了，所以可以预先计算生成《坏字符规则表》和《好后缀规则表》。使用时，只要查表比较一下就可以了。

#### java实现

```java
package singleStrMatch;
/**
 * Created by vino on 2016/11/13.
 */
public class BM {
    final static int CARD_CHAR_SET = 256;// 字符集规模
    /*
     * @param text 主串
     * @param pattern 模式串
     */
    public static int getMatchIndex(String text, String pattern) {
        int[] BC = BuildBC(pattern); // 坏字符表
        int[] GS = BuildGS(pattern); // 好后缀表
        // 查找匹配
        int i = 0; // 模式串相对于主串的起始位置（初始时与主串左对齐）
        while (text.length() - pattern.length() >= i) { // 在到达最右端前，不断右移模式串
            int j = pattern.length() - 1; // 从模式串最末尾的字符开始
            while (pattern.charAt(j) == text.charAt(i + j))
                if (0 > --j) // 自右向左比较
                    break;
            if (0 > j) // 若最大匹配后缀 == 整个模式串（说明已经完全匹配）
                break;
            else
                i += MAX(GS[j], j - BC[text.charAt(i + j)]);// 在位移量BC和GS之间选择大者，相应地移动模式串
        }
        return (i);
    }
    /*
     * 构造Bad Charactor Shift表BC[] - 坏字符表
     */
    protected static int[] BuildBC(String pattern) {
        int[] BC = new int[CARD_CHAR_SET]; // 初始化坏字符表
        int j;
        for (j = 0; j < CARD_CHAR_SET; j++)
            BC[j] = -1; // 首先假设该字符没有在P中出现
        for (j = 0; j < pattern.length(); j++) // 自左向右迭代：更新各字符的BC[]值
            BC[pattern.charAt(j)] = j;
        return BC;
    }
    /*
     * 构造Good Suffix Shift表GS[] - 好后缀表
     */
    protected static int[] BuildGS(String pattern) {
        int m = pattern.length();
        int[] SS = ComputeSuffixSize(pattern); // 计算各字符对应的最长匹配后缀长度
        int[] GS = new int[m]; // Good Suffix Index
        int j;
        for (j = 0; j < m; j++)
            GS[j] = m;
        int i = 0;
        for (j = m - 1; j >= -1; j--)
            if (-1 == j || j + 1 == SS[j]) // 若定义SS[-1] = 0，则可统一为：if (j+1 == SS[j])
                for (; i < m - j - 1; i++)
                    if (GS[i] == m)
                        GS[i] = m - j - 1;
        for (j = 0; j < m - 1; j++)
            GS[m - SS[j] - 1] = m - j - 1;
        return GS;
    }
    /*
     * 计算P的各前缀与P的各后缀的最大匹配长度
     */
    protected static int[] ComputeSuffixSize(String pattern) {
        int m = pattern.length();
        int[] SS = new int[m];// Suffix Size Table
        int s, t; // 子串P[s+1, ..., t]与后缀P[m+s-t, ..., m-1]匹配
        int j; // 当前字符的位置
        SS[m - 1] = m; // 对最后一个字符而言，与之匹配的最长后缀就是整个P串
        s = m - 1; // 从倒数第二个字符起，自右向左扫描P，依次计算出SS[]其余各项
        t = m - 2;
        for (j = m - 2; j >= 0; j--) {
            if ((j > s) && (j - s > SS[(m - 1 - t) + j]))
                SS[j] = SS[(m - 1 - t) + j];
            else {
                t = j; // 与后缀匹配之子串的终点，就是当前字符
                s = MIN(s, j); // 与后缀匹配之子串的起点
                while ((0 <= s) && (pattern.charAt(s) == pattern.charAt((m - 1 - t) + s)))
                    s--;
                SS[j] = t - s;// 与后缀匹配之最长子串的长度
            }
        }
        return SS;
    }
    protected static int MAX(int a, int b) {
        return (a > b) ? a : b;
    }
    protected static int MIN(int a, int b) {
        return (a < b) ? a : b;
    }
    // 测试类
    public static void main(String[] args) {
        String text = "HERE IS A SIMPLE EXAMPLE";
        String pattern = "EXAMPLE";
        System.out.println("字符串匹配的位置为: " + getMatchIndex(text, pattern));
    }
}

```

#### Sunday 

Sunday算法的思想和BM算法中的坏字符思想非常类似。差别只是在于Sunday算法在匹配失败之后，是取文本串中当前和模式串对应的部分后面一个位置的字符来做坏字符匹配。

Sunday算法是从前往后匹配，在匹配失败时关注的是主串中参加匹配的最末位字符的下一位字符。

* 如果该字符没有在模式串中出现则直接跳过，即移动位数 = 模式串长度 + 1；
* 否则，其移动位数 = 模式串长度 - 该字符最右出现的位置(以0开始) = 模式串中该字符最右出现的位置到尾部的距离 + 1。

下面举个例子说明下Sunday算法。假定现在要在主串”substring searching”中查找模式串”search”。

* 刚开始时，把模式串与文主串左边对齐：

![][12]

* 结果发现在第2个字符处发现不匹配，不匹配时关注主串中参加匹配的最末位字符的下一位字符，即标粗的字符 i，因为模式串search中并不存在i，所以模式串直接跳过一大片，向右移动位数 = 匹配串长度 + 1 = 6 + 1 = 7，从 i 之后的那个字符（即字符n）开始下一步的匹配，如下图：

![][13]

* 结果第一个字符就不匹配，再看主串中参加匹配的最末位字符的下一位字符，是’r’，它出现在模式串中的倒数第3位，于是把模式串向右移动3位（m - 3 = 6 - 3 = r 到模式串末尾的距离 + 1 = 2 + 1 =3），使两个’r’对齐，如下：

![][14]

* 匹配成功

和BM一样，Sunday也需要提前计算出一个表来获取移动的长度。

#### java实现

```java
package singleStrMatch;
import java.util.HashMap;
import java.util.Map;
/**
 * Created by vino on 2016/11/13.
 */
public class Sunday {
    /**
     * 统计pattern每个字符出现的位置 -1为没有出现在模式字符串中
     * @since: 1.0.0
     * @param pattern
     * @return
     */
    private static Map calculateCharsTable(String pattern) {
        Map badTables = new HashMap();
        // 从右到左遍历，也可以从左到右（0，plen-1)，只不过右边重复的字符会覆盖之前的index
        for (int j = pattern.length() - 1; j >= 0; j--) {
            if (!badTables.containsKey(pattern.charAt(j))) {
                // 模式字符串最右边出现的位置
                badTables.put(pattern.charAt(j), j);
            }
        }
        return badTables;
    }
    /**
     * sunday 算法
     * @since: 1.0.0
     * @param text
     * @param pattern
     */
    private static int sundaySerarch(String text, String pattern) {
        if (text == null || text.isEmpty() || pattern == null || pattern.isEmpty()) {
            return -1;
        }
        int tlen = text.length();
        int plen = pattern.length();
        if (tlen < plen) {
            return -1;
        }
        int maxCount = tlen - plen;
        Map badCharsTable = calculateCharsTable(pattern);
        int i = 0;
        int j;
        while (i <= maxCount) {
            j = 0;
            //子串比较
            while ( j< plen && text.charAt(i + j) == pattern.charAt(j)){
                j++;
            }
            //全部匹配
            if (j == plen) {
                //System.out.println(text + "=================>found:" + pattern + ",i=" + i);
                return i;
            }
            //有字符不匹配
            i += plen;
            if (i < tlen) {
                // 从下一个字符（bad char）处查找出现的位置
                Integer badCharFound = (Integer) badCharsTable.get(text.charAt(i));
                if (badCharFound == null) {
                    // 没有在模式串中找到
                    badCharFound = -1;
                }
                i -= badCharFound;
            }
        }
        // not found
         return -1;
        //System.out.println(text + "=================>not found:" + pattern);
    }
    public static void main(String[] args){
        String text = "HERE IS A SIMPLE EXAMPLE";
        String pattern = "EXAMPLE";
        System.out.println("字符串匹配的位置为:" + sundaySerarch(text,pattern));
    }
}
```

#### 小结 

3种算法的效率和时间复杂度可参照此文： [http://www.voidcn.com/blog/cy_cai/article/p-6305505.html][15]

* BM比KMP查找效率好2-6倍。
* 随着搜索字符增长，BM与KMP查找效率比差距越来越大。
* Sunday比BM查找效率又稍微好点。
* 随着搜索字符增长，BM和Sunday效率越来越突出。

参考：

* [http://blog.csdn.net/q547550831/article/details/51860017][16]
* [http://www.youzhixu.com/reading/HgSWfRL#toc--0][17]
* [http://blog.csdn.net/zdp072/article/details/13168605][18]

</font>


[1]: http://vinoit.me/2016/11/13/single-string-match-KMP-BM-Sunday/
[4]: ./img/mUNbMvv.png
[5]: ./img/BzQjE3.png
[6]: ./img/7rQZ3q2.png
[7]: ./img/qEfaiee.png
[8]: ./img/zamUzmQ.png
[9]: ./img/vq2uMjB.png
[10]: ./img/6vUfuqb.png
[11]: ./img/ZbeE7v6.png
[12]: ./img/qEvq6jE.png
[13]: ./img/zq6bMzJ.png
[14]: ./img/i2iuEbM.png
[15]: http://www.voidcn.com/blog/cy_cai/article/p-6305505.html
[16]: http://blog.csdn.net/q547550831/article/details/51860017
[17]: http://www.youzhixu.com/reading/HgSWfRL#toc--0
[18]: http://blog.csdn.net/zdp072/article/details/13168605