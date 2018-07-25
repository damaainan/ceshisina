## Shell脚本编程----变量的使用

来源：[https://juejin.im/post/5b027d2d51882542c8330bd6](https://juejin.im/post/5b027d2d51882542c8330bd6)

时间 2018-05-22 09:58:11

 
接触Linux Shell脚本编程很久了，但是发现每次学完之后就会忘记，这似乎又印证了那句"好记性不如烂笔头"的言语，事实确实如此，坚持写博客，定期回顾，你会感觉很充实。
 
这是本系列教程的第一篇----变量的使用，该系列文章不是我的原创制作，但它们是我从英文翻译而来，行文中很夹杂着一些自己的理解和实践。我喜欢看英文文档，不过有时候看着看着就想睡觉，本系列教程不会回答诸如Shell是什么？有什么作用的问题，我们会直接敲代码，直接了当~~接下来让我们一起一步一步学习Shell编程吧！
 
在本章中，我们将学习如何在Unix中使用Shell变量。变量是我们为其分配值的字符串。分配的值可以是`数字`，`文本`，`文件名`，`设备`或`任何其他类型的数据`。一个变量只不过是指向实际数据的指针，或者说是一个容器，你可以在Shell中创建、分配和删除变量。
 
原文网址：https://www.tutorialspoint.com/unix/unix-special-variables.htm
 
### 一、变量名
 
一个Sheel变量名只能由英文字母`（A-Z/a-z）`、数字`（0-9）`或下划线`_`组成，并且不能由数字打头，这和Java中变量的命名其实是一样的~
 
按照惯例，Shell变量一般都是大写字母，当然，这并不是必须的。
 
举个例子，下面的变量名是合法的：
 
```sh
_ALI
TOKEN_A
VAR_1
VAR_2
```
 
下面的变量是非法的：
 
```sh
2_VAR  # 以数字打头
-VARIABLE  # -是非法字符
VAR1-VAR2
VAR_A! # !是特殊字符
```
 
你之所以不能使用像`!`、`*`、`？`、`-`这些字符进行命名，因为这些变量在Shell中有特殊含义。
 
### 变量定义
 
Shell中采用下面的形式定义变量：
 
```sh
variable_name=variable_value
```
 
左边是变量的名字，右边是变量值。举个例子：
 
```sh
NAME="Zara Ali"
```
 
上面定义了一个Name变量，它的值为`Zara Ali`,对于这种类型的变量，Shell中称为标量变量，一个标量变量在某一时刻只能有一个值与之对应。
 
Shell使您能够在变量中存储任何想要的值。举个例子：
 
```sh
VAR1="Zara Ali"
VAR2=100
```
 
### 二、使用变量
 
如果你想访问变量的值，在Shell中你需要使用`$`前缀，举个例子，下面的脚本将会访问之前定义的`Name`变量并将它打印出来。
 
```sh
#!/bin/sh

NAME="Zara Ali"
echo $NAME
```
 
上面的脚本将打印出`Zara Ali`.
 
笔者在自己的阿里云测试如下：
 
 ![][0]
 
### 三、只读变量
 
Shell允许你使用`read-only`命令将变量声明为只读方式，一旦将变量声明为只读模式，变量的值不能被改变。
 
举个栗子，下面的脚本片段将抛出一个异常信息，因为我们试图改变一个只读变量的值：
 
```sh
#!/bin/sh

NAME="Zara Ali"
readonly NAME
NAME="Qadiri"
```
 
执行结果如下：
 `./FirstShell.sh: line 3: NAME: readonly variable`笔者亲测如下图：
 
 ![][1]
 
### 四、取消变量赋值
 
取消变量赋值或删除变量会指示shell从它跟踪的变量列表中删除变量。一旦你取消了某个变量的赋值，你就不能访问到该变量的值了。
 `unset`命令用于取消某个变量的赋值，其语法格式如下：
 `unset variable_name`举个例子：
 
```sh
#!/bin/sh

NAME="Zara Ali"
unset NAME
echo $NAME
```
 
上面的脚本不会打印出任何东西，因为你不能输出使用`unset`命令修饰的变量，此时变量已经没有值了。你可能会问，那么被`unset`修饰过的变量还可以再次被赋值吗？答案是肯定的。笔者将脚本修改如下：
 
```sh
#!/bin/sh

NAME="Zara Ali"
unset NAME
NAME="S"
echo $NAME
```
 
上面的脚本将会输出`S`

### 五、变量类型
 
Shell中主要存在三种变量类型，分别是：
 
 
* 1、局部变量
局部变量就是只存在与某个shell实例的变量，它不适用于由shell启动的程序。 它们在命令提示符处设置。
  
* 2、环境变量
环境变量可用于shell的任何子进程。 某些程序需要环境变量才能正常工作。 通常，shell脚本只定义它运行的程序所需的那些环境变量。
  
* 3、Shell变量
一个shell变量是一个特殊的变量，它由shell设置并且为了正常工作而被shell需要。 其中一些变量是环境变量，而另一些则是局部变量。
  
 
 
### 六、特殊变量
 
在此节中我们将讨论Unix中的特殊变量。举个例子，`$`表示当前Shell所处的进程号PID：
 `echo $$`上面将输出你当前的PID，如：
 
 ![][2]
 
下面表格列出了你能在Shell脚本中使用的特殊变量：
 
| S.NO | Variable & Description | 
|-|-|
| 1 | **`$0`**  当前脚本的文件名称 | 
| 2 | **`$n`**  这些变量对应于脚本被调用的参数,如$1表示调用该脚本时传入参数中的第一个参数，$2表示第二个，以此类推 | 
| 3 | **`$#`**  调用当前脚本传入的参数个数 | 
| 4 | **`$`**  *  传递给脚本或函数的所有参数 | 
| 5 | **`$@`**  传递给脚本或函数的所有参数 | 
| 6 | **`$?`**  上个命令的退出状态，或函数的返回值 | 
| 7 | **`$$`**  当前Shell进程ID。对于 Shell 脚本，就是这些脚本所在的进程ID。 | 
| 8 | **`$!`**  最后一个后台命令的进程号PID | 
 
 
### 七、命令行参数
 
命令行参数`$1`,`$2`，`$3`,...,`$9`是位置参数，`$0`指向实际的命令，程序，shell脚本或函数，`$1`,`$2`,`$3`,...,`$9`作为参数命令。下面的栗子展示了各种特殊变量的使用：
 
```sh
#!/bin/sh

echo "File Name: $0"
echo "First Parameter : $1"
echo "Second Parameter : $2"
echo "Quoted Values: $@"
echo "Quoted Values: $*"
echo "Total Number of Parameters : $#"
```
 
采用下面的方式运行，结果如下：
 
```sh
$./FirstShell.sh Zara Ali
File Name : ./FirstShell.sh
First Parameter : Zara
Second Parameter : Ali
Quoted Values: Zara Ali
Quoted Values: Zara Ali
Total Number of Parameters : 2
```
 
 ![][3]
 
### 八、`$*`和`$@`的区别 
 `$*`和`$@`都表示传递给函数或脚本的所有参数，不被双引号(`""`)包含时，都以`$1 $2 … $n`的形式输出所有参数。
 
但是当它们被双引号(`""`)包含时，`$*`会将所有的参数作为一个整体，以`$1``$2``…``$n`的形式输出所有参数；`$@`会将各个参数分开，以`$1``$2``…``$n`的形式输出所有参数
 
```sh
#!/bin/sh

for TOKEN in "$*"
do
   echo $TOKEN
done

for token in "$@"
do
   echo $token
done
```
 
上面脚本中 *被双引号包括，当采用下面方式调用时，两者输出的结果是不一样的，如下：
 
 ![][4]
 
### 九、退出状态
 `$!`表示上一条命令或者函数的执行状态，如果返回0，则表示执行成功，1表示失败
 


[0]: https://img2.tuicool.com/3ymiuu7.png 
[1]: https://img0.tuicool.com/IbYJjmU.png 
[2]: https://img0.tuicool.com/BvmiauV.png 
[3]: https://img2.tuicool.com/fUVzeiU.png 
[4]: https://img0.tuicool.com/fiQj2eV.png 