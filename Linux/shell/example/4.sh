#!/bin/bash

var=$1 # 将脚本的第一个参数赋值给变量var

if test $var # test - check file types and compare values
then
  if [ $var == "right" ];then # “[]”是调用test命令的一种形式，"[]"两边必须加空格;
    echo "Right!"
  elif test $var = "wrong";then # 比较字符串时，“=”等价与“==”，两边必须加空格；
    echo "Wrong!"
  else
    echo "Nothing!"
  fi
else
  echo "No arguments!"
fi





#   ### if/else流程控制
#   - elif语句和else语句是非必须语句，根据需要添加；
#   - 条件下的执行体不能为空；
#   
#   
#   ### test命令
#   - Shell中的test命令用于检查某个条件是否成立，可以进行数值、字符和文件的测试
#   - 通常用“[]”来表示条件测试。使用"[]"时，两边必须加空格;
#   - 通过man命令获取test命令的详细信息；
#   0）常用
#     [ -f somefile ] ：判断是否是一个文件 
#     [ -x /bin/ls ] ：判断/bin/ls是否存在并有可执行权限 
#     [ -n $var ] ：判断$var变量是否有值 
#     [ $a = $b ] ：判断$a和$b是否相等  
#   1）判断表达式 
#     if test (表达式为真) 
#     if test !表达式为假 
#     test 表达式1 –a 表达式2 两个表达式都为真 
#     test 表达式1 –o 表达式2 两个表达式有一个为真 
#   2）判断字符串 
#     test –n 字符串 字符串的长度非零 
#     test –z 字符串 字符串的长度为零 
#     test 字符串1＝字符串2 字符串相等 
#     test 字符串1！＝字符串2 字符串不等 
#   3）判断整数 
#     test 整数1 –eq 整数2 整数相等 
#     test 整数1 –ge 整数2 整数1大于等于整数2 
#     test 整数1 –gt 整数2 整数1大于整数2 
#     test 整数1 –le 整数2 整数1小于等于整数2 
#     test 整数1 –lt 整数2 整数1小于整数2 
#     test 整数1 –ne 整数2 整数1不等于整数2 
#   4）判断文件 
#     test File1 –ef File2 两个文件具有同样的设备号和i结点号 
#     test File1 –nt File2 文件1比文件2 新 
#     test File1 –ot File2 文件1比文件2 旧 
#     test –b File 文件存在并且是块设备文件 
#     test –c File 文件存在并且是字符设备文件 
#     test –d File 文件存在并且是目录 
#     test –e File 文件存在 
#     test –f File 文件存在并且是正规文件 
#     test –g File 文件存在并且是设置了组ID 
#     test –G File 文件存在并且属于有效组ID 
#     test –h File 文件存在并且是一个符号链接（同-L） 
#     test –k File 文件存在并且设置了sticky位 
#     test –b File 文件存在并且是块设备文件 
#     test –L File 文件存在并且是一个符号链接（同-h） 
#     test –o File 文件存在并且属于有效用户ID 
#     test –p File 文件存在并且是一个命名管道 
#     test –r File 文件存在并且可读 
#     test –s File 文件存在并且是一个套接字 
#     test –t FD 文件描述符是在一个终端打开的
#   
#   
#   ###  “[]”与test命令 
#   - 实际上“[]”是调用test命令的一种形式；
#   - 左中括号“[”是系统的一个内置命令,存在路径是“/bin/[”,是调用test命令的标识；
#   - 右中括号“]”是关闭条件判断的标识；