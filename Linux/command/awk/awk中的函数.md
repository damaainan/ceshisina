## awk中的函数

来源：[https://www.jellythink.com/archives/140](https://www.jellythink.com/archives/140)

时间 2018-03-16 19:47:50



## 另开炉灶

在总结《玩玩awk》这篇文章中，发现写着，写着就收不住了，内容有点多，在最后总结函数时，发现还是另起一篇文章比较好，所以这里就单独写一篇文章总结awk中的函数。希望通过这篇文章以及上一篇《玩玩awk》，大家能够基本掌握awk，并且能在工作中将awk作为一个工具来使用。


## 开门见山

由于awk是脚本语言，没有高级语言中那些复杂的数据类型定义。在awk中定义变量时，每个变量都有一个字符串类型值和数字类型值，所以，在awk中涉及的函数无非就是算术运算，字符串处理，同时也支持用户自定义函数。接下来的文章内容将按照以下三部分展开来说：



* 算术函数
* 字符串函数
* 自定义函数
  

开始踏上征程。


## 算术函数

算术函数基本上都接收数值型参数并返回数值型，常用的算术函数如下：

| 函数名称 | 描述 | 举例说明 |
|-|-|-|
| int(x) | 对小数向下取整 | awk '{print int(4.5), int(4.1), int(4.6)}'，输出：4 4 4 |
| rand(x) | 返回随机数r，其中0<=r<1 | awk '{print rand(), rand(), rand()}'，输出：0.237788 0.291066 0.845814 |
| srand(x) | 生成rand()的新的种子数，如果没有指定种子数，就用当天的时间。该函数返回旧的种子值 | 请参见以下详细总结 |


上述的三个函数中，随机数是我们使用最多的，下面就详细说说`rand`和`srand`这两个函数。

有这么一段awk脚本：

```sh
BEGIN {
    print rand();
    print rand();
    srand();
    print rand();
    print rand();
}
```

我们将这段脚本保存为rand.awk。

```sh
awk -f rand.awk
```

运行第一次输出以下结果：

```sh
0.237788
0.291066
0.0118226
0.115346
```

运行第二次输出以下结果：

```sh
0.237788
0.291066
0.779411
0.897179
```

你会发现，两次运行的前两个输出的随机数值是一样的，这也是使用这两个函数需要注意的地方。如果没有调用`srand`函数，awk在开始执行程序之前默认以某个常量为参数调用`srand`，类似于`srand(2)`这样的；这就使得程序在每次运行时都从同一个种子数开始，从而导致了输出了相同的随机数。如果我们希望每次运行脚本都输出不同的随机数，最好的办法就是在BEGIN部分调用`srand`函数。


## 字符串函数

在任何一门语言中，字符串的处理都是非常重要的，awk也不例外，现在看看awk中的字符串函数：

| 函数名称 | 描述 |
|-|-|
| gsub(r, s, t) | 在字符串t中用字符串s替换正则表达式r匹配的所有字符串。返回替换的个数。如果没有给出t，默认$0 |
| index(s, t) | 返回子串t在字符串s中的位置 |
| length(s) | 返回字符串s的长度，当没有给出s时，返回\$0的长度 |
| match(s, r) | 如果正则表达式r在s中出现，则返回出现的起始位置；如果在s中没有出现r，则返回0 |
| split(s, a, sep) | 使用字段分隔符sep将字符串s分解到数组a的元素中，返回元素的个数。如果没有给出sep，则使用FS。数组分隔和字段分隔采用同样的方式 |
| sprintf | 格式化输出 |
| sub(r, s, t) | 在字符串t中用s替换正则表达式r的首次匹配。如果成功则返回1，否则返回0，如果没有给出t，默认为\$0 |
| substr(s, p, n) | 返回字符串s中从位置p开始最大长度为n的子串。如果没有给出n，返回从p开始剩余的字符串 |
| tolower(s) | 将字符串s中的所有大写字符转换为小写，并返回新串，原字符串并不会被改变 |
| toupper(s) | 将字符串s中的所有小写字符转换为大写，并返回新串，原字符串并不会被改变 |


在awk中提供了两个字符串替换函数：`gsub`和`sub`。两者的区别是`gsub`是全局替换，而`sub`只替换第一次匹配的内容。

```
测试数据：Jelly:26:12474125874:04713365412:0081245:Jelly

{
    # 将每行上匹配"Jelly"的字符串全部替换为"JellyThink"
    if (gsub(/Jelly/, "JellyThink"))
        print           # 输出：JellyThink:26:12474125874:04713365412:0081245:JellyThink

    # 将第一个匹配"JellyThink"的字符串替换为"Jelly"
    if (sub(/JellyThink/, "Jelly"))
        print           # 输出：Jelly:26:12474125874:04713365412:0081245:JellyThink

    # 将所有大写字符转换成小写    
    print tolower($0)   # 输出：jelly:26:12474125874:04713365412:0081245:jellythink

    # 将所有小写字符转换成大写
    print toupper($0)   # 输出：JELLY:26:12474125874:04713365412:0081245:JELLYTHINK

    # 返回"T"字符的位置，只能返回字符的位置
    print index($0, "T")

    # 将$0进行分割，并计算每个字段的长度，输出如下：
    # [1]=Jelly       , 长度:5
    # [2]=26          , 长度:2
    # [3]=12474125874 , 长度:11
    # [4]=04713365412 , 长度:11
    # [5]=0081245     , 长度:7
    # [6]=JellyThink  , 长度:10
    n = split($0, field, ":")
    for (i=1; i<=n; ++i)
    {
        value=sprintf("[%d]=%-12s, 长度:%d", i, field[i], length(field[i]));
        print value
    }

    if (location = match($0, reg))
    {
        printf("在%d位置匹配到了%s\n", location, reg)
    }
    else
    {
        printf("很抱歉，没有匹配到了%s\n", reg)
    }
}
```


## 自定义函数

让人进行DIY，总是能让人感到兴奋，在awk中，我们也可以自定义我们自己的函数，在awk中定义函数的写法如下：

```sh
function name(parameter-list)
{
    statements
}
```

其中parameter-list是用逗号分隔的参数列表，当函数被调用时，它们被作为参数传递到函数中。接下来使用一个简单的例子来说明自定义函数的使用：

```sh
测试数据：HelloWorld

# 定义函数
function insert(string, pos, ins)
{
    before = substr(string, 1, pos)
    after = substr(string, pos + 1)
    return before ins after
}

# 脚本主体
{
    print insert($0, 5, "JellyThink")
print before #输出：Hello
print after  #输出：World
print $0     #输出：HelloWorld
}
```

在脚本的主体部分，我们打印before和after的值时，发现是可以输出的。这里有一点需要注意。

awk中，函数中定义的变量，默认是全局的，而传递的参数都是值传递，也就是说即使在函数内部修改了传递进来的参数的值，在函数外部，该参数的值是不会发生改变的。这到和Lua有几分相像。

再看这样的写法：

```sh
测试数据：HelloWorld

# 定义函数
function insert(string, pos, ins, before, after)
{
    before = substr(string, 1, pos)
    after = substr(string, pos + 1)
    return before ins after
}

# 脚本主体
{
    print insert($0, 5, "JellyThink")
    print before   #输出：<空>
    print after    #输出：<空>
    print $0       #输出：HelloWorld
}
```

现在明白了么？在工作中写awk函数时，需要注意以下两点：



* 参数是值传递
* 参数内部定义的变量也是全局变量
  

## 总结

总结这么一篇文章不容易，又要想好怎么排版写这篇总结，又要去验证文章中的每一段代码，这篇文章和《玩玩awk》这篇，在十月初就已经动手开始写了，后来折腾阿里云，浪费了不少时间，还好，今天终于写完了。不容易！！！Fighting~~~

果冻想-一个原创技术文章分享网站。

2015年10月19日 于呼和浩特。


