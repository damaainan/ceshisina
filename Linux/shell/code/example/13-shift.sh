#!/bin/bash  

if [ -z "$1" ] # -z 字符串的长度为零 
then
  echo "No arguments!"
fi

function show_help(){
  echo "Usage:"
  echo "    ./$0 -a    Option A"
  echo "    ./$0 -b    Option B"
  echo "    ./$0 -c    Option C" 
  echo "    ./$0 -h    Show help information"
}

count=1
while [ -n "$1" ] # 判断参数是否存在；–n 字符串的长度非零 
do

  echo "第 $count 个命令行参数 : $1"  
  count=$[ $count+1 ]  

  case $1 in
    -a) echo "Perform action A" ;;
    -b) echo "Perform action B" ;;
    -c) echo "Perform action C" ;;
    -h) show_help ;;
    *)  echo "$1 is not an option!" 
        break ;;
  esac
  
  shift # 命令行参数值左移一位 
  # shift 2 # 使用"shift n"对命令行参数左移n位
  
done





#   ### 简单的命令行选项（只有选项，没有参数）
#   - 适合使用shift命令和case语句处理
#   
#   
#   ### shift命令
#   - shift命令能够改变命令行参数的相对位置；
#   - 默认将每个参数变量左移一个位置；变量$3的值移给变量$2，变量$2的值移位给变量$1，而变量$1的值被丢弃；变量$0的值和程序名称都保持不变；
#   - 特别注意：将某一位参数移位掉后，该参数值永久丢失，不可恢复；
#   - “shift n”表示移动n个位置；
#   
#   
#   ### case语句
#   - 本例中，case语句会匹配和执行所有的有效选项；
#   - 如果只想匹配和执行第一个有效选项，不使用while循环和shift命令即可；
#   - case语句的catch-all部分可以处理其他参数；