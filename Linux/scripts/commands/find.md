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