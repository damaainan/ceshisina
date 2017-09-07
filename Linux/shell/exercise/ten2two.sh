#!/bin/bash

# shell 10 进制转 2 进制

for((i=0;i<64;i++))
do
    str=( [0]='0' [1]='0' [2]='0' [3]='0' [4]='0' [5]='0')
    echo $i

    if [ $(($i/32)) -eq 1 ]
    then
       str[0]='1'
    fi

    # if [ $(($i%32/16)) -eq 1 ]
    # then
    #     str[1]='1'
    # fi

    # if [ $(($i%16/8)) -eq 1 ]
    # then
    #     str[2]='1'
    # fi

    # if [ $(($i%8/4)) -eq 1 ]
    # then
    #     str[3]='1'
    # fi

    # if [ $(($i%4/2)) -eq 1 ]
    # then
    #     str[4]='1'
    # fi

    # if [ $(($i%2/1)) -eq 1 ]
    # then
    #     str[5]='1'
    # fi

# 循环优化判断


    for((j=0;j<5;j++))
    do
        k=$[ $j+1 ]  # 中括号 中 有空格   等号两边无空格
        p=$[ 2**$k ]  # 幂运算 **
        q=$[ 2**$j ]
        # echo $k
        if [ $(($i%$p/$q)) -eq 1 ]
        then 
            str[5-$j]='1'
        fi
    done

    

    echo ${str[0]}""${str[1]}""${str[2]}""${str[3]}""${str[4]}""${str[5]}
        
done




# i=9
# if [ $(($i/4)) -eq 2 ]
# then 
#   echo '//' 
# else
#     echo 999
# fi