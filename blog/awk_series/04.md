[awk 字符串连接操作(字符串转数字，数字转字符串）][0]

awk中数据类型，是不需要定义，自适应的。 有时候需要强制转换。我们可以通过下面操作完成。

**一、awk字符串转数字**

    [chengmo@centos5 ~]$ awk 'BEGIN{a="100";b="10test10";print (a+b+0);}'   
    110 

只需要将变量通过”+”连接运算。自动强制将字符串转为整型。非数字变成0，发现第一个非数字字符，后面自动忽略。 

**二、awk数字转为字符串**

    [chengmo@centos5 ~]$ awk 'BEGIN{a=100;b=100;c=(a""b);print c}'   
    100100 

只需要将变量与””符号连接起来运算即可。 

**三、awk字符串连接操作**

    [chengmo@centos5 ~]$ awk 'BEGIN{a="a";b="b";c=(a""b);print c}'   
    ab 

    [chengmo@centos5 ~]$ awk 'BEGIN{a="a";b="b";c=(a+b);print c}'   
    0 

字符串连接操作通”二“，”+”号操作符。模式强制将左右2边的值转为 数字类型。然后进行操作。

[0]: http://www.cnblogs.com/chengmo/archive/2010/10/09/1846639.html