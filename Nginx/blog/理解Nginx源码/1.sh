#!/bin/bash
# echo $IFS

# 改变奇数行的语言标志

MY_SAVEIFS=$IFS  # 改变分隔符
# IFS=$(echo -en "\n\b")  
IFS=$'\n'  
# echo $IFS
for i in `ls *.md`
do
    # echo $i
    name=$i
    echo $name
    awk '/```/{i++;if(i%2==1)print NR}' "${name}" | xargs -I[ sed -i '[s@```@```c@' "${name}"
done

IFS=$MY_SAVEIFS  
# echo $IFS