
[Source](http://oss.org.cn/ossdocs/php/php_coding_standard_cn.html "Permalink to PHP程序编码规范标准")

# PHP程序编码规范标准

([English version][1])

### 最后修改日期: 2000-11-16


PHP编程标准是经由Todd Hoff许可，基于《C++ 编程标准》为PHP而重写的，
作者为[Fredrik Kristiansen][2]，


**使用本标准，**如果您想拷贝一份留做自用的话，那是完全免费的，这也是我们制作它的原因。假如您发现了任何的错误又或者是有任何的改进，请您给笔者发一个email，以便笔者将它们合并到[最新更新][3]中去。

***

* **介绍** 

  * 标准化的重要性 
  * 解释 
  * 认同观点 

* **命名规则**

  * 合适的命名 
  * 缩写词不要全部使用大写字母 
  * 类命名 
  * 类库命名 
  * 方法命名 
  * 类属性命名
  * 方法中参数命名 
  * 变量命名
  * 引用变量和函数返回引用
  * 全局变量 
  * 定义命名 / 全局常量
  * 静态变量 
  * 函数命名 
  * php文件扩展名 

*  **文档规则**

  * 评价注释 
  * Comments Should Tell a Story 
  * Document Decisions 
  * 使用标头说明 
  * Make Gotchas Explicit 
  * Interface and Implementation Documentation 
  * 目录文档 

*  **复杂性管理规则**

  * Layering 
  * Open/Closed Principle 
  * Design by Contract 

*  **类规则**

  * Different Accessor Styles 
  * 别在对象架构期做实际的工作 
  * Thin vs. Fat Class Interfaces 
  * 短方法 

*  **进程规则**

  * Use a Design Notation and Process 
  * Using Use Cases 
  * Code Reviews 
  * Create a Source Code Control System Early and Not Often
  * Create a Bug Tracking System Early and Not Often
  * RCS关键词、更改记录和历史记录规则
  * Honor Responsibilities 

*  **格式化**

  * 大括号 {} 规则 
  * 缩进/制表符/空格 规则 
  * 小括号、关键词和函数 规则
  * _If Then Else_ 格式
  * _switch_ 格式 
  * _continue,break_ 和 _? _的使用 
  * 每行一个语句
  * 声明块的定位

*  **流行神话**

  * Promise of OO 

*  **杂项**

  * 不要不可思议的数字
  * 错误返回检测规则
  * 不要采用缺省值测试非零值
  * 布尔逻辑类型
  * 通常避免嵌入式的赋值
  * 重用您和其他人的艰苦工作
  * 使用if (0)来注释外部代码块
  * 其他杂项

***

## 介绍

### 标准化的重要性

标准化问题在某些方面上让每个人头痛，让人人都觉得大家处于同样的境地。这有助于让这些建议在许多的项目中不断演进，许多公司花费了许多星期逐子字逐句的进行争论。标准化不是特殊的个人风格，它对本地改良是完全开放的。

#### 优点

当一个项目尝试着遵守公用的标准时，会有以下好处：

* 程序员可以了解任何代码，弄清程序的状况
* 新人可以很快的适应环境
* 防止新接触php的人出于节省时间的需要，自创一套风格并养成终生的习惯
* 防止新接触php的人一次次的犯同样的错误
* 在一致的环境下，人们可以减少犯错的机会
* 程序员们有了一致的敌人 :-)

#### 缺点

现在轮到坏处了:

* 因为标准由一些不懂得php的人所制定，所以标准通常看上去很傻
* 因为标准跟我做的不一样，所以标准通常看上去很傻
* 标准降低了创造力
* 标准在长期互相合作的人群中是没有必要的
* 标准强迫太多的格式
* 总之人们忽视标准

#### 讨论

许多项目的经验能得出这样的结论：采用编程标准可以使项目更加顺利地完成。

标准是成功的关键么？当然不。但它们可以帮助我们，而且我们需要我们能得到的所有的帮助！

老实说，对一个细节标准的大部分争论主要是源自自负思想。

对一个合理的标准的很少决定能被说为是缺乏技术性的话，那只是口味的原因罢了。

所以，要灵活的控制自负思想，记住，任何项目都取决于团队合作的努力。


### 解释

#### 惯例

在本文档中使用"要"字所指的是使用本规范的所有项目需要遵守规定的标准。

使用"应该"一词的作用是指导项目定制项目细节规范。

因为项目必须适当的`包括 (include）`，`排除(exclude)`或`定制（tailor）`需求。

使用"可以"一词的作用与"应该"类似，因为它指明了可选的需求。

#### 标准实施

首先应该在开发小组的内部找出所有的最重要的元素，也许标准对你的状况还不够恰当。

它可能已经概括了 `重要的问题`，也可能还有人对其中的某些问题表示强烈的反对。

无论在什么情况下，只要最后顺利的话，人们将成熟的明白到这个标准是合理的，然后其他的程序员们也会发现它的合理性，并觉得带着一些保留去遵循这一标准是值得的。

如果没有自愿的合作，可以制定需求：`标准一定要经过代码的检验`。

如果没有检验的话，这个解决方案仅仅是一个建立在不精确的基础上的一大群可笑的人。

### 认同观点

1. 这行不通；
2. 也许可行吧，但是它既不实用又无聊；
3. 这是真的，而且我也告诉过你啊；
4. 这个是我先想到的；
5. 本来就应该这样。


