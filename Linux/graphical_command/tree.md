 **tree**命令-->以树状图的方式列出目录的内容，有的版本需要安装tree包

![][0]

**备注:**

1) 以tree命令来示范源代码编译过程

```
ftp://mama.indstate.edu/linux/tree/tree-1.7.0.tgz  
ubuntu: apt-get install tree  
  
到ftp://mama.indstate.edu/linux/tree/下载最新的tree命令源代码压缩包。  
[root@localhost tree-1.5.3]# tree   
-bash: tree: command not found  
   
[root@localhost setup]# ls -l tree-1.5.3.tgz   
-rw-r--r--    1 root     root        34494 12月  3 20:56 tree-1.5.3.tgz  
[root@localhost setup]# tar zxf tree-1.5.3.tgz   
[root@localhost setup]# cd tree-1.5.3   
[root@localhost tree-1.5.3]# ls   
CHANGES  INSTALL  LICENSE  Makefile  man  README  strverscmp.c  tree.c  
[root@localhost tree-1.5.3]# make   
gcc -ggdb -Wall -DLINUX -D_LARGEFILE64_SOURCE -D_FILE_OFFSET_BITS=64   -c -o tree.o tree.c  
gcc  -o tree tree.o   
[root@localhost tree-1.5.3]# cp -af tree /usr/bin   
[root@localhost tree-1.5.3]# tree  
```

[0]: ./img/20160813181502777.png
[1]: #