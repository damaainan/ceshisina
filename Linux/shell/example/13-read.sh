#!/bin/sh

echo -n "Enter your name:" # “echo -n”移掉字符串末尾的换行符，允许紧跟其后输入数据
read -t 5 name # 设置超时时间为3秒
echo "Hi, $name !"

read -p "Enter your age:" age  # -p选项，在read命令中指定提示
days=$[ $age * 365 ]
echo "$days days !"

read -s -p "What do you want :" w1 w2 # 指定多个变量来获取多个输入
echo "You want to get more $w1 and $w2 !"

read -n1 -p "Do you want to continue [Y/N]?" answer # -n1选项，接受到一个字符就退出
case $answer in
    Y | y)
    echo -e "\n Continue!";;
    N | n)
    echo -e "\n Goodbye!";;
    *)
    echo -e "\n Error Choice!";;
esac



### 示例：read命令从文件中读取内容的方法
echo -e "aaa\n222\nccc" > test.log # 创建示例文件
cat test.log

# count=1
# cat test.log | while read line
# do
#    echo "Line $count : $line"
#    count=$[ $count + 1 ]
# done

# cat test.log | \
# while read CMD; do
#     echo $CMD
# done

while read CMD; do
    echo "$CMD"
done < test.log

rm -rf test.log

exit 0





#   ### 在脚本运行时，通过read命令以交互的方式获取输入
#   - 在脚本中使用“read variable”获取标准输入，并将数据存放到标准变量中；使用“$varialbe”调用输入；
#   - read命令可以指定多个变量来获取多个输入；如果输入的值多于变量，多出的值会统一分配给最后一个变量；
#   - 如果不指定变量，read命令会将所有接收到的数据都放到特殊环境变量REPLY中;
#   
#   
#   ### read命令常用选项
#   -p ： 省略echo命令并指定变量名字，可以多个变量；
#   -t <n> ： 设置超时时间为n秒；
#   -n<n> : 当输入的字符数目达到预定数目n时，自动退出，并将输入的数据赋值给变量;
#   -s ： 隐藏输入（不显示输入的数据），但实际上只是将输入字符的颜色设置与背景色相同；
#   
#   
#   ### read命令从文件中读取内容
#   - 利用管道和while循环，可以实现逐行读取文件内容；
#   - 如果文件为空（没有数据），read命令会退出并返回非零退出状态码；