如果您带着否定的成见而来看待事物的话，请您保持开放的思想。你仍可以做出它是废话的结论，但是做
出结论的方法就是你必须要能够接受不同的思想。请您给自己一点时间去做到它。

### 项目的四个阶段

1. 数据库结构
2. 设计
3. 数据层
4. HTML层


***

#### 合适的命名

命名是程序规划的核心。古人相信只要知道一个人真正的名字就会获得凌驾于那个人之上的不可思议的力
量。只要你给事物想到正确的名字，就会给你以及后来的人带来比代码更强的力量。别笑！

名字就是事物在它所处的生态环境中一个长久而深远的结果。总的来说，只有了解系统的程序员才能为系
统取出最合适的名字。如果所有的命名都与其自然相适合，则关系清晰，含义可以推导得出，一般人的推
想也能在意料之中。

如果你发觉你的命名只有少量能和其对应事物相匹配的话， 最好还是重新好好再看看你的设计吧。

#### **类命名**

* 在为类（class ）命名前首先要知道它是什么。如果通过类名的提供的线索，你还是想不起这个类是
什么 的话，那么你的设计就还做的不够好。
* 超过三个词组成的混合名是容易造成系统各个实体间的混淆，再看看你的设计，尝试使用（CRC Se-
ssion card)看看该命名所对应的实体是否有着那么多的功用。
* 对于派生类的命名应该避免带其父类名的诱惑，一个类的名字只与它自身有关，和它的父类叫什么无
关。
* 有时后缀名是有用的，例如：如果你的系统使用了代理（agent ），那么就把某个部件命名为"下
载代理"（DownloadAgent）用以真正的传送信息。

#### 方法和函数命名

* 通常每个_**方法**_和_**函数**_都是执行一个动作的，所以对它们的命名应该清楚的说明它们是做什么的：用
CheckForErrors()代替ErrorCheck()，用DumpDataToFile()代替DataFile()。这么做也可以使功能和
数据成为更可区分的物体。

* 有时后缀名是有用的:
 * Max - 含义为某实体所能赋予的最大值。
 * Cnt - 一个运行中的计数变量的当前值。
 * Key - 键值。

> 例如：RetryMax 表示最多重试次数，RetryCnt 表示当前重试次数。

* 有时前缀名是有用的：
 * Is - 含义为问一个关于某样事物的问题。无论何时，当人们看到Is就会知道这是一个问题。
 * Get - 含义为取得一个数值。
 * Set - 含义为设定一个数值

> 例如：IsHitRetryLimit。


### 缩写词不要全部使用大写字母

* 无论如何，当遇到以下情况，你可以用首字母大写其余字母小写来代替全部使用大写字母的方法来表
示缩写词。

 > 使用: GetHtmlStatistic. 
 
 > 不使用: GetHTMLStatistic.

#### 理由

* 当命名含有缩略词时，人们似乎有着非常不同的直觉。统一规定是最好，这样一来，命名的含义就完
全可以预知了。

举个_NetworkABCKey_的例子，注意C是应该是ABC里面的C还是key里面的C，这个是很令人费解的。有些
人不在意这些，其他人却很讨厌这样。所以你会在不同的代码里看到不同的规则，使得你不知道怎么
去叫它。

#### 例如

```
class FluidOz             // 不要写成 FluidOZ
class GetHtmlStatistic       // 不要写成 GetHTMLStatistic
```

* * *

### 类命名

* 使用大写字母作为词的分隔，其他的字母均使用小写
* 名字的首字母使用大写
* 不要使用下划线('_')

#### 理由

* 根据很多的命名方式，大部分人认为这样是最好的方式。

#### 例如


    class NameOneTwo

    class Name


* * *

### **类库命名**

* 目前命名空间正在越来越广泛的被采用，以避免不同厂商和团体类库间的类名冲突。
* 当尚未采用命名空间的时候，为了避免类名冲突，一般的做法是在类名前加上独特的前缀，两个字符就
可以了，当然多用一些会更好。

#### 例如

John Johnson的数据结构类库可以用Jj做为前缀，如下：


    class JjLinkList
    {
    }


* * *

### 方法命名

* 采用与类命名一致的规则

#### 理由

* 使用所有不同规则的大部分人发现这是最好的折衷办法。

#### 例如


    class NameOneTwo
    {
        function DoIt() {};
        function HandleError() {};
    }


* * *

### 类属性命名

* 属性命名应该以字符'm'为前缀。
* 前缀'm'后采用于类命名一致的规则。
* 'm'总是在名字的开头起修饰作用，就像以'r'开头表示引用一样。

#### 理由

* 前缀'm'防止类属性和方法名发生任何冲突。你的方法名和属性名经常会很类似，特别是存取元素。

#### 例如


    class NameOneTwo
    {
        function VarAbc() {};
        function ErrorNumber() {};
        var mVarAbc;
        var mErrorNumber;
        var mrName;
    }


* * *

### 方法中参数命名

* 第一个字符使用小写字母。
* 在首字符后的所有字都按照类命名规则首字符大写。

#### 理由

* 你可以随时知道那个变量对应那个变量。
* 你可以使用与类名相似的名称而不至于产生重名冲突。

#### 例如


       class NameOneTwo
       {
          function StartYourEngines(
                    &$rSomeEngine,
                    &$rAnotherEngine);
       }


* * *

### 变量命名

* 所有字母都使用小写
* 使用'_'作为每个词的分界。

