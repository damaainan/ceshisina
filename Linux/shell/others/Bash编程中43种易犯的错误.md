## 译 | Bash编程中43种易犯的错误

来源：[http://www.techug.com/post/bash-pitfalls.html](http://www.techug.com/post/bash-pitfalls.html)

时间 2018-07-23 13:21:45

 
 
文章介绍了40多条日常 Bash 编程中，老手和新手都容易忽略的错误编程习惯。每条作者在给出错误的范例上，详细分析与解释错误的原因，同时给出正确的改写建议。文中有不少引用的文章，也值得大家仔细阅读。仔细阅读了这篇文章后，收获很多，不感独享，把这篇文章以半翻译半笔记的形式分享给大家。
 
### 1. for i in $(ls *.mp3)
 
Bash写循环代码的时候，确实比较容易犯下面的错误：
 
```sh
for i in $(ls *.mp3); do    # 错误!
    some command $i         # 错误!
done

for i in $(ls)              # 错误!
for i in `ls`               # 错误!

for i in $(find . -type f)  # 错误!
for i in `find . -type f`   # 错误!

files=($(find . -type f))   # 错误!
for i in ${files[@]}        # 错误!
```
 
这里主要两个问题：
 
 
* 使用命令展开时不带引号，其执行结果会使用IFS作为分隔符，拆分成参数传递给for循环处理； 
* [不应该让脚本去解析ls命令的结果][2] ；  
 
 
我们不能避免某些文件名中包含空格，Shell会对`$(ls *.mp3)`展开的结果会被做单词拆分( [WordSplitting][3] )的处理。假设有一个文件，名字为01 – Don't Eat the Yellow Snow.mp3，for循环处理的时候，会今次遍历文件名中的每个单词：01, -, Don't, Eat等等：
 
```sh
$ for i in $(ls *.mp3); do echo $i; done
01
-
Don't
Eat
the
Yellow
Snow.mp3
```
 
比这更差的情况是，上面命令展开的结果可能被Shell进一步处理，比如 [文件名展开][4] 。比如，ls执行的结果中包含*号，按照通配符的规则, *号会被展开成当前目录下的所有文件:
 
```sh
$ touch "1*.mp3" "1.mp3" "11.mp3" "12.mp3"
$ for i in $(ls *.mp3); do echo $i; done
1*.mp3 1.mp3 11.mp3 12.mp3
1.mp3
11.mp3
12.mp3
1.mp3
11.mp3
12.mp3
```
 
不过，在这种场景下，你即使加上引号，也是无济于事的：
 
```sh
$ for i in "$(ls *.mp3)"; do echo --$i--; done
--1*.mp3 1.mp3 11.mp3 12.mp3--
```
 
加上引号后，ls执行的结果会被当成一个整体，所以for循环只会执行一次，达不到预期的效果。
 
事实上，这种情况下，根本不需要使用ls命令。ls命令的结果本身就设计成给人读的，而不是给脚本解析的。正确的处理方法是，直接使用文件名展开（通配符）的功能：
 
```sh
$ for i in *.mp3; do
>     echo "$i"
> done
1*.mp3
1.mp3
11.mp3
12.mp3
```
 
文件名展开是位于各种展开（花括号展开、变量替换、命令展开等）功能中的最后一个环节，所以不会有之前不带引号的命令展开的副作用。如果你需要递归地处理文件，可以考虑 [使用Find命令][5] 。
 
到这一步，之间的问题看样子已经修复了。但是，如果你进一步思考，假设当前目录上没有文件时会怎么样？没有文件的时候，*.mp3不会被展开直接传递给for循环处理，所以这个时候循环还是会执行一次。这种情况不是我们预期的行为。保险起见，可以在循环处理的时候，检查下文件是否存在：
 
```sh
# POSIX
for i in *.mp3; do
    [ -e "$i" ] || continue
    some command "$i"
done
```
 
如果你有 [使用引号][6] 和避免 [单词拆分][7] 的习惯，你完全可以避免很多错误。
 
注意下循环体内部的"$i"，这里会导致下面我们要说的另外一个比较容易犯的错误。
 
### 2. cp $file $target
 
上面的命令有什么问题呢？如果你提前知道，$file和$target文件名中不会包含空格或者*号。否则，这行命令执行前在经过单词拆分和文件名展开的时候会出现问题。所以，两次强调，在使用展开的地方切勿忘记使用引号：
 
```sh
$ cp -- "$file" "$target"
```
 
如果不带引号，当你执行如下命令时就会出错：
 
```sh
$ file="01 - Don't Eat the Yellow Snow.mp3"
$ target="/tmp"
$ cp $file $target
cp: cannot stat '01': No such file or directory
..
```
 
如果带上引号，就不会有上面的问题，除非文件名以'-'开头，在这种情况下，cp会认为你提供的是一个命令行选项，这个错误下面会介绍。
 
### 3. 文件名中包含短横'-'
 
文件名以'-'开头会导致许多问题，*.mp3这种通配符会根据当前的 [locale][8] 展开成一个列表，但在绝大多数环境下，'-'排序的时候会排在大多数字母前。这个展开的列表传递给有些命令的时候，会错误的将-filename解析成命令行选项。这里有两种方法来解决这个问题。
 
第一种方法是在命令和参数之间加上–，这种语法告诉命令不要继续对–之后的内容进行命令行参数/选项解析：
 
```sh
$ cp -- "$file" "$target"
```
 
这种方法可以解这个问题，但是你需要在每个命令后面都要加上–，而且依赖具体的命令解析的方式，如果一些命令不兼容这种约定俗成的规范，这种做法是无效的。
 
另外一种方法是，确保文件名都使用相对或者绝对的路径，以目录开头：
 
```sh
for i in ./*.mp3; do
    cp "$i" /target
    ...
done
```
 
这种情况下，即使某个文件以-开头，展开后文件名依然是./-foo.mp3这种形式，完全不会有问题。
 
### 4. [ $foo = “bar" ]
 
这是一个与第2个问题类似的问题，虽然用到了引号，但是放错了位置，对于字符串字面值，除非有特殊符号，否则不大需要用引号括起来。但是，你应该把变量的值用括号括起来，从而避免它们包含空格或能通配符，这一点我们在前面的问题中都解释过。
 
这个例子在以下情况下会出错：
 
 
* 如果[中的变量不存在，或者为空，这个时候上面的例子最终解析结果是：

```sh
[ = "bar" ] # 错误!
```
并且执行会出错：unary operator expected，因为=是二元操作符，它需要左右各一个操作数。
  
* 如果变量值包含空格，它首先在执行之前进行单词拆分，因此[命令看到的样子可能是这样的：
```sh
[ multiple words here = "bar" ];
```
  
 
 
正确的做法应该是：
 
```sh
# POSIX
[ "$foo" = bar ]
```
 
这种写法，在POSIX兼容的实现中都不会有问题，即使$foo以短横"-“开头，因为POSIX实现的test命令通过传递的参数来确定执行的行为。
 
只有一些非常古老的shell可能会遇到问题，这个时候你可以使用下面的写法来解决（相信你肯定看到过这种写法）：
 
```sh
# POSIX / Bourne
[ x"$foo" = xbar ]
```
 
在Bash中，还有另外一种选择是使用 [[[关键字][9] ：
 
```sh
# Bash / Ksh
[[ $foo == bar ]]
```
 
这里你不需要使用引号，因为在[[里面参数不会进行展开，当然带上引号也不会有错。
 
不过有一点要注意的是，[[里的==不仅仅是文本比较，它会检查左边的值是否匹配右侧的表达式，==右侧的值加上引号，会让它成为一个普通的字面量，*?等通配符会失去特殊含义。
 
### 5. cd $(dirname “$f")
 
这又是一个引号的问题，命令展开的结果会进一步地进行单词拆分或者文件名展开。因此下面的写法才是正确的：
 
```sh
cd "$(dirname "$f")"
```
 
但是，上面引号的写法可能比较怪异，你可能会认为第一、二个引号，第三、四个引号是一组的。
 
但是事实上，Bash将命令替换里面的引号当成一组，外面的当成另外一组。如果你是用反引号的写法，引号的行为就不是这样的了，所以 [$()写法更加推荐][10] 。
 
### 6. [ “$foo" = bar && “$bar" = foo ]
 
不要在 [test命令][11] 内部使用&&，Bash解析器会把你的命令分隔成两个命令，在&&之前和之后。你应该使用下面的写法：
 
```sh
[ bar = "$foo" ] && [ foo = "$bar" ] # POSIX
[[ $foo = bar && $bar = foo ]]       # Bash / Ksh
```
 
尽量避免使用下面的写法，虽然它是正确的，但是这种写法可移植性不好，并且已经在POSIX-2008中被废弃：
 
```sh
[ bar = "$foo" -a foo = "$bar" ]
```
 
### 7. [[ $foo > 7 ]]
 
原文作者认为算术比较不应该用[[，而是用((，我没弄明白是为什么。
 
如果有理解的同学，欢迎以评论回复，谢谢。
 
### 8. grep foo bar | while read -r; do ((count++)); done
 
 这种写法初看没有问题，但是你会发现当执行完后，count变量并没有变化。原因是管道后面的命令是在一个 [子Shell][12] 中执行的。
 
POSIX规范并没有说明管道的最后一个命令是不是在子Shell中执行的。一些shell，例如ksh93或者Bash>=4.2可以通过`shopt -s lastpipe`命令，指明管道中的最后一个命令在当前shell中执行。由于篇幅限制，在此就不展开，有兴趣的可以看 [Bash FAQ #24][13] 。
 
### 9. if [grep foo myfile]
 
初学者会错误地认为，[是if语法的一部分，正如C语言中的if ()。但是事实并非如此，if后面跟着的是一个命令，[是一个命令，它是内置命令test的简写形式，只不过它要求最后一个参数必须是]。下面两种写法是一样的：
 
```sh
# POSIX
if [ false ]; then echo "HELP"; fi
if test false; then echo "HELP"; fi
```
 
两个都是检查参数"false"是不是非空的，所以上面两个语句都会输出HELP。
 
if语句的语法是：
 
```sh
if COMMANDS
then <COMMANDS>
elif <COMMANDS> # optional
then <COMMANDS>
else <COMMANDS> # optional
fi # required
```
 
再次强调，[是一个命令，它同其它常规的命令一样接受参数。if是一个复合命令，它包含其它命令，[并不是if语法中的一部分。
 
如果你想根据grep命令的结果来做事情，你不需要把grep放到[里面，只需要在if后面紧跟grep即可：
 
```sh
if grep -q fooregex myfile; then
...
fi
```
 
如果grep在myfile中找到匹配的行，它的执行结果为0(true)，then后面的部分就会执行。
 
### 10. if [bar="$foo"]; then …
 
正如上一个问题中提到的，[是一个命令，它的参数之间必须用空格分隔。
 
### 11. if [ [ a = b ] && [ c = d ] ]; then …
 
不要用把[命令看成C语言中if语句的条件一样，它是一个命令。
 
如果你想表达一个复合的条件表达式，可以这样写：
 
```sh
if [ a = b ] && [ c = d ]; then ...
```
 
注意，if后面有两个命令，它们用&&分开。等价于下面的写法：
 
```sh
if test a = b && test c = d; then ...
```
 
如果第一个test(或者[)命令返回false，then后面的语句不会执行；如果第一个返回true，第二个test命令会执行；只有第二个命令同样返回true的情况下，then后面的语句才会执行。
 
除此之外，还可以使用[[关键字，因为它支持&&的用法：
 
```sh
if [[ a = b && c = d ]]; then ...
```
 
### 12. read $foo
 
read命令中你不需要在变量名之前使用$。如果你想把读入的数据存放到名为foo的变量中，下面的写法就够了：
 
```sh
read foo
```
 
或者，更加安全地方法：
 
```sh
IFS= read -r foo
```
 `read $foo`会把一行的内容读入到变量中，该变量的名称存储在$foo中。所以两者的含义是完全不一样的。
 
### 13. cat file | sed s/foo/bar/ > file
 
你不应该在一个管道中，从一个文件读的同时，再往相同的文件里面写，这样的后果是未知的。
 
你可以为此创建一个临时文件，这种做法比较安全可靠：
 
### 13. cat file | sed s/foo/bar/ > file
 
你不应该在一个管道中，从一个文件读的同时，再往相同的文件里面写，这样的后果是未知的。
 
你可以为此创建一个临时文件，这种做法比较安全可靠：
 
```sh
# sed 's/foo/bar/g' file > tmpfile && mv tmpfile file
```
 
或者，如果你用得是 GNU Sed 4.x 以上的版本，可以使用-i 选项即时修改文件的内容：
 
```sh
# sed -i 's/foo/bar/g' file
```
 
### 14. echo $foo
 
这种看似无害的命令往往会给初学者千万极大的困扰，他们会怀疑是不是因为 $foo 变量的值是错误的。事实却是因为，$foo 变量在这里没有使用双引号，所以在解析的时候会进行 [单词拆分][7] 和 [文件名展开][4] ，最终导致执行结果与预期大相径庭：
 
```sh
msg="Please enter a file name of the form *.zip"
echo $msg
```
 
这里整句话会被拆分成单词，然后其中的通配符会被展开，例如*.zip。当你的用户看到如下的结果时，他们会怎样想：
 
```sh
Please enter a file name of the form freenfss.zip lw35nfss.zip
```
 
再举一个例子（假设当前目录下有以 .zip 结尾的文件）：
 
```sh
var="*.zip"   # var 包括一个星号，一个点号和 zip
echo "$var"   # 输出 *.zip
echo $var     # 输出所有以 .zip 结尾的文件
```
 
实际上，这里使用 echo 命令并不是绝对的安全。例如，当变量的值包含-n时，echo 会认为它是一个合法的选项而不是要输出的内容（当然如果你能够保证不会有-n 这种值，可以放心地使用 echo 命令）。
 
完全可靠的打印变量值的方法是使用 printf：
 
```sh
printf "%s\n" "$foo"
```
 
### 15. $foo=bar
 
略过
 
### 16. foo = bar
 
当赋值时，等号两边是不允许出现空格的，这同 C 语言不一样。当你写下 foo = bar 时，shell 会将该命令解析成三个单词，然后第一个单词 foo 会被认为是一个命令，后面的内容会被当作命令参数。
 
同样地，下面的写法也是错误的：
 
```sh
foo= bar    # WRONG!
foo =bar    # WRONG!
$foo = bar; # COMPLETELY WRONG!

正确的写法应该是这样的：

```sh

foo=bar     # Right.
foo="bar"   # More Right.
```
 
### 17. echo <<EOF
 
当脚本需要嵌入大段的文本内容时， [here document][16] 往往是一个非常有用的工具，它将其中的文本作为命令的标准输入。不过，echo 命令并不支持从标准输入读取内容，所以下面的写法是错误的：
 
```sh
# This is wrong:
echo <<EOF
Hello world
How's it going?
EOF
```
 
正确的方法是，使用 cat 命令来完成：
 
```sh
# This is what you were trying to do:
cat <<EOF
Hello world
How's it going?
EOF
```
 
或者可以使用双引号，它也可以跨越多行，而且因为 echo 命令是内置命令，相同情况下它会更加高效：
 
```sh
echo "Hello world
How's it going?"
```
 
### 18. su -c 'some command'
 
这种写法“几乎"是正确的。问题是，在许多平台上，su 支持 -c 参数，但是它不一定是你认为的。比如，在 OpenBSD 平台上你这样执行会出错：
 
```sh
$ su -c 'echo hello'
su: only the superuser may specify a login class
```
 
在这里， [-c是用于指定login-class][17] 。如果你想要传递 -c 'some command' 给 shell，最好在之前显示地指定 username：
 
```sh
$ su root -c 'some command' # Now it's right.
```
 
### 19. cd /foo; bar
 
如果你不检查 cd 命令执行是否成功，你可以会在错误的目录下执行 bar 命令，这有可能会带来灾难，比如 bar 命令是 rm -rf *。
 
你必须经常检查 cd 命令执行是否有错误，简单的做法是：
 
```sh
cd /foo && bar
```
 
如果在 cd 命令后有多个命令，你可以选择这样写：
 
```sh
cd /foo || exit 1
bar
baz
bat ... # Lots of commands.
```
 
出错时，cd 命令会报告无法改变当前目录，同时将错误消息输出到标准错误，例如"bash: cd: /foo: No such file or directory"。如果你想要在标准输出同时输出自定义的错误提示，可以使用复合命令（ [command grouping][18] ）:
 
```sh
cd /net || { echo "Can't read /net. Make sure you've logged in to the Samba network, and try again."; exit 1; }
do_stuff
more_stuff
```
 
注意，在{号和 echo 之间需要有一个空格，同时}之前要加上分号。
 
顺便提一下，如果你要在脚本里频繁改变当前目录，可以看看 pushd/popd/dirs 等命令，可能你在代码里面写的 cd/pwd 命令都是没有必要的。
 
说到这，比较下下面两种写法：
 
```sh
find ... -type d -print0 | while IFS= read -r -d '' subdir; do
   here=$PWD
   cd "$subdir" && whatever && ...
   cd "$here"
done
```
 
```sh
find ... -type d -print0 | while IFS= read -r -d '' subdir; do
   (cd "$subdir" || exit; whatever; ...)
done
```
 
下面的写法，在循环中 fork 了一个子 shell 进程，子 shell 进程中的 cd 命令仅会影响当前 shell的环境变量，所以父进程中的环境命令不会被改变；当执行到下一次循环时，无论之前的 cd 命令有没有执行成功，我们会回到相同的当前目录。这种写法相较前面的用法，代码更加干净。
 
### 20. [ bar == “$foo" ]
 
正确的用法:
 
```sh
[ bar = "$foo" ] && echo yes
[[ bar == $foo ]] && echo yes
```
 
### 21. for i in {1..10}; do ./something &; done
 
你不应该在&后面添加分号，删除它：
 
```sh
for i in {1..10}; do ./something & done
```
 
或者改成多行的形式：
 
```sh
for i in {1..10}; do
    ./something &
done
```
 
&和分号一样也可以用作命令终止符，所以你不要将两个混用到一起。一般情况下，分号可以被换行符替换，但是不是所有的换行符都可以用分号替换。
 
### 22. cmd1 && cmd2 || cmd3
 
有些人喜欢把&&和||作为if…then…else…fi 的简写语法，在多数情况下，这种写法没有问题。例如：
 
```sh
[[ -s $errorlog ]] && echo "Uh oh, there were some errors." || echo "Successful."
```
 
但是，这种结构并不是在所有情况下都完全等价于 if…fi 语法。这是因为在&&后面的命令执行结束时也会生成一个返回码，如果该返回码不是真值（0代表 true），||后面的命令也会执行，例如：
 
```sh
i=0
true && ((i++)) || ((i--))
echo $i # 输出 0
```
 
看起来上面的结果应该是返回1，但是结果却是输出0，为什么呢？原因是这里 i++ 和 i– 都执行了一遍。
 
其中，((i++))命令执行算术运算，表达式计算的结果为0。这里和 C 语言一样，表达式的结果为0被认为是 false。所以当 i=0 的时候，((i++))命令执行的返回码为1（false），从而会执行接下来的((i–))命令。
 
如果我们在这里使用前缀自增运算符的话，返回的结果恰恰为1，因为((++i))执行的返回码是0（true）：
 
```sh
i=0
true && (( ++i )) || (( --i ))
echo $i # Prints 1
```
 
不过在你无法保证 y 的执行结果是，绝对不要依靠 x && y || z这种写法。上面这种巧合，在 i 初始化为-1时也会有问题。
 
如果你喜欢代码更加安全健壮，建议使用 if…fi 语法：
 
```sh
i=0
if true; then
   ((i++))
else
   ((i--))
fi

echo $i # 输出 1
```
 
### 23. echo “Hello World!"
 
在交互式的 Shell 环境下，你执行以上命令会遇到下面的错误：
 
```sh
bash: !": event not found
```
 
这是因为，在默认的交互式 Shell 环境下，Bash 发现感叹号时会执行历史命令展开。在 Shell 脚本中，这种行为是被禁止的，所以不会发生错误。
 
不幸地是，你认为明显正确地修复方法，也不能工作，你会发现 [反斜杠并没有转义感叹号][19] ：
 
```sh
# echo "hi\!"
hi\!
```
 
最简单地方法是禁用 histexpand 选项，你可以通过 set +H 或者 set +o histexpand 命令来完成。
 
下面四种写法都可以解决：
 
```sh
# 1. 使用单引号
echo 'Hello World!'

# 2. 禁用 histexpand 选项
set +H
echo "Hello World!"

# 3. 重置 histchars
histchars=

# 4. 控制 shell 展开的顺序，命令行历史展开是在单词拆分之前执行的
# 参见：[Bash man 手册的History Expansion一节][20]
exmark='!'
echo "Hello, world$exmark"
```
 
### 24. for arg in $*
 
和大多数 Shell 一样，Bash 支持依次读取单个命令行参数的语法。不过这并是$*或者[email protected]，这两种写法都不正确，它们只能得到完整的参数列表，并非单独的一个个参数。
 
正确的语法是（没错要加上引号）：
 
```sh
for arg in "[[email protected]][21]"

# 或者更简单的写法
for arg
```
 
在脚本中遍历所有参数是一个再普遍不过的需求，所以 for arg 默认等价于 for arg in “[email protected]"。[email protected]使用双引号后就有特殊的魔力，每个参数展开后成为一个独立的单词。（"[email protected]"等价于"$1" “$2" “$3" …）
 
下面是一个错误的例子:
 
```sh
for x in $*; do
   echo "parameter: '$x'"
done
```
 
执行的结果为：
 
```sh
$ ./myscript 'arg 1' arg2 arg3
parameter: 'arg'
parameter: '1'
parameter: 'arg2'
parameter: 'arg3'
```
 
正确的写法：
 
```sh
for x in "[[email protected]][22]"; do
   echo "parameter: '$x'"
done
```
 
执行的结果为：
 
```sh
$ ./myscript 'arg 1' arg2 arg3
parameter: 'arg 1'
parameter: 'arg2'
parameter: 'arg3'
```
 
上面正确的例子中，第一个参数'arg 1'在展开后依然是一个独立的单词，而不会被拆分成两个。
 
### 25. function foo()
 
这种写法不一定能够兼容所有 shell，兼容的写法是：
 
```sh
foo() {
  ...
}
```
 
### 26. echo “~"
 
[波浪号展开（Tilde expansion）][23] 仅当~没有引号的时候发生，在上面的例子中，只会向标准输出打印~符号，而不是当前用户的家目录路径。
 
当用引号将路径参数引起来时，
 
如果要用引号将相对于家目录的路径引起来时，推荐使用 $HOME 而不是 ~, 假如 $HOME 目录是"/home/my photos"，路径中包含空格。
 
下面是几组例子：
 
```sh
"~/dir with spaces" # expands to "~/dir with spaces"
~"/dir with spaces" # expands to "~/dir with spaces"
~/"dir with spaces" # expands to "/home/my photos/dir with spaces"
"$HOME/dir with spaces" # expands to "/home/my photos/dir with spaces"
```
 
### 27. local varname=$(command)
 
当在函数中声明局部变量时， [local][24] 作为一个独立的命令，这种奇特的行为有时候可能会导致困扰。比如，当你想要捕获 [命令替换][25] 的返回码时，你就不能这样做。local命令的返回码会覆盖它。
 
这种情况下，你只能分成两行写：
 
```sh
local varname
varname=$(command)
rc=$?
```
 
### 28. export foo=~/bar
 
export 与 local 命令一样，并不是赋值语句的一部分。因此，在有些 Shell 下（比如Bash），export foo=~/bar会展开，但是有些（比如 Dash）却不行。
 
下面是两种比较健壮的写法：
 
```sh
foo=~/bar; export foo    # Right!
export foo="$HOME/bar"   # Right!
```
 
### 29. sed 's/$foo/good bye/'
 
单引号内部不会展开 $foo变量，在这里可以换成双引号：
 
```sh
foo="hello"; sed "s/$foo/good bye/"
```
 
但是要注意，如果你使用了双引号，就需要考虑更多转义的事情，具体可以看 [Quotes][26] 这一页。.
 
### 30. tr [A-Z] [a-z]
 
这里至少有三个问题。第一个问题是， [A-Z] 和 [a-z] 会被 shell 认为是通配符。如果在当前目录下没用文件名为单个字母的文件，这个命令似乎能正确执行，否则会错误地执行，也许你会在周末耗费许多小时来修复这个问题。
 
第二个问题是，这不是 tr 命令正确的写法，实际上，上面的命令会把[转换成[，将任意大写字符转换成对应的小写字符，将]转换成]，所以你根本不需要加上括号，这样第一个问题就可以解决了。
 
第三个问题是，上面的命令执行结果依赖于当前的 [locale][8] ，A-Z 或者 a-z 不一定会代表26个 ASCII 字母。实际上，在一些语言环境下，z 位于字母表的中间位置。这个问题的解法，取决于你希望发生的行为是哪一种。
 
如果你仅希望改变26个英文字母的大小写（强制 locale为 C）：
 
```sh
LC_COLLATE=C tr A-Z a-z
```
 
如果你希望根据实际的语言环境来转换：
 
```sh
tr '[:upper:]' '[:lower:]'
```
 
### 31. ps ax | grep gedit
 
这里的根本问题是正在运行的进程名称，本质上是不可靠的。可能会有多个合法的gedit进程，也有可能是别的东西伪装成gedit进程（改变执行命令名称是一件简单的事情 ）,更多细节可以看 [ProcessManagement][28] 这一篇文章。
 
执行以上命令，往往会在结果中包含 grep 进程：
 
```
# ps ax | grep gedit
10530 ?        S      6:23 gedit
32118 pts/0    R+     0:00 grep gedit
```
 
这个时候，需要过滤多余的结果：
 
```
# ps ax | grep -v grep | grep gedit
```
 
上面的写法比较丑陋，另外一种方法是：
 
```
# ps ax | grep [g]edit
```
 
### 32. printf “$foo"
 
如果$foo 变量的值中包括\或者%符号，上面命令的执行结果可能会出乎你的意料之外。
 
下面是正确的写法：
 
```sh
printf %s "$foo"
printf '%s\n' "$foo"
```
 
### 33. for i in {1..$n}
 
Bash的 [命令解释器][29] 会优先 [展开大括号][30] ，所以这时大括号{}表达式里面看到的是文字上的$n（没有展开）。$n 不是一个数值，所以这里的大括号{}并不会展开成数字列表。可见，这导致很难使用大括号来展开大小只能在运行时才知道的列表。
 
可以用下面的方法：
 
```sh
for ((i=1; i<=n; i++)); do
...
done
```
 
注：之前我也有写过一篇文章来介绍这个问题： [Shell生成数字序列][31] 。
 
### 34. if [[ $foo = $bar ]]
 
在[[内部，当=号右边的值没有用引号引起来，bash 会将它当作模式来匹配，而不是一个简单的字符串。所以，在上面的例子中 ，如果 bar 的值是一个*号，执行的结果永远是 true。
 
所以，如果你想检查两侧的字符串是否相同，等号右侧的值一定要用引号引起来。
 
```sh
if [[ $foo = "$bar" ]]
```
 
如果你确实要执行模式匹配，聪明的做法是取一个更加有意义的变量名（例如$patt），或者加上注释说明。
 
### 35. if [[ $foo =~ 'some RE' ]]
 
同上，如果=~号右侧的值加上引号，它会散失特殊的正则表达式含义，而变成一个普通的字符串。
 
如果你想使用一个长的或者复杂的正则表达式，避免大量的反斜杠转义，建议把它放在一个变量中：
 
```sh
re='some RE'
if [[ $foo =~ $re ]]
```
 
### 36. [ -n $foo ] or [ -z $foo ]
 
这个例子中，$foo 没有用引号引起来，当$foo包含空格或者$foo为空时都会出问题：
 
```sh
$ foo="some word" && [ -n $foo ] && echo yes
-bash: [: some: binary operator expected

$ foo="" && [ -n $foo ] && echo yes
yes
```
 
正确的写法是：
 
```sh
[ -n "$foo" ]
[ -z "$foo" ]
[ -n "$(some command with a "$file" in it)" ]

[[ -n $foo ]]
[[ -z $foo ]]
```
 
### 37. [[ -e “$broken_symlink" ]] returns 1 even though $broken_symlink exists
 
这里-e 选项是看文件是否存在，当紧跟的文件是一个软链接时，它不看软链接是否存在，而是看实际指向的文件是否存在。所以当软链接损坏时，即实际指向的文件被删除后，-e 的结果返回1。
 
所以如果你确实要判断后面的文件是否存在，正确的写法是：
 
```sh
[[ -e "$broken_symlink" || -L "$broken_symlink" ]]
```
 
### 38. ed file <<<“g/d\{0,3\}/s//e/g" fails
 
ed 命令使用的正则语法，不支持0次出现次数，下面的就可以正常工作：
 
```sh
ed file <<<"g/d\{1,3\}/s//e/g"
```
 
略过，现在很少会有人用 ed 命令吧。
 
### 39. expr sub-string fails for “match"
 
下面的例子多数情况下运行不会有问题：
 
```sh
word=abcde
expr "$word" : ".\(.*\)"
bcde
```
 
但是当 $work 不巧刚好是 match 时，就有可能出错了（MAC OSX 下的 expr 命令不支持 match，所以依然能正常工作）：
 
```sh
word=match
expr "$word" : ".\(.*\)"
```
 
原因是 match 是 expr 命令里面的一个特殊关键字，针对 GNU系统，解决方法是在前面加一个'+'：
 
```sh
word=match
expr + "$word" : ".\(.*\)"
atch
```
 
'+'号可以让 expr 命令忽略后续 token 的特殊含义。
 
另外一个建议是，不要再使用 expr 命令了，expr 能做的事情都可以用 Bash 原生支持的参数展开（ [Parameter Expansion][32] ）或者字符串展开（Substring Expansion）来完成。并且相同情况下，内置的功能肯定比外部命令的效率要高。
 
上面的例子，目的是为了删除单词中的首字符，可以这样做：
 
```sh
$ word=match
$ echo "${word#?}"    # PE
atch
$ echo "${word:1}"    # SE
atch
```
 
### 40. On UTF-8 and Byte-Order Marks (BOM)
 
多数情况下，UNIX 下 UTF-8 类型的文本不需要使用 BOM，文本的编码是根据当前语言环境，MIME类型或者其它文件元数据信息确定的。人为阅读时，不会因为在文件开始处加 BOM 标记而腚影响，但是当文件要被脚本解释执行时，BOM 标记会像 MS-DOS 下的换行符（^M）一样奇怪。
 
### 41. content=$(<file)
 
这里没有什么错误，不过你要知道命令替换会删除结尾多余的换行符。
 
略过，原文给的优化方法需要 Bash 4.2+ 以上的版本，手头没有这样的环境。
 
### 42. somecmd 2>&1 >>logfile
 
这是一个很常见的错误，显然你本来是想将标准输出与标准错误输出都重定向到文件logfile 中，但是你会惊讶地发现，标准错误依然输出到屏幕中。
 
这种行为的原因是， [重定向][33] 在命令执行之前解析，并且是从左往右解析。上面的命令可以翻译成，将标准错误输出重定向到标准输出（此刻是终端），然后将标准输出重定向到文件 logfile 中。所以，到最后，标准错误并没有重定向到文件中，而是依然输出到终端：
 
```sh
somecmd >>logfile 2>&1
```
 
更加详细的说明见 [BashFAQ][34] 。
 
### 43. cmd; (( ! $? )) || die
 
只有需要捕获上一个命令的执行结果进，才需要记录$?的值，否则如果你只需要检查上一个命令是否执行成功，直接检测命令：
 
```sh
if cmd; then
    ...
fi
```
 
或者使用 case 语句来检测多个或能的返回码：
 
```sh
cmd
status=$?
case $status in
    0)
        echo success >&2
        ;;
    1)
        echo 'Must supply a parameter, exiting.' >&2
        exit 1
        ;;
    *)
        echo 'Unknown error, exiting.' >&2
        exit $status
esac
```
 
英文原文： [Bash Pitfalls][35]
 


[2]: http://mywiki.wooledge.org/ParsingLs
[3]: http://mywiki.wooledge.org/WordSplitting
[4]: http://mywiki.wooledge.org/glob
[5]: http://mywiki.wooledge.org/UsingFind
[6]: http://mywiki.wooledge.org/Quotes
[7]: http://mywiki.wooledge.org/WordSplitting
[8]: http://mywiki.wooledge.org/locale
[9]: http://mywiki.wooledge.org/BashFAQ/031
[10]: http://mywiki.wooledge.org/BashFAQ/082
[11]: http://mywiki.wooledge.org/BashFAQ/031
[12]: http://mywiki.wooledge.org/SubShell
[13]: http://mywiki.wooledge.org/BashFAQ/024
[14]: http://mywiki.wooledge.org/WordSplitting
[15]: http://mywiki.wooledge.org/glob
[16]: http://www.tldp.org/LDP/abs/html/here-docs.html
[17]: http://www.openbsd.org/cgi-bin/man.cgi?query=su&sektion=1
[18]: http://mywiki.wooledge.org/BashGuide/CompoundCommands
[19]: https://www.gnu.org/software/bash/manual/html_node/Double-Quotes.html
[20]: http://linux.die.net/man/1/bash
[21]: /cdn-cgi/l/email-protection
[22]: /cdn-cgi/l/email-protection
[23]: https://www.gnu.org/software/bash/manual/html_node/Tilde-Expansion.html
[24]: http://tldp.org/LDP/abs/html/localvar.html
[25]: http://mywiki.wooledge.org/CommandSubstitution
[26]: http://mywiki.wooledge.org/Quotes
[27]: http://mywiki.wooledge.org/locale
[28]: http://mywiki.wooledge.org/ProcessManagement
[29]: http://mywiki.wooledge.org/BashParser
[30]: http://mywiki.wooledge.org/BraceExpansion
[31]: http://kodango.com/generate-number-sequence-in-shell
[32]: http://mywiki.wooledge.org/BashFAQ/073
[33]: http://wiki.bash-hackers.org/howto/redirection_tutorial
[34]: http://mywiki.wooledge.org/BashPitfalls#cat_file_.7C_sed_s.2Ffoo.2Fbar.2F_.3E_file
[35]: http://mywiki.wooledge.org/BashPitfalls
