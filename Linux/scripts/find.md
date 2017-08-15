

#### 递归统计文件夹下各种类型文件数目

    ls -lR | grep "^-" | grep '[(png)(jpg)(gif)]$' | wc -l

     ls -lR | grep "^-" | grep 'php$' | wc -l


     ls -lR | grep "^-" | grep 'md$' | wc -l