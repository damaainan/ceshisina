#!/bin/bash
#条件表达式
declare A
#判断变量A是否set
[[ -v A ]] && echo "var A is set" || echo "var A is unset"
#判断变量A的值是否为空
[ ! $A ] && echo false || echo true
test -z $A && echo "var A is empty"
#通配与正则
A="1234567890abcdeABCDE"
B='[0-9]*'
C='[0-9]{10}\w+'
[[ $A = $B ]] && echo '变量A匹配通配符[0-9]*' || echo '变量A不匹配通配符[0-9]*'
[ $A == $B ] && echo '[ expr ]中能够使用通配符' || echo '[ expr ]中不能使用通配符'
[[ $A =~ $C ]] && echo '变量A匹配正则[0-9]{10}\w+' || echo '变量A不匹配正则[0-9]{10}\w+'
#if语句
# 此例并没有什么特殊的意义，只为说明几点需要注意的地方：
# 1、if后面可以是任何能够判断返回值的命令
# 2、直接执行复合命令((...))没有输出，要取得表达式的值必须通过数学扩展 $((...))
# 3、复合命令((...))中表达式的值非0，返回值才是0
number=1
if  if test -n $A
    then
        ((number+1))
    else
        ((number-1))
    fi
then
    echo "数学表达式值非0，返回值为0"
else
    echo "数学表达式值为0，返回值非0"
fi
# if语句和控制操作符 && || 连接的命令非常相似，但要注意它们之间细微的差别：
# if语句中then后面的命令不会影响else后的命令的执行
# 但&&后的命令会影响||后的命令的执行
echo '---------------'
if [[ -r file && ! -d file ]];then
    grep -q hello file
else
    awk '/world/' file
fi
echo '---------------'
# 上面的if语句无输出，但下面的命令有输出
[ -r file -a ! -d file ] && grep -q hello file || awk '/world/' file
# 可以将控制操作符连接的命令写成这样来忽略&&后命令的影响(使用了内置命令true来返回真):
echo '---------------'
[ -r file -a ! -d file ] && (grep -q hello file;true) || awk '/world/' file