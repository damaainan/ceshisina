在编程语言中，循环语句是最基本的语法之一，在Shell（这里是Bash）中也不例外，再把以前自己写过的相关内容整理一下吧。  
  
这里包括for/while/until循环，以及变量自增的语法实例。

Shell（以Bash为例）中的循环语句一般有for、while、until这几种，偶尔还有写错语法的时候，这里结合实例来自己总结一下。也为今后使用提供一个快捷的资料获取渠道。

**一、for循环语句**

实例1.1 最基本的for循环： （传统的形式，for var in …)


```shell  
#!/bin/bash  
for x in one two three four  
do  
    echo number $x  
done
```
  
注：”for” 循环总是接收 “in” 语句之后的某种类型的字列表。在本例中，指定了四个英语单词，但是字列表也可以引用磁盘上的文件，甚至文件通配符。  
实例1.2 对目录中的文件做for循环  

```shell  
#!/bin/bash  
for x in /var/log/*  
do  
    #echo "$x is a file living in /var/log"  
    echo $(basename $x) is a file living in /var/log  
done
```
  
注：这个$x获得的是绝对路径文件名；可以使用 “basename” 可执行程序来除去前面的路径信息。如果只引用当前工作目录中的文件（例如，如果输入 “for x in *”），则产生的文件列表将没有路径信息的前缀。  
实例1.3 对位置参数做for循环  

```shell  
#!/bin/bash  
for thing in "$@"  
do  
    echo you typed ${thing}.  
done
```
  
实例1.4 for循环中用seq产生循环次数，加上C语言形式的for循环语句  

```shell  
#!/bin/bash  
echo "for: Traditional form: for var in ..."  
for j in $(seq 1 5)  
do  
    echo $j  
done  
  
echo "for: C language form: for (( exp1; exp2; exp3 ))"  
  
for (( i=1; i<=5; i++ ))  
    do  
    echo "i=$i"  
done
```
  
注：对于固定次数的循环，可以通过seq命令来实现，就不需要变量的自增了；这里的C语言for循环风格是挺熟悉的吧。 **二、while循环语句**  
实例2.1 循环输出1到10的数字


```shell  
#!/bin/bash  
myvar=1  
while [ $myvar -le 10 ]  
do  
    echo $myvar  
    myvar=$(( $myvar + 1 ))  
done
```
  
注：只要特定条件为真，”while” 语句就会执行 **三、until循环语句**  
实例3.1 循环输出1到10的数字  
“Until” 语句提供了与 “while” 语句相反的功能：只要特定条件为假，它们就重复。下面是一个与前面的 “while” 循环具有同等功能的 “until” 循环。


```shell  
#!/bin/bash  
myvar=1  
until [ $myvar -gt 10 ]  
do  
    echo $myvar  
    myvar=$(( $myvar + 1 ))  
done
```
  
Linux Shell中写循环时，常常要用到变量的自增，现在总结一下整型变量自增的方法。  
我所知道的，bash中，变量自增，目前有五种方法：  

1. i=`expr $i + 1`;  
2. let i+=1;  
3. ((i++));  
4. i=$[$i+1];  
5. i=$(( $i + 1 ))  

可以实践一下，简单的实例如下：  

```shell
    #!/bin/bash
    i=0;
    while [ $i -lt 4 ];
    do
          echo $i;
          i=`expr $i + 1`;
          # let i+=1;
          # ((i++));
          # i=$[$i+1];
          # i=$(( $i + 1 ))
    done
```
