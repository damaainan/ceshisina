## vim 常用命令整理

来源：[https://blog.qxzzf.com/archives/361/](https://blog.qxzzf.com/archives/361/)

时间 2018-05-27 20:24:00

 
### 文件操作
 
```
vim filename    //打开filename文件
:w              //保存文件
:w filename     //保存至vpser.net文件
:q              //退出编辑器，如果文件已修改请使用下面的命令
:q!             //退出编辑器，且不保存
:wq             //退出编辑器，且保存文件
```
 
### 进入插入模式
 
```
i   //在光标所在字符前进入插入模式
a   //在光标所在字符后进入插入模式
I   //在当前行的开始处添加文本(非空字符的行首)
A   //在当前行的末尾位置添加文本
s   //删除光标所在位置字符并进入插入模式
cw  //删除光标所在位置至当前单词结尾内容并进入插入模式
c$  //删除光标所在位置至当前行结尾内容并进入插入模式
cc  //删除光标所在行所有内容并进入插入模式
o   //在当前行下方插入新行并进入插入模式
O   //在当前行上方插入新行并进入插入模式
R   //替换(覆盖)当前光标位置及后面的若干文本
J   //合并光标所在行及下一行为一行(依然在命令模式)
```
 
### 复制、粘贴
 
```
yy          //将当前行复制到缓存区，也可以用 "ayy 复制，"a 为缓冲区，a也可以替换为a到z的任意字母，可以完成多个复制任务。
nyy、yny    //将当前行向下n行复制到缓冲区，也可以用 "anyy 复制，"a 为缓冲区，a也可以替换为a到z的任意字母，可以完成多个复制任务。
yw          //复制从光标开始到词尾的字符。
nyw         //复制从光标开始的n个单词。
y^          //复制从光标到行首的内容。  VPS侦探
y$          //复制从光标到行尾的内容。
p           //粘贴剪切板里的内容在光标后，如果使用了前面的自定义缓冲区，建议使用"ap 进行粘贴。
P           //粘贴剪切板里的内容在光标前，如果使用了前面的自定义缓冲区，建议使用"aP 进行粘贴。
```
 
### 删除、撤销
 
```
x           //删除光标所在位置字符
nx          //删除从光标开始的n个字符
dw          //删除光标所在位置至当前单词结尾内容
d$          //删除光标所在位置至当前行结尾内容
dd          //删除当前行，删除内容会进入 vim 剪贴板
dnd、ndd    //删除n行，例：d3d 为删除3行

u       //撤销上一步操作
U       //撤销对当前行的所有操作
```
 
### 移动
 
```
h,j,k,l     //左、上、下、右
空格键      //向右
Backspace   //向左
Enter       //移动到下一行首
-           //移动到上一行首
w           //正向移动到下一单词的开头
e           //正向移动到当前/下一单词的结尾
b           //反向移动到当前/上一单词的开头
ge          //反向移动到上一单词的结尾
gg          //到文档顶部
G           //到文档底部
n+          //向下跳n行
n-          //向上跳n行
ngg、nG     //到第 n 行

f{char}     //正向移动到下一个{char}所在位置
F{char}     //反向移动到上一个{char}所在位置
t{char}     //正向移动到下一个{char}的前一个字符上
T{char}     //反向移动到上一个{char}的后一个字符上

``  //当前文件上次跳转操作的位置
`.  //上次修改操作的地方
`^  //上次插入的地方
`[  //上次修改或复制的起始位置
`]  //上次修改或复制的结束位置
`<  //上次高亮选区的起始位置
`>  //上次高亮选区的结束位置
```
 
### 查找和替换
 
```
/vpser      //向光标下搜索vpser字符串
?vpser      //向光标上搜索vpser字符串
n           //向下搜索前一个搜素动作
N           //向上搜索前一个搜索动作

:s/old/new          //用new替换行中首次出现的old
:s/old/new/g        //用new替换行中所有的old
:n,m s/old/new/g    //用new替换从n到m行里所有的old
:%s/old/new/g       //用new替换当前文件里所有的old
```
 
### 其他
 
```
=               //格式化，例 gg=G 格式化整个文件
:e otherfile    //编辑文件名为 otherfile 的文件
:r otherfile    //将 otherfile 文件中的内容写到当前文件
:set  nu        //显示行号
:set nonu       //取消显示行号
:set fileformat=unix    //将文件修改为unix格式，如win下面的文本文件在linux下会出现^M
:w !sudo tee %          //保存时获取sudo权限
```
 
### vim 的常规语法
 
#### 动词
 
动词代表了我们打算对文本进行什么样的操作。例如：
 
 
* d 表示删除（delete） 
* r 表示替换（replace） 
* c 表示修改（change） 
* y 表示复制（yank） 
* v 表示选取（visual select） 
 
 
#### 名词
 
名词代表了我们即将处理的文本。Vim 中有一个专门的术语叫做文本对象（text object），下面是一些文本对象的示例：
 
 
* $ 表示行尾 
* ^ 表示行首 
* w 表示一个单词（word） 
* s 表示一个句子（sentence） 
* p 表示一个段落（paragraph） 
* t 表示一个 HTML 标签（tag） 
* 引号或者各种括号所包含的文本称作一个文本块。 
 
 
#### 介词
 
介词界定了待编辑文本的范围或者位置。例如：
 
 
* i 表示“在...之内”（inside） 
* a 表示“环绕...”（around） 
* t 表示“到...位置前”（to） 
* f 表示“到...位置上”（forward） 
 
 
![][0]
 
#### 组词为句
 
动词 + 介词 + 名词
 
```
dip //删除一个段落: delete inside paragraph
vis //选取一个句子: visual select inside sentence
ciw //修改一个单词: change inside word
caw //修改一个单词: change around word
dtx //删除文本直到字符“x”（不包括字符“x”）: delete to x
dfx //删除文本直到字符“x”（包括字符“x”）: delete forward x
```
 
动词 + 数词 + 名词 or 数词 + 动词 + 名词
 
```
c3w //修改三个单词：change three words
d2w //删除两个单词：delete two words

2dw //两次删除单词（等价于删除两个单词）: twice delete word
3x  //三次删除字符（等价于删除三个字符）：three times delete character
```
 
### 参考文档
 
 
* [有关vi(vim)的常用命令][1]  
* [Vim常用文档动作命令总结][2]  
* [一起来说 Vim 语][3]  
 
 


[1]: https://mp.weixin.qq.com/s/zZAWpZbDtSFK6EROxaBRKw
[2]: https://www.jianshu.com/p/52b1b41de71f
[3]: https://www.jianshu.com/p/a361ce8c97bc
[0]: https://img2.tuicool.com/ERBfQfr.png 