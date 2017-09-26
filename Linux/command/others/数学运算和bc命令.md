# [SHELL脚本--数学运算和bc命令][0]

- - -

**本文目录：**

[1.6.1 基本整数运算][1]

[1.6.2 bc命令高级算术运算][2]

- - -

使用`let`、`(())`、`$(())`或`$[]`进行基本的整数运算，使用bc进行高级的运算，包括小数运算。其中expr命令也能进行整数运算，还能判断参数是否为整数，具体用法见 [expr命令全解][3] 。

其中let和(())几乎完全等价，除了做数学运算，还支持数学表达式判断，例如数值变量a是否等于3：let a==3或((a==3))，但一般不会使用它们来判断，而是使用test命令结合条件表达式：test "$a" -eq 3。因此，本文只介绍let的赋值运算功能。

## 1.6.1 基本整数运算

    [root@xuexi tmp]# str=10
    [root@xuexi tmp]# let str=str+6  # 等价于let str+=6
    [root@xuexi tmp]# let str-=5     # 等价于let str=str-5
    [root@xuexi tmp]# echo $str
    11

**let也可以使用(( ))进行替换，它们几乎完全等价。且额外的功能是：如果最后一个算术表达式结果为0，则返回状态码1，其余时候返回状态码0。**

如果想在命令行中做计算，则可以使用`$(())`或`$[]`。

    [root@xuexi ~]# str=10
    [root@xuexi ~]# echo $((str+=6))
    16
    
    [root@xuexi ~]# echo $[str=str-6]
    10

当然，在为变量赋算术值的时候也可以使用`$(())`和`$[]`。

    [root@xuexi ~]# str=10
    [root@xuexi ~]# str=$((str+=6));echo $str
    16
    
    [root@xuexi ~]# str=$[str-=6];echo $str
    10

其实，在算数计算过程中，等号右边的变量是可以带上`$`符号的，但等号左边的变量不允许带上`$`符号，因为它是要操作的变量，不是引用变量。例如：

    [root@xuexi ~]# let str=$str-1         # 等价于let str=str-1
    [root@xuexi ~]# str=$(($str-1))        # 等价于str=$((str-1))
    [root@xuexi ~]# srt=$[$str-1]          # 等价于str=$[str-1]
    [root@xuexi ~]# echo $((str=$str-1))   # 等价于echo $((str=str-1))，但不能写成echo $(($str=str-1))
    [root@xuexi ~]# echo $[str=$str-1]     # 等价于echo $[str=str-1]，但不能写成echo $[$str=str-1]

还可以自增、自减运算。"++"和"--"表示变量自动加1和减1。但是位置不同，返回的结果是不同的。

> x++：先返回结果，再加1

> ++x：先加1再返回结果

> x--：先返回结果，再减1

> --x：先减1再返回结果

假如x的初始值为10，则`echo $[x++]`将显示10，但在显示完后(即返回结果之后)，x的值已经变成了11，再执行echo $x将返回11。

    [root@xuexi ~]# x=10;echo $((x++));echo $x
    10
    11

如果此时再echo $[x++]仍将返回11，但此时x已经是12了。

    [root@xuexi ~]# echo $((x++));echo $x
    11
    12

再将x变量的初始值初始化为10，则`echo $[++x]`将显示11，因为先加1后再赋值给x，echo再显示x的值。++x完全等价于x=x+1，它们都是先加1后赋值。

    [root@xuexi ~]# x=10;echo $((++x));echo $x
    11
    11

同理自减也是一样的。

因此，在使用自增或自减进行变量赋值时，需要注意所赋的值是否加减立即生效的。例如：

    [root@xuexi ~]# x=10;y=$((x++));echo $y;echo $y
    10
    10

因为`y=$((x++))`赋给y的值是加1前的值，虽然赋值结束后，`$((x++))`已经变成11，但这和y无关。

所以，对于自增自减类的变量赋值应该使用先计算再显示的"++x"或"--x"的方式。

    [root@xuexi ~]# x=10;y=$((++x));echo $y;echo $y
    11
    11

