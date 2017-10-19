## 最通俗易懂的php正则表达式教程

**基础知识**

* 字符集
* POSIX 扩展正则表达式函数
* Perl 兼容正则表达式函数

**从邮件验证说起**

邮件的格式: 

tenssun@163.com 

其中tenssun是用户名,163.com是服务器名 

用户名只能由英文字母a～z(不区分大小写)、数字0～9、下划线组成。 

用户名的起始字符必须是英文字母.如：netease_2005 

用户名长度为5～20个字符。 

服务器名只能由英文字母a～z(不区分大小写)、数字0～9、下划线及点组成,@后点前面长度限制为1-10个字符,点后面的限制为com,cn,com.cn,net。 

示例： 
```php
    <?php
    $email='wjj7r8y6@jj.net';
    if(ereg ("^[a-zA-Z][0-9a-zA-Z_]{4,19}@[0-9a-zA-Z_]{1,10}(\.)(com|cn|com.cn|net)$",$email)) {
        echo 'email格式正确';
    }
    ?>
```
**^ 和 $** 看到前面的邮件验证大部分人可能会感到头痛,别急下面我们慢慢分解。 

还是得说说 `^` 和 `$` 他们是分别用来匹配字符串的开始和结束，下面法举例说明： 

"^The": 开头一定要有"The"字符串; 

"of despair$": 结尾一定要有"of despair" 的字符串; 那么, 

"^abc$": 就是要求以abc开头和以abc结尾的字符串，实际上是只有abc匹配 

"notice": 匹配包含notice的字符串 你可以看见如果你没有用我们提到的两个字符（最后一个例子），就是说 模式（正则表达式） 可以出现在被检验字符串的任何地方，你没有把他锁定到两边 (开始或结束) 

**'*', '+',和 '?',**

接着,说说  `*` ,  `+` ,和  `?` , 他们用来表示一个字符可以出现的次数或者顺序. 他们分别表示： 

`*` 表示出现0次或1次或多次 相当于{0,}, 

`+` 表示出现1次或多次 相当于{1,}, 

`?` 表示出现0次或1次 相当于{0,1}, 这里是一些例子: 

* `ab*`: 和ab{0,}同义,匹配以a开头,后面可以接0个或者N个b组成的字符串("a", "ab", "abbb", 等);
* `ab+`: 和ab{1,}同义,同上条一样，但最少要有一个b存在 ("ab", "abbb", 等.);
* `ab?`: 和ab{0,1}同义,可以没有或者只有一个b;
* `a?b+$`: 匹配以一个或者0个a再加上一个以上的b结尾的字符串.

要点：'*', '+',和 '?'只管它前面那个字符. 

**{ }**

你也可以在大括号里面限制字符出现的个数，比如 

* `ah{2}`: 要求a后面一定要跟两个h（一个也不能少）("ahh");
* `ah{2,}`: 要求a后面一定要有两个或者两个以上h(如"ahh", "ahhhh", 等.);
* `ah{3,5}`: 要求a后面可以有3－5个h("ahhh", "ahhhh", or "ahhhhh").

**() {}**

现在我们把一定要的几个字符放到小括号里，比如： 

* `a(bc)*`: 匹配 a 后面跟0个或者多个"bc";
* `a(bc){1,5}`: 一个到5个 "bc."

**'│'**

还有一个字符 '│', 相当于OR(或者) 操作: 

* "hi│hello": 匹配含有"hi" 或者 "hello" 的 字符串;
* "(b│cd)ef": 匹配含有 "bef" 或者 "cdef"的字符串;
* "(a│b)*c": 匹配含有这样多个（包括0个）a或b，后面跟一个c 的字符串;

**'.'**

一个点('.')可以代表所有的单一字符,不包括**`\n`** 

如果,要匹配包括`\n`在内的所有单个字符,怎么办? 

用`[\n.]`这种模式. 

`a.[0-9]` : 一个a加一个字符再加一个0到9的数字 

