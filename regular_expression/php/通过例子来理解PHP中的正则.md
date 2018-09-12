# 通过例子来理解PHP中的正则

2017.03.14 13:23  字数 1103  

最近使用 PHP 写了一个应用，主要是正则表达式的处理，趁机系统性的学习了相应知识。  
这篇文章的写作方式不是讲理论，而是通过具体的例子来了解正则，这样也更有实践性，在此基础上再去看正则表达式的基本概念会更有收获。

#### 禁止分组的捕获

在正则中分组很有用，可以定义子模式，然后可以通过后向引用来引用分组的内容，但是有的时候仅仅想通过分组来进行范围定义，而不想被分组来捕获，通过一个例子就能明白：

    $str = "http://www.google.com";
    $preg= "/http:\/\/\w+\.\w+.(?:net|com|cn)+/is";
    $preg2= "/http:\/\/\w+\.\w+.(net|com|cn)+/is";
    preg_match($preg,$str,$arr);
    preg_match($preg2,$str,$arr2);

当模式中出现?:表示这个括号的分组不会被引用，运行下例子就能明白。

#### preg_match() 和 preg_match_all() 的区别

preg_match() 在匹配模式的时候匹配到一次就结束，而 preg_match_all() 则进行全局匹配，通过一个例子就能明白：

    $str='hello world china';
    $preg="/\w+\s/is";
    preg_match($preg,$str,$arr);
    print_r($arr);
    preg_match_all($preg,$str,$arr);
    print_r($arr);

#### 正确理解 $ 和 ^

先说一个正则，为了匹配是否是手机号:

    $str = "13521899942a";
    $preg="/1[\d]{3,15}/is";
    if (preg_match($preg,$str,$arr)) {
        echo "ok";
    }

虽然字符串中有一个英文字母，但是这个子模式却匹配了，原因就在于模式匹配到后就结束了，不会再去寻找英文字母，为了解决这问题 `$` 和 `^` 就发挥作用了，比如让字符串的开始和结尾必须匹配一定的模式，修改如下：

    $str = "13521899942a";
    $preg="/1[\d]{3,15}$/is";
    if (preg_match($preg,$str,$arr)) {
        echo "ok";
    }

#### $ 和 ^ 的跨行模式

默认的情况下，`$` 和 `^` 只会匹配完整段落的开始和结尾，但是通过改变选项，允许匹配文本的每一行的开始和结尾，通过下面的例子就能明白

    $str='hello
    world';
    $preg='/\w+$/ism';//$preg='/(?m)\w+$/is';
    preg_match_all($preg,$str,$arr);
    print_r($arr);

#### 分组命名

在正则中通过括号分组后，可以使用 `\1`,`\2` 这样的数字进行后向引用，但是假如正则中模式太多，在使用的时候就会比较混乱，这时候可以采用分组命名来进行引用，看个例子：

    $str ="email:ywdblog@gmail.com;";
    preg_match("/email:(?<email>\w+?)/is", $str, $matches);
    echo  $matches["email"] . "_" .  $matches['no'];

#### 懒惰模式

正则在匹配的时候是贪婪的，只要符合模式就会一直匹配下去，下面的例子，匹配到的文本是 `<h2>hello</h2><h2>world</h2>`

    $str = "<h2>hello</h2><h2>world</h2>";
    $preg = "/<h2>.*<\/h2>/is";
    preg_match($preg,$str,$arr);
    print_r($arr);

通过改变一个选项可以修改为懒惰模式，就是一旦匹配到就中止，修改代码如下：

    $str = "<h2>hello</h2><h2>world</h2>";
    $preg = "/<h2>.*?<\/h2>/is";
    preg_match($preg,$str,$arr);
    print_r($arr);

#### 进一步理解 preg_match_all()

通过这函数的最后一个参数，能够返回不同形式的数组：

    $str= 'jiangsu (nanjing) nantong
    guangdong (guangzhou) zhuhai
    beijing (tongzhou) haidian';
    $preg = '/^\s*+([^(]+?)\s\(([^)]+)\)\s+(.*)$/m';
    preg_match_all($preg,$str,$arr,PREG_PATTERN_ORDER);
    print_r($arr);
    preg_match_all($preg,$str,$arr,PREG_SET_ORDER);
    print_r($arr);

#### 强大的正则替换回调

虽然 preg_replace() 函数能完成大多数的替换，但是假如你想更好的控制，可以使用回调，不用多说看例子：

    $str = "china hello world";
    $preg = '/\b(\w+)(\w)\b/';
    function fun($m){
            return $m[1].strtoupper($m[2]);
    }
    echo  preg_replace_callback($preg,"fun",$str);

在这一点上，PHP 比 Python 强大的多，Python 中没有正则回调，不过可以使用闭包的方式解决，可看我以前的文章。

#### preg_quote()

这个函数类似于 Python 中的 re.compile() 函数，假如在模式中一些元字符仅仅想表达字符的本身含义，可以转义，但是假如在模式中写太多的转义，会显得很混乱，可以使用这个函数来统一转义：

    $str = '\\*china*world';
    $preg = "\*china";
    $preg = preg_quote($preg);
    echo $preg;
    preg_match( "/{$preg}/is",$str,$arr);
    print_r($arr);

#### 向前查找 ?= 的妙用

用英文解释可能比较贴切：

> The "?=" combination means "the next text must be like this". This construct doesn't capture the text.

（1）这个例子可以获取 URL 中的协议部分，比如 https,ftp，注意 ?: 后面的部分不在返回的内容中。

    $str = "http://www.google.com";
    $str = "https://www.google.com";
    $preg = '/[a-z]+(?=:)/';
    preg_match($preg,$str,$arr);
    print_r($arr);

（2）"invisible" 分隔符

也叫 “zero-width” 分隔符，参考下面的例子：

    $str = ("chinaWorldHello");
    $preg = "/(?=[A-Z])/";
    $arr = preg_split($preg,$str);
    print_r($arr);

（3）匹配强密码

> instead of specifying the order that things should appear, it's saying that it must appear but we're not worried about the order.  
> The first grouping is (?=._{8,}). This checks if there are at least 8 characters in the string. The next grouping (?=._[0-9]) means "any alphanumeric character can happen zero or more times, then any digit can happen". So this checks if there is at least one number in the string. But since the string isn't captured, that one digit can appear anywhere in the string. The next groupings (?=._[a-z]) and (?=._[A-Z]) are looking for the lower case and upper case letter accordingly anywhere in the string.

    $str= "HelloWorld2016";
    if (preg_match("/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/", $str,$arr)){
        print_r($arr);
    }

#### 向后查找 ?<=

`?<=` 表示假如匹配到特定字符，则返回该字符后面的内容。  
`?=` 表示假如匹配到特定字符，则返回该字符前面的内容。

    $str = 'chinadhello';
    $preg = '/(?<=a)d(?=h)/';   
    preg_match($preg, $str, $arr);
    print_r($arr);
