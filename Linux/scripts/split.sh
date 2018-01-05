#!/bin/bash
# 分割 php 文件 Functional-PHP 项目

# /*  
# *  需要转义

for i in `ls -l | grep -E 'Ch|App' | awk '{print $NF}'`
do
    dir=${i}
    cd out
    rm -rf ${dir} 
    mkdir ${dir}
    cd ..
    echo ${dir}

    cd ${dir}
    for j in `ls *.php`
    do
        file=${j}
        name=`echo ${file} | awk -F'.' '{print $1}'`
        # echo ${name}
        echo ${file}
        # awk '/^<\?php/{++i}{print i"*"$0}' ${file}
        # for k in `awk '/^<\?php/{++i}{print i"*"$0}' ${file}`  ## 内容有空格按多个处理了
        # 有 \  的行会重复

        # awk '/^<\?php/{++i}{if(match($0,/\\/)>-1)gsub(/\\/,"\\\\",$0)}{print i"###"$0" "}'  # 处理反斜杠

        # awk '/^<\?php/{++i}{print i"###"$0" "}' ${file} | sed 's@\\@\\\\@gp' |  while read k  
        
        awk '/^<\?php/{++i}{if(match($0,/\\/)>-1)gsub(/\\/,"\\\\",$0)}{print i"###"$0" "}' ${file} | while read k  
        do
            # echo "${k}" # 按 ### 分割   \ 命名空间反斜杠会丢失？
	        # echo 这一步 \ 反斜杠已无
            turn=`echo "${k}" | awk -F'###' '{print $1}'` # 加 "" 避免转义 *  /*   
            con=`echo "${k}" | awk -F'###' '{print $2}'`
            # echo ${turn}
            # echo ${con}
            echo "${con}" >> ../out/${dir}/${name}-${turn}.php
        done

    done
    cd ..
done