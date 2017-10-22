# shell中四种括号作用

 时间 2017-10-22 16:45:27 

原文[http://www.jianshu.com/p/112b0a85fe90][1]


### 单括号

* 一次性执行多条命令，各命令之间用分号分隔，最后一个命令可以没有分号 例：(cd /home; touch demo;)
* 将命令执行结果输出给一个变量 例：a=$(date) ，下面3个赋值语句效果均相同

```shell
    1. a=$(date)
    2. a=`date`
    3. a=$`date`
```
* 初始化数组 例：arr=(a b c d)

### 双括号

* 所有变量可以不加"$"前缀，直接进行四则运算

```shell
    注： 1、2、3 等价，b都等于2
    a=3.14
    1. ((b=a+1))
    2. b=`expr $a + 1`
    3. let "b=a+1"
    4. b=$[$a+1]
    5. b=`echo "scale=2;$a+0.69"|bc` 
    6. b=`echo "$a 3.14"|awk '{printf("%g",$1*$2)}'`
    echo $b
```

等式5中的bc可进行浮点型计算，使用scale进行精度设置 

等式6是利用awk进行计算，awk也支持浮点型计算，且内置有 log、sqr、cos、sin等函数 

* 可以像C语言一样，执行C语言规则运算，如a++、b--

```shell
    a=1
    ((a++))
    echo a
```

* 可进行逻辑运算

```shell
    a=1
    echo $((a>1?8:9))
```

* 双括号结构扩展了for、while、if等条件测试运算

#### for语法

```shell
    num=100
    total=0
    for((i=0;i<=num;i++))
    do
        ((total+=i))
    done
    echo $total
```

#### while语法

```shell
    num=100
    total=0
    i=0
    while((i<=num))
    do
        ((total+=i,i++))
    done
    echo $total
```

#### if语法

```shell
    a=3
    if((a>1)); then
      echo 'yes'
    fi
```

### 单中括号

* 通过 `type [` 可知，`[` 是内建命令( [ is a shell builtin )，它是调用 test 命令的标识，右中括号关闭条件判断
* `[ ]`字符串比较是按照字典顺序，常用 ==、!=
* `[ ]`整数比较采用 -eq、-gt、-lt 的形式
* `[ ]`是shell命令，所以中间的表达式是命令行参数，如在比较时使用 >< 时，需要用 \ 转义，否则就变成重定向
* `[ ]`的逻辑与用 -a ，逻辑或用 -o
* 字符范围，用作正则表达式的一部分
* 在一个array结构的上下文中，[]用来引用数组中的每个元素的编号
* `[ ]`不会进行算数扩展，例

```shell
    if [ 99+1 -eq 100 ]; then
      echo 'yes'
    fi
    
     bash: [: 99+1: integer expression expected
```

### 双中括号

* `type [[` 可知， `[[` 是`shell关键字`
* `[[ ]]`字符串比较是按照字典顺序，常用 ==、!=
* `[[ ]]`整数比较采用 -eq、-gt、-lt 的形式
* 当比较表达式中其中一个比较数可能为空或者不存在时，使用 `[ ]` 会报错，使用 `[[ ]]` 可以避免报错的问题

```shell
    if [ a -eq 3 ]; then      --- [: a: integer expression expected
    if [[ a -eq 3 ]]; then
     echo 'yes'
     fi
    fi
```

* `[[ ]]`的逻辑与用 && ，逻辑或用 ||
* `[[ ]]`支持算数扩展，例

```shell
    if [[ 99+1 -eq 100 ]]; then
      echo 'yes'
    fi
```


[1]: http://www.jianshu.com/p/112b0a85fe90
