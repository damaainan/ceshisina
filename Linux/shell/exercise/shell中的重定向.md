# shell中的重定向

作者  Rose92 关注 2017.09.18 18:41  字数 24  

#### linux shell下常用输入输出操作符是：

    1.  标准输入   (stdin) ：代码为 0 ，使用 < 或 << ； /dev/stdin -> /proc/self/fd/0   0代表：/dev/stdin 
    2.  标准输出   (stdout)：代码为 1 ，使用 > 或 >> ； /dev/stdout -> /proc/self/fd/1  1代表：/dev/stdout
    3.  标准错误输出(stderr)：代码为 2 ，使用 2> 或 2>> ； /dev/stderr -> /proc/self/fd/2 2代表：/dev/stderr
    

#### 输出重定向

    “>”和“>>”用于重定向标准输出
    command [1-n] > file或文件操作符或设备
    上面命令意思是：将一条命令执行结果（标准输出，或者错误输出，本来都要打印到屏幕上面的）  重定向其它输出设备（文件，打开文件操作符，或打印机等等）1,2分别是标准输出，错误输出。
    
    1） 可以省略，不写，默认所至标准输出
    student@student-VirtualBox:/tmp$ ls text.sh
    ls: 无法访问text.sh: 没有那个文件或目录
    
    "2>"和“2>>”用于重定向标准输出
    2）把错误输出，不输出到屏幕，输出到t1.txt
    student@student-VirtualBox:/tmp$ ls text.sh 2>t1.txt 
    student@student-VirtualBox:/tmp$ cat t1.txt 
    ls: 无法访问text.sh: 没有那个文件或目录
    
    3) 继续追加把输出写入t1.txt  “>>”追加操作符
    student@student-VirtualBox:/tmp$ cat t1.txt 
    ls: 无法访问text2.sh: 没有那个文件或目录
    student@student-VirtualBox:/tmp$ ls text3.sh 2>>t1.txt 
    student@student-VirtualBox:/tmp$ cat t1.txt 
    ls: 无法访问text2.sh: 没有那个文件或目录
    ls: 无法访问text3.sh: 没有那个文件或目录
    
    "&>"同时重定向标准输出及标准错误输出
    特殊设备文件： /dev/null
    /dev/null 这个设备，是linux 中黑洞设备，什么信息只要输出给这个设备，都会给吃掉
    
    student@student-VirtualBox:~$ ls -ldh /etc/ &> /dev/null 
    
    注意：
    1、shell遇到”>”操作符，会判断右边文件是否存在，如果存在就先删除，并且创建新文件。不存在直接创建。 无论左边命令执行是否成功。右边文件都会变为空。
    2、“>>”操作符，判断右边文件，如果不存在，先创建。以添加方式打开文件，会分配一个文件描述符[不特别指定，默认为1,2]然后，与左边的标准输出（1）或错误输出（2） 绑定。
    3、当命令：执行完，绑定文件的描述符也自动失效。0,1,2又会空闲。
    4、一条命令启动，命令的输入，正确输出，错误输出，默认分别绑定0,1,2文件描述符。
    5、一条命令在执行前，先会检查输出是否正确，如果输出设备错误，将不会进行命令执行
    

#### 输入重定向

    command-line [n] <file或文件描述符&设备
    
    将然有，命令默认从键盘获得的输入，改成从文件，或者其它打开文件以及设备输入。执行这个命令，将标准输入0，与文件或设备绑定。将由它进行输入
    
    cat >file 记录的是键盘输入,相当于从键盘创建文件,并且只能创建新文件,不能编辑已有文件.
    student@student-VirtualBox:/tmp$ cat > catfile
    test
    hello,word
    #这里按下 [ctrl]+d 离开 
    #从标准输入【键盘】获得数据，然后输出给catfile文件
    student@student-VirtualBox:/tmp$ cat catfile 
    test
    hello,word
    
    student@student-VirtualBox:/tmp$ cat>catfile < t1.txt 
    student@student-VirtualBox:/tmp$ cat catfile
    abcdef
    # cat从catfile输入数据，然后输出给文件catfile
    
    cat <<EOF,
    cat命令是linux下的一个文本输出命令，通常是用于观看某个文件的内容的；
    EOF是"end of file"，表示文本结束符。
    结合这两个标识，即可避免使用多行echo命令的方式，并实现多行输出的结果。
    
    student@student-VirtualBox:/tmp$ cat>catfile <<eof
    > test a file
    > test
    > haha!
    > eof