#### 理由

* 通过这一途径，代码中变量的作用域是清晰的。
* 所有的变量在代码中都看起来不同，容易辨认。

#### 例如


    function HandleError($errorNumber)
    {
          $error = OsErr();
          $time_of_error = OsErr->getTimeOfError;
          $error_processor = OsErr->getErrorProcessor;
    }


* * *

### 引用变量和函数返回引用

* 引用必须带'r'前缀

#### 理由

* 使得类型不同的变量容易辨认
* 它可以确定哪个方法返回可更改对象，哪个方法返回不可更改对象。

#### 例如


    class Test
    {
        var mrStatus;
        function DoSomething(&$rStatus) {};
        function &rStatus() {};
    }


* * *

### 全局变量

* 全局变量应该带前缀'g'。

#### 理由

* 知道一个变量的作用域是非常重要的。

#### 例如


        global $gLog;
        global &$grLog;


* * *

### 定义命名 / 全局常量

* 全局常量用'_'分隔每个单词。

#### 理由

这是命名全局常量的传统。你要注意不要与其它的定义相冲突。

#### 例如



    define("A_GLOBAL_CONSTANT", "Hello world!");


* * *

### 静态变量

* 静态变量应该带前缀's'。

#### 理由

* 知道一个变量的作用域是非常重要的。

#### 例如


    function test()
    {
    static $msStatus = 0;
    }


* * *

### 函数命名

* 函数名字采用C GNU的惯例，所有的字母使用小写字母，使用'_'分割单词。

#### 理由

* 这样可以更易于区分相关联的类名。

#### 例如


    function some_bloody_function()
    {
    }


* * *

### 错误返回检测规则
* 检查所有的系统调用的错误信息，除非你要忽略错误。
* 为每条系统错误消息定义好系统错误文本以便include。

* * *

### 大括号 {} 规则



在三种主要的大括号放置规则中，有两种是可以接受的，如下的第一种是最好的：
将大括号放置在关键词下方的同列处：


       if ($condition)       while ($condition)
       {                     {
          ...                   ...
       }                     }

传统的UNIX的括号规则是，首括号与关键词同行，尾括号与关键字同列：


       if ($condition) {     while ($condition) {
          ...                   ...
       }                     }
   
### 理由

* 引起剧烈争论的非原则的问题可通过折衷的办法解决，两种方法任意一种都是可以接受的，然而对于大
多数人来说更喜欢第一种。原因就是心理研究学习范畴的东西了。

对于更喜欢第一种还有着更多的原因。如果您使用的字符编辑器支持括号匹配功能的话（例如vi），最
重要的就是有一个好的样式。为什么？我们说当你有一大块的程序而且想知道这一大块程序是在哪儿结
束的话。你先移到开始的括号，按下按钮编辑器就会找到与之对应的结束括号，例如：


      if ($very_long_condition && $second_very_long_condition)
      {
        ...
      }
      else if (...)
      {
        ...
      }


从一个程序块移动到另一个程序块只需要用光标和你的括号匹配键就可以了，不需要来回的移动到行末去
找匹配的括号。

* * *

### 缩进/制表符/空格 规则

* 使用制表符缩进。
* 使用三到四个空格为每层次缩进。
* 不再使用只要一有需要就缩排的方法。对与最大缩进层数，并没有一个固定的规矩，假如缩进层数大于四或
者五层的时候，你可以考虑着将代码因数分解(factoring out code)。

### 理由

* 许多编程者支持制表符。
* Tabs was invented for a rason
* 当人们使用差异太大的制表符标准的话，会使阅读代码变得很费力。
* 如此多的人愿意限定最大的缩进层数，它通常从未被看作是一件工作。我们相信程序员们会明智的选择嵌套
的深度。

### 例如

```
  function func()
  {
    if (something bad)
    {
       if (another thing bad)
       {
          while (more input)
          {
          }
       }
    }
  }
```

* * *

### 小括号、关键词和函数 规则

* 不要把小括号和关键词紧贴在一起，要用空格隔开它们。
* 不要把小括号和函数名紧贴在一起。
* 除非必要，不要在Return返回语句中使用小括号。

### 理由

* 关键字不是函数。如果小括号紧贴着函数名和关键字，二者很容易被看成是一体的。

### 例如



        if (condition)
        {
        }

        while (condition)
        {
        }

        strcmp($s, $s1);

        return 1;


* * *

### RCS关键词、更改记录和历史记录规则
直接使用RCS关键词的规则必须改变，其中包括使用CVS等类似的支持RCS风格关键词的源代码控制系统：

* 别在文件以内使用 RCS 关键词。
* 别在文件中保存历史修改记录。
* 别在文件中保存作者信息记录。

### 理由

* The reasoning is your source control system already keeps all this  information. There is no reason to clutter up source files with duplicate  information that:
    * makes the files larger
    * makes doing diffs difficult as non source code lines change
    * makes the entry into the file dozens of lines lower in the file which  makes a search or jump necessary for each file
    * is easily available from the source code control system and does not  need embedding in the file
* When files must be sent to other organizations the comments may contain  internal details that should not be exposed to outsiders.

* * *

### 别在对象架构期做实际的工作

别在对象架构期做真实的工作，在架构期初始化变量和/或做任何不会有失误的事情。

当完成对象架构时，为该对象建立一个Open()方法，Open()方法应该以对象实体命名。

### 理由

* 构造不能返回错误 。

