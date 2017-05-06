[linux shell awk 流程控制语句（if,for,while,do)详细介绍][0]

在linux awk的 while、do-while和for语句中允许使用break,continue语句来控制流程走向，也允许使用exit这样的语句来退出。break中断当前正在执行的循环并跳到循环外执行下一条语句。if 是流程选择用法。 awk中，流程控制语句，语法结构，与c语言类型。下面是各个语句用法。

**一.条件判断语句(if)**
```
if(表达式) # if ( Variable in Array )   
语句1  
else  
语句2
```
格式中"语句1"可以是多个语句，如果你为了方便Unix awk判断也方便你自已阅读，你最好将多个语句用{}括起来。Unix awk分枝结构允许嵌套，其格式为： 
```
if(表达式)

{语句1}

else if(表达式)  
{语句2}  
else  
{语句3}
```
```sh
[chengmo@localhost nginx]# awk 'BEGIN{   
test=100;
if(test>90)
{
    print "very good";
}
else if(test>60)
{
    print "good";
}
else
{
    print "no pass";
}
}'

very good
```
每条命令语句后面可以用“；”号结尾。 

**二.循环语句(while,for,do)**

> **1.while语句**

> **格式：**

    while(表达式)
    
    {语句}

> **例子：**

```sh
[chengmo@localhost nginx]# awk 'BEGIN{ 
test=100;
total=0;
while(i<=test)
{
    total+=i;
    i++;
}
print total;
}'
5050
```

> **2.for 循环**

> **for循环有两种格式：**

> **格式1：**

    for(变量 in 数组)
    
    {语句}

> **例子：**

```sh
[chengmo@localhost nginx]# awk 'BEGIN{ 
for(k in ENVIRON)
{
    print k"="ENVIRON[k];
}
}'

AWKPATH=.:/usr/share/awk
OLDPWD=/home/web97
SSH_ASKPASS=/usr/libexec/openssh/gnome-ssh-askpass
SELINUX_LEVEL_REQUESTED=
SELINUX_ROLE_REQUESTED=
LANG=zh_CN.GB2312

。。。。。。

```

> 说明：ENVIRON 是awk常量，是子典型数组。

> **格式2：**

    for(变量;条件;表达式)
    
    {语句}

> **例子：**

```sh
[chengmo@localhost nginx]# awk 'BEGIN{ 
total=0;
for(i=0;i<=100;i++)
{
    total+=i;
}
print total;
}'

5050
```

> **3.do循环**

> **格式：**

    do
    
    {语句}while(条件)

> **例子：**

    [chengmo@localhost nginx]# awk 'BEGIN{ 
    total=0;
    i=0;
    do
    {
        total+=i;
        i++;
    }while(i<=100)
    print total;
    }'
    5050

以上为awk流程控制语句，从语法上面大家可以看到，与c语言是一样的。有了这些语句，其实很多shell程序都可以交给awk，而且性能是非常快的。 

-|-
-|-
 break |  当 break 语句用于 while 或 for 语句时，导致退出程序循环。  
 continue |  当 continue 语句用于 while 或 for 语句时，使程序循环移动到下一个迭代。  
 next |  能能够导致读入下一个输入行，并返回到脚本的顶部。这可以避免对当前输入行执行其他的操作过程。  
 exit |  语句使主输入循环退出并将控制转移到END,如果END存在的话。如果没有定义END规则，或在END中应用exit语句，则终止脚本的执行。

**三、性能比较**
```
[chengmo@localhost nginx]# time (awk 'BEGIN{ total=0;for(i=0;i<=10000;i++){total+=i;}print total;}')  
50005000 

real 0m0.003s  
user 0m0.003s  
sys 0m0.000s  
[chengmo@localhost nginx]# time(total=0;for i in $(seq 10000);do total=$(($total+i));done;echo $total;)  
50005000 

real 0m0.141s  
user 0m0.125s  
sys 0m0.008s 

实现相同功能，可以看到awk实现的性能是shell的50倍！
```


[0]: http://www.cnblogs.com/chengmo/archive/2010/10/04/1842073.html