# Linux如何查找处理文件名后包含空格的文件

 时间 2017-11-13 17:07:00  潇湘隐者

原文[http://www.cnblogs.com/kerrycode/p/7827118.html][1]


**Linux如何查找处理文件名后包含空格的文件**

当Linux下文件名中出现空格这类特殊情况话，如何查找或确认那些文件名后有空格呢？ 又怎么批量替换处理掉这些空格呢？ 

方法1： 输入文件名后使用Tab键，如果使用Tab键后面出现`\ \ \`这样的可见字符，那么该文件名包含空格。当然，这个方法弊端很大，例如，效率低下，不能批量查找，只有当你怀疑某个文件名后有空格，这个方法才比较凑效。另外，不能查找文件中间包含空格的文件名。如下测试所示： 

    [root@DB-Server kerry]# cat >"test.txt    "
    it is only for test!
     
    [1]+  Stopped                 cat > "test.txt    "
    [root@DB-Server kerry]# cat >"tes t.txt"
    it is only for test too!
     
    [2]+  Stopped                 cat > "tes t.txt"
    [root@DB-Server kerry]# ls test.txt
    ls: test.txt: No such file or directory
    [root@DB-Server kerry]# ls test
    test~         test1.py      test.py       test.sh       test.txt      
    [root@DB-Server kerry]# ls test.txt\ \ \ \  
    test.txt    
    [root@DB-Server kerry]# ls tes
    test~         test1.py      test.py       test.sh       tes t.txt     test.txt   
    

 ![][3]

方法2： 使用find命令查找文件名中包含空格的文件。 

    [root@DB-Server kerry]# find . -type f -name "* *" -print 

    ./test.txt 

    ./tes t.txt 

那么如何将这些空格替换掉呢？  下面脚本可以替换文件中间的空格,用下划线替换空格，但是只能替换文件中间的空格，并不能替换文件名后面的空格。如下测试所示： 

    find . -type f -name "* *" -print |
    while read name; do
    na=$(echo $name | tr ' ' '_')
    if [[ $name != $na ]]; then
    mv "$name" "$na"
    fi
    done

 ![][4]

上面脚本只能将文件名中间有空格的替换为下划线。那么如何解决文件名后有空格的情况呢？ 可以用其它shell脚本实现，如下所示： 

    [root@DB-Server kerry]# rm -rf *
    [root@DB-Server kerry]# cat >"test.txt    "
    12
    [root@DB-Server kerry]# cat >"tes t.txt"
    12
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    ./test.txt    
    ./tes t.txt
    [root@DB-Server kerry]# for file in *; do mv "$file" `echo $file | tr ' ' '_'` ; done
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    [root@DB-Server kerry]# ls -lrt
    total 8
    -rw-r--r-- 1 root root 0 Nov 13 10:04 test.txt
    -rw-r--r-- 1 root root 0 Nov 13 10:04 tes_t.txt
    

 ![][5]

  如上所示，虽然 文件名中间的空格被替换为了下划线，但是后面的空格没有替换为下划线，而是将那些空格直接截断了。Why？下面使用sed命令也是如此

    [root@DB-Server kerry]# rm -rf *
    [root@DB-Server kerry]# cat >"test.txt    "
    12
    [root@DB-Server kerry]# cat >"tes t.txt"
    12
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    ./test.txt    
    ./tes t.txt
    [root@DB-Server kerry]# for i in *' '*; do   mv "$i" `echo $i | sed -e 's/ /_/g'`; done
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    [root@DB-Server kerry]# ls -lrt
    total 8
    -rw-r--r-- 1 root root 0 Nov 13 09:29 test.txt
    -rw-r--r-- 1 root root 0 Nov 13 09:29 tes_t.txt
    [root@DB-Server kerry]# 
    [root@DB-Server kerry]# 
    

 ![][6]

其实，这个是因为读取文件名是$file 与"$file"是不同的，$file不会识别文件名后面的空格，而"$file"才会失败文件名后面的空格。所以上面脚本其实只是取巧而已。 

    [root@DB-Server kerry]# rm -rf *;
    [root@DB-Server kerry]# cat >"test.txt    "
    123
    [root@DB-Server kerry]#  for file in *; do echo "$file"; echo "$file" | wc -m ;   done;
    test.txt    
    13
    [root@DB-Server kerry]#  for file in *; do echo $file; echo $file | wc -m ;   done;
    test.txt
    9
    [root@DB-Server kerry]# 
    

 ![][7]

所以，正确的替换空格的命令应该为如下： 

方案1： 

    [root@DB-Server kerry]# rm -rf *
    [root@DB-Server kerry]# cat >"test.txt    "
    123456
     
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    ./test.txt    
    [root@DB-Server kerry]# for file in *; do mv "$file" `echo "$file" | tr ' ' '\n'` ; done
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    [root@DB-Server kerry]# ls test.txt
    test.txt
    [root@DB-Server kerry]# 
    

方案2： 

    [root@DB-Server kerry]# 
    [root@DB-Server kerry]# rm -rf *
    [root@DB-Server kerry]# cat >"test.txt    "
    123456
     
    [root@DB-Server kerry]# for file in *' '*; do   mv "$file" `echo "$file" | sed -e 's/ /n/g'`; done
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    

但是对于文件名中间包含空格的情况，上面两个脚本都无法完美解决。如下所示： 

    [root@DB-Server kerry]# 
    [root@DB-Server kerry]# rm -rf *
    [root@DB-Server kerry]# cat >"tes t.txt"
    123456
     
    [root@DB-Server kerry]# for file in *; do mv "$file" `echo "$file" | tr ' ' '_'` ; done
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    [root@DB-Server kerry]# ls -lrt 
    total 8
    -rw-r--r-- 1 root root 7 Nov 13 16:00 tes_t.txt
    [root@DB-Server kerry]# 
     
     
    [root@DB-Server kerry]# rm -rf *
    [root@DB-Server kerry]# cat >"tes t.txt"
    123456
    [root@DB-Server kerry]# cat >"test.txt    "
    654321
     
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    ./test.txt    
    ./tes t.txt
    [root@DB-Server kerry]# for file in *; do mv "$file" `echo "$file" | tr ' ' '_'` ; done
    [root@DB-Server kerry]# find . -type f -name "* *" -print
    [root@DB-Server kerry]# ls -lrt
    total 12
    -rw-r--r-- 1 root root 0 Nov 13 15:59 tes_t.txt
    -rw-r--r-- 1 root root 7 Nov 13 15:59 test.txt____
    

当然对于这两种特殊情况，上面脚本都不能一起处理，如上所示，后面的空格被替换成了下划线。这反而不是我们想要的，反而最上面两种脚本，可以误打误撞的解决这两种问题。当让前提是你得知其然知其所以然！ 

**参考资料：**

[http://www.eygle.com/digest/2006/11/linux_replace_blank.html](http://www.eygle.com/digest/2006/11/linux_replace_blank.html) 

[https://stackoverflow.com/questions/1806868/linux-replacing-spaces-in-the-file-names](https://stackoverflow.com/questions/1806868/linux-replacing-spaces-in-the-file-names) 

[https://www.keakon.net/2011/10/20/bash%E4%B8%8B%E5%A4%84%E7%90%86%E5%8C%85%E5%90%AB%E7%A9%BA%E6%A0%BC%E7%9A%84%E6%96%87%E4%BB%B6%E5%90%8D](https://www.keakon.net/2011/10/20/bash%E4%B8%8B%E5%A4%84%E7%90%86%E5%8C%85%E5%90%AB%E7%A9%BA%E6%A0%BC%E7%9A%84%E6%96%87%E4%BB%B6%E5%90%8D)

[1]: http://www.cnblogs.com/kerrycode/p/7827118.html
[3]: ../IMG/UZRNBvM.png
[4]: ../IMG/R3uU7bu.png
[5]: ../IMG/naYjiq3.png
[6]: ../IMG/YFjIzeE.png
[7]: ../IMG/6ZJJJbz.png