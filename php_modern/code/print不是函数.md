# print不是函数

 时间 2019-03-01 17:59:47  风雪之隅

_原文_[http://www.laruence.com/2019/03/01/4904.html][1]


这个源自于一个看似很诡异的问题:

    if (print("1\n") && print("2\n") && print("3\n") && print("4\n")) {
        ;
    }

你期待这段代码输出什么呢?

实际上的输出是:

很多时候我们会忽略了print是一个语法结构(language constructs), 他并不是一个函数, 参数的list并不要求有括号(即使你写了括号, 括号也会在语法分析阶段被忽略), 他只是一个永远返回结果是1的”表达式(expr)”.

所以其实上面的代码在php看来是:

    if (print ("1\n" && print ("2\n" && print ("3\n" && print "4\n")))) {
      ;
    }

所以就是, 输出4, 然后输出 “3\n” && print的结果1 , 然后输出 “2\n” && 1, 最后是 “1\n” && 1

而如果想要达到上面代码的本身想要的意图, 我们应该这么写:

    if ((print "1\n") && (print "2\n") && (print "3\n") && (print "4\n")) {
        ;
    }

类似的是语法结构而不是函数的还有: echo, unset, isset, empty, include, require, die, 他们都不要求用括号来传递参数, 即使你写了括号, 在语法分析阶段也会被忽略….

[1]: http://www.laruence.com/2019/03/01/4904.html?utm_source=tuicool&utm_medium=referral
