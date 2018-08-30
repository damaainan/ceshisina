## shell重温---基础篇（shell数组&amp;数组操作）

来源：[http://blog.csdn.net/luyaran/article/details/79360914](http://blog.csdn.net/luyaran/article/details/79360914)

时间 2018-02-24 14:45:57


上篇博客已经分析重温了shell的运行方式以及其中的变量还有字符串，之后按照套路就是数组方面了，废话不多说，直接进入正题哈。（小白笔记，各位看官勿喷。。。）

bash shell呢，支持一位数组，不支持多维数组，并且没有限定数组的大小，类似于C语言，元素的下标由0开始编号，下表可以是整数或者算术表达式，其值应大于或者等于0，在shell中用括号来表示数组，数组中的元素用空格来分割开来。定义数组的一般形式为：

```sh
数组名=(值1 值2 ... 值n)
```

例如：

```sh
array_name=(value0 value1 value2 value3)
```

或者：

```sh
array_name=(
value0
value1
value2
value3
)
```

还可以单独定义数组中的各个元素：

```sh
array_name[0]=value0
array_name[1]=value1
array_name[n]=valuen
```

定义晚了数组之后就要开始使用了，首先就是读取：

```sh
${数组名[下标]}
```

例如：

```sh
valuen=${array_name[n]}
```

使用@符号可以获取数组中的所有元素，例如：

```sh
echo ${array_name[@]}
```

还有就是获取我们定义的数组的长度了：

```sh
# 取得数组元素的个数
length=${#array_name[@]}
# 或者
length=${#array_name[*]}
# 取得数组单个元素的长度
lengthn=${#array_name[n]}
```

好了，现在数组也定义了，长度也能获取了，接下来，理所应当必须是各种循环来处理数组啊。。。

先来一个if判断的语法格式开开胃：

```sh
if condition
then
    command1 
    command2
    ...
    commandN
else
    command
fi
```

既然都有if判断了，那么elseif也同样：

```sh
if condition1
then
    command1
elif condition2 
then 
    command2
else
    commandN
fi
```

下面就给大家来个实例了：

```sh
a=10
b=20
if [ $a == $b ]
then
   echo "a 等于 b"
elif [ $a -gt $b ]
then
   echo "a 大于 b"
elif [ $a -lt $b ]
then
   echo "a 小于 b"
else
   echo "没有符合的条件"
fi
```

输出的结果为：

```sh
a 小于 b
```

既然都说到这里了，再跟大家介绍一个比较实用的，test（用于检测某个条件是否成立，可以进行数值，字符和文件三个方面的检测，后文会有详解）:

```sh
num1=$[2*3]
num2=$[1+5]
if test $[num1] -eq $[num2]
then
    echo '两个数字相等!'
else
    echo '两个数字不相等!'
fi

#输出结果：两个数字相等

```

接下来就是重头戏了，for循环和while循环：

```sh
for var in item1 item2 ... itemN
do
    command1
    command2
    ...
    commandN
done
```

写成一行就是：

```sh
for var in item1 item2 ... itemN; do command1; command2… done;
```

下面来一个实例哈：

```sh
for loop in 1 2 3 4 5
do
    echo "The value is: $loop"
done
```

输出的结果就是：

```sh
The value is: 1
The value is: 2
The value is: 3
The value is: 4
The value is: 5
```

for循环还可以顺序输出字符串来着：

```sh
for str in 'This is a string'
do
    echo $str
done
```

结果就是：

```sh
This is a string
```

然后呢就是while循环的格式了：

```sh
while condition
do
    command
done
```

看一下实例哈（其中使用了let命令，它用于执行一个或者说多个表达式，变量计算中不需要加上$来表示变量）：

```sh
#!/bin/sh
int=1
while(( $int<=5 ))
do
    echo $int
    let "int++"
done
```

这就是结果输出：

while呢，还可以用于读取键盘信息，下面这个实例中，输入信息设定为变量FILM，按下Ctrl+d键结束：

```sh
echo '按下 <CTRL-D> 退出'
echo -n '输入你最喜欢的网站名: '
while read FILM
do
    echo "是的！$FILM 是一个好网站"
done
```

输出的结果为：

```sh
按下 <CTRL-D> 退出
输入你最喜欢的网站名:朋恋冉曲
是的！朋恋冉曲 是一个好网站
```

再来的话就是无限循环了：

```sh
while :
do
    command
done
```

或者嘞：

```sh
while true
do
    command
done
```

还有就是：

```
for (( ; ; ))
```

然后呢就是until循环了：

官方解释就是：

until循环执行一系列命令直至条件为真时停止。

until循环与while循环在处理方式上刚好相反。

一般while循环优于until循环，但在某些时候—也只是极少数情况下，until循环更加有用。

条件可为任意测试条件，测试发生在循环末尾，因此循环至少执行一次—请注意这一点。

语法格式就是：

```sh
until condition
do
    command
done

```

有了循环，我们当然要结束这个循环了，php中是break和continue，在shell中也是一样，接下来实例附上：

```sh
#!/bin/bash
while :
do
    echo -n "输入 1 到 5 之间的数字:"
    read aNum
    case $aNum in
        1|2|3|4|5) echo "你输入的数字为 $aNum!"
        ;;
        *) echo "你输入的数字不是 1 到 5 之间的! 游戏结束"
            break
        ;;
    esac
done
```

执行以上代码，输出的结果为：

```sh
输入 1 到 5 之间的数字:3
你输入的数字为 3!
输入 1 到 5 之间的数字:7
你输入的数字不是 1 到 5 之间的! 游戏结束

```

```sh
#!/bin/bash
while :
do
    echo -n "输入 1 到 5 之间的数字: "
    read aNum
    case $aNum in
        1|2|3|4|5) echo "你输入的数字为 $aNum!"
        ;;
        *) echo "你输入的数字不是 1 到 5 之间的!"
            continue
            echo "游戏结束"
        ;;
    esac
done
```

运行代码你就会发现，当输入大于5的数字时，循环不会结束，语句echo "游戏结束"永远不会被执行。

顺道再提一个case多选择语句，它可以用来匹配一个值或者一个模式，当匹配成功，会执行相应的代码，其格式为：

```
case 值 in
模式1)
    command1
    command2
    ...
    commandN
    ;;
模式2）
    command1
    command2
    ...
    commandN
    ;;
esac
```

下面实例是提示输入一到四，于每一种模式进行匹配：

```sh
echo '输入 1 到 4 之间的数字:'
echo '你输入的数字为:'
read aNum
case $aNum in
    1)  echo '你选择了 1'
    ;;
    2)  echo '你选择了 2'
    ;;
    3)  echo '你选择了 3'
    ;;
    4)  echo '你选择了 4'
    ;;
    *)  echo '你没有输入 1 到 4 之间的数字'
    ;;
esac
```

运行上面的代码，随着输入的值的不同，会有不同的返回值，例如：

```
输入 1 到 4 之间的数字:
你输入的数字为:
3
你选择了 3
```

好啦，今天的笔记就到这里了，以后的，会持续接上。。。

再啰嗦一下，本人纯属小白自学，各位看官千万勿喷哈。。。    

  


