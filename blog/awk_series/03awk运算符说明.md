[awk 运算符（算术运算符，赋值运算符，关系运算符，逻辑运算符，正则运算符）说明][0]

awk作为文本处理优秀工具之一，它有独自丰富的运算符。下面我们一起归纳总结一下，所有运算符。 可以分为：算术运算符，赋值运算符，关系运算符，逻辑预算法，正则运算符。

**一、运算符介绍**

运算符 | 描述 
-|-
**赋值运算符** | - 
= += -= *= /= %= ^= **= | 赋值语句 
**逻辑运算符** | - 
&#124;&#124; | 逻辑或 
&& | 逻辑与 
**正则运算符** | - 
~ ~! | 匹配正则表达式和不匹配正则表达式 
**关系运算符** | - 
< <= > >= != == | 关系运算符 
**算术运算符** | - 
+ - | 加，减 
* / & | 乘，除与求余 
+ - ! | 一元加，减和逻辑非 
^ *** | 求幂 
++ -- | 增加或减少，作为前缀或后缀 
**其它运算符** | - 
$ | 字段引用 
空格 | 字符串连接符 
?: | C条件表达式 
in | 数组中是否存在某键值 

说明：awk运算符基本与c语言相同。表达式及功能基本相同

**二、实例介绍**

* awk赋值运算符
```
    a+=5; 等价于：a=a+5; 其它同类
```
* awk逻辑运算符
```
    [chengmo@localhost ~]$ awk 'BEGIN{a=1;b=2;print (a>5 && b<=2),(a>5 || b<=2);}'  
    0 1
```
* awk正则运算符
```
    [chengmo@localhost ~]$ awk 'BEGIN{a="100testa";if(a ~ /^100*/){print "ok";}}'  
    ok
```
* awk关系运算符
```
    如： > < 可以作为字符串比较，也可以用作数值比较，关键看操作数如果是字符串 就会转换为字符串比较。两个都为数字 才转为数值比较。字符串比较：按照ascii码顺序比较。

    [chengmo@localhost ~]$ awk 'BEGIN{a="11";if(a >= 9){print "ok";}}'

    [chengmo@localhost ~]$ awk 'BEGIN{a=11;if(a >= 9){print "ok";}}'   
    ok
```
* awk算术运算符
```
    说明，所有用作算术运算符 进行操作，操作数自动转为数值，所有非数值都变为0。

    [chengmo@localhost ~]$ awk 'BEGIN{a="b";print a++,++a;}'   
    0 2
```
* 其它运算符
```
    ?:运算符

    [chengmo@localhost ~]$ awk 'BEGIN{a="b";print a=="b"?"ok":"err";}'  
    ok 

    in运算符

    [chengmo@localhost ~]$ awk 'BEGIN{a="b";arr[0]="b";arr[1]="c";print (a in arr);}'  
    0 

    [chengmo@localhost ~]$ awk 'BEGIN{a="b";arr[0]="b";arr["b"]="c";print (a in arr);}'  
    1 

    in运算符，判断数组中是否存在该键值。
```


[0]: http://www.cnblogs.com/chengmo/archive/2010/10/11/1847515.html