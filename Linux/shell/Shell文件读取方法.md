# [Shell文件读取方法集锦][0] 

 2016-08-18 15:37  119人阅读 

 分类：

**1 ，在这里总结一下Shell 读取文件的方法**

```
a),  
#使用read命令读取一行数据  
while read myline  
do  
    echo "LINE:"$myline  
done < datafile.txt  
  
b),  
#使用read命令读取一行数据  
cat datafile.txt | while read myline  
do   
    echo "LINE:"$myline  
Done  
  
c),  
#读取一行数据  
cat datafile.txt | while myline=$(line)  
do   
    echo "LINE:"$myline  
Done  
  
d),  
#读取一行数据  
while myline=$(line)  
do   
    echo "LINE:"$myline  
done < datafile.txt  
  
e),  
#使用read命令读取变量数据  
cat datafile.txt | while read paraa parab parac  
do  
    echo "PARAA:"$paraa  
    echo "PARAB:"$parab  
    echo "PARAC:"$parac  
Done  
  
f),  
#使用read命令读取变量数据  
while read paraa parab parac  
do  
    echo "PARAA:"$paraa  
    echo "PARAB:"$parab  
    echo "PARAC:"$parac  
done < datafile.txt  
```

G),

下面这个是在学习公司的代码时碰到的，其实不能算是读取文件，应该算是从标准输入读取，代码如下：

```
#!/bin/sh   
  
ip=192.168.253.111  
while read line <&3 ; do  
        echo "  attempt with ($line)"  
        # Try to connect and exit when done if it worked.  
        $line && exit 0  
done 3<<EOF  
/usr/bin/rlogin -l snap-admin $ip  
/usr/bin/ssh dev@$ip  
/usr/bin/ssh snap-admin@$ip  
/usr/bin/ssh root@$ip  
EOF  
```

网上有解释说下面这个格式 :

<<EOF

（内容）

EOF

把 EOF 替换成其他东西

意思是把内容当作标准输入传给程序

在这个例子中这么写 3<<EOF 应该是把它重定向到一个文件描述符中，大家都知道文件描述符都是一个整形，这里的 3 就是作为一个文件描述符来用。

这里再简要回顾一下 < < 的用法。当 s h e l l 看到 < < 的时候，它就会知道下一个词是一个分界符。在该分界符以后的内容都被当作输入，直到 s h e l l 又看到该分界符 ( 位于单独的一行 ) 。这个分界符可以是你所定义的任何字符串。

下面是对常见的文件描述符命令的整理：

```
command > filename  把标准输出重定向到一个新文件中  
 command >> filename  把标准输出重定向到一个文件中(追加)  
 command 1 > filename  把标准输出重定向到一个文件中  
 command > filename 2 >&1 把标准输出和标准错误一起重定向到一个文件中  
 command 2 >filename  把标准错误重定向到一个文件中  
 command 2 >> filename  把标准错误重定向到一个文件中(追加)  
 command >> filename 2 >&1 把标准输出和标准错误一起重定向到一个文件中(追加)  
 command < filename > filename2 command命令以filename文件作为标准输入,  
      以filename2文件作为标准输出  
 command < filename  command命令以filename文件作为标准输入  
 command << delimiter  从标准输出中读入，直至遇到delimiter分界符  
 command <&m   把文件描述符m作为标准输出  
 command >&m   把标准输出重定向到文件描述符m中  
 command <&-   关闭标准输入  

```

**2 ，就read 命令的使用方法 整理如下：**

read 命令从 标准输入读取一行，并把输入行的每个字段（以指定的分隔符分隔）的值赋给命令行上的变量。 

    read [-ers] [-u fd] [-t timeout] [-p prompt] [-a array] [-n nchars] [-d delim] [name ...]  

参数解析： 

`-e`

`-r`

指定读取命令把 “ \ ” ( 反斜杠 ) 做为输入行的一个普通字符，而非控制字符。

`-s`

安静模式。如果指定该参数且从终端读入数据，那么输入的时候将不回显在屏幕上。

`-u <fd>`

指定读入数据的文件描述符，不再使用默认的标准输入。

`-t <timeout>`

等待标准输入的超时时间，单位为秒。如果在指定的时间内没有输入，即时返回。

`-p <prompt>`

打印提示符，等待输入，并将输入赋值给 REPLY 变量或者指定的变量。

`-a <array>`

读入一组词，依次赋值给数组 array 。

`-n <nchars>`

读取指定的字符数。如果已读取 n 个字符，马上返回，下次从返回点继续读取；如果已到行结束，无论满不满足 n 个字符都返回。

`-d <delim>`

指定行结束符，读到该字符就当做一行的结束。

name ...

指定 read 变量。 read 读取一行数据后，分隔行数据为各个字段，然后将字段依次赋给指定的变量。如果分隔后的字段数目比指定的变量多，那么将把剩余的全部字段值都赋给最后的那个变量；反之，剩余的变量被赋值为空字符串。如果 read 没有指定变量，系统使用默认的 REPLY 作为缺省变量名。

使用重定向读取数据 

```
exec 6< datafile.txt  
while read -u 6 myline  
do  
    echo "LINE:"$myline  
done 
```

变量分隔符   
read 命令默认的分隔符是空格，多个空格被当做一个空格处理。我们也可以使用 IFS （内部字段分隔符）指定的的字符作为分隔符。假如有如下内容的一个文件，它以 “ $ ” 来分隔变量，希望把每个变量区别开来，可以使用如下脚本：

baidu$google$tencnt$sina

123456789

```
#使用read命令读取变量数据  
while read paraa parab parac parad  
do  
    echo "PARAA:"$paraa  
    echo "PARAB:"$parab  
    echo "PARAC:"$parac  
    echo "PARAD:"$parad  
done < datafile.txt  
执行脚本的输出如下：   
PARAA:baidu  
PARAB:google  
PARAC:tencent  
PARAD:sina  
PARAA:123456789  
PARAB:  
PARAC

```

[0]: http://blog.csdn.net/s1070/article/details/52241511
[1]: http://www.csdn.net/tag/shell

[6]: http://blog.csdn.net/xj178926426/article/details/6925770#