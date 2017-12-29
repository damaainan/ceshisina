#!/bin/bash -x

for filename in t1 t2 t3
do
  touch $filename.txt
  echo "Create new file: $filename.txt"
done

for rmfile in *.txt; do rm $rmfile; echo "Delete $rmfile!"; done;

# set -x
for filelist in `ls /root`
do
  echo "filename : "$filelist
done
# set +x




#   ### 常用Shell脚本调试选项
#   -v （verbose）详细模式，将所有执行过的脚本命令打印到标准输出；
#   -n （noexec 或 no ecxecution）语法检查模式，读取脚本并检查语法错误，但不执行；
#   -x （xtrace 或 execution trace）跟踪模式，可以识别语法错误和逻辑错误，显示所有执行的命令、参数和结果； 
#   
#   
#   ### 执行调试的方法
#   1.在命令行提供参数，调试整个脚本，例如“$bash -x script.sh”；
#   2.脚本开头提供参数，调试整个脚本，例如“#!/bin/bash -x”；
#   3.在脚本中用set命令对特定部分进行调试，例如“set -x”启用调试和“set +x”禁用调试；
#   
#   
#   ### set命令
#   - 使用内置命令set可以调试Shell脚本的指定部分；
#   - 启用调试：“set -<选项>”;
#   - 禁用调试：“set +<选项>”;
#   
#   -x    xtrace    调试模式
#   -v    verbose   verbose模式
#   -n    noexec    检查语法
#   -e    errexit   如果命令运行失败，脚本退出执行；
#   -u    nounset   如果存在未声明的变量，脚本退出执行；