# 介绍 

tr命令可以对来自标准输入的字符进行替换、压缩和删除。tr只能接收来自标准的输入流，不能接收参数。

语法

tr [OPTION]... SET1 [SET2]

注意：SET2是可选项

**OPTION:**

    不带参数：将SET2中的每个字符替换SET1中的每个字符，字符是顺序替换，如果SET1的字符长度大于SET2，那么将SET1中多出来的字符用SET2中的最后一个字符替换。
    -t:将SET2中的每个字符替换SET1中的每个字符，字符字符顺序1对1替换，无论SET1还是SET2哪个长，只替换对应的字符，多出的不替换。
    -c:取反操作，取数据流中SET1中指定字符的补集。
    -d:删除SET1中指定的字符，这里没有SET2
    -s:将SET1中指定的连续的连续重复的字符用单个字符替代，可以使用-s '\n'删除空行。

**字符集代码:**



    [:alnum:]：字母和数字,可以用来替代'a-zA-Z0-9' 
    [:alpha:]：字母，可以用来替代'a-zA-Z' 
    [:cntrl:]：控制（非打印）字符 
    [:digit:]：数字,可以用来替代'0-9' 
    [:graph:]：图形字符 
    [:lower:]：小写字母,可以用来替代'a-z' 
    [:print:]：可打印字符 
    [:punct:]：标点符号 
    [:space:]：空白字符 
    [:upper:]：大写字母,可以用来替代'A-Z' 
    [:xdigit:]：十六进制字符



      \\        反斜杠
      \a        终端鸣响
      \b        退格
      \f        换页
      \n        换行
      \r        回车
      \t        水平制表符
      \v        垂直制表符  
     \0        null字符



示例：

1.不带参数将SET2替换SET1替换，且SET1长度大于SET2

    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr 'abc' '12'
    11AA122BB222CC3

a被替换成1，b被替换成2，c被替换成2

2.不带参数将SET2替换SET1替换，且SET1长度小于SET2

    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr 'ab' '123'
    11AA122BB2ccCC3

a被替换成1，b被替换成2

3.-t参数

    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr -t 'abc' '12'
    11AA122BB2ccCC3
    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr -t 'ab' '123'
    11AA122BB2ccCC3

都是a被替换成1，b被替换成2

4.删除指定字符,-d

    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr -d 'a-z' 
    AA1BB2CC3
    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr -d -c 'a-z\n'  
    aabbcc

第一个是删除小写字符，第二个是删除小写字符之外的其它字符， 下面这种使用字符集的效果是一样的。

    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr -d '[:lower:]' 
    AA1BB2CC3
    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr -d -c '[:lower:]\n'
    aabbcc

5.替换连续字符，-s

    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr -s 'a-zA-Z'
    aA1bB2cC3
    [root@localhost ~]# echo "aaAA1bbBB2ccCC3" | tr -s '[:alnum:]\n'
    aA1bB2cC3

上面两种方法都是将重复的多个字符替换成单个字符

6.-c操作

    [root@localhost test]# echo "name" |tr -d -c 'a \n'
    a

上述操作是删除标准输入中除“a”，空格 "\n"之外的字符

其它用法：将null字符用换行符替代

 


    [root@localhost ~]# cat /proc/4518/environ \n
    TERM=xtermPATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/local/mysql/binPWD=/usr/local/mysqlSHLVL=2OLDPWD=/_=/usr/local/mysql/bin/mysqld_safecat: n: No such file or directory  

    [root@localhost ~]# cat /proc/4518/environ |tr  '\0' '\n'
    TERM=xterm
    PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/local/mysql/bin
    PWD=/usr/local/mysql
    SHLVL=2
    OLDPWD=/
    _=/usr/local/mysql/bin/mysqld_safe
    [root@localhost ~]# 


将一句话拆成多行。

**其它的巧妙用法：**

1.文本内容相加

    [root@localhost test]# cat test
    0 1 2 3 4
    5 6 7 8 9

test文件是由两行空格的数字组成，接下来需要将里面的数字想加

    [root@localhost test]# cat test|tr ' ' '\n'|echo $[ $( tr '\n' '+' ) 0 ]
    45

2.加密

    [root@localhost test]# echo "name" |tr 'name' 'xcbe'
    xcbe

# **总结** 

有一个误区很容易被误理解成SET1，SET2是一个字符组合，其实不是这样的；SET1和SET2里面都是值的单个字符之间的替换，比如'ab'不要把ab理解成一个组合，tr还有很多的巧妙的用法这需要多去实践。

