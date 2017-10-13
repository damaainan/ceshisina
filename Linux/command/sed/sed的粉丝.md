## [sed的粉丝][0]


```
版权申明：本文为博主窗户(Colin Cai)原创，欢迎转帖。如要转贴，必须注明原文网址

http://www.cnblogs.com/Colin-Cai/p/7222794.html

作者：窗户

QQ：6679072

E-mail：6679072@qq.com 
```
UNIX/LINUX下有个工具叫sed，起源于ed命令，但没有人机交互，完全是脚本语言。sed虽然是结构化的程序，但其虚拟出来的机器与我们实际机器相差甚远，依靠模式空间和保留空间的交替使用、正则表达式不断替换达到处理的目的。

sed有相当一部分粉丝，就如同lisp那样，因为与众不同，而用sed写出sed不擅长的事情是粉丝的追求，似乎这种方式很有黑客精神的感觉，其实很屌丝。

[http://sed.sourceforge.net/][2]

这个网址叫the sed $HOME，里面汇聚了很多精英脚本，一个个神一样的sed脚本啊。

[http://sed.sourceforge.net/grabbag/scripts/dc.sed][3]

the sed $HOME 里面的上面这个脚本，被我们当成是sed的终极脚本，脚本十分诡异，实现了一个dc计算器(UNIX下的一个基于逆波兰式的任意精度计算器)，我曾试图读懂它，但不得不说，sed程序的确不是拿来给人读的。

我也曾经是sed粉丝中的一员，无论什么样的文本处理，我都希望尝试着用sed去写。

翻以前在论坛里的帖子，我曾经出过一道用sed题目：

    得出一行中最大的数
    比如
    00123xdsd0176ddsdw201eew
    得出201 19
    19为其位置

sed里面没有任何直接的数学运算，此类问题都需要给诡异的技巧。我给了一个解答如下：


    #!/usr/bin/sed -rnf
    /[0-9]/!d
    s/[^0-9]/ /g
    s/$/ 0123456789/
    tloop
    :loop
    s/^( *)([0-9]+)( +)([0-9]+)( .*0123456789)$/\1b\2e\3b\4e\5/
    tmain
    
    h
    s/[0-9].*/ /
    s/./1/g
    :cnt
    s/(^|;)1111111111/1;/g
    tcnt
    s/111111111/9/g
    s/11111111/8/g
    s/1111111/7/g
    s/111111/6/g
    s/11111/5/g
    s/1111/4/g
    s/111/3/g
    s/11/2/g
    :zero
    s/;;/;0;/g
    tzero
    s/(^$)|;$/0/
    s/;//g
    G
    tend
    :end
    s/(.*)\n *([0-9]+).*0123456789$/\2 \1/
    tend2
    d
    :end2
    p
    d
    
    :main
    s/b(0+)/\1b/g
    h
    :a
    s/(bf*)[0-9]/\1f/
    ta
    /(bf*)e.*\1f/ {
            :e
            g
            :b
            s/[0-9]([0-9]*b.*b)/ \1/
            tb
            :B
            s/(b *)[0-9](.*b)/\1 \2/
            tB
            s/[be]//g
            tloop
    }
    s/(bf*e)(.*)(bf*e)/\3\1/
    /(bf*)e.*\1f/ {
            :f
            g
            :c
            s/(e.* )[0-9]([0-9]*b)/\1 \2/
            tc
            :C
            s/(b *)[0-9]([^b]+)$/\1 \2/
            tC
            s/[be]//g
            tloop
    }
    g
    :d
    /b(.).*b\1/! {
            /b(.).*b(.).*\1[0-9]*\2[0-9]*$/be
            bf
    }
    
    s/b([0-9])(.*)b([0-9])/\1b\2\3b/
    td
    be


看了看，几年之前的代码居然也大致看懂了。随着论坛的衰落，现在没有这个心境写sed了，突然有些怀念以前。

[0]: http://www.cnblogs.com/Colin-Cai/p/7222794.html
[1]: #
[2]: http://sed.sourceforge.net/
[3]: http://sed.sourceforge.net/grabbag/scripts/dc.sed