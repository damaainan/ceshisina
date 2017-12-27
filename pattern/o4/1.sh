#!/bin/bash

# 图片改名

# ss=`` 处 等号两边不能有空格

# 涉及 awk 内置函数的使用


for i in `ls | grep png | awk '{split($0,a,"amp");print a[2]}'`
do
        echo $i
        ss=`ls | grep $i`
        echo $ss
        mv $ss $i
        #echo "****"
        #num=`echo $i | awk -F'amp' "{print $2}"`
        #echo $num
done