`.{3}$` : 三个任意字符结尾 . 

**[ ]**

中括号括住的内容只匹配一个单一的字符 

`[ab]` : 匹配单个的 a 或者 b ( 和 "a│b" 一样); 

`[a-d]` : 匹配'a' 到'd'的单个字符 (和"a│b│c│d" 还有 "[abcd]"效果一样); 一般我们都用`[a-zA-Z]`来指定字符为一个大小写英文 

`^[a-zA-Z]` : 匹配以大小写字母开头的字符串 

`[0-9]%` : 匹配含有 形如 `x％` 的字符串 

`,[a-zA-Z0-9]$` : 匹配以逗号再加一个数字或字母结尾的字符串 

**`^[]`和`[^ ]`的区别**

你也可以把你不想要得字符列在中括号里，你只需要在总括号里面使用 `^`  作为开头  `%[^a-zA-Z]%`  匹配含有两个百分号里面有一个非字母的字符串. 

要点：`^`用在中括号开头的时候,就表示排除括号里的字符 

不要忘记在中括号里面的字符是这条规路的例外—在中括号里面, 所有的特殊字符，包括(''), 都将失去他们的特殊性质 `[*\+?{}.]`匹配含有这些字符的字符串. 

**{ } \b**

看了上面的例子,你对{n,m}应该理解了吧.要注意的是,n和m都不能为负整数,而且n总是小于m. 这样,才能 最少匹配n次且最多匹配m次. 如"p{1,5}"将匹配 "pvpppppp"中的前五个p 

下面说说以\开头的 

`\b` 书上说他是用来匹配一个单词边界,就是...比如've\b',可以匹配love里的ve而不匹配very里有ve 

`\B` 正好和上面的\b相反.例子我就不举了 

**应用一**

好,说了这么多下面我们再回过头来看我们的邮件正则怎么构造的: 

用户名的正则表达式 

`^[a-zA-Z][0-9a-zA-Z_]{4,19}` 

a-z表示a到z的所有小写字母，A-Z表示A到Z的所有大写字母，但是[ ]只能取一个字符，所以`[a-zA-Z]`只能取其中一个符,也就是从所有的大小写英文字母中只能取一个字母，^放在[]外面表示开始， 所以`^[a-zA-Z]`表示以一个英文字母开头。 

**应用二**

`[0-9a-zA-Z_]`表示从所有的阿拉伯数字和英文及_中取一个字符,而{4,19}表示匹配最少4次,最多9次显然 

`[0-9a-zA-Z_]{4,19}`表示前面的字符至少出现4次,最多出现19次. 

那么现在请问下面这个表达式所表达的意思 

`^[a-zA-Z][0-9a-zA-Z_]{4,19}` 

`(\.)`表示一个点别和 `.` 混淆了 `.` 是表示除 `\n` 的任意一个字符 `()` 表示这是个子母式 

`(com|cn|com.cn|net)$` 

表示以com或cn或com.cn或net结尾的一串字符

----


**正则表达式简介**

正则表达式是用于描述字符排列模式一种语法规则。它主要用于字符串的模式分割、匹配、查找及替换操作。到目前为止，我们前面所用过的精确（文本）匹配也是一种正则表达式。 

在PHP中，正则表达式一般是由正规字符和一些特殊字符（类似于通配符）联合构成的一个文本模式的程序性描述。 

在程序语言中，通常将模式表达式（即正则表达式）包含在两个反斜线"/"之间，如"/apple/"。用户只要把需要匹配的模式内容放入定界之间即可。 

如果使用一个没有特殊字符的正则表达式，相当于纯文本搜索，使用strstr( )函数也可达到同样的效果。 

