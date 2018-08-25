#!/bin/bash
#简单命令
echo $PATH > file
#管道命令
cat file|tr ':' ' '
#序列命令
IFS=':' read -a ARRAY <file && echo ${ARRAY[4]} || echo "赋值失败"
echo "命令返回值为：$?。"
#验证变量的临时作用域
echo "$IFS"|sed 'N;s/[ \t\n]/-/g'