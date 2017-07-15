# PHP正则表达式的应用

 时间 2017-07-13 14:35:19  [锅子博客][0]

_原文_[https://www.gzpblog.com/20170713/1141.html][1]

 主题 [正则表达式][2][PHP][3]

关于PCRE的介绍以及实现正则表达式功能的所有说明，都可以在官方手册中看到： [正则表达式(兼容 Perl)][4]

## 一 认识PCRE

### 1. 什么是PCRE

PCRE 库是一个实现了与 perl 5 在语法和语义上略有差异的正则表达式模式匹配功能的函数集。

### 2. PCRE 库介绍

PCRE 是 PHP 核心扩展，所以总是启用的。

默认情况下，该扩展使用内置的 PCRE library。或者，也可以通过指定 configure 选项 –with-pcre-regex=DIR 设置外部 PCRE library 目录，DIR 是 PCRE 的 include 和 library 文件位置。 PHP 5.6/7.0 推荐使用 PCRE 8.10 或更高版本。

这些函数中使用的模式语法非常类似 perl。表达式必须用分隔符闭合，比如一个正斜杠(/)。 分隔符可以使任意非字母数字，除反斜杠(\)和空字节之外的非空白 ascii 字符。 如果分隔符 在表达式中使用，需要使用反斜线进行转义。自php 4.0.4开始，可以使用 perl 样式的()、 {}、 [] 以及 <> 作为分隔符。

### 3. PCRE正则与POSIX 正则

除了PCRE正则库，还有POSIX 正则库。 **自PHP 5.3.0起， POSIX 正则表达式扩展被废弃。** 所以，这里也没有必要研究 POSIX 正则了，这里我们就看PCRE正则。 

### 4. 版本特性

PHP 7.0.0 起 PCRE 默认支持 JIT（just-in-time）编译技术，PHP 7.0.12 起可以通过 –without-pcre-jit 禁用 PCRE 的 JIT 功能。

PHP 的 Windows 版本已内建对此扩展的支持。不需要载入额外的扩展来使用这些函数。

PHP 5.3.0 的之前版本，可通过 –without-pcre-regex 配置选项禁用此扩展。

## 二. 预定义常量

下列常量由此扩展定义，且仅在此扩展编译入 PHP 或在运行时动态载入时可用。

**常量** | **描述** | **自哪个版本起** 
-|-|-
PREG_PATTERN_ORDER | 结果按照”规则”排序，仅用于preg_match_all()，即$matches[0]是完整规则的匹配结果，$matches[1]是第一个子组匹配的结果，等等。 | since 
PREG_SET_ORDER | 结果按照”集合”排序，仅用于preg_match_all()，即$matches[0]保存第一次匹配结果的所有结果(包含子组)信息, $matches[1]保存第二次的结果信息，等等。 
PREG_OFFSET_CAPTURE | 查看PREG_SPLIT_OFFSET_CAPTURE的描述。 | 4.3.0 
PREG_SPLIT_NO_EMPTY | 这个标记告诉preg_split()仅返回非空部分。 
PREG_SPLIT_DELIM_CAPTURE | 这个标记告诉preg_split()同时捕获括号表达式匹配到的内容。 | 4.0.5 
PREG_SPLIT_OFFSET_CAPTURE | 如果设置了这个标记，每次出现的匹配子串的偏移量也会被返回。注意，这会改变返回数组中的值，每个元素都是由匹配子串作为第0个元素，它相对目标字符串的偏移量作为第1个元素的数组。这个标记只能用于preg_split()。 | 4.3.0 
PREG_NO_ERROR | 没有匹配错误时调用 preg_last_error() 返回。 | 5.2.0 
PREG_INTERNAL_ERROR | 如果有PCRE内部错误时调用 preg_last_error() 返回。 | 5.2.0 
PREG_BACKTRACK_LIMIT_ERROR | 如果调用回溯限制超出，调用preg_last_error()时返回。 | 5.2.0 
PREG_RECURSION_LIMIT_ERROR | 如果递归限制超出，调用preg_last_error()时返回。 | 5.2.0 
PREG_BAD_UTF8_ERROR | 如果最后一个错误时由于异常的utf-8数据(仅在运行在UTF-8 模式正则表达式下可用)。导致的，调用preg_last_error()返回。 | 5.2.0 
PREG_BAD_UTF8_OFFSET_ERROR | 如果偏移量与合法的urf-8代码不匹配(仅在运行在UTF-8 模式正则表达式下可用)。调用preg_last_error()返回。 | 5.3.0 
PREG_JIT_STACKLIMIT_ERROR | 当 PCRE 函数因 JIT 栈空间限制而失败，preg_last_error() 就会返回此常量。 | 7.0.0 
PCRE_VERSION | PCRE版本号和发布日期(比如： “7.0 18-Dec-2006”)。 | 5.2.4 

## 三 正则语法

1. 详细的正则语法可以参考： [http://php.net/manual/zh/reference.pcre.pattern.syntax.php][5]

2. 之前的一个总结，正则表达式字符表和常用正则表达式：正则表达式 

## 四 PCRE 函数

这篇文章的关注重点将在于函数，和函数的应用，即是正则在PHP中的具体实现。加下来一个一个看。

### 1. preg_filter() 执行一个正则表达式搜索和替换

语法：mixed preg_filter ( mixed $pattern , mixed $replacement , mixed $subject [, int $limit = -1 [, int &$count ]] )

说明：preg_filter()等价于preg_replace() 除了它仅仅返回(可能经过转化)与目标匹配的结果。

返回值： 如果subject是一个数组，返回一个数组， 其他情况返回一个字符串。如果没有找到匹配或者发生了错误，当subject是数组 时返回一个空数组，其他情况返回NULL。

例子：
```php
    <?php
    $subject = array('1', 'a', '2', 'b', '3', 'A', 'B', '4'); 
    $pattern = array('/\d/', '/[a-z]/', '/[1a]/'); 
    $replace = array('A:$0', 'B:$0', 'C:$0'); 
    
    print_r(preg_filter($pattern, $replace, $subject)); //使用filter
    
    print_r(preg_replace($pattern, $replace, $subject)); //使用replace
    
    /*
    返回： 
    Array
    (
        [0] => A:C:1
        [1] => B:C:a
        [2] => A:2
        [3] => B:b
        [4] => A:3
        [7] => A:4
    )
    Array
    (
        [0] => A:C:1
        [1] => B:C:a
        [2] => A:2
        [3] => B:b
        [4] => A:3
        [5] => A
        [6] => B
        [7] => A:4
    )
    preg_filter()只返回匹配到的；preg_replace() 返回所有
    */
```
### 2. preg_grep() 返回匹配模式的数组条目；正则检索一个数组的所有元素

语法：array preg_grep ( string $pattern , array $input [, int $flags = 0 ] )

说明：返回给定数组input中与模式pattern 匹配的元素组成的数组.

参数：

* pattern，要搜索的模式, 字符串形式.
* input，输入数组.
* flags，如果设置为PREG_GREP_INVERT, 这个函数返回输入数组中与 给定模式pattern不匹配的元素组成的数组   
返回值：返回使用input中key做索引的数组。

例子：
```php
    <?php
    // 找出有p的
    $foods = array("pasta", "steak", "fish", "potatoes");
    $p_foods = preg_grep("/p(\w+)/", $foods);
    print_r($p_foods)
    // 输出
    // Array ( [0] => pasta [3] => potatoes )
```
### 3. preg_last_error() 返回最后一个PCRE正则执行产生的错误代码

语法：int preg_last_error ( void )

返回值：返回最后一次PCRE正则执行的错误代码。

* PREG_NO_ERROR 没有匹配错误
* PREG_INTERNAL_ERROR 有PCRE内部错误
* PREG_BACKTRACK_LIMIT_ERROR 调用回溯限制超出
* PREG_RECURSION_LIMIT_ERROR 递归限制超出
* PREG_BAD_UTF8_ERROR 异常的utf-8数据导致
* PREG_BAD_UTF8_OFFSET_ERROR （自 PHP 5.3.0 起） 偏移量与合法的urf-8代码不匹配
* PREG_JIT_STACKLIMIT_ERROR (自 PHP 7.0.0 起) 因 JIT 栈空间限制而失败

具体错误代码的详情在上面预定义常量部分。

例子：
```php
    </pre>
    <?php
    $a = preg_match('/(?:\D+|<\d+>)*[!?]/', 'foobar foobar foobar');
    print_r($a);
    
    if (preg_last_error() == PREG_BACKTRACK_LIMIT_ERROR) {
    print 'Backtrack limit was exhausted!';
    }
    // 输出：Backtrack limit was exhausted!
```
### 4. preg_match() 执行匹配正则表达式

语法：int preg_match ( string $pattern , string $subject [, array &$matches [, int $flags = 0 [, int $offset = 0 ]]] )

说明：搜索subject与pattern给定的正则表达式的一个匹配。

参数：

* pattern，要搜索的模式，字符串类型。
* subject，输入字符串。
* matches，如果提供了参数matches，它将被填充为搜索结果。 $matches[0]将包含完整模式匹配到的文本， $matches[1] 将包含第一个捕获子组匹配到的文本，以此类推。
* flags，flags可以被设置为以下标记值：   
○ PREG_OFFSET_CAPTURE，如果传递了这个标记，对于每一个出现的匹配返回时会附加字符串偏移量(相对于目标字符串的)。 注意：这会改变填充到matches参数的数组，使其每个元素成为一个由 第0个元素是匹配到的字符串，第1个元素是该匹配字符串 在目标字符串subject中的偏移量。
* offset，通常，搜索从目标字符串的开始位置开始。可选参数 offset 用于 指定从目标字符串的某个位置开始搜索(单位是字节)。   
返回值：preg_match()返回 pattern 的匹配次数。 它的值将是0次（不匹配）或1次，因为preg_match()在第一次匹配后 将会停止搜索。preg_match_all()不同于此，它会一直搜索subject 直到到达结尾。 如果发生错误preg_match()返回 FALSE。

例子：
```php
    <?php
    //从URL中获取主机名称
    preg_match('@^(?:http://)?([^/]+)@i', "http://www.php.net/index.html", $matches);
    print_r($matches);
    // 输出 Array ( [0] => http://www.php.net [1] => www.php.net )
    
    preg_match('/[^.]+\.[^.]+$/', $matches[1], $matches);
    print_r($matches);
    // 输出 Array ( [0] => php.net )
```
### 5. preg_match_all() 执行一个全局正则表达式匹配

语法：int preg_match_all ( string $pattern , string $subject [, array &$matches [, int $flags = PREG_PATTERN_ORDER [, int $offset = 0 ]]] )

说明： 搜索subject中所有匹配pattern给定正则表达式的匹配结果并且将它们以flag指定顺序输出到matches中。在第一个匹配找到后，子序列继续从最后一次匹配位置搜索。

参数：

* pattern，要搜索的模式，字符串形式。
* subject，输入字符串。
* matches，多维数组，作为输出参数输出所有匹配结果, 数组排序通过flags指定。
* flags，可以结合下面标记使用(注意不能同时使用PREG_PATTERN_ORDER和PREG_SET_ORDER)   
○ PREG_PATTERN_ORDER 结果排序为$matches[0]保存完整模式的所有匹配, $matches[1] 保存第一个子组的所有匹配，以此类推。   
○ PREG_SET_ORDER 结果排序为$matches[0]包含第一次匹配得到的所有匹配(包含子组)， $matches[1]是包含第二次匹配到的所有匹配(包含子组)的数组，以此类推。   
○ PREG_OFFSET_CAPTURE 如果这个标记被传递，每个发现的匹配返回时会增加它相对目标字符串的偏移量。 注意这会改变matches中的每一个匹配结果字符串元素，使其 成为一个第0个元素为匹配结果字符串，第1个元素为 匹配结果字符串在subject中的偏移量。   
○ 如果没有给定排序标记，假定设置为PREG_PATTERN_ORDER。
* offset，通常， 查找时从目标字符串的开始位置开始。可选参数offset用于 从目标字符串中指定位置开始搜索(单位是字节)。   
返回值：返回完整匹配次数（可能是0），或者如果发生错误返回FALSE。

例子：
```php
    <?php
    // \\2是一个后向引用的示例. 这会告诉pcre它必须匹配正则表达式中第二个圆括号(这里是([\w]+))
    // 匹配到的结果. 这里使用两个反斜线是因为这里使用了双引号.
    $html = "<b>bold text</b><a href=howdy.html>click me</a>";
    preg_match_all("/(<([\w]+)[^>]*>)(.*?)(<\/\\2>)/", $html, $matches, PREG_SET_ORDER);
    print_r($matches);
    /*
    输出
    Array
    (
        [0] => Array
            (
                [0] => <b>bold text</b>
                [1] => <b>
                [2] => b
                [3] => bold text
                [4] => </b>
            )
    
        [1] => Array
            (
                [0] => <a href=howdy.html>click me</a>
                [1] => <a href=howdy.html>
                [2] => a
                [3] => click me
                [4] => </a>
            )
    
    )
    */
```
### 6. preg_quote() 转义正则表达式字符

语法：string preg_quote ( string $str [, string $delimiter = NULL ] )

说明： preg_quote()需要参数 str 并向其中每个正则表达式语法中的字符前增加一个反斜线。 这通常用于你有一些运行时字符串需要作为正则表达式进行匹配的时候。

* 正则表达式特殊字符有： . \ + * ? [ ^ ] $ ( ) { } = ! < > | : –
* 注意 / 不是正则表达式特殊字符。

注意：preg_quote() 的应用场景不是用于 preg_replace() 的 $replacement 字符串参数。

参数：

* str，输入字符串
* delimiter，如果指定了可选参数 delimiter，它也会被转义。这通常用于 转义PCRE函数使用的分隔符。 / 是最常见的分隔符。   
返回值：返回转义后的字符串。

例子：
```php
    <?php
    $keywords = '$40 for a g3/400';
    $keywords = preg_quote($keywords, '/');
    echo $keywords; 
    // 返回 \$40 for a g3\/400
    // $是正则表达式特殊字符, /被当参数传入也转义
```
### 7. preg_replace_callback() 执行一个正则表达式搜索并且使用一个回调进行替换

语法：mixed preg_replace_callback ( mixed $pattern , callable $callback , mixed $subject [, int $limit = -1 [, int &$count ]] )

说明：这个函数的行为除了 可以指定一个 callback 替代 replacement 进行替换 字符串的计算，其他方面等同于 preg_replace()。

参数：

* pattern，要搜索的模式，可以使字符串或一个字符串数组。
* callback， 一个回调函数，在每次需要替换时调用，调用时函数得到的参数是从subject 中匹配到的结果。回调函数返回真正参与替换的字符串。这是该回调函数的签名：string handler ( array $matches ) 。你可能经常会需要callback函数而 仅用于preg_replace_callback()一个地方的调用。在这种情况下，你可以 使用匿名函数来定义一个匿名函数作 为preg_replace_callback()调用时的回调。 这样做你可以保留所有 调用信息在同一个位置并且不会因为一个不在任何其他地方使用的回调函数名称而污染函数名称空间。   
● subject，要搜索替换的目标字符串或字符串数组。   
● limit，对于每个模式用于每个 subject 字符串的最大可替换次数。 默认是-1（无限制）。   
● count，如果指定，这个变量将被填充为替换执行的次数。   
返回值： 如果subject是一个数组， preg_replace_callback()返回一个数组，其他情况返回字符串。 错误发生时返回 NULL。如果查找到了匹配，返回替换后的目标字符串（或字符串数组）， 其他情况subject 将会无变化返回。

例子：
```php
    <?php
    /* 将文本中的年份增加一年 */
    $text = "April fools day is 04/01/2002\n";
    $text.= "Last christmas was 12/24/2001\n";
    // 回调函数
    function next_year($matches)
    {
        /*
        print_r($matches);$matches为:
        Array
        (
            [0] => 04/01/2002
            [1] => 04/01/
            [2] => 2002
        )
        Array
        (
            [0] => 12/24/2001
            [1] => 12/24/
            [2] => 2001
        )
        说明匹配到一个就扔进来一次
        */
        return $matches[1].($matches[2]+1);
    }
    $text = preg_replace_callback("|(\d{2}/\d{2}/)(\d{4})|", "next_year", $text);
    echo $text;
    /*
    输出
    April fools day is 04/01/2003
    Last christmas was 12/24/2002
    */
```
### 8. preg_replace_callback_array() 执行一个正则表达式搜索并且使用多个回调进行替换

语法：mixed preg_replace_callback_array ( array $patterns_and_callbacks , mixed $subject [, int $limit = -1 [, int &$count ]] )

说明：类似于 preg_replace_callback(), 除了回调函数是基于每个参数。

参数：

* patterns_and_callbacks，参数（keys）对应回调函数（values）的数组。
* subject，要搜索替换的目标字符串或字符串数组。
* limit，对于每个模式用于每个 subject 字符串的最大可替换次数。 默认是-1（无限制）。
* count，如果指定，这个变量将被填充为替换执行的次数。

返回值：

preg_replace_callback_array() 如果参数是数组则会返回一个数组，否则为字符串。出错时返回 NULL；如果匹配到，会返回一个新的subject，否则

例子：
```php
    <?php 
    $subject = 'Aaaaaa Bbb';
    preg_replace_callback_array(
        [
            '~[a]+~i' => function ($match) {
                echo strlen($match[0]), ' matches for "a" found', PHP_EOL;
            },
            '~[b]+~i' => function ($match) {
                echo strlen($match[0]), ' matches for "b" found', PHP_EOL;
            }
        ],
        $subject
    );
    /*
    输出
    6 matches for "a" found
    3 matches for "b" found
    */
```
### 9. preg_replace() 执行一个正则表达式的搜索和替换

语法：mixed preg_replace ( mixed $pattern , mixed $replacement , mixed $subject [, int $limit = -1 [, int &$count ]] )

说明：搜索subject中匹配pattern的部分， 以replacement进行替换。

参数：

* pattern，要搜索的模式。可以使一个字符串或字符串数组。 可以使用一些PCRE修饰符。
* replacement，用于替换的字符串或字符串数组。如果这个参数是一个字符串，并且pattern 是一个数组，那么所有的模式都使用这个字符串进行替换。如果pattern和replacement 都是数组，每个pattern使用replacement中对应的 元素进行替换。如果replacement中的元素比pattern中的少， 多出来的pattern使用空字符串进行替换。replacement中可以包含后向引用\\n 或$n，语法上首选后者。 每个 这样的引用将被匹配到的第n个捕获子组捕获到的文本替换。 n 可以是0-99，\\0和$0代表完整的模式匹配文本。 捕获子组的序号计数方式为：代表捕获子组的左括号从左到右， 从1开始数。如果要在replacement 中使用反斜线，必须使用4个(“\\\\”，译注：因为这首先是php的字符串，经过转义后，是两个，再经过 正则表达式引擎后才被认为是一个原文反斜线)。当在替换模式下工作并且后向引用后面紧跟着需要是另外一个数字(比如：在一个匹配模式后紧接着增加一个原文数字)， 不能使用\\1这样的语法来描述后向引用。比如， \\11将会使preg_replace() 不能理解你希望的是一个\\1后向引用紧跟一个原文1，还是 一个\\11后向引用后面不跟任何东西。 这种情况下解决方案是使用${1}1。 这创建了一个独立的$1后向引用, 一个独立的原文1。当使用被弃用的 e 修饰符时, 这个函数会转义一些字符(即：’、”、 \ 和 NULL) 然后进行后向引用替换。当这些完成后请确保后向引用解析完后没有单引号或 双引号引起的语法错误(比如： ‘strlen(\’$1\’)+strlen(“$2”)’)。确保符合PHP的 字符串语法，并且符合eval语法。因为在完成替换后， 引擎会将结果字符串作为php代码使用eval方式进行评估并将返回值作为最终参与替换的字符串。
* subject，要进行搜索和替换的字符串或字符串数组。如果subject是一个数组，搜索和替换回在subject 的每一个元素上进行, 并且返回值也会是一个数组。
* limit，每个模式在每个subject上进行替换的最大次数。默认是 -1(无限)。
* count，如果指定，将会被填充为完成的替换次数。   
返回值： 如果subject是一个数组， preg_replace()返回一个数组， 其他情况下返回一个字符串。如果匹配被查找到，替换后的subject被返回，其他情况下 返回没有改变的 subject。如果发生错误，返回 NULL 。

例子：
```php
    <?php 
    $patterns = array ('/(19|20)(\d{2})-(\d{1,2})-(\d{1,2})/', '/^\s*{(\w+)}\s*=/');
    $replace = array ('\3/\4/\1\2', '$\1 =');
    echo preg_replace($patterns, $replace, '{startDate} = 1999-5-27');
    // 输出: $startDate = 5/27/1999
```
### 10. preg_split() 通过一个正则表达式分隔字符串

语法：array preg_split ( string $pattern , string $subject [, int $limit = -1 [, int $flags = 0 ]] )

说明：通过一个正则表达式分隔给定字符串.

参数：

* pattern，用于搜索的模式，字符串形式。
* subject，输入字符串
* limit，如果指定，将限制分隔得到的子串最多只有limit个，返回的最后一个 子串将包含所有剩余部分。limit值为-1， 0或null时都代表”不限制”， 作为php的标准，你可以使用null跳过对flags的设置。
* flags，flags 可以是任何下面标记的组合(以位或运算 | 组合)：   
○ PREG_SPLIT_NO_EMPTY，如果这个标记被设置， preg_split() 将进返回分隔后的非空部分。   
○ PREG_SPLIT_DELIM_CAPTURE，如果这个标记设置了，用于分隔的模式中的括号表达式将被捕获并返回。   
○ PREG_SPLIT_OFFSET_CAPTURE，如果这个标记被设置, 对于每一个出现的匹配返回时将会附加字符串偏移量. 注意：这将会改变返回数组中的每一个元素, 使其每个元素成为一个由第0 个元素为分隔后的子串，第1个元素为该子串在subject 中的偏移量组成的数组。

返回值：返回一个使用 pattern 边界分隔 subject 后得到 的子串组成的数组， 或者在失败时返回 FALSE。

例子：
```php
    <?php
    $keywords = preg_split ("/[\s,]+/", "hypertext language, programming"); 
    print_r($keywords);
    // 输出
    // Array ( [0] => hypertext [1] => language [2] => programming )
```

[0]: /sites/Qn2QJvI
[1]: https://www.gzpblog.com/20170713/1141.html
[2]: /topics/11110097
[3]: /topics/11120000
[4]: https://www.gzpblog.com/go/?url=aHR0cDovL3BocC5uZXQvbWFudWFsL3poL2Jvb2sucGNyZS5waHA=
[5]: https://www.gzpblog.com/go/?url=aHR0cDovL3BocC5uZXQvbWFudWFsL3poL3JlZmVyZW5jZS5wY3JlLnBhdHRlcm4uc3ludGF4LnBocA==