## Linux之awk数组的使用

2018.08.02 23:47

来源：[https://www.jianshu.com/p/92d93f2dfae7](https://www.jianshu.com/p/92d93f2dfae7)



* awk数组描述

在其他的编程语言中，数组的下标都是从0开始的，也就是说，如果想反向引用数组中       的第一个元素，则需要引用对应的下标[0]，在awk中数组也是通过引用下标的方法，但是在awk中数组的下标是从1开始的，在其他语言中，你可能会习惯于先“声明”一个数组，在awk中，则不用这样，直接为数组的元素赋值即可（其实如果自己给数组赋值，下标从1或者从0开始那就无所谓了！）

* 在声明数组时，可能值很多，命令太长，降低命令可读性，所以使用反斜杠“\”，来进 行换行，效果是完全一样的，代码如下所示：

```
[zkpk@master as]$ awk 'BEGIN{arr[1]="one";arr[2]="two";arr[3]="three"\
;arr[4]="four";print arr[3]}'
three
[zkpk@master as]$ awk 'BEGIN{arr[1]="one";arr[2]="two";arr[3]="three";arr[4]="four";print arr[3]}'
three

```


* 数组的元素设置为空，是允许的，当数组中没有某个元素而直接引用它的时候，它默认被赋值为空，所以判断某个元素是否存在，不能采用数组元素值为空的方法，而应该采用下面的方法：


```
[zkpk@master as]$ awk 'BEGIN{arr[1]="one";arr[2]="two";arr[3]="three";if(4 in arr){print "four in this arr"}}'
[zkpk@master as]$ awk 'BEGIN{arr[1]="one";arr[2]="two";arr[3]="three";if(3 in arr){print "three in this arr"}}'
three in this arr

```

也可以采用取反的方式（使用运算符！）

```
[zkpk@master as]$ awk 'BEGIN{arr[1]="one";arr[2]="two";arr[3]="three";if(!(4 in arr)){print "four not  in this arr"}}'
four not  in this arr

```


* awk数组下标

在awk中数组的下标不仅可以是“数字”，还可以是“任意字符串”，其实，awk中的数组本来就是“关联数组”，之所以先用数字作为下标举例子是为了方便之前的习惯，能够有个好的过渡，不过，以数字作为数组的下标在某些场景有一定的优势，但是本质上也是“关联数组”，awk默认会把“数字”下标转换成“字符串”，所以它本质上还是一个使用字符串作为下标的“关联数组”

* 删除数组元素

使用`delete`可以删除数组中的元素，也可以使用`delete`删除整个数组

```
[zkpk@master as]$ awk 'BEGIN{arr[1]="one";arr[2]="two";arr[3]="three";\
> delete arr[1];print arr[1]}'
____（空）
[zkpk@master as]$ awk 'BEGIN{arr[1]="one";arr[2]="two";arr[3]="three";\
print arr[1];print arr[3];delete arr;print arr[1]}'
one
three
____（空）

```


* 使用for循环遍历数组



语法：for（变量 in 数组名）{ 代码语句 }

注：其中变量循环的是数组的下标

```
[zkpk@master as]$ awk 'BEGIN{arr[1]="one";arr[2]="two";arr[3]="three";arr[4]="four";\
> for ( i in arr){print arr[i]}}'
four
one
two
three
#无序的打印数组元素，进一步证明其是“关联数组”

```

```
#有序的打印数组元素
[zkpk@master as]$ awk 'BEGIN{arr[1]="one";arr[2]="two";arr[3]="three";arr[4]="four";\
for ( i=1;i<=4;i++){print arr[i]}}'
one
two
three
four

```


* awk数组使用实例


```
[zkpk@master as]$ awk 'BEGIN{a=1;print a; a=a+1 ; print a}'
1
2
[zkpk@master as]$ awk 'BEGIN{a=1;print a; a++ ; print a}'
1
2

```

将变量a设置为1,对其进行自加运算,则其数值会增加1,这不难理解,那么如果变量a是一个字符串哪？

```
[zkpk@master as]$ awk 'BEGIN{a="test";print a; a++ ; print a}'
test
1

```

当a的值为字符串时,竟然也可以参与运算,而且可以看出,字符串被当成数字0参与运算,那么空字符串参与运算时也会被当成0运算吗？

```
[zkpk@master as]$ awk 'BEGIN{a="";print a; a++ ; print a}'
____（空）
1

```

结果显示,空字符串在参与运算时也会被当做数字0,之前我们说过,当我们引用数组中一个不存在的元素,元素被赋值成空字符串,当对这个元素进行自加运算时,元素的值就变成了1,因此当我们对一个不存在的元素进行自加运算后,这个元素的值就变成了自加的次数,自加x次,元素的值就被赋值为x,自加y次元素的值就被赋值为y，所以我们可以通过awk数组的这个特性来统计文本中某字符串出现的次数，代码如下所示

```
[zkpk@master as]$ cat text
Alice
Bob
Tom
Peter
Alice
Alice
Tom
Bob
Peter
Bob
[zkpk@master as]$ awk '{count[$1]++};END{for(i in count){print i,count[i]}}' text
Bob 3
Tom 2
Alice 3
Peter 2

```

这回你该发现awk数组这个特性的强大所在了吧，好，也许你会说我不用awk照样可以统计啊，代码如下所示：

```
[zkpk@master as]$ cat text | sort | uniq -c
      3 Alice
      3 Bob
      2 Peter
      2 Tom

```

好吧，我承认你这个思路很棒，但是你看看下面例子哪？统计文本中人名出现的次数

```
[zkpk@master as]$ cat -tE text
Alice^IBob$
Bob^IAlice Alice     Peter$
Tom  Bob$
Peter Alice $
Alice Tom$
Alice^I^ITom $
Tom Peter$
Bob Bob$
Peter Alice$
Bob Alice Alice    Tom$
#我们可以看出上面的文本中人名之间的分隔符有制表符，也有空格，来吧，统计人名出现的次数吧，
#我使用awk数组的方式可以这样统计
[zkpk@master as]$ awk '{for(i=1;i<=NF;i++){count[$i]++} }END{for(j in count)\
{print j , count[j]}}' text
Bob 6
Tom 5
Alice 9
Peter 4

```

但若你不用awk，非得用其他命令实现可以参考如下代码（`^_^`）

```
[zkpk@master as]$ cat text | tr -s "\t" " " | tr -s " " "\n" | sort | uniq -c
      9 Alice
      6 Bob
      4 Peter
      5 Tom

```


* 结尾

本文介绍了awk数组的基本使用方法，但是要学会灵活的运用，我在上面的示例中也写出了一些可以在某种程度上替换awk数组的方式，所以本文不单单是介绍awk数组该如何使用，而是如何在合适的场景，选择出最优的解决方案，快速高效的解决问题。这就是我一直追求的，也是我学习Linux命令的真实意图。


