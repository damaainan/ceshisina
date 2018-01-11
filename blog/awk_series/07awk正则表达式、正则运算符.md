[awk 正则表达式、正则运算符详细介绍][0]

前言：使用awk作为文本处理工具，正则表达式是少不了的。 要掌握这个工具的正则表达式使用。其实，我们不必单独去学习它的正则表达式。正则表达式就像一门程序语言，有自己语法规则已经表示意思。 对于不同工具，其实大部分表示意思相同的。在linux众多文本处理工具（awk,sed,grep,perl)里面用到正则表达式。其实就只有3种类型。详细可以参考：[**linux shell 正则表达式(BREs,EREs,PREs)差异比较**][1] 。只要是某些工具是属于某种类型的正则表达式。那么它的语法规则基本一样。 通过那篇文章，我们知道awk的正则表达式，是属于：**扩展的正则表达式**（Extended Regular Expression 又叫 Extended RegEx 简称 EREs）。

**一、awk Extended Regular Expression (ERES)基础表达式符号介绍**

字符 | 功能
-|-
+ | 指定如果一个或多个字符或扩展正则表达式的具体值（在 +（加号）前）在这个字符串中，则字符串匹配。命令行：awk '/smith+ern/' testfile将包含字符 smit，后跟一个或多个 h 字符，并以字符 ern 结束的字符串的任何记录打印至标准输出。此示例中的输出是：smithern, harry smithhern, anne 
? | 指定如果零个或一个字符或扩展正则表达式的具体值（在 ?（问号）之前）在字符串中，则字符串匹配。命令行：awk '/smith?/' testfile将包含字符 smit，后跟零个或一个 h 字符的实例的所有记录打印至标准输出。此示例中的输出是：smith, alan smithern, harry smithhern, anne smitters, alexis
&#124; | 指定如果以 |（垂直线）隔开的字符串的任何一个在字符串中，则字符串匹配。命令行：awk '/allen | alan /' testfile将包含字符串 allen 或 alan 的所有记录打印至标准输出。此示例中的输出是：smiley, allen smith, alan
( ) | 在正则表达式中将字符串组合在一起。命令行：awk '/a(ll)?(nn)?e/' testfile将具有字符串 ae 或 alle 或 anne 或 allnne 的所有记录打印至标准输出。此示例中的输出是：smiley, allen smithhern, anne
{m} | 指定如果正好有 m 个模式的具体值位于字符串中，则字符串匹配。命令行：awk '/l{2}/' testfile打印至标准输出smiley, allen
{m,} | 指定如果至少 m 个模式的具体值在字符串中，则字符串匹配。命令行：awk '/t{2,}/' testfile打印至标准输出：smitters, alexis
{m, n} | 指定如果 m 和 n 之间（包含的 m 和 n）个模式的具体值在字符串中（其中m <= n），则字符串匹配。命令行：awk '/er{1, 2}/' testfile打印至标准输出：smithern, harry smithern, anne smitters, alexis
[String] | 指定正则表达式与方括号内 String 变量指定的任何字符匹配。命令行：awk '/sm[a-h]/' testfile将具有 sm 后跟以字母顺序从 a 到 h 排列的任何字符的所有记录打印至标准输出。此示例的输出是：smawley, andy
[^ String] | 在 [ ]（方括号）和在指定字符串开头的 ^ (插入记号) 指明正则表达式与方括号内的任何字符不匹配。这样，命令行：awk '/sm[^a-h]/' testfile打印至标准输出：smiley, allen smith, alan smithern, harry smithhern, anne smitters, alexis
~,!~ | 表示指定变量与正则表达式匹配（代字号）或不匹配（代字号、感叹号）的条件语句。命令行：awk '$1 ~ /n/' testfile将第一个字段包含字符 n 的所有记录打印至标准输出。此示例中的输出是：smithern, harry smithhern, anne
^ | 指定字段或记录的开头。命令行：awk '$2 ~ /^h/' testfile将把字符 h 作为第二个字段的第一个字符的所有记录打印至标准输出。此示例中的输出是：smithern, harry
$ | 指定字段或记录的末尾。命令行：awk '$2 ~ /y$/' testfile将把字符 y 作为第二个字段的最后一个字符的所有记录打印至标准输出。此示例中的输出是：smawley, andy smithern, harry
.（句号） | 表示除了在空白末尾的终端换行字符以外的任何一个字符。命令行：awk '/a..e/' testfile将具有以两个字符隔开的字符 a 和 e 的所有记录打印至标准输出。此示例中的输出是：smawley, andy smiley, allen smithhern, anne
*（星号） | 表示零个或更多的任意字符。命令行：awk '/a.*e/' testfile将具有以零个或更多字符隔开的字符 a 和 e 的所有记录打印至标准输出。此示例中的输出是：smawley, andy smiley, allen smithhern, anne smitters, alexis
\ (反斜杠)  | 转义字符。当位于在扩展正则表达式中具有特殊含义的任何字符之前时，转义字符除去该字符的任何特殊含义。例如，命令行：/a\/\//将与模式 a // 匹配，因为反斜杠否定斜杠作为正则表达式定界符的通常含义。要将反斜杠本身指定为字符，则使用双反斜杠。有关反斜杠及其使用的更多信息，请参阅以下关于转义序列的内容。


与PERs相比，主要是一些结合类型表示符没有了：包括：”\d,\D,\s,\S,\t,\v,\n,\f,\r”其它功能基本一样的。 我们常见的软件：javascript,.net,java支持的正则表达式，基本上是：EPRs类型。

**二、awk 常见调用正则表达式方法**

* awk语句中：
```
    awk ‘/REG/{action}’

    /REG/为正则表达式，可以将$0中，满足条件记录 送入到：action进行处理.
```
* awk正则运算语句(~,~!等同!~)
```
    [chengmo@centos5 ~]$ awk 'BEGIN{info="this is a test";if( info ~ /test/){print "ok"}}'  
    ok
```
* awk内置使用正则表达式函数
```
    gsub( Ere, Repl, [ In ] )

    sub( Ere, Repl, [ In ] )

    match( String, Ere )

    split( String, A, [Ere] )

    详细函数使用，可以参照：[** linux awk 内置函数详细介绍（实例）**][2]
```

[0]: http://www.cnblogs.com/chengmo/archive/2010/10/11/1847772.html
[1]: http://www.cnblogs.com/chengmo/archive/2010/10/10/1847287.html
[2]: http://www.cnblogs.com/chengmo/archive/2010/10/08/1845913.html