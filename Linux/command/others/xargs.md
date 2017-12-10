# xargs：从stdin读取参数的工具

 时间 2017-12-09 09:52:16  

原文[https://jacobpan3g.github.io/cn/2017/12/09/xargs-stdin-parameter-reader/][1]

xargs设计的初衷是从stdin中读取内容作为参数执行命令。

    find . -type f | xargs rm
    rm `find . type f`

第一条命令中，find命令的输出，通过xargs成为rm命令的参数，相比第二条使用起来更方便。

    find . type f -exec rm '{}'

上述的find命令也可以达到同样的效果

## options

* -0 当stdin含有特殊字元时，将其当成一般字符，如 / , ' , 空格等
* -t 在执行命令是先打印
* -n 把stdin分为以num个作为一组(默认以空格为分隔符)，执行命令。在一些linux系统，如centos6，对参数的长度有限制，太长的话就会报错，这个option就是用来处理这个问题的。
```
    rm `find . -type f`     #参数过长而报错
    find . -type f | xargs -n 10 rm
```
如上面例子，参数过程时可以通过xargs把参数拆除多个子串分别rm
* -i 相当于 -n 1 , 然后还可以在命令用以 {} 代替所读取参数的位置 
```
    ls | xargs -i echo {} hello
```
## 注意

xargs 后面执行的命令只能是一条，不能带有 ; , && 等 

    ls | xargs -t -i [ -f {} ] && echo yes

根据输出，可以发现， && 后面的命令被当做与xargs”并列”的命令，而不在xargs要执行的命令”并列”。 

Jacob Pan [( jacobpan3g.github.io/cn )][3]


[1]: https://jacobpan3g.github.io/cn/2017/12/09/xargs-stdin-parameter-reader/

[3]: http://jacobpan3g.github.io/cn