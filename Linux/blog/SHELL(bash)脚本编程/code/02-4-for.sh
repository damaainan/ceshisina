#!/bin/bash
# word举例
for i in ${a:=3} $(head -1 /etc/passwd) $((a+=2))
do
    echo -n "$i "
done
echo $a
# 省略 in word
declare -a array
for number
do
    array+=($number)
done
echo ${array[@]}
# 数学表达式格式
for((i=0;i<${#array[*]};i++))
do
    echo -n "${array[$i]} "|sed 'y/1234567890/abcdefghij/'
done;echo