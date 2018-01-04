## linux for循环变量中有空格怎么处理

我有如下脚本，怎么把find命令获取到的文件赋值给for循环 
    
    #!/bin/bash 
    for i in `find . -name *.txt` 
    do 
        export file_path=$i echo $file_path 
    done 

文件路径如下， /this is test dir/abc.txt /this is test dir/a test/dsfd.txt 路径中带...


这个需要更改 shell分隔符为换行  
在for循环之前修改IFS变量  

    IFS=$'\n'   
    OLDIFS="$IFS"  
    for i in `find . `  
    do  
        echo "$i"  
    done  
    IFS="$OLDIFS"  
  
这样循环就会以换行作为单词分界.你的文件名如果出现换行，那就无能为力。  
虽然linux 支持文件名包含特殊字符 
但并不推荐，会导致别的脚本出错误。通常用下划线或短线代替空格。  
  
还有一种用  

    find some | while read i  
    do  
        echo "$i"  
    done  
用`read i`，每个循环读取一行，等价与用换行作为分隔符。