### 例如


       class Device
       {
          function Device()    { /* initialize and other stuff */ }
          function Open()  { return FAIL; }
       };
       $dev = new Device;
       if (FAIL == $dev->Open()) exit(1);


* * *
### _If Then Else_ 格式

### 布局

这由程序员决定。不同的花括号样式会产生些微不同的样观。一个通用方式是：


       if (条件1)                 // 注释
       {
       }
       else if (条件2)            // 注释
       {
       }
       else                           // 注释
       {
       }


如果你有用到_else if_ 语句的话，通常最好有一个_else_块以用于处理未处理到的其他情况。

可以的话放一个记录信息注释在_else_处，即使在_else_没有任何的动作。

### 条件格式

总是将恒量放在等号/不等号的左边，例如：

if ( 6 == $errorNum ) ...

一个原因是假如你在等式中漏了一个等号，语法检查器会为你报错。第二个原因是你能立刻找到数值
而不是在你的表达式的末端找到它。需要一点时间来习惯这个格式，但是它确实很有用。

* * *

### switch 格式 

* Falling through a case statement into the next case statement shall be permitted  as long as a comment is included.
* _default_ case总应该存在，它应该不被到达，然而如果到达了就会触发一个错误。
* 如果你要创立一个变量，那就把所有的代码放在块中。

### 例如


       switch (...)
       {
          case 1:
             ...
          // FALL THROUGH

          case 2:
          {
             $v = get_week_number();
             ...
          }
          break;

          default:
       }


* * *
### _continue,break_ 和 _?_ 的使用:

### Continue 和 Break

Continue 和 break 其实是变相的隐蔽的 goto方法。

Continue 和 break 像 goto 一样，它们在代码中是有魔力的，所以要节俭（尽可能少）的使用它们。
使用了这一简单的魔法，由于一些未公开的原因，读者将会被定向到只有上帝才知道的地方去。

Continue有两个主要的问题：

* 它可以绕过测试条件。
* 它可以绕过等/不等表达式。

看看下面的例子，考虑一下问题都在哪儿发生：


    while (TRUE)
    {
       ...
       // A lot of code
       ...
       if (/* some condition */) {
          continue;
       }
       ...
       // A lot of code
       ...
       if ( $i++ > STOP_VALUE) break;
    }


> 注意："A lot of code"是必须的，这是为了让程序员们不能那么容易的找出错误。

通过以上的例子，我们可以得出更进一步的规则：continue 和 break 混合使用是引起灾难的正确方法。

### ?:

麻烦在于人民往往试着在 ? 和 : 之间塞满了许多的代码。以下的是一些清晰的连接规则：

* 把条件放在括号内以使它和其他的代码相分离。
* 如果可能的话，动作可以用简单的函数。
* 把所做的动作，"?"，":"放在不同的行，除非他们可以清楚的放在同一行。

#### 例如


       (condition) ? funct1() : func2();

       or

       (condition)
          ? long statement
          : another long statement;


* * *

### 声明块的定位
* 声明代码块需要对齐。

### 理由Justification

* 清晰。
* 变量初始化的类似代码块应该列表。
* The ??token should be adjacent to the type, not the name.

### 例如


       var       $mDate
       var&      $mrDate
       var&      $mrName
       var       $mName

       $mDate    = 0;
       $mrDate   = NULL;
       $mrName   = 0;
       $mName    = NULL;


* * *
### 每行一个语句

除非这些语句有很密切的联系，否则每行只写一个语句。

* * *
### 短方法
* 方法代码要限制在一页内。

#### 理由

* 这个思想是，每一个方法代表着一个完成单独目的的技术。
* 从长远来说，过多的无效参数是错误的。
* 调用函数比不调用要慢，但是这需要详细考虑做出决定（见premature optimization 未完善的优化）。

* * *

### 记录所有的空语句

总是记录下for或者是while的空块语句，以便清楚的知道该段代码是漏掉了，还是故意不写的。



       while ($dest++ = $src++)
          ;         // VOID


* * *
### 不要采用缺省方法测试非零值

不要采用缺省值测试非零值，也就是使用：

       if (FAIL != f())


比下面的方法好：



       if (f())


即使 FAIL 可以含有 0 值 ，也就是PHP认为false的表示。在某人决定用-1代替0作为失败返回值的时候，
一个显式的测试就可以帮助你了。就算是比较值不会变化也应该使用显式的比较；例如：**if (!($bufsize % strlen($str)))**
应该写成：**if (($bufsize % strlen($str)) == 0)**以表示测试的数值（不是布尔）型。一个经常出
问题的地方就是使用strcmp来测试一个字符等式，结果永远也不会等于缺省值。

非零测试采用基于缺省值的做法，那么其他函数或表达式就会受到以下的限制:

* 只能返回0表示失败，不能为/有其他的值。
* 命名以便让一个真(true)的返回值是绝对显然的，调用函数IsValid()而不是Checkvalid()。

* * *

### 布尔逻辑类型