总结下数值变量的赋值运算的方法：

> let i=i-1

> let i=$i-1

> let i-=1

> i=$((i-1))

> i=$(($i-1))

> i=$[ i - 1 ]

> i=$[ $i - 1 ]

> echo $((i=i-1))

> echo $((i=$i-1))

除了变量可以数学运算，数组也一样支持数学运算，且和变量一样，都能支持自增和自减等操作。其实数组其实质就是变量，只不过变量在内存中是离散的空间，而数组在内存中是顺序的空间。

例如，数组arr_test[a]=10，则：

> let arr_test[a]=${arr_test[0]} - 1

> let arr_test[a]-=1

> echo $((arr_test[a]++))

> echo $[ arr_test[a]++ ]

其它运算方法都类似，就不赘述了。其实和变量相比，只不过变量名改成arr_test[a]，引用数组变量时改为${arr_test[a]}。

## 1.6.2 bc命令高级算术运算

bc可用于浮点数的计算，是linux中的计算器。

以下是一个基本的功能示例：

    [root@node1 ~]# bc
    b 1.06.95          # 首先输出bc的版本信息，可以使用-q选项不输出头部信息
    Copyright 1991-1994, 197, 1998, 2000, 2004, 2006 Free Software Foundation, Inc.
    This is free software with ABSOLUTELY NO WARRANTY.
    For details type `warranty'.
    pie=3.1415   # 可以变量赋值
    pie*3*3      # 运算时不需要空格
    28.2735
    r=3
    pie*r*r
    28.2735
    pie*r^2      # 可以使用幂次方
    28.2735
    r=3 /* 将半径设置为3 */  # 还可以使用C语言风格的注释


输入quit命令可以退出bc计算器。

还支持自增和自减的功能。

    [root@node1 ~]# bc -q
    r=3
    r++
    3
    r++
    4
    ++r
    6
    ++r
    7
    --r
    6


bc运算器有一个内建的变量scale，用于表示计算的精度，默认精度为0，所以除法运算的默认结果是整数。

    13/(1+3)
    3
    scale=3
    13/(1+3)
    3.250

更人性化的功能是可以通过命令替换来实现批处理模式的计算。

它的一般格式参考如下：

> var=`echo "option1;option2;...;expression"|bc`

其中options部分一般设置精度scale，和变量赋值，expression部分是计算表达式，最后将它们放在反引号中赋值给变量。如：

    [root@node1 ~]# area=`echo "scale=2;r=3;3.1415*r*r"|bc`
    
    [root@xuexi ~]# echo $area
    28.2735

由于是在命令行中指定，所以这样的使用方式限制较多。bc接受使用here string和here document的方式接收参数。最常做法是将它们放置于脚本中。

    #!/bin/bash
    # script for calculate something
     
    var1=haha
    var2=hehe
     
    value=`bc<<EOF  # 在反引号中使用here string的方式
    scale=3
    r=3
    3.1415*r*r
    EOF`
    echo $value


以下是计算1+2+...+10的几种不同方式，要求输出在屏幕上的结果为"1+2+3+4+5+6+7+8+9+10=计算结果"，这是非常不错的例子。

    [root@node1 tmp]# echo $(seq -s "+" 10)=`seq -s "+" 10|bc`
    1+2+3+4+5+6+7+8+9+10=55

    [root@node1 tmp]# echo $(seq -s "+" 10)=$((`seq -s "+" 10`))
    1+2+3+4+5+6+7+8+9+10=55

    [root@node1 tmp]# echo $(seq -s "+" 10)=$(seq -s " + " 10|xargs expr)  # 注意"+"和" + "
    1+2+3+4+5+6+7+8+9+10=55

[0]: http://www.cnblogs.com/f-ck-need-u/p/7231870.html
[1]: #blog161
[2]: #blog162
[3]: http://www.cnblogs.com/f-ck-need-u/p/7231832.html