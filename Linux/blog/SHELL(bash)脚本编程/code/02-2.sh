#!/bin/bash
# (list)
(ls|wc -l)
#命令替换并赋值给数组 注意区分数组赋值array=(...)和命令替换$(...)
array=($(seq 10 10 $(ls|wc -l) | sed -z 's/\n/ /g'))
#数组取值
echo "${array[*]}"
# { list; }
#将文件file1中的第一行写入file2，{ list; } 是一个整体。
{ read line;echo $line;} >file2 <file1
#数学扩展
A=$(wc -c file2 |cut -b1)
#此时变量A的值为5
B=4
echo $((A+B))
echo $(((A*B)**2))
#赋值并输出
echo $((A|=$B))
#条件运算符 此命令意为：判断表达式A>=7是否为真，如果为真则计算A-1，否则计算(B<<1)+3。然后将返回结果与A作异或运算并赋值给A。
((A^=A>=7?A-1:(B<<1)+3))
echo $A