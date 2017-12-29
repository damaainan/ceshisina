#!/bin/bash
echo "hello shell!" # 打印字符串“hello shell!”

echo "Date： " `date` # 显示命令执行结果

echo "\"It is a test!\"" # \ 转义字符
echo '\"It is a test!\"' # 在单引号中原样输出字符串，不进行转义或取变量

echo -e "Pass! \n" # -e 启用反斜线控制字符的转换，\n 显示换行
echo "Pass! \n" # 默认关闭反斜线控制字符的转换





#   #! --- 指定解释器
#   echo --- display a line of text
#   # --- 单行注释，Shell不支持多行注释
#   
#   
#   ### Shell与Shell脚本
#   - Shell是提供访问操作系统内核服务界面的应用程序；
#   - 通过Shell脚本（shell script）可以在Shell中实现特定功能；
#   - 脚本执行时，一边解释一边执行，如果脚本包含错误，只要没执行到这一行，就不会报错； 
#   - 查看可用shell: cat /etc/shells
#   - 查看shell版本: bash --version
#   
#   
#   ### 执行脚本的方法
#   1. 使用chmod命令为脚本文件添加可执行权限，然后直接执行（脚本内需要指定解释器路径）
#   2. 通过“bash test.sh”方式将脚本作为解释器的参数，直接调用解释器
#   3. 使用source命令或"."执行文件，例如“source test.sh”（在父进程中直接执行，不会创建子进程）
#     
#  
#   ### echo命令
#   通过“man echo”获取命令详细信息
#   参数“-E”：关闭反斜线控制字符的转换（默认）
#   参数“-e”：启用反斜线控制字符的转换
#   参数“-n”：取消行末的自动换行