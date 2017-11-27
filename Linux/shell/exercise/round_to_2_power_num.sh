#!/bin/bash
num=$1
for (( i=1; i<=64; i++ ))
do
    low=$(( 2 ** (($i-1)) ))
    high=$(( 2 ** $i ))
    avg=$(( (( $low + $high )) / 2 ))
    if [ $num -le 0 ]; then
        echo "error! we only support positive integer."
        exit 1
    fi
    if [ $num -eq $low -o $num -eq $high ]; then
        echo "num:$num  2-power-num:$num"
        break
    fi
    if [ $num -gt $low -a $num -lt $high ]; then
        if [ $num -lt $avg ]; then
            echo "num:$num  2-power-num:$low"
            break
        else
            echo "num:$num  2-power-num:$high"
            break
        fi
    fi
done

# Shell脚本实现求一个整数接近最近的2的次幂数的整数
# RFS（Receive Flow Steering）扩展了 RPS 的性能以增加 CPU 缓存命中率，以此减少网络延迟。

# RFS中的有个配置参数：/proc/sys/net/core/rps_sock_flow_entries

# 设置此文件至同时活跃连接数的最大预期值。对于中等服务器负载，推荐值为 32768 。所有输入的值四舍五入至最接近的2的幂。

# 当echo一个值设置到rps_sock_flow_entries时，会被设置为离这个值最近的2的次幂。由于一些需要，我需要用Shell实现这个类似的功能：给定一个正整数，计算出离它最近的2的次幂整数是多少。

# 写了个shell脚本： https://github.com/smilejay/shell/blob/master/sh2017/round_to_2_power_num.sh