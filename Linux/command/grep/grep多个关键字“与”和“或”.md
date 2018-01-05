# Linux: grep多个关键字“与”和“或”

时间 2014-05-15 01:31:47  [Just Code http://www.tuicool.com/articles/6VJzI3v][0]

原文[http://justcoding.iteye.com/blog/2066598][1]


**1、或操作**

    grep -E '123|abc' filename  // 找出文件（filename）中包含123或者包含abc的行
    egrep '123|abc' filename    // 用egrep同样可以实现
    awk '/123|abc/' filename   // awk 的实现方式

**2、与操作**

    grep pattern1 files | grep pattern2 //显示既匹配 pattern1 又匹配 pattern2 的行。

**3、其他操作**

    grep -i pattern files   //不区分大小写地搜索。默认情况区分大小写，
    grep -l pattern files   //只列出匹配的文件名，
    grep -L pattern files   //列出不匹配的文件名，
    grep -w pattern files  //只匹配整个单词，而不是字符串的一部分（如匹配‘magic’，而不是‘magical’），
    grep -C number pattern files //匹配的上下文分别显示[number]行，

[0]: http://www.tuicool.com/sites/YNzYvu
[1]: http://justcoding.iteye.com/blog/2066598?utm_source=tuicool&utm_medium=referral
[2]: http://www.tuicool.com/topics/11200019