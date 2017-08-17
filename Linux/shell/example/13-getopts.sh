#!/bin/bash

function show_help(){
  echo "Usage:"
  echo "    ./$0 -a               option A"
  echo "    ./$0 -b <paramter>    option B"
  echo "    ./$0 -c <paramter>    option C" 
  echo "    ./$0 -h               Show help information"
}

if [ -z "$1" ]
then
  echo "No arguments!"
  show_help
  exit 1
fi

str="$1"
if [ "${str:0:1}" != "-" ] || [ -z "${str:1:2}" ]; then 
  echo "Invalid option!"
  exit 1
fi

while getopts "ab:c:h" opt; 
do
  case "$opt" in
    a) echo "Perform an action A"
       echo "Next option index : $OPTIND";;
    b) echo "Perform an action B with parameter value $OPTARG"
       echo "Next option index : $OPTIND";;
    c) echo "Perform an action C with parameter value $OPTARG"
       echo "Next option index : $OPTIND";;
    h) show_help;;
    \?) echo "Invalid option or parameter!"
       show_help
       exit 1;;
  esac
done





#   ### getopt和getopts
#   - getopt和getopts命令可用于解析所有命令行参数，能够使Shell脚本实现选项列表的功能；
#   - getopt是外部命令，可以支持长选项和可选参数；
#   - getopts是shell内置命令，能够处理选项和参数之间的空格；自动去除选项前的破折号；但无法处理非-开头的选项；
#   
#     
#   ### getopts命令
#   - 使用形式：“getopts <optstring> <variable>”
#   - 如果选项字符串optstring以冒号开始，表明禁止输出错误消息；如果其中的选项以冒号结尾，表明此选  项需要参数值；
#   - 当前参数放在variable中；
#   - 选项必须在其参数之前，而且必须用空格分开；
#   - 如果需要参数的选项没有指定参数，将向标准错误中写入错误消息；
#   
#   
#   ### getopts命令的变量
#   - $OPTARG变量保存选项的参数值；
#   - $OPTIND变量保存原始$*中下一个要处理的选项位置；
#   - $OPTIND初值为1，遇到不带参数的选项将“optind += 1”，遇到带参数的选项将“optind += 2”