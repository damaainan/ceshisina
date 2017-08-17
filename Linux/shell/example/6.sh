#!/bin/bash

# for循环
for filename in t1 t2 t3
do
  touch $filename.txt
  echo "Create new file: $filename.txt"
done

for rmfile in *.txt; do rm $rmfile; echo "Delete $rmfile!"; done; # 写成一行的方式

for filelist in `ls /root` # 循环显示/root目录下的文件及目录
do
  echo $filelist
done


# while循环
num=0
while ((num<3)) # ((expression))结构，整数型的扩展计算
do
    ((num++))
    echo "while : num=$num"
done


# until循环
i=3
until ((i==0))
do
    ((i--))
    echo "until : i=$i"
done





#   ### for循环
#   - 使用变量名获取列表中的变量当前取值；
#   - in列表是可选的，默认为“in "$@"”, 即执行时传入的参数列表；
#   
#   
#   ### while循环
#   - 连续执行一系列命令，直到条件为假时停止；
#   - 可用于从输入中读取数据等；
#   
#   
#   ### until循环
#   - 与while循环在处理方式上刚好相反，连续执行一系列命令，直到条件为真时停止；
#   - 一般while循环优于until循环，极少数情况下使用until循环；
#   
#   
#   ### 跳出循环
#   - break跳出循环； 
#   - continue跳出本次循环；
#   
#   
#   ### 无限循环（死循环）
#   将while循环的条件设置为“true”或“:”;