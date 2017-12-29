#!/bin/bash

pwd > 1.log # 输出重定向到指定文件
date 1> 1.log # “>”与“1>”作用相同；覆盖指定文件的原有内容
date >> 1.log # 追加内容到指定文件的末尾
echo "1.log: " `cat 1.log`

echo -e "one\ntwo\nthree" > 2.log
echo "Number of rows:" `wc -l < 2.log` # 输入重定向；统计2.log文件内容的行数
echo "2.log: " `cat 2.log`

echo -e "111\n222\n333" > 3.log
echo "3.log: " `cat 3.log`
wc -l < 3.log > 4.log # 同时重定向输入和输出，从3.log读取输入，然后将输出写入到4.log
echo "4.log - Number of rows:" `cat 4.log`

rm -rf 123 # 确保文件不存在
ls 123 2> 5.log # 错误重定向到指定文件
ls 123 2>> 5.log # 追加内容到指定文件的末尾
echo "5.log: " `cat 5.log`

ls 123 >> 6.log 2>&1 # 将stdout和stderr合并后重定向到指定文件
pwd >> 6.log 2>&1 # 将stdout和stderr合并后重定向到指定文件
echo "6.log: " `cat 6.log`

ls 123 2>> 7.log >> 8.log # 分别重定向到指定文件
pwd 2>> 7.log >> 8.log
echo "7.log: " `cat 7.log`
echo "8.log: " `cat 8.log`

pwd > /dev/null # 屏蔽stdout
ls /root > /dev/null 2>&1 # 屏蔽stdout和stderr

rm -rf [0-9].log

# Here Document
cat << !
abc
123
ABC
!
# 将两个delimiter(这里使用!符号)之间的内容(document) 作为输入传递给command





#   ### 标准
#   - 标准输入文件(stdin)的文件描述符为0，默认从stdin读取数据;
#   - 标准输出文件(stdout)的文件描述符为1，默认向stdout输出数据;
#   - 标准错误文件(stderr)的文件描述符为2，默认向stderr中写入错误信息;
#   
#   
#   ### 输出重定向
#   - “command > file”：标准输出(stdout)重定向到指定文件，覆盖指定文件的原有内容
#   - “command >> file”：标准输出(stdout)重定向到指定文件，追加内容到指定文件的末尾
#   - “>”与“1>”作用相同
#   
#   
#   ### 输入重定向
#   - “command < file”：从标准输入(stdin)获取内容重定向到从指定文件中获取内容
#   
#   
#   ### 错误重定向
#   - “command 2 > file”：标准错误(stderr)重定向到指定文件，覆盖指定文件的原有内容
#   - “command 2 >> file”：标准错误(stderr)重定向到指定文件，追加内容到指定文件的末尾
#   
#   
#   ### 合并重定向
#   将stdout和stderr合并后重定向到指定文件
#   - “command > file 2>&1”：覆盖指定文件的原有内容
#   - “command >> file 2>&1”：追加内容到指定文件的末尾
#   
#   
#   ### Here Document
#   - 特殊的重定向方式，用来将输入重定向到一个交互式Shell脚本
#   - 作用是将两个delimiter之间的内容(document)作为输入传递给command
#   - 开始的delimiter前后的空格会被忽略
#   - 结尾的delimiter必须顶格写，前后不能有任何字符，包括空格和 tab 缩进
#   
#   
#   ### “/dev/null”文件
#   - 写入到“/dev/null”文件的内容都会被丢弃；
#   - 也无法从该文件读取内容；