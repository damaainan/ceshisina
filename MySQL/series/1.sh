#!/bin/bash
# 将文件中的图片地址替换为本地地址
# 难点在于切割和替换
# awk -F': ' '/!web/{print $2}' 1.md 2.md  3.md | awk -F! '{print $1}' > 2.txt

for i in `awk -F'/' '{print $NF}' 2.txt`
do
        #echo $i
        #awk  -F': ' "/${i}/{print $2}" 1.txt
        num=`awk -F': ' "/$i/"'{print $1}' 1.md 2.md 3.md`
        sed  -i "/${i}/c ${num}: ./img/${i}" 1.md 2.md 3.md
        #echo ${num}
done

