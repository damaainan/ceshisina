#### 递归统计文件夹下各种类型文件数目

    ls -lR | grep "^-" | grep '[(png)(jpg)(gif)]$' | wc -l

     ls -lR | grep "^-" | grep 'php$' | wc -l


     ls -lR | grep "^-" | grep 'md$' | wc -l

> 列出的结果作为一个文件供下一个命令使用

#### 递归统计文件夹下各种类型文件大小

    ls -lR | grep "^-" | grep 'php$' | awk '{sum += $5}; END{print sum}' | awk '{sum = $1/1024};END{print sum}'

    ls -lR | grep "^-" | grep '[(png)(jpg)(gif)]$' |  awk '{sum += $5}; END{print sum}' | awk '{sum = $1/1024/1024};END{print sum}'

    ls -lR | grep "^-" | grep 'md$' | awk '{sum += $5}; END{print sum}' | awk '{sum = $1/1024/1024};END{print sum}'


#### 生成html 相册

    find . -iname '*.jpg' | sed 's/.*/<img src="&">/' > gallery.html 


# 查找某目录下占用空间最大的10个文件


目前没有单个命令来完成查找的工作，通常可以使用一些命令的组合来帮助您找出磁盘上比较占用空间的文件或者文件夹。主要用到下面的三个命令：

* du : 计算出单个文件或者文件夹的磁盘空间占用.
* sort : 对文件行或者标准输出行记录排序后输出.
* head : 输出文件内容的前面部分.

用下面的命令组合就可以完成上述查找工作：

    # du -a /var | sort -n -r | head -n 10

如果需要输出可读性高的内容，请使用如下命令：

    $ cd /path/to/some/where
    $ du -hsx * | sort -rh | head -10
