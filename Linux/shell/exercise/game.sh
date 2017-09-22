#!/bin/bash
#Author:丁丁历险(Jacob)
#定义数组，保存所有出拳的可能性
game=(石头 剪刀 布)
num=$[RANDOM%3]
computer=${game[$num]}
#通过随机数获取计算机的出拳
#出拳的可能性保存在一个数组中，game[0],game[1],game[2]分别是3中不同的可能
 
echo "请根据下列提示选择您的出拳手势"
echo "1.石头"
echo "2.剪刀"
echo "3.布"
 
read  -p  "请选择1-3:"  person
#提示用户出拳，根据提示出拳即可
#再通过case语句判断用户输入的值是1还是2还是3，根据不同的输入判断不同的结果
case  $person  in
1)
       if [ $num -eq 0 ];then
              echo "平局"
       elif [ $num -eq 1 ];then
              echo "你赢"
       else
              echo "计算机赢"
       fi;;
2)    
       if [ $num -eq 0 ];then
              echo "计算机赢"
       elif [ $num -eq 1 ];then
              echo "平局"
       else
              echo "你赢"
       fi;;
3)
       if [ $num -eq 0 ];then
              echo "你赢"
       elif [ $num -eq 1 ];then
              echo "计算机赢"
       else
              echo "平局"
       fi;;
*)
       echo "必须输入1-3的数字"
esac