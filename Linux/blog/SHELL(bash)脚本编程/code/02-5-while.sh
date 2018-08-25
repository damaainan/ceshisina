#!/bin/bash
# while
unset i j
# while ((i++<$(grep -c '^processor' /proc/cpuinfo)))
# do
#     #每个后台运行的yes命令将占满一核CPU
#     yes >/dev/null &
# done
# -------------------------------------------------
# until
# 获取yes进程PID数组
PIDS=($(ps -eo pid,comm|grep -oP '\d+(?= yes$)'))
# 逐个杀掉yes进程
until ! ((${#PIDS[*]}-j++))
do
    kill ${PIDS[$j-1]}
done
# -------------------------------------------------
# case
user_define_command &>/dev/null
case $? in
0) echo "执行成功" ;;
1) echo "未知错误" ;;
2) echo "误用shell命令" ;;
126) echo "权限不够" ;;
127) echo "未找到命令" ;;
130) echo "CTRL+C终止" ;;
*) echo "其他错误" ;;
esac
# -------------------------------------------------
#定义数组
c=(1 2 3 4 5)
#关于各种复合命令结合使用的例子：
echo -e "$(
for i in ${c[@]}
do
    case $i in 
    (1|2|3)
        printf "%d\n" $((i+1))
        ;;
    (4|5)
        printf "%d\n" $((i-1))
        ;;
    esac
done
)" | while read NUM
do
    if [[ $NUM -ge 4 ]];then
        printf "%s\n" "数字${NUM}大于等于4"
    else
        printf "%s\n" "数字${NUM}小于4"
    fi
done