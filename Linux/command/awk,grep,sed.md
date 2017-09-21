# awk(报告生成器),grep(文本过滤器),sed(流编辑器)使用入门

 时间 2017-09-20 00:15:18  

原文[http://www.jianshu.com/p/19f14bab6acc][1]


![][4]

三剑客

linux下的文本三剑客

### grep

```shell
    egrep,grep,fgrep 
    文本查找的需要
    grep：根据模式搜索文本，并将符合模式的文本行显示出来。
    pattern：文本符和正则表达式的元字符组合而成的匹配条件
    
    grep [option] "pattern" file 
    grep root /etc/passwd
    
    -i：忽略大小写 
    --color：匹配的字符高亮显示  alias
    alias  grep='grep --color'
    -v:反向查找 
    -o：只显示被模式匹配的字符串（不显示行）
```

#### globbing

```shell
    *：任意长度的任意字符
    ？：任意单个字符
    []:任意一个字符
    [^]:其中任意一个非
```

#### 正则表达式：Regular ExPression,REGEXP

```shell
    元字符：
    .:匹配任意单个字符
    []:匹配指定范围内的任意字符
    [^]:匹配指定范围内的任意单个字符
    [:digit:][:lower:][:upper:] []
    
    字符匹配次数：
    *：表示匹配前面的字符任意次（0-inf）
       a*b 
       a.*b
    .*:表示任意长度的，任意字符
    工作在贪婪模式 
    \?:匹配其前面的字符一个或0次。
        部分匹配 
      a?b 
    \{m,n\}:匹配其前的字符至少m，至多n次。
       \{1,\}
      \{0,3\}
      a\{1,3\}
      a.\{1,3\}
```

#### 位置锚定：

```shell
    ^:锚定行首，此字符后面的任意内容必须出现在行首。
    grep "^root" /etc/passwd 
    
    $:锚定行尾，此字符前面的任意内容必须出现在行尾。
    
    grep "bash$" /etc/passwd 
    ^$:空白行 
    grep '^$' /etc/passwd
```

#### 数字:

```shell
    [0-9]:
    
    grep "[[:space:]][[:digit:]]$" 
    
    r555t
```

#### 锚定单词：

```shell
    \<或\b:其后面的任意字符必须出现在行首
    \>或\b:其前面的任意字符必须出现在行尾。
    
    This is root.
    The user is mroot
    rooter is dogs name.
    chroot is a command.
    grep "root\>" test.txt 
    grep "\<root" test.txt 
    grep "\<root\>" test.txt
```

#### 分组：

```shell
    \(\)
    \(ab\)* :ab一个整体 
      
      后向引用
      
    He love his lover.
    She like her liker.
    He  love his liker.
    She like her lover.
    
    grep 'l..e*l..e*' text.txt 
    grep "l..e.*\1" text.txt
    grep "\(l..e\)" 
    
    \1:调用第一个左括号以及与之对应的右括号之间的内容。
    \2:
    \3:
    
    /etc/inittab 
    grep '\([0-90]\).*\1$'  /etc/inittab
```

### REGEXP：regular Expresssion

pattern:文本的过滤条件

正则表达式：

basic REGEXP:基本正则表达式

Extent REGEXP ：扩展正则表达式

基本正则表达式

```shell
    .
    []
    [^]
    
    次数匹配：
    *:
    \?:0或1次
    \{m,n\}:至少m次，至多n次
    
    .*:
    
    锚定：
    ^:
    $:
    \<,\b: 
    \>,\b:
    
    \(\)
    \1,\2....
```

#### grep：使用基本的正则表达式定义的模式来过滤文本的命令：

```shell
    -i：忽略大小写 
    -v 
    -o 
    --color 
    
    -E 支持扩展的正则表达式 
    -A  # ：显示匹配行及以后多少行也显示 
      after 
    -B：显示匹配行以及前面的n行
       before 
    -C:显示匹配行以及前后的n行
       contest 
    grep -A 2 ""  file 
    
    
    扩展正则表达式：
       贪婪模式
    
    字符匹配：
    .
    []
    [^]
    
    次数匹配：
    *：
    ?:
    +:匹配其前面的字符至少一次
    {m,n}
    
    位置锚定：
    ^
    $
    \<
    \>
    
    分组：
    ():分组
    \1,\2,\3.....
    
    或者：
    a|b  or 
    
    C|cat: 
    (C|c)at: 
    
    grep --color -E '^[[:space:]]+' /boot/grub/grub.conf 
    
    grep -E = egrep 
    
    egrep --color '\<([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-5][0-9]|25[0-5])\>' 
    
    (\<([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-5][0-9]|25[0-5])\>\.){3}'\<([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-5][0-9]|25[0-5])\>\.'
    
    IPV4：
    5类：
    A B C D E 
    A:1-127 
    B:128-191 
    C: 192--223 
    
    \<[1-9]|[1-9][0-9]|1[0-9]{2}|2[01][0-9]|22[0-30]\>
```

### sed(流编辑器)


#### sed基本用法：

```shell
    sed:stream Editor 
    行编辑器 
       文本编辑器 
       逐行处理文本 
      
    全屏编辑器：vim 
     
    内存空间：模式空间 
    sed 模式空间 
    匹配模式空间后，进行操作，将结果输出。仅对模式空间中的数据进行处理，而后，处理结束，将模式空间打印至屏幕；
    
    默认sed不编辑原文件，仅对模式空间中的数据进行处理。
```

#### sed [option] [sed-scripts]


#### option：

```shell
    -n：静默模式 
    -i:直接修改原文件
    -e scripts -e script:可以同时执行多个脚本。
    -f /path/to/sed_scripts  命令和脚本保存在文件里调用。
      sed -f /path/to/scripts  file 
    -r：表示使用扩展的正则表达式。
       只是进行操作，不显示默认模式空间的数据。
```

#### comamnd:

```shell
    address:指定处理的行范围
    
    sed 'addressCommand' file ... 
    对符合地址范围进行操作。
    Address: 
    1.startline,endline 
     比如1,100
       $:最后一行
    2./RegExp/ 
      /^root/
    3./pattern1/,/pattern2/ 
      第一次被pattern匹配到的行开始，至第一次pattern2匹配到的行结束，这中间的所有行。
    4.LineNumber 
       指定行 
    5.startline，+N 
     从startline开始，向后的N行。
     
    Command：
     d:删除符合条件的行。
         sed '3,$d' /etc/fstab
         sed '/oot/d' /etc/fstab 
    注意：模式匹配，要使用 // 
        sed '1d' file 
    p:显示符合条件的行 
     sed '/^\//d' /etc/fstab 
     sed '/^\//p' /etc/fstab 
       会显示两次
        先显示P匹配，再显示所有模式空间的数据。
    a \string:在指定的行后面追加新行，内容为"string"
    sed '/^\//a \# hello world' /etc/fstab 
    添加两行：
    sed '/^\//a \#hello world \n #hi' /etc/fstab 
    
    i \sting:在指定行的前面添加新行，内容为string。
    
    r file:将指定的文件的内容添加在指定行后面。
      sed '2r /etc/issue'   /etc/fstab 
      sed '$r /etc/issue' /etc/fstab 
    
    w file:将地址指定的范围的内容另存至另一文件中。
     sed '/oot/w /tmp/oot.txt' /etc/fstab 
     
    s/pattern/string/：查找并替换 
         sed  's/oot/OOT/'  /etc/fstab 
    sed 's/^\//#/' /etc/fstab 
    sed 's/\//#/'/etc/fstab 仅替换每一行第一次被模式匹配的串。
      加修饰符 
       g：全局替换 
       i：忽略大小写 
     sed 's/\//#/g'/etc/fstab
     
     s///:s###
     s@@@
     
    sed 's#+##' 
    
    后向引用
    
    l..e:like----->liker 
         love----->lover 
         
    sed 's#l..e#&r#' file
    &:表示模式匹配的引用 
    
    sed 's#l..e#\1r#' file 
    
    like---->Like
    love---->Love 
    sed 's#l\(..e\)#L\1#g' file 
    
    
    history |sed 's#[[:space:]]##g'
    history | sed 's#^[[:space:]]##g'
    
    sed ''dirname
```

#### 例子:chestnut:

```shell
    1.删除/etc/grub.conf文件中行首的空白符；
     sed  's/^[[:space:]]+//g' /etc/grub.conf 
     2.替换/etc/inittab文件中"id:3:initdefault:"一行中的3
     sed 's#id:3:init#id:5:initd#'
     sed 's@\(id:\)[0-9]\(:initdefault:\)@\15\2@g' /etc/inittab 
     3.删除/etc/inittab文件中的空白行。
      sed '/^$/d' /etc/inittab
    4.删除/etc/inittab文件中开头的#号
    sed 's/^#//'  
    5.删除莫文件中开头的#号以及空白行。
    sed 's/^[[:space:]]+//g' 
    6.删除某文件中以空白字符后面跟#类的行中开头的空白字符以及#
    sed -r 's/^[[:space:]]+#//g' 
    7.取出一个文件路径的目录名称
    echo '/etc/rc.d'|sed -r 's@^(/.*/)[^/]+/?@\1@g'
```

### awk（报告生成器）

```shell
    grep ：文本过滤器
    sed:流编辑器 
    
    
    grep option pattern file 
    sed addresscommmand file 
    sed 'comand/pattern/' file
```

awk（报告生成器）

```shell
    根据定义好的格式，显示出来。
    nawk 
    gawk
    gnu awk 
    
    awk option 'script' file file2 
    awk [option] 'pattern {action}' file file2 
    
    print 
    printf 自定义显示格式
    
    
    awk一次抽取一行，然后对每一行进行切割分片，每一片可以使用变量进行引用。
    $0:表示引用一整行
    $1:第一个切片
    $2:第二个切片 
    
    awk '{print $1}' text.txt 
    awk '{print $1,$2}' text.txt
```

#### 选项：

```shell
    -F  指定分隔符
    awk -F ''
    
    awk 'BEGIN{OPS="#"}{print $1,$2}' test.txt
    BEGIN{OPS=""} 输出分隔符
    
    输出特定字符
    awk '{print $1,"hello",$2,$3,$4,$5}' file 
    
    awk 'BEGIN{print "line one\nline two\nline tree"}'
    
    print的格式：
    print item1，item2...
    
    awk -F: 输入分隔符 
    OFS="#"   输出分隔符
```

#### awk变量

```shell
    awk内置变量
    FS: filed separator,读取文本时，所用字段分隔符
    RS:recordsepartor，输入文本信息所使用的换行符。
    OFS:OUT filed separator 
    ORS:Output ROw separator 
    
    awk -F:
    OFS="#"
    FS=":"
```

#### awk内置变量之数据变量

```shell
    NR: the number of input record ,awk命令所处理的记录，如果有多个文件，这个数据是所有处理的行数。
    FNR：当前文件所处理的行是本文件第多少行。
    NF：当前所处理的行有多少个字段。
    
    
    awk '{print NF}' file 
    awk '{print $NF}' file 
    awk '{print NR}' file
```

#### -v 定义变量

```shell
    awk -v test="hello awk" '{print test}' 
    awk -v test="hell awk" 'BEGIN{print test}'
    
    
    awk  'BEGIN{test='hello awk',print test}'
```

#### printf 格式化显示

```shell
    printf  format,item1，item2...
    
    awk 'BEGIN{printf %c,}'
    注意：printf不换行  
    
    %d 
    %e 
    %f 
    %g 
    
    修饰符
    -：左对齐 
    %nd：显示宽度 
    awk '{printf %-10s%-10s\n,$1,$2}' file
```

awk的操作符  
算术操作符  
字符串操作符  
布尔表达式
```
    x < y 
    x <= y 
    x > y 
    x != y 
    x ~ y 匹配 
    x !~ y
```

#### 表达式间的逻辑关系符

```shell
    && 
    ||
```

#### 条件表达式

```
    select?if-true-exp:if-false-exp 
    a>b?a=1:b=2
```

#### awk模式

```shell
    1.正则表达式 /pattern/
    2.表达式 
    3.REGEXP 指定匹配范围 
    4.BEGIN/END 
    5Empty  
    
    
    awk -F : '/^r/ {print $1}' /etc/passwd 
    awk -F ： '$3>=500{printf $1,$3}' /etc/passwd 
    awk -F: '$3+1>=500{print $1,$3}' /etc/passwd
    
    awk -F: '$7~"bash$"{print $1,$7}' /etc/passwd 
    进行匹配测试
    awk -F: '$7!~"bash$"{print $1,$7}' /etc/passwd 
    
    awk -F: '/^r/,/^m/{print $1,$7}' /etc/passwd 
    
    awk -F: '$3==0,$7~"bash"{print $1,$3,$7}' /etc/passwd 
    
    awk -F '{printf "%-10s%-10s%-20s\n",$1,$2,$3}' /etc/passwd 
    
    BEGIN ,END 
    
    awk -F: '$3==0,$7~"nologin"BEGIN{print "Username       ID    shell"}{printf "%-10s%-10s%-20s\n"$1,$3,$7} END{print "ending"}' /etc/passwd
```

#### action

```shell
    1.ExPression 
    2.control statements 
    3.compound statements 
    4.INput statment 
    5 output statements
```

#### 控制语句

```
    if-else
    
    if(condition) {then-body} else {[else-body]}
    eg:
    awk -F:
```

#### while

```shell
    while (condition){statement1;statement2;...}
    循环每一字段 
    length([string])
    
    awk -F: '{i=1; while (1<=NF) if {(length($i)>4) {print $i}; i++}}'
    
    df -hP |awk '{if($4 >=) Print $0}'
    
    
    do while 
    do{statement1,statement2,...} while(condition)
    
    for 
    for( ; ; ){statement1;statement2....}
    
    awk -F: '{for(i=1:i<=NF;i++){if(length($i)>=4){print $i}}}'  /etc/passwd 
```
    
    case 
    switch (exprssion) {case value or /regexp/:statement1,statement2,...default:statement,....}
     
    break和continue 
    contine是遍历字段的 
    
    next 
    提前结束对本行文本的处理，并接着处理下一行，

#### 数组

```
    数组下表是从1开始的
    awk[mon]=1 
    awk[tus]=2 
    
    
    for (var in arrary){statement,....}
    
    awk -F: '{shell[$NF]++}END {for(A in shell) {print A,shell[A]}}' /etc/passwd 
    
    nestat -tan 
    
    netstat -tan |awk '/^tcp/{STATE[$NF]++}END{for (S in STATE){print S,STATE[S]}}'
    
    awk '{count[$1]++}END{for ip in count}{printf "%-20s:%d\n",ip,count[ip]}}'  access_log
```


[1]: http://www.jianshu.com/p/19f14bab6acc

[4]: http://img2.tuicool.com/2umq2uJ.png