大部分函数在FALSE的时候返回0，但是发挥非0值就代表TRUE，因而不要用1（TRUE，YES，诸如此类）等式检测一个布尔值，应该用0（FALSE，NO，诸如此类）的不等式来代替：



       if (TRUE == func()) { ...


应该写成：



       if (FALSE != func()) { ...


* * *

### 通常避免嵌入式的赋值

有时候在某些地方我们可以看到嵌入式赋值的语句，那些结构不是一个比较好的少冗余，可读性强的方法。



       while ($a != ($c = getchar()))
       {
          process the character
       }


++和--操作符类似于赋值语句。因此，出于许多的目的，在使用函数的时候会产生副作用。使用嵌入式赋值
提高运行时性能是可能的。无论怎样，程序员在使用嵌入式赋值语句时需要考虑在增长的速度和减少的可维
护性两者间加以权衡。例如：



       a = b + c;
       d = a + r;


不要写成：



       d = (a = b + c) + r;


虽然后者可以节省一个周期。但在长远来看，随着程序的维护费用渐渐增长，程序的编写者对代码渐渐遗忘，
就会减少在成熟期的最优化所得。

* * *

### 重用您和其他人的艰苦工作 

跨工程的重用在没有一个通用结构的情况下几乎是不可能的。对象符合他们现有的服务需求，不同的过程有着
不同的服务需求环境，这使对象重用变得很困难。

开发一个通用结构需要预先花费许多的努力来设计。当努力不成功的时候，无论出于什么原因，有几种办法推
荐使用：

### 请教！给群组发Email求助

这个简单的方法很少被使用。因为有些程序员们觉得如果他向其他人求助，会显得自己水平低，这多傻啊!做新
的有趣的工作，不要一遍又一遍的做别人已经做过的东西。

**如果你需要某些事项的源代码，如果已经有某人做过的话，就向群组发email求助。结果会很惊喜哦！**

在许多大的群组中，个人往往不知道其他人在干什么。你甚至可以发现某人在找一些东西做，并且自愿为你写代
码，如果人们在一起工作，外面就总有一个金矿。

### 告诉！当你在做事的时候，把它告诉所有人

如果你做了什么可重用的东西的话，让其他人知道。别害羞，也不要为了保护自豪感而把你的工作成果藏起来。
一旦养成共享工作成果的习惯，每个人都会获得更多。

### Don't be Afraid of Small Libraries

对于代码重用，一个常见的问题就是人们不从他们做过的代码中做库。一个可重用的类可能正隐蔽在一个程序目
录并且决不会有被分享的激动，因为程序员不会把类分拆出来加入库中。

这样的其中一个原因就是人们不喜欢做一个小库，对小库有一些不正确感觉。把这样的感觉克服掉吧，电脑才不
关心你有多少个库呢。

如果你有一些代码可以重用，而且不能放入一个已经存在的库中，那么就做一个新的库吧。如果人们真的考虑重
用的话，库不会在很长的一段时间里保持那么小的。

If you are afraid of having to update makefiles when libraries are recomposed  or added then don't include libraries in your makefiles, include the idea of  **services**. Base level makefiles define services that are each composed of  a set of libraries. Higher level makefiles specify the services they want. When  the libraries for a service change only the lower level makefiles will have to  change.

### Keep a Repository

Most companies have no idea what code they have. And  most programmers still don't communicate what they have done or ask for what  currently exists. The solution is to keep a repository of what's available.

In an ideal world a programmer could go to a web page, browse or search a  list of packaged libraries, taking what they need. If you can set up such a  system where programmers voluntarily maintain such a system, great. If you have  a librarian in charge of detecting reusability, even better.

Another approach is to automatically generate a repository from the source  code. This is done by using common class, method, library, and subsystem headers  that can double as man pages and repository entries.

* * *

### 评价注释

### 注释应该是讲述一个故事

Consider your comments a story describing  the system. Expect your comments to be extracted by a robot and formed into a  man page. Class comments are one part of the story, method signature comments  are another part of the story, method arguments another part, and method  implementation yet another part. All these parts should weave together and  inform someone else at another point of time just exactly what you did and why.

### Document Decisions

Comments should document decisions. At every point  where you had a choice of what to do place a comment describing which choice you  made and why. Archeologists will find this the most useful information.

### 使用标头说明

利用类似[ccdoc][6]的文档抽取系统。在这一文档的其他部分描述的是怎么利用ccdoc记录一个类和方法。
这些标头说明可以以这样的一个方式来提取并分析和加以组织，它们不像一般的标头一样是无用的。
因此花时间去填上他吧。

### 注释布局

工程的每部分都有特定的注释布局。

### Make Gotchas Explicit

Explicitly comment variables changed out of the  normal control flow or other code likely to break during maintenance. Embedded  keywords are used to point out issues and potential problems. Consider a robot  will parse your comments looking for keywords, stripping them out, and making a  report so people can make a special effort where needed.

#### Gotcha Keywords

* **:TODO: topic**
Means there's more to do here, don't forget.
* **:BUG: [bugid] topic**
means there's a Known bug here, explain it  and optionally give a bug ID.
* **:KLUDGE:**
When you've done something ugly say so and explain how  you would do it differently next time if you had more time.
* **:TRICKY:**
Tells somebody that the following code is very tricky  so don't go changing it without thinking.
* **:WARNING:**
Beware of something.
* **:PHARSER:**
Sometimes you need to work around a pharser problem.  Document it. The problem may go away eventually.
* **:ATTRIBUTE: value**
The general form of an attribute embedded in a  comment. You can make up your own attributes and they'll be extracted.

#### Gotcha Formatting

* Make the gotcha keyword the first symbol in the comment.
* Comments may consist of multiple lines, but the first line should be a  self-containing, meaningful summary.
* The writer's name and the date of the remark should be part of the  comment. This information is in the source repository, but it can take a quite  a while to find out when and by whom it was added. Often gotchas stick around  longer than they should. Embedding date information allows other programmer to  make this decision. Embedding who information lets us know who to ask.

#### Example


       // :TODO: tmh 960810: possible performance problem
       // We should really use a hash table here but for now we'll
       // use a linear search.

       // :KLUDGE: tmh 960810: possible unsafe type cast
       // We need a cast here to recover the derived type. It should
       // probably use a virtual method or template.


### See Also

See [Interface and  Implementation Documentation ][7]for more details on how documentation should be  laid out.

* * *

### Interface and Implementation Documentation 

There are two main audiences  for documentation:

* Class Users
* Class Implementors
With a little forethought we can extract both  types of documentation directly from source code.

### Class Users

Class users need class interface information which when  structured correctly can be extracted directly from a header file. When filling  out the header comment blocks for a class, only include information needed by  programmers who use the class. Don't delve into algorithm implementation details  unless the details are needed by a user of the class. Consider comments in a  header file a man page in waiting.

### Class Implementors

Class implementors require in-depth knowledge of how  a class is implemented. This comment type is found in the source file(s)  implementing a class. Don't worry about interface issues. Header comment blocks  in a source file should cover algorithm issues and other design decisions.  Comment blocks within a method's implementation should explain even more.

* * *
### 目录文档

所有的目录下都需要具有README文档，其中包括：

* 该目录的功能及其包含内容
* 一个对每一文件的在线说明（带有link），每一个说明通常还应该提取文件标头的一些属性名字。
* 包括设置、使用说明
* 指导人民如何连接相关资源：
    * 源文件索引
    * 在线文档
    * 纸文档
    * 设计文档
* 其他对读者有帮助的东西
考虑一下，当每个原有的工程人员走了，在6个月之内来的一个新人，那个孤独受惊吓的探险者通过整个工程的源代码目录树，阅读说明文件，源文件的标头说明等等做为地图，他应该有能力穿越整个工程。

* * *
### Use a Design Notation and Process

Programmers need to have a common  language for talking about coding, designs, and the software process in general.  This is critical to project success.

Any project brings together people of widely varying skills, knowledge, and  experience. Even if everyone on a project is a genius you will still fail  because people will endlessly talk past each other because there is no common  language and processes binding the project together. All you'll get is massive  fights, burnout, and little progress. If you send your group to training they  may not come back seasoned experts but at least your group will all be on the  same page; a team.

There are many popular methodologies out there. The point is to do some  research, pick a method, train your people on it, and use it. Take a look at the  top of this page for links to various methodologies.

You may find the **CRC** (class responsibility cards) approach to teasing  out a design useful. Many others have. It is an informal approach encouraging  team cooperation and focusing on objects doing things rather than objects having  attributes. There's even a whole book on it: Using CRC Cards by Nancy M.  Wilkinson.

* * *
### Using Use Cases 

A _use case_ is a generic description of an entire  transaction involving several objects. A use case can also describe the  behaviour of a set of objects, such as an organization. A use case model thus  presents a collection of use cases and is typically used to specify the behavior  of a whole application system together with one or more external actors that  interact with the system.

An individual use case may have a name (although it is typically not a simple  name). Its meaning is often written as an informal text description of the  external actors and the sequences of events between objects that make up the  transaction. Use cases can include other use cases as part of their behaviour.

### Requirements Capture

Use cases attempt to capture the requirements for  a system in an understandable form. The idea is by running through a set of use  case we can verify that the system is doing what it should be doing.

Have as many use cases as needed to describe what a system needs to  accomplish.

### The Process

* Start by understanding the system you are trying to build.
* Create a set of use cases describing how the system is to be used by all  its different audiences.
* Create a class and object model for the system.
* Run through all the use cases to make sure your model can handle all the  cases. Update your model and create new use cases as necessary.

* * *
### Open/Closed Principle 
The Open/Closed principle states a class must be  open and closed where:

* open means a class has the ability to be extended.
* closed means a class is closed for modificatio
* 布尔逻辑类型
* 通常避免嵌入式的赋值
* 重用您和其他人的艰苦工作
* 使用if (0)来注释外部代码块
* 其他杂项

* * *

### Implementing Accessors

There are three major idioms for creating  accessors.

#### Get/Set

           class X
       {
          function GetAge()        { return $this->mAge; }
          function SetAge($age)    { $mAge= $age; }
          var $mAge;
       }


#### One Method Name

           class X
       {
          function Age()           { return $mAge; }
          function Age($age)       { $mAge= $age; }
          var $mAge;
       }


Similar to Get/Set but cleaner. Use this approach when not using the  _Attributes as Objects_ approach.

#### Attributes as Objects

           class X
       {
          function Age()           { return $mAge; }
          function rAge()          { return &$mAge; }

          function Name()          { return mName; }
          function rName()         { return &$mName; }

          var $mAge;
          var $mName;
       }

       X $x;
       $x->rName()= "test";


The above two attribute examples shows the strength and weakness of the  Attributes as Objects approach.

When using _rAge()_, which is not a real object, the variable is set  directly because _rAge()_ returns a **reference**. The object can do no  checking of the value or do any representation reformatting. For many simple  attributes, however, these are not horrible restrictions.

* * *
### Layering

Layering is the primary technique for reducing complexity in a  system. A system should be divided into layers. Layers should communicate  between adjacent layers using well defined interfaces. When a layer uses a  non-adjacent layer then a layering violation has occurred.

A layering violation simply means we have dependency between layers that is  not controlled by a well defined interface. When one of the layers changes code  could break. We don't want code to break so we want layers to work only with  other adjacent layers.

Sometimes we need to jump layers for performance reasons. This is fine, but  we should know we are doing it and document appropriately.

* * *
### Code Reviews
If you can make a formal code review work then my hat is off to you. Code reviews can be very useful. Unfortunately they often degrade into nit picking sessions and endless arguments about silly things. They also  tend to take a lot of people's time for a questionable payback.

My god he's questioning code reviews, he's not an engineer!

Not really, it's the form of code reviews and how they fit into normally late  chaotic projects is what is being questioned.

First, code reviews are **way too late** to do much of anything useful.  What needs reviewing are requirements and design. This is where you will get  more bang for the buck.

Get all relevant people in a room. Lock them in. Go over the class design and  requirements until the former is good and the latter is being met. Having all  the relevant people in the room makes this process a deep fruitful one as  questions can be immediately answered and issues immediately explored. Usually  only a couple of such meetings are necessary.

If the above process is done well coding will take care of itself. If you  find problems in the code review the best you can usually do is a rewrite after  someone has sunk a ton of time and effort into making the code "work."

You will still want to do a code review, just do it offline. Have a couple  people you trust read the code in question and simply make comments to the  programmer. Then the programmer and reviewers can discuss issues and work them  out. Email and quick pointed discussions work well. This approach meets the  goals and doesn't take the time of 6 people to do it.

* * *
### Create a Source Code Control System Early and Not Often
A common build  system and source code control system should be put in place as early as  possible in a project's lifecycle, preferably before anyone starts coding.  Source code control is the structural glue binding a project together. If  programmers can't easily use each other's products then you'll never be able to  make a good reproducible build and people will piss away a lot of time. It's  also hell converting rogue build environments to a standard system. But it seems  the right of passage for every project to build their own custom environment  that never quite works right.

Some issues to keep in mind:

* Shared source environments like CVS usually work best in largish projects.
* If you use CVS use a reference tree approach. With this approach a master build tree is kept of various builds. Programmers checkout source against the build they are working on. They only checkout what they need because the make system uses the build for anything not found locally. Using the -I and -L flags makes this system easy to setup. Search locally for any files and libraries then search in the reference build. This approach saves on disk space and build time.
* Get a lot of disk space. With disk space as cheap it is there is no reason not to keep plenty of builds around.
* Make simple things simple. It should be dead simple and well documented on how to:
 * check out modules to build
 * how to change files
 * how to add new modules into the system
 * how to delete modules and files
 * how to check in changes
 * what are the available libraries and include files
 * how to get the build environment including all compilers and other tools

 Make a web page or document or whatever. New programmers shouldn't have to go around begging for build secrets from the old timers.

* On checkins log comments should be useful. These comments should be collected every night and sent to interested parties.

### Sources

If you have the money many projects have found [Clear Case ][9]a good system. Perfectly  workable systems have been build on top of GNU make and CVS. CVS is a freeware  build environment built on top of RCS. Its main difference from RCS is that is  supports a shared file model to building software.

* * *
### Create a Bug Tracking System Early and Not Often

The earlier people get  used to using a bug tracking system the better. If you are 3/4 through a project  and then install a bug tracking system it won't be used. You need to install a  bug tracking system early so people will use it.

Programmers generally resist bug tracking, yet when used correctly it can  really help a project:

* Problems aren't dropped on the floor.
* Problems are automatically routed to responsible individuals.
* The lifecycle of a problem is tracked so people can argue back and forth  with good information.
* Managers can make the big schedule and staffing decisions based on the  number of and types of bugs in the system.
* Configuration management has a hope of matching patches back to the  problems they fix.
* QA and technical support have a communication medium with developers.

Not sexy things, just good solid project improvements.

FYI, it's not a good idea to reward people by the number of bugs they fix :-)

Source code control should be linked to the bug tracking system. During the  part of a project where source is frozen before a release only checkins  accompanied by a valid bug ID should be accepted. And when code is changed to  fix a bug the bug ID should be included in the checkin comments.

### Sources

Several projects have found [DDTS ][10]a workable system (I  've not verified this link for this PHP release, DDTS may not work for PHP).  There is also a GNU bug tracking system available. Roll your own is a popular  option but using an existing system seems more cost efficient.

* * *
### Honor Responsibilities

Responsibility for software modules is scoped.  Modules are either the responsibility of a particular person or are common.  Honor this division of responsibility. Don't go changing things that aren't your  responsibility to change. Only mistakes and hard feelings will result.

Face it, if you don't own a piece of code you can't possibly be in a position  to change it. There's too much context. Assumptions seemingly reasonable to you  may be totally wrong. If you need a change simply ask the responsible person to  change it. Or ask them if it is OK to make such-n-such a change. If they say OK  then go ahead, otherwise holster your editor.

Every rule has exceptions. If it's 3 in the morning and you need to make a  change to make a deliverable then you have to do it. If someone is on vacation  and no one has been assigned their module then you have to do it. If you make  changes in other people's code try and use the same style they have adopted.

Programmers need to mark with comments code that is particularly sensitive to  change. If code in one area requires changes to code in an another area then say  so. If changing data formats will cause conflicts with persistent stores or  remote message sending then say so. If you are trying to minimize memory usage  or achieve some other end then say so. Not everyone is as brilliant as you.

The worst sin is to flit through the system changing bits of code to match your coding style. If someone isn't coding to the standards then ask them or ask your manager to ask them to code to the standards. Use common courtesy.


Code with common responsibility should be treated with care. Resist making radical changes as the conflicts will be hard to resolve. Put comments in the file on how the file should be extended so everyone will follow the same rules. Try and use a common structure in all common files so people don't have to guess on where to find things and how to make changes. Checkin changes as soon as possible so conflicts don't build up.

As an aside, module responsibilities must also be assigned for bug tracking purposes.


* * *

### PHP Code Tags


我见过许多种PHP文件的扩展名（.html, .php, .php3, .php4, .phtml, .inc, .class...）


* 所有浏览者可见页面使用.html
* 所有类、函数库文件使用.php

### 理由

* 扩展名描述的是那种数据是用户将会收到的。PHP是解释为HTML的。

* * *
### No Magic Numbers

一个在源代码中使用了的赤裸裸的数字是不可思议的数字，因为包括作者，在三个月内，没人它的含义。例如：


    if      (22 == $foo) { start_thermo_nuclear_war(); }
    else if (19 == $foo) { refund_lotso_money(); }
    else if (16 == $foo) { infinite_loop(); }
    else                { cry_cause_im_lost(); }


在上例中22和19的含义是什么呢？如果一个数字改变了，或者这些数字只是简单的错误，你会怎么想？

使用不可思议的数字是该程序员是业余运动员的重要标志，这样的程序员从来没有在团队环境中工作过，
又或者是为了维持代码而不得不做的，否则他们永远不会做这样的事。

你应该用define()来给你想表示某样东西的数值一个真正的名字，而不是采用赤裸裸的数字，例如：


    define("PRESIDENT_WENT_CRAZY", "22");
    define("WE_GOOFED", "19");
    define("THEY_DIDNT_PAY", "16");

    if      (PRESIDENT_WENT_CRAZY == $foo) { start_thermo_nuclear_war(); }
    else if (WE_GOOFED            == $foo) { refund_lotso_money(); }
    else if (THEY_DIDNT_PAY       == $foo) { infinite_loop(); }
    else                                   { happy_days_i_know_why_im_here(); }


现在不是变得更好了么？

* * *

### OO 

OO has been hyped to the extent you'd figure it would  solve world hunger and usher in a new era of world peace. Not! OO is an  approach, a philosophy, it's not a recipe which blindly followed yields quality.

Robert Martin put OO in perspective:

* OO, when properly employed, does enhance the reusability of software. But  it does so at the cost of complexity and design time. Reusable code is more  complex and takes longer to design and implement. Furthermore, it often takes  two or more tries to create something that is even marginally reusable.
* OO, when properly employed, does enhance the software's resilience to  change. But it does so at the cost of complexity and design time. This trade  off is almost always a win, but it is hard to swallow sometimes.
* OO does not necessarily make anything easier to understand. There is no  magical mapping between the software concepts and every human's map of the  real world. Every person is different. What one person percieves to be a  simple and elegant design, another will perceive as convoluted and opaque.
* If a team has been able, by applying point 1 above, to create a repository  of reusable items, then development times can begin to shrink significantly  due to reuse.
* If a team has been able, by applying point 2 above, to create software  that is resilient to change, then maintenance of that software will be much  simpler and much less error prone.

* * *

### Thin vs. Fat Class Interfaces
How many methods should an object have?  The right answer of course is just the right amount, we'll call this the  Goldilocks level. But what is the Goldilocks level? It doesn't exist. You need  to make the right judgment for your situation, which is really what programmers  are for :-)

The two extremes are **thin** classes versus **thick** classes. Thin  classes are minimalist classes. Thin classes have as few methods as possible.  The expectation is users will derive their own class from the thin class adding  any needed methods.

While thin classes may seem "clean" they really aren't. You can't do much  with a thin class. Its main purpose is setting up a type. Since thin classes  have so little functionality many programmers in a project will create derived  classes with everyone adding basically the same methods. This leads to code  duplication and maintenance problems which is part of the reason we use objects  in the first place. The obvious solution is to push methods up to the base  class. Push enough methods up to the base class and you get **thick** classes.

Thick classes have a lot of methods. If you can think of it a thick class  will have it. Why is this a problem? It may not be. If the methods are directly  related to the class then there's no real problem with the class containing  them. The problem is people get lazy and start adding methods to a class that  are related to the class in some willow wispy way, but would be better factored  out into another class. Judgment comes into play again.

Thick classes have other problems. As classes get larger they may become  harder to understand. They also become harder to debug as interactions become  less predictable. And when a method is changed that you don't use or care about  your code will still have to be retested, and rereleased.

* * *

1. 2000-11-16. Release

* * *

© Copyright 1995-2000. Todd Hoff and Fredrik Kristiansen. All rights  reserved.

[1]: http://oss.org.cn/php_coding_standard.html
[2]: mailto:russlndr%40online.no
[3]: http://utvikler.start.no/code/php_coding_standard.html#changes
[6]: http://www.joelinoff.com/ccdoc/index.html
[7]: http://utvikler.start.no/code/php_coding_standard.html#two
[8]: http://utvikler.start.no/code/php_coding_standard.html#liskov
[9]: http://www.pureatria.com/
[10]: http://www.pureatria.com/products/pureddts/
  