#!/bin/bash

echo '##### Number of *.conf : '
find /etc -name *.conf | grep system | wc -l

echo '##### *user.conf : '
find /etc -name *user.conf

echo '##### *user.conf - xargs : '
find /etc -name *user.conf | xargs 

# find /etc -name *user.conf |ls -l # 无法获得正确结果

echo '##### *user.conf - xargs ls -l : '
find /etc -name *user.conf | xargs ls -l





#   ### 管道 Pipe
#   利用管道可以将实现单个功能的指令串联起来，实现连续复杂的操作
#   - 管道命令操作符是“|”；
#   - 通过管道可以将前一个指令的标准输出做为下一个指令的标准输入；
#   - 管道仅能处理前一个指令的正确输出，无法处理错误输出；
#   - 如果“|”右边的命令不支持使用管道来传递参数，可以使用xargs命令来规避；
#   
#   
#   ### xargs命令
#   - 能够捕获一个命令的输出，然后传递给另外一个命令，通常结合管道一起使用；
#   - 通过管道传递给后一个指令的输入往往包含换行和空白，但经过xargs处理后，换行和空白将被空格取代；