[![regular expression.jpg](http://www.neirong.org/content/uploadfile/201610/thum-d8901476554589.jpg "点击查看原图")](http://www.neirong.org/content/uploadfile/201610/d8901476554589.jpg)

在PHP中有两套正则表达式函数库，两者功能相似，只是执行效率略有差异： 

* 一套是由PCRE（Perl Compatible Regular Expression）库提供的。使用"preg_"为前缀命名的函数；
* 一套由POSIX（Portable Operation System interface）扩展提供的。使用以"ereg_"为前缀命名的函数；

使用正则表达式的原因之一，是在典型的搜索和替换操作中，只能对确切文字进行匹配，对象动态文本的搜索就有困难了，甚至是不可能的。 

    

1. `/^-?\d+$|^-?0[xX][\da-fA-F]+$/`
1. `/^[0-9a-zA-Z_-]+@[0-9a-zA-Z_-]+(\.[0-9a-zA-Z_-]+){0,3}]$/`
正则表达式较重要和较有用的角色是验证用户数据的有效性检查。PHP中，正则表达式有三个作用： 

* 匹配，也常常用于从字符串中析取信息。
* 用新文本代替匹配文本。
* 将一个字符串拆分为一组更小的信息块。

**正则表达式的语法规则**

正则表达式是主要由: 

* 原子（普通字符，如英文字符）
* 元字符（有特殊功用的字符）
* 以及模式修正字符组成。

一个正则表达式中至少包含一个原子。 

**原子(Atom)**

原子是组成正则表达式的基本单位,在分析正则表达式时，应作为一个整体。 

原子字符是由所有末显式指定为元字符的打印和非打印字符组成。这包括所有的英文字母、数字、标点符号以及其他一些符号。原子也包括以下内容。 

* 单个字符、数字，如a~z,A~Z,0~9.
* 模式单元，如（ABC）.可以理解为由多个原子组成的大的原子。
* 原子表，如[ABC].
* 重新使用的模式单元。
* 普通转义字符。
* 转义元字符。

**正则表达式所使用的普通转义字符**

原子 | 说明 
-|-
`\d` | 匹配一个数字；等价于 `[0-9]` 
`\D` | 匹配除数字以外任何一个字符；等价于 `[^0-9]` 
`\w` | 匹配一个英文字母、数字或下划线；等价于 `[0-9a-zA-Z_]` 
`\W` | 匹配除英文字母、数字和下划线以外任何一个字符；等价于 `[^0-9a-zA-Z_]` 
`\s` | 匹配一个空白字符；等价于 `[\f\n\r\t\v]` 
`\S` | 匹配除空白字符以外任何一个字符；等价于 `[^\f\n\r\t\v]` 
`\f` | 匹配一个换页符等价于 `\x0c` 或 `\cL` 
`\n` | 匹配一个换行符；等价于 `\x0a` 或 `\cJ` 
`\r` | 匹配一个回车符等价于 `\x0d` 或 `\cM` 
`\t` | 匹配一个制表符；等价于 `\x09\` 或 `\cl` 
`\v` | 匹配一个垂直制表符；等价于 `\x0b` 或 `\ck` 
`\oNN` | 匹配一个八进制数字 
`\xNN` | 匹配一个十六进制数字 
`\cC` | 匹配一个控制字符 

**元字符(Meta-character)**

元字符是用于构造规则表达式的具有特殊含义的字符。如果要在正则表达式中包含元字符本身，必须在其前加上"\"进行转义 

说明 | 元字符 
-|-
`*` | 0 次、 1 次或多次匹配其前的原子 
`+` | 1 次或多次匹配其前的原子 
`?` | 0 次或 1 次匹配其前的原子 
`.` | 匹配任何一个字符
`|` | 匹配两个或多个选择 
`^`或`\A` | 匹配字符串串首的原子 
`$`或`\Z` | 匹配字符串串尾的原子 
`\b` | 匹配单词的边界 
`\B` | 匹配除单词边界以外的部分 
`[]` | 匹配方括号中的任一原子 
`[^]` | 匹配除方括号中的原子外的任何字符 
`()` | 整体表示一个原子 
`{m}` | 表示其前原子恰好出现 m 次 
`{m,n}` | 表示其前原子至少出现 m 次，至多出现 n 次 (n>m) 
`{m,}` | 表示其前原子出现不少于 m 次

**原子表**  

* 原子表 `[]` 中存放一组原子，彼此地位平等，且仅匹配其中的一个原子。如果想匹配一个 "a" 或 "e" 使用 `/[ae]/`, 例如: `/Pr[ae]y/` 匹配 "Pray" 或者 "Prey "。  
* 原子表  `^`  或者称为排除原子表，匹配除表内原子外的任意一个字符。例如：`/p[^u]/`匹配"part"中的"pa"，但无法匹配"computer"中的"pu"因为"u"在匹配中被排除。  
* 通常，在原子表中用 `-` 连接一组按ASCII码顺序排列的原子，用以简化书写。 如`/x[0123456789]`可以写成`/x[0-9]/`,用来匹配一个由 "x" 字母与一个数字组成的字符串；  
* `/0[xX][0-9a-fA-F]/`匹配一个简单的十六进制数字，如"0x9"。`/[^0-9a-zA-Z_]/`匹配除英文字母、数字和下划线以外任何一个字符，其等价于`/\W/`。  

**重复匹配**

正则表达式中有一些用于重复匹配其前原子的元字符： `?` 、 `*` 、 `+` 。他们主要的不同是重复匹配的次数不同。 

* 元字符 `？` 表示0次或1次匹配紧接在其前的原子。例如：`/colou?r/`匹配"colour"或"color"。
* 元字符 `*` 表示0次、1次或多次匹配紧接在其前的原子。例如：`/^<[A-Za-z][A-Za-z0-9]*>$/`可以匹配`"<P>"、"<h1>"`或"<Body>"等HTML标签，并且不严格的控制大小写。
* 元字符 `+` 表示1次或多次匹配紧接在其前的原子。例如：`/go+gle/`匹配"gogle"、"google"或"gooogle"等中间含有多个o的字符串。上文中提及的十六进制数字的例子，实际上更加完善的匹配表达式是`/^0?[ xX][0-9a-fA-F]+$/`,可以匹配"0x9B3C"或者"X800"等。

要准确地指定原子重复的次数，还可以使用元字符 `{}` 指定所匹配的原子出现的次数。 `{m}` 表示其前原子恰好出现m次； `{m，n}` 表示其前原子至少出现m次，至多出现n次； `{m，}` 表示其前原子出现不少于m次。 

以下是一些示例。 

* `/zo{1,3}m/`只能匹配字符串"zom"、"zoom"、或"zooom"。
* `/zo{3}m/`只能匹配字符串"zooom"
* `/zo{3，}m/` 可以匹配以 "z" 开头，"m"结束，中间至少为3个"o"的字符串。
* `/bo{0,1}u/`可以匹配字符串"bought a butter" 中的"bou"和"bu",起完全等价于`/bo?u/`。

**边界限制**

在某些情况下，需要对匹配范围进行限定，以获得更准确的匹配结果。 

元字符 `^` （或 `\A` ）置于字符串的开始确保模式匹配出现在字符串首端； 

`$` （或 `\Z` ）置于字符串的结束，确保模式匹配出现字符串尾端。 

例如，在字符串"Tom and Jerry chased each other in the house until tom's uncel come in"中使用/^Tom或Atom匹配句首的"Tom"； 而`/in$`或`/in\Z`匹配句末"come in"中的"in"。如果不加边界限制元字符，将获得更多的匹配结果。 

在使用各种编辑软件的查找功能时，可以通过选择"按单词查找"获得更准确的结果。正则表达式中也提供类似的功能。 

元字符 `\b` 对单词的边界进行匹配； 

`\B` 对单词的内部进行匹配。 

例如：在字符串"This island is a beautiful land"中使用`/\bis\b/`可以匹配单词"is"而与"This"或者"island"无关。`/\bis/`与单词边界匹配，可以匹配单词"is"和"island"中的"is"；`/\Bis/`不与单词左边界匹配，可以匹配单词"is"和"This"中的"is".`/\Bis\B`将明确的指示不与单词的左、右边界匹配，只匹配单词的内部。所以在这个例子中没有结果。 

**元字符  `.` **

元字符 `.` 匹配除换行符外任何一个字符，相当于`[^\n]`(Unix系统)或`[^\r\n]`(windows系统)。 

例如：`/pr.y/`可以匹配的字符串 "prey"、"pray"或"pr%y"等。 

通常可以使用 `.*` 组合来匹配除换行符外的任何字符。在一些书籍中也称其为"全匹配符" 或 "单含匹配符"。 

例如`/a.*z/`表示可以匹配字母"a"开头，字母"z"结束的任意不包括换行符的字符串。 `.+` 也可以完成类似的匹配功能所不同的是其至少匹配一个字符。例如`/a.+z/`将不匹配字符串"az"。 

**模式选择符**

元字符"|"又称模式选择符。在正则表达式中匹配两个或更多的选择之一。 

例如： 

在字符串"There are many apples and pears."中，`/apple|pear/` 在第一次运行时匹配"apple"；再次运行时匹配" pear"。也可以继续增加选项，如`/apple|pear|banana|lemon/`。 

**模式单元**

元字符 `（）` 将其中的正则表达式变为原子（或称模式单元）使用。与数学表达式中的括号类似， `（）` 可以做一个单元被单独使用。 

例如： 

`/(Dog)+/` 匹配的"Dog"、"DogDog"、"DogDogDog"……..,因为紧接着 `+` 前的原子是元字符 `（）` 括起来的字符串"Dog"。 

一个模式单元中的表达式将被优先匹配或运算。系统自动将这些模式单元的匹配依次存储起来，在需要时可以用 `\1` 、 `\2` 、 `\3` 的形式进行引用。当正则表达式包含有相同的模式单元时，这种方法非常便于对其进行管理。 

例如： 

`/^\d{2}([\W])\d{2}\\1\d{4}$`匹配"12-31-2006"、"09/27/1996"、"86 01 4321"等字符串。但上述正则表达式不匹配"12/34-5678"的格式。这是因为模式 `[\W]` 的结果 `/` 已经被存储。下个位置 `\1` 引用时，其匹配模式也是字符 `/` 。 

**模式修正符（Pattern Modifiers）**

模式修正符扩展了正则表达式在字符匹配、替换操作时的某些功能。这些扩展或者说修正增强了正则表达式的处理能力。模式修正符一般标记于整个模式之外，并且可以组合使用，如 `/apple/i` 、 `/cat|dog/uis` 等。表列出了一些常用的模式修正符极其功能说明。 

模式修正符 说明 
`i` 可同时匹配大小写字母 
`M` 将字符串视为多行
`S` 将字符串视为单行，换行符做为普通字符看待 
`x` 模式中的空白忽略不计 
`S` 当一个模式将被使用若干次时，为加速匹配起见值得先对其进行分析 
`U` 匹配到最近的字符串 
`e` 将替换的字符串作为表达使用 

下面是几个简单的示例，可以说明模式修正符的使用 `/apple/i` 匹配"apple"或"Apple"等，忽略大小写。 
```
/I love you/ix匹配"iloveYou",忽略大小写以及空白。 

/<.*>/ U将依次匹配字符串"<b>Cool</b> music<hr>Few years ago….. "中的"<b>"、"</b>"和"<hr>"。而/<.*>/却匹配到最后一个可用的字符串，既"<b>Cool</b>music<hr>" 。 

/<h .*>/Uis将HTML文件视为单行字符串，忽略大小写和换行符。匹配其中中的所有以"h"开头的标签，如"<Hl>"、"<hr size>"等。
```

----

**POSIX风格的正则表达式的字符类：**

* [[:alnum:]] 文字数字字符
* [[:alpha:]] 字母字符
* [[:lower:]] 小写字母
* [[:upper:]] 大写字母
* [[:digit:]] 小数
* [[:xdigit:]] 十六进制数字
* [[:punct:]] 标点符号
* [[:blank:]] 制表符和空格
* [[:space:]] 空白字符
* [[:cntrl:]] 控制符

**PHP中的正则表达式函数**

在PHP中有两套正则表达式函数库。 

* 一套是由PCRE(Perl Compatible Regular Expression)库提供的。PCRE库使用和perl相同的语法规则实现了正则表达式的模式匹配，其使用以"preg_"为前缀命名的函数。
* 另一套是由POSIX(Portable Operation System interface)扩展库提供的。POSIX扩展的正则表达式由POSIX 1003.2定义，一般使用以"ereg_"为前缀命名的函数。

两套函数库的功能相似，执行效率稍有不同。一般而言，实现相同的功能，使用PCRE库的效率略占优势。 

**正则表达式的匹配**

1、preg_match() 函数 

函数原形： 

    int preg_match(string $pattern, string $content[,array $matches])

preg_match()函数在$content字符串中搜索与$pattern给出的正则表达式相匹配的内容。如果提供了`$matches`,则将匹配结果放入其中。`$matches[0]`将包含与整个匹配的文本，`$matches[1]`将包含第一个捕获的与括号中的模式单元所匹配的内容，以此类推。该函数只作一次匹配，最终返回0或1的匹配结果数。 
```php
    <?php
    //需要匹配的字符串。date函数返回当前时间
    $content = "Current date and time is ".date("Y-m-d h:i a").", we are learning PHP together.";
    
    //使用通常的方法匹配时间
    if (preg_match ("/\d{4}-\d{2}-\d{2} \d{2}:\d{2} [ap]m/", $content, $m))
    {
        echo "匹配的时间是：" .$m[0]. "\n";
    }
    
    //由于时间的模式明显，也可以简单的匹配
    if (preg_match ("/([\d-]{10}) ([\d:]{5} [ap]m)/", $content, $m))
    {
        echo "当前日期是：" .$m[1]. "\n";
        echo "当前时间是：" .$m[2]. "\n";
    }
    ?>
```
2、ereg()和eregi() 

Ereg()是POSIX扩展中正则表达式的匹配函数.eregi()是ereg()函数的忽略大小的版本.两者与preg_match的功能类似,但函数返回的是一个布尔值,表明匹配成功与否. 需要说明的是,POSIX扩展库函数的第一个参数接受的是正则表达式字符串,即不需要使用分解符. 

通常情况下,使用与Perl兼容的正则表达式匹配函数perg_match(),将比使用ereg()或eregi()的速度更快. 如果只是查找一个字符串中是否包含某个子字符串,建议使用strstr()或strpos()函数.   

```php
    <?php
    $username = $_SERVER['REMOTE_USER'];
    $filename = $_GET['file'];
    
    //对文件名进行过滤，以保证系统安全
    if (!ereg('^[^./][^/]*$', $userfile))
    {
        die('这是一个非法的文件名！');
    }
    
    //对用户名进行过滤
    if (!ereg('^[^./][^/]*$', $username))
    {
        die('这不是一个有效的用户名');
    }
    
    //通过安全过滤，拼合文件路径
    $thefile = "/home/$username/$filename";
    ?>
```

3、preg_grep() 

函数原型: 

    

    array preg_grep(string $pattern,array $input)
Preg_grep()函数返回一个数组,其中包括了$input数组中与给定的$pattern模式相匹配的单元。对于输入数组$input中的每个元素，preg_grep()也只进行一次匹配。 
```php
    <?php
    $subjects = array(
    "Mechanical Engineering",  "Medicine",
    "Social Science",          "Agriculture",
    "Commercial Science",     "Politics"
    );
    
    //匹配所有仅由有一个单词组成的科目名
    $alonewords = preg_grep("/^[a-z]*$/i", $subjects);
    ?>
```

**进行全局正则表达式匹配** 


1、preg_match_all() 

与preg_match()函数类似。如果使用了第三个参数，将把所有可能的匹配结果放入。本函数返回整个模式匹配的次数（可能为0），如果出错返回False。 
```php
    <?php
    //功能：将文本中的链接地址转成HTML
    //输入：字符串
    //输出：字符串
    function url2html($text)
    {
        //匹配一个URL，直到出现空白为止
        preg_match_all("/http:\/\/?[^\s]+/i", $text, $links);
        
        //设置页面显示URL地址的长度
        $max_size = 40;
        foreach($links[0] as $link_url)
        {
            //计算URL的长度。如果超过$max_size的设置，则缩短。
            $len = strlen($link_url);
            
            if($len > $max_size)
            {
                $link_text = substr($link_url, 0, $max_size)."...";
            } else {
                $link_text = $link_url;
            }
            
            //生成HTML文字
            $text = str_replace($link_url,"<a href='$link_url'>$link_text</a>",$text);
        }
        return $text;
    }
    
    //运行实例
    $str = "这是一个包含多个URL链接地址的多行文字。欢迎访问http://www.taoboor.com";
    print url2html($str);
    
    /*输出结果
    这是一个包含多个URL链接地址的多行文字。欢迎访问<a href='http://www.taoboor.com'>
    http://www.taoboor.com</a>
    */
    ?>
```
**多行匹配** 仅仅使用POSIX下的正则表达式函数，很难进行复杂的匹配操作。例如，对整个文件（尤其是多行文本）进行匹配查找。使用ereg()对此进行操作的一个方法是分行处理。 
```php
    <?php
    $rows = file('php.ini');  //将php.ini文件读到数组中
    
    //循环便历
    foreach($rows as $line)
    {
        If(trim($line))
    {
    //将匹配成功的参数写入数组中
    if(eregi("^([a-z0-9_.]*) *=(.*)", $line, $matches))
    {
        $options[$matches[1]] = trim($matches[2]);
    }
    unset($matches);
    }
    }
    
    //输出参数结果
    print_r($options);
    ?>
```

正则表达式的替换 

1、ereg_replace()和eregi_replace() 

函数原形： 

    

    string ereg_replace (string $pattern,string $replacement,string $string)
    String eregi_replace(string $pattern,string $replacerment,string $string)

Ereg_replace()在$string中搜索模式字符串$pattern,并将所匹配结果替换为$sreplacement。 

当$pattern中包含模式单元（或子模式）时，$replacement中形如"\1"或"$1"的位置将依次被这些子模式所匹配的内容替换。而"\0"或"$0"是只整个的匹配字符串的内容。需要注意的是，在双引号中反斜线作为转义符使用，所以必须使用"\\0"，\\1的形式。 

Eregi_replace()和ereg_replace()的功能一致，只是前者忽略大小写 

```php
    <?php
    $lines = file('source.php'); //将文件读入数组中
    
    for($i=0; $i<count($lines); $i++)
    {
    //将行末以"\\"或"#"开头的注释去掉
    $lines[$i] = eregi_replace("(\/\/|#).*$", "", $lines[$i]);
    //将行末的空白消除
    $lines[$i] = eregi_replace("[ \n\r\t\v\f]*$", "\r\n", $lines[$i]);
    }
    
    //整理后输出到页面
    echo htmlspecialchars(join("",$lines));
    ?>
```

2、preg_replace() 

函数原形： 

    

    mixed preg_replace(mixed $patten,mixed $replacement,mixed $subject[,int $limit])

Preg_replace较ereg_replace的功能更加强大，其前三个参数均可以使用数组；第四个参数$limit可以设置替换的次数，默认为全部替换。 
```php
    <?php
    //字符串
    $string = "Name: {Name}<br>\nEmail: {Email}<br>\nAddress: {Address}<br>\n";
    
    //模式
    $patterns =array(
    "/{Address}/",
    "/{Name}/",
    "/{Email}/"
    );
    
    //替换字串
    $replacements = array (
    "No.5, Wilson St., New York, U.S.A",
    "Thomas Ching",
    "tom@emailaddress.com",
    );
    
    //输出模式替换结果
    print preg_replace($patterns, $replacements, $string);
    ?>
```

**正则表达式的拆分** 1、split()和spliti() 

函数原型： 

    

    array split (string $pattern,string $string[,int $limit])

本函数返回一个字符串数组，每个单元为$string经正则表达式$pattern作为边界分割出的子串。如果设定了$limit,则返回的数组最多包含$limit个单元。而其中最后一个单元包含了$string中剩余的所有部分。Spliti是split的忽略大小版本。 

    
```php
    <?php
    $date = "08/30/2006";
    
    //分隔符可以是斜线，点，或横线
    list($month, $day, $year) = split ('[/.-]', $date);
    
    //输出为另一种时间格式
    echo "Month: $month; Day: $day; Year: $year<br />\n";
    ?>
```
2、preg_split() 

本函数与split函数功能一致。 

    
```php
    <?php
    $seek  = array();
    $text   = "I have a dream that one day I can make it. So just do it, nothing is impossible!";
    
    //将字符串按空白，标点符号拆分（每个标点后也可能跟有空格）
    $words = preg_split("/[.,;!\s']\s*/", $text);
    foreach($words as $val)
    {
        $seek[strtolower($val)] ++;
    }
    
    echo "共有大约" .count($words). "个单词。";
    echo "其中共有" .$seek['i']. "个单词"I"。";
    ?>
```
**正则表达式的Wed验证应用** 电子邮件地址的校验 
```php
    <?php
    /* 校验邮件地址 */
    function checkMail($email)
    {
    //用户名，由"\w"格式字符、"-"或"."组成
    $email_name  = "\w|(\w[-.\w]*\w)";
    
    //域名中的第一段，规则和用户名类似，不包括点号"."
    $code_at     = "@";
    $per_domain  = "\w|(\w[-\w]*\w)";
    
    //域名中间的部分，至多两段
    $mid_domain  = "(\." .$per_domain. "){0,2}";
    
    //域名的最后一段，只能为".com"、".org"或".net"
    $end_domain  = "(\.(com|net|org))";
    
    $rs = preg_match(
    "/^{$email_name}@{$per_domain}{$mid_domain}{$end_domain}$/",
    $email
    );
    return (bool)$rs;
    }
    
    //测试，下面均返回成功
    var_dump( checkMail("root@localhost") );
    var_dump( checkMail("Frank.Roulan@esun.edu.org") );
    var_dump( checkMail("Tom.024-1234@x-power_1980.mail-address.com") );
    ?>
```
URL地址的校验 

    
```php
    <?php
    /* 校验URL地址 */
    function checkDomain($domain)
    {
        return ereg("^(http|ftp)s? ://(www\.)?.+(com|net|org)$", $domain);
    }
    
    $rs = checkDomain ("www.taodoor.com");               //返回假
    $rs = checkDomain ("http://www.taodoor.com");        //返回真
    ?>
```
电话号码 
```php

    <?php
    /* 校验电话号码 */
    function checkTelno($tel)
    {
        //去掉多余的分隔符
        $tel = ereg_replace("[\(\)\. -]", "", $tel);
        
        //仅包含数字，至少应为一个6位的电话号（即没有区号）
        if(ereg("^\d+$", $tel))
        {
            return true;
        }else{
            return false;
        }
    }
    
    $rs = checkTelno("(086)-0411-12345678");         //返回真
    ?>
```
邮政编码的校验 

```php
    <?php
    /* 校验邮政编码 */
    function checkZipcode($code)
    {
        //去掉多余的分隔符
        $code = preg_replace("/[\. -]/", "", $code);
        
        //包含一个6位的邮政编码
        if(preg_match("/^\d{6}$/", $code))
        {
            return true;
        }else{
            return false;
        }
    }
    
    $rs = checkZipCode("123456");    //返回真
    ?>
```
至此，最通俗易懂的php正则表达式教程结束！