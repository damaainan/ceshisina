#!/bin/bash
read -p "Please input a num: " num
if [[ $num =~ [^0-9] ]];then
        echo "input error" 
else
        for i in `seq 1 $num` ;do
                xing=$[2*$i-1]
                for j in `seq 1 $[$num-$i]`;do
                        echo -ne " "
                done
                for k in `seq 1 $xing`;do
                        color=$[$[RANDOM%7]+31]
                        echo -ne "\033[1;${color};5m*\033[0m"
                done
                echo
        done
fi