# Shell文本处理三剑客之grep

 时间 2017-01-05 09:57:52  李振良的技术博客

_原文_[http://lizhenliang.blog.51cto.com/7876557/1889166][1]



7.1 grep

过滤来自一个文件或标准输入匹配模式内容。

除了grep外，还有egrep、fgrep。egrep是grep的扩展，相当于grep -E。fgrep相当于grep -f，用的少。

Usage: grep [OPTION]... PATTERN [FILE]...

| 支持的正则 | 描述 |
|-|-|
|-E，--extended-regexp | 模式是扩展正则表达式（ERE）  |
|-F，--fixed-strings | 模式是换行分隔固定字符串  |
|-G，--basic-regexp | 模式是基本正则表达式（BRE） |
| -P，--perl-regexp | 模式是Perl正则表达式 |
| -e，--regexp=PATTERN | 使用模式匹配，可指定多个模式匹配  |
| -f，--file=FILE | 从文件每一行获取模式  |
| -i，--ignore-case | 忽略大小写  |
| -w，--word-regexp | 模式匹配整个单词  |
| -x，--line-regexp | 模式匹配整行  |
| -v，--invert-match | 打印不匹配的行|

| 输出控制 | 描述 |
|-|-|
| -m，--max-count=NUM | 输出匹配的结果num数  |
| -n，--line-number | 打印行号  |
| -H，--with-filename | 打印每个匹配的文件名  |
| -h，--no-filename | 不输出文件名  |
| -o，--only-matching | 只打印匹配的内容  |
| -q，--quiet | 不输出正常信息  |
| -s, --no-messages | 不输出错误信息 |
| -r，--recursive  | 递归目录。 |
| --include=FILE_PATTERN  | 只搜索匹配的文件。 |
| --exclude=FILE_PATTERN  | 跳过匹配的文件。 |
| --exclude-from=FILE  | 跳过匹配的文件，来自文件模式。 |
| --exclude-dir=PATTERN   | 跳过匹配的目录 |
| -c，--count | 只打印每个文件匹配的行数 |

| 内容行控制 | 描述 |
|-|-|
|  -B，--before-context=NUM | 打印匹配的前几行  |
|  -A，--after-context=NUM | 打印匹配的后几行  |
|  -C，--context=NUM | 打印匹配的前后几行 |
| --color[=WHEN], | 匹配的字体颜色|

博客地址：http://lizhenliang.blog.51cto.com

QQ群：323779636（Shell/Python运维开发群）

示例：

1） 输出b文件中在a文件相同的行

    # grep -f a b

2） 输出b文件中在a文件不同的行

    # grep -v -f a b

3） 匹配多个模式

    # echo "a bc de" |xargs -n1 |grep -e 'a' -e 'bc'
    a
    bc

4） 去除空格http.conf文件空行或开头#号的行

    # grep -E -v "^$|^#" /etc/httpd/conf/httpd.conf

5） 匹配开头不分大小写的单词

    # echo "A a b c" |xargs -n1 |grep -i a
    或
    # echo "A a b c" |xargs -n1 |grep '[Aa]'
    A
    a

6） 只显示匹配的字符串

    # echo "this is a test" |grep -o 'is'
    is
    is

7） 输出匹配的前五个结果

    # seq 1 20  |grep -m 5 -E '[0-9]{2}'
    10
    11
    12
    13
    14

8）统计匹配多少行

    # seq 1 20  |grep -c -E '[0-9]{2}'
    11

9） 匹配b字符开头的行

    # echo "a bc de" |xargs -n1 |grep '^b'
    bc

10） 匹配de字符结尾的行并输出匹配的行

    # echo "a ab abc abcd abcde" |xargs -n1 |grep -n 'de$'
    5:abcde

11） 递归搜索/etc目录下包含ip的conf后缀文件

    # grep -r '192.167.1.1' /etc --include *.conf

12） 排除搜索bak后缀的文件

    # grep -r '192.167.1.1' /opt --exclude *.bak

13） 排除来自file中的文件

    # grep -r '192.167.1.1' /opt --exclude-from file

14） 匹配41或42的数字

    # seq 41 45 |grep -E '4[12]'
    41
    42

15） 匹配至少2个字符

    # seq 13 |grep -E '[0-9]{2}'
    10
    11
    12
    13

16） 匹配至少2个字符的单词，最多3个字符的单词

    # echo "a ab abc abcd abcde" |xargs -n1 |grep -E -w -o '[a-z]{2,3}'
    ab
    abc

17） 匹配所有IP

    # ifconfig |grep -E -o "[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}"

18） 打印匹配结果及后3行

    # seq 1 10 |grep 5 -A 3
    5
    6
    7
    8

19） 打印匹配结果及前3行

    # seq 1 10 |grep 5 -B 3
    2
    3
    4
    5

20） 打印匹配结果及前后3行

    # seq 1 10 |grep 5 -C 3
    2
    3
    4
    5
    6
    7
    8

21） 不显示输出

不显示错误输出：

    # grep 'a' abc
    grep: abc: No such file or directory
    # grep -s 'a' abc
    # echo $?
    2
    不显示正常输出：
    # grep -q 'a' a.txt

grep支持上一章的基础和扩展正则表达式字符。


[1]: http://lizhenliang.blog.51cto.com/7876557/1889166?utm_source=tuicool&utm_medium=referral
