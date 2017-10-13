# 用sed实现wc -c的功能

 时间 2017-10-14 00:02:00  

原文[http://www.cnblogs.com/Colin-Cai/p/7663831.html][1]


sed是所谓的流编辑器，我们经常用它来做一些文本替换的事情，这是sed最擅长的事情，如`sed 's/Bob/Tom/g'`就是把文章中所有的Bob改成Tom。

sed是图灵完备的，作为sed的粉丝，喜欢用sed做各种sed不擅长的事情，这里实现一下`wc -c`的功能，也就是统计文章单词数量。

我习惯喜欢加上`n`和`r`，`n`表示每行结束时不会自动打印，`r`表示正则表达式的扩展方式，我实在很讨厌写那么多`\`，所以sed基本上我是一定加这两个东西的。

先从sed擅长的开始，先用s命令做替换，把每个单词都替换为单个1。这一步其实很简单，`s/[^ \t\r]+/1/g`即可，也就是把不是空格的连续匹配替换为1，`g`是表示对一行中所有满足这样的模式都替换为1，再考虑到正则表达式的贪婪，其实我们的`[^ \t\r]+`实际上就是指完整的一个单词，熟悉regex替换的应该不难理解。

然后为了整齐，替换为1之后，再把空格都去掉，其实也就是把不是1的去掉，那么紧接着一条`s/[^1]+//g`即可，然后再用`p`打印一下。

一口吃不成胖子，先从简单的来，我们可以看一下效果。在此之前先找篇文章，就节选一下google的pixel buds新闻吧。

    linux-p94b:/tmp/testhere # cat 1.txt
    American company Google recently announced the release of its Google Pixel 2 phone and other products that work together with the phone.
    One of the new products is a pair of wireless earphones Google calls Pixel Buds.
    The earphones are seen as the company's answer to competitor Apple's popular AirPod headphones.
    At a launch event on October 4, Google said its Pixel Buds were built to provide high-quality sound and hands-free use. All of their operations can be controlled by simply touching the right earphone.
    Once the headphones are paired with a Pixel phone, its many features can be used through the Pixel Buds.
    One example is Google Assistant, the company's artificial intelligence, or AI, service. Users can now talk directly to Pixel Buds to ask Google Assistant questions, get information or other help. This can all be done without touching the telephone.
    The Pixel Buds also can work with Google Translate, the service that provides words and expressions in over 100 languages.
    Google product manager Juston Payne demonstrated this feature during the launch event. He was able to talk with someone whose native language is Swedish.
    When the person spoke Swedish into the Pixel Buds, the phone's speakers provided the translation in English. The English speaker's response was then translated in real time into Swedish and heard through the Pixel Buds.
    linux-p94b:/tmp/testhere # cat wc-w.sed
    #!/usr/bin/sed -nrf
    s/[^ \t\r]+/1/g
    s/[^1]+//g
    p
    linux-p94b:/tmp/testhere # ./wc-w.sed <1.txt
    1111111111111111111111
    111111111111111
    11111111111111
    1111111111111111111111111111111111
    1111111111111111111
    111111111111111111111111111111111111111
    11111111111111111111
    111111111111111111111111
    11111111111111111111111111111111111

对一下，确实没有错，只是出来了一堆1，而且还是分行的，那么第二步，把这个分行给去掉。当然，加个管道，`tr -d` '\n'就去掉了，不过我们要的是单个sed解决，那么需要再动一点点脑筋。

我们可以在上面的基础上稍微改动改动，把这些1先缓存进保持空间(hold space)，最后再从保持空间中取出，然后用`s/\n//g`去掉所有的回车符，再打印。

    linux-p94b:/tmp/testhere # cat wc-w.sed
    #!/usr/bin/sed -nrf
    s/[^ \t\r]+/1/g
    s/[^1]+//g
    H
    $ {
            g
            s/\n//g
            p
    }
    linux-p94b:/tmp/testhere # ./wc-w.sed <1.txt
    111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111

`H`命令就是放在保持空间的最后，`$`是判定输入结束，`g`是用保持空间的内容替换模式空间。

上面打印出了222个1，离结果222已经很近了。

最后就是如何整合成222了，这里的确是需要一点点技巧了。我们建立以下计数方法：

1..1;1..1;1..1...

每一堆1的个数假设为n k ,n k-1 ,...,n 0

允许数量为0的堆

每一堆1之间用分号隔开，如果看到有多个分号在一起，那么中间实际上有数量为0的堆

整个计数表示的是n k *10 k +n k-1 *10 k-1 +...+n 0

很明显，我们十进制表示方法和整个很类似，只是，十进制表示里，每一堆都小于10而已。

于是我们可以创立一个算法，也就是，当我们发现一堆里有10个1，那么我们就可以往高位进1。

很容易证明这个算法可以结束。

假设{n k ,n k-1 ,...,n 0 }有限序列是非负整数num的一个表示，序列里的每一个数字是一个非负整数，最高位n k 大于0，除非num等于0。 

显然，一个具体整数的表示方法是有限的，实际上，这个k不可能大于num对10取对数，序列中的每一项不可能大于num。

序列可以比较大小，

{m j ,m j-1 ,...,m 0 }有限序列是num的另外一个表示，那么 

{n k ,n k-1 ,...,n 0 } 〉{m j ,m j-1 ,...,m 0 } 当且仅当 k > j 或者 k = j且n k =m k ...n k-p =m k-p ,n k-p-1 >m k-p-1

以上比较大小的方法可以把一个非负整数的所有表示串成一个全序集。

之前的算法中，每当升位，其表示都会变的比之前大。因为所有的表示为有限个，而最大的表示则是十进制的表示方法，从而可以知道算法是可以结束得到十进制表示的。

那么我们根据这个，不停的找10个0，每当找到，就进位，最后再把每堆挨个替换为9,8,7,6,5,4,3,2,0，再去掉分号，就完成了。有点费脑子吧，我实现一下如下:

    linux-p94b:/tmp/testhere # cat wc-w.sed
    #!/usr/bin/sed -nrf
    s/[^ \t\r]+/1/g
    s/[^1]+//g
    H
    $ {
            g
            s/\n//g
            :a
            s/;1111111111/1;/
            s/^1111111111/1;/
            ta
            s/111111111/9/g
            s/11111111/8/g
            s/1111111/7/g
            s/111111/6/g
            s/11111/5/g
            s/1111/4/g
            s/111/3/g
            s/11/2/g
            :b
            s/;;/;0;/g
            tb
            s/;$/;0/
            s/;//g
            /^$/s/^/0/
            p
    }
    linux-p94b:/tmp/testhere # ./wc-w.sed <1.txt
    222


[1]: http://www.cnblogs.com/Colin-Cai/p/7663831.html
