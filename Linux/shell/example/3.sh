#!/bin/bash

str="Shell"
str2="Hello $str !"
str3="Hello ${str} !"
echo "拼接字符串: $str2"
echo "拼接字符串: $str3"

test1="一二三四五六七八九零"
echo "截取test1: " $test1
echo '${#var},返回变量字符串长度：' ${#test1}
echo '${var:index},截取字符串：' ${test1:2} # 返回从index开始到末尾的内容
echo '${var:index},截取字符串：' ${test1:0-3} # 返回从倒数index开始到末尾的内容
echo '${var:index:length},截取字符串：' ${test1:3:5} # 返回从index开始的length个字符的内容

test2="http://192.168.1.1/index.htm"
echo "过滤test2: " $test2
echo '${var#string},短匹配过滤字符串：' ${test2#*/} # 返回从左边删除string后的字符串
echo '${var##string},长匹配过滤字符串：' ${test2##*/} # 返回从左边删除string后的字符串
echo '${var%string},短匹配过滤字符串：' ${test2%/*} # 返回从右边删除string后的字符串
echo '${var%%string},长匹配过滤字符串：' ${test2%%/*} # 返回从右边删除string后的字符串

test3="12345678901234567890"
echo "替换test3: " $test3
echo '${var/substring/newstring},替换字符串：' ${test3/0/零} # 返回var中第一个substring被替换成newstring后的字符串
echo '${var//substring/newstring},替换字符串：' ${test3//0/零} # 返回var中所有substring被替换成newstring后的字符串




#   ### 特殊的替换
#   
#   ${var:-string}    
#   - 若变量var值为空时，string作为${var:-string}的值；
#   - 若变量var值不为空时，变量var的值作为${var:-string}的值；
#   
#   ${var:+string}    
#   - 当变量var值为空时，变量var的值作为${var:-string}的值；
#   - 当变量var值不为空时，string作为${var:-string}的值；
#   
#   ${var:=string}    
#   - 若变量var值为空时，string作为${var:-string}的值，并且变量var也被赋值为string；
#   - 若变量var值不为空时，变量var的值作为${var:-string}的值；
#   - 可用于判断变量是否赋值，如果值为空则指定一个默认值；
#   
#   ${var:?string} 
#   - 若变量var为空，则把string输出到标准错误中，并从脚本中退出； 
#   - 若变量var不为空，变量var的值作为${var:-string}的值；
#   - 可用于判断变量是否赋值；