#!/bin/bash

# 获取等宽的二进制数字转的字符串 

for s in `seq 64 127 | xargs -i[ echo "obase=2;[" | bc`
do 
  echo $s
  tt=${s:1}
  echo $tt

done