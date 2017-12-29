#!/bin/bash

if [ -n "$1" ];then # 验证参数是否传入
  echo "The first parameter is ${1}."
else
  echo "No arguments!"
fi

echo '$0 当前shell脚本的名称:' $0
echo '$0 当前shell脚本的PID:' $$
echo '$* 当前shell脚本的所有参数:' $*
echo '$@ 当前shell脚本的所有参数:' $@
echo '$# 当前shell脚本的参数个数:' $#

for param in "$*" # 遍历$*
do
  echo "\$* Parameters : $param"
done

for param in "$@" # 遍历$@
do
  echo "\$@ Parameters : $param"
done



### 依次读取当前shell脚本的所有输入参数
while [ -n "$1" ] 
do
  echo "参数为：$1, 参数个数为：$#"
  shift # shift命令将参数变量左移一个位置
done





#   ### 执行脚本时，通过对应的位置参数和特殊变量来完成输入；
#   $0  当前shell脚本的名称
#   $n  当前shell脚本的第n个参数,$1是第一个参数，$2是第二个参数，${10}是第十个参数
#   $*  当前shell脚本的所有参数(不包括脚本本身),将所有参数当作一个字符串整体
#   $@  当前shell脚本的所有参数(不包括脚本本身),循环遍历所有参数  
#   $#  当前shell脚本的参数个数(不包括脚本本身)
#   $$  当前shell脚本的PID
#   $?  最后一个指令的返回值(退出状态)；0表示没有错误，非0表示有错误
#   
#   
#   ### 移动参数变量
#   - shift命令将移动命令行参数变量左移一个位置，$2变量的值将会移动到$1，而$1变量的值将会被删除，且不能恢复；但是$0变量的值不变；
#   - “shift n”表示移动n个位置；