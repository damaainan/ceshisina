# PHP超实用系列·自动捕获Fatal Error

 时间 2017-05-06 17:00:00  

原文[http://www.talkpoem.com/post/design-patterns/error_hadler][2]


## 重要使命

经过十几天的忙碌，张小五手上的项目终于如期上线，虽然很累，但内心无比的充实与喜悦。喝了杯热咖啡，小五在椅子上慵懒地躺着，享受着这份静谧的时光。

"嗨，小五，这几天累坏了吧？"

"哈哈，是有点累，不过还好。"

"周末好好休息下吧，我先跟你讨论个事儿啊。"

"好的，Z哥。"

"咱们线上运行的代码，出于各种各样的情况，可能会有好多Fatal Error、Exception。有没有办法，在出现Fatal Error、Exception的时候，咱们能自动捕获，并写到Log文件里？"

"嗯...这个嘛，出现Fatal Error的时候，脚本就终止了，不好捕获啊。"

"对，是不好捕获。但是对于出现的Fatal Error、Exception我们不知道的话，不能提前发现问题，就像身边有个隐形的刺客一样，让人内心特别虚啊..."

"这样啊，Z哥，那我这几天试一下吧！"

"好的，小五，这个挺重要的，相信你！"

"哈哈，Z哥你还是不要抱太大希望，我努力试一下就是了。"

## 从Google到SO

对于码农来说，从Google到Stackoverflow是解决问题的通途，当然张小五也不例外。

哈！不搜不知道，一搜吓一跳，PHP还真有捕获Error和Exception的函数。

    //设置一个用户的函数来处理脚本中出现的错误。
    set_error_handler($callback)
    //设置一个用户的函数来处理脚本中出现的异常。
    set_exception_handler($callback)

张小五不自觉的笑了笑：“哈哈，不愧是世界上最好的语言！”

说干就干，看看这两个函数的威力怎样,不一会,小五就写出了测试代码。

```php
<?php
//设置异常捕获函数
set_exception_handler("my_exception");
function my_exception($exception){
    echo 'Exception Catched:'.$exception->getMessage();
}
//抛出异常
throw new Exception("I am Exception");
```

![][4]

Yes，抛出的一个Exception真的被捕获了！

"接下来再测下set_error_handler()，你可不能让我失望啊！"小五心想。

```php
<?php
set_error_handler("error_handler");
function error_handler($errno,$errstr,$errfile,$errline){
    $str=<<<EOF
         "errno":$errno
         "errstr":$errstr
         "errfile":$errfile
         "errline":$errline
EOF;
//获取到错误可以自己处理，比如记Log、报警等等
    echo $str;
}
echo $test;//$test未定义，会报一个notice级别的错误
```

![][5]

不错，Notice级别的错误也捕获到了！

接下来再测一下Fatal Error，如果Fatal Error也能捕获到，这个需求就实现了!

抑制住激动的心情，小五很快写完了测试代码。

```php
<?php
set_error_handler("error_handler");
function error_handler($errno,$errstr,$errfile,$errline){
    $str=<<<EOF
         "errno":$errno
         "errstr":$errstr
         "errfile":$errfile
         "errline":$errline
EOF;
//获取到错误可以自己处理，比如记Log、报警等等
    echo $str;
}
//调用一个不存在的函数，会出现Fatal Error
test();
```

小五屏住呼吸，等待着奇迹的出现。"咣当"，手起指落，几行报错跃然屏上...

![][6]

神马？Fatal Error竟然没捕获到？怎么可能？

正在小五陷入沉思的时候，不经意间，小五瞥见了函数的说明：

以下级别的错误不能由用户定义的函数来处理： E_ERROR、 E_PARSE、 E_CORE_ERROR、 E_CORE_WARNING、 E_COMPILE_ERROR、 E_COMPILE_WARNING，和在 调用 set_error_handler() 函数所在文件中产生的大多数 E_STRICT。

也就是：set_error_handler($callback)只能捕获系统产生的一些Warning、Notice级别的Error。

呜呼悲催，好不容易找到了解决办法，没想到这函数竟然还是个半吊子，很多级别的错误捕获不到...:sob:

## 众里寻他千百度

王小五从不是轻言放弃的人，他又继续搜索，寻找着解决办法...

"嗯？哈哈，SO上还真有人遇到这问题!"

小五专注地看着答案，边看边敲了起来：

要实现这个需求，需要用到两个函数: register_shutdown_function() 和 error_get_last() 。 

#### register_shutdown_function()

    register_shutdown_function($callback)

register_shutdown_function()，就把你要注册进去的function放进【假装是队列吧】，等到脚本正常退出或显式调用exit()时，再把注册进去的function拉出来执行.

#### register_shutdown_function()调用的3种情况：

* 脚本正常退出时；
* 在脚本运行(run-time not parse-time)出错退出时；
* 用户调用exit方法退出时。

#### error_get_last()

    error_get_last();//函数获取最后发生的错误。

该函数以数组的形式返回最后发生的错误。

返回的数组包含 4 个键和值：

`[type]` - 错误类型

`[message]` - 错误消息

`[file]` - 发生错误所在的文件

`[line]` - 发生错误所在的行

### 强烈注意

在parse-time出错的时候，是不会调用register_shutdown_function()函数的。只有在run-time出错的时候，才会调用register_shutdown_function()。

为了更好的理解，下面我们举例说明：

#### NO.1

#### error_handler.php

```php
<?php
register_shutdown_function("error_handler");
function error_handler(){
    echo "Yeah,it's worked!";
}
function test(){}
function test(){}
```

执行结果如下：

![][7]

#### 原因分析

在执行error_handler.php的时候，由于重复定义了两个函数test(),在php的parse-time就出错了（不是run-time），所以不能回调register_shutdown_function()中注册的函数。

#### NO.2

#### error_handler.php

```php
<?php
register_shutdown_function("error_handler");
function error_handler(){
    echo "Yeah,it's worked!";
}
if(true){
   function test(){}
}
function test(){}
```

执行结果如下：

![][8]

#### 原因分析

我们看到，上面回调了register_shutdown_function()中注册的函数。

因为我们加了一个if()判断，if()里面的test()方法，相当于一个闭包，与外面的test()名称不冲突。

也就是，上面的代码在parse-time没有出错，而是在run-time的时候出错了，所以我们能够获取到fatal error。

#### NO.3

#### error_handler.php

```php
<?php
register_shutdown_function("error_handler");
function error_handler(){
    echo "Yeah,it's worked!";
}
```

#### test.php

```php
<?php
include './error_handler.php';
function test(){}
function test(){}
```

执行 test.php的结果如下

![][9]

#### 原因分析

当我们在运行test.php的时候,因为redeclare了两个test()方法，所以php的语法解析器在parse-time的时候就出错了。 所以不能回调register_shutdown_function()中的方法，不能catch住这个fatal error。

#### NO.4

#### error_handler.php

```php
<?php
register_shutdown_function("error_handler");
function error_handler(){
    echo "Yeah,it's worked!";
}
```

#### test.php

```php
<?php
function test(){}
function test(){}
```

#### include_all.php

```php
<?php
require './error_handler.php';
require './test.php';
```

执行 include_all.php的结果如下

![][10]

#### 结果分析

上面我们捕获了fatal_error。

因为在运行include_all.php的时候，include_all.php本身语法并没有出错，也就是在parse-time的时候并没有出错，而是include的文件出错了，也就是在run-time的时候出错了，这个时候是能回调register_shutdown_function()中的函数的。

#### 强烈建议：如果我们要使用register_shutdown_function进行错误捕捉，使用NO.4，最后一种方法，可以确保错误都能捕捉到。

## 蓦然回首解需求

"哇塞，原来可以这样啊!"

王小五按答案中举的例子认真的敲完代码，瞬间明白了解决的办法。

真可谓"众里寻他千百度，蓦然回首，那人却在灯火阑珊处。"小二不自觉的感叹道！

"好了，我自己就写一个error_handler脚本吧，确保每次都能获取到想要的Fatal Error。"

```php
<?php
register_shutdown_function( "fatal_handler" );
set_error_handler("error_handler");

define('E_FATAL',  E_ERROR | E_USER_ERROR |  E_CORE_ERROR | 
        E_COMPILE_ERROR | E_RECOVERABLE_ERROR| E_PARSE );

//获取fatal error
function fatal_handler() {
    $error = error_get_last();
    if($error && ($error["type"]===($error["type"] & E_FATAL))) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        error_handler($errno,$errstr,$errfile,$errline);
  }
}
//获取所有的error
function error_handler($errno,$errstr,$errfile,$errline){
    $str=<<<EOF
         "errno":$errno
         "errstr":$errstr
         "errfile":$errfile
         "errline":$errline
EOF;
//获取到错误可以自己处理，比如记Log、报警等等
    echo $str;
}
```
有了这个脚本，我再按SO上说的第四种方法去执行，那这个需求就实现了！

## 不负众望

王小五兴冲冲的找到Z哥，详细的说明了自己的研究成果。

第二天，小五按照公司现有的框架规则，结合上面的解决办法，不一会就实现了需求。

"不错啊，小五，我就说你可以吧！" Z哥高兴的说到。

"哈哈，Z哥，这下所有的错误都在掌握之中了!"


[2]: http://www.talkpoem.com/post/design-patterns/error_hadler
[4]: http://img1.tuicool.com/MVBBRnA.png
[5]: http://img2.tuicool.com/e6bqaej.png
[6]: http://img2.tuicool.com/eqYjInB.png
[7]: http://img1.tuicool.com/BvymiaQ.png
[8]: http://img1.tuicool.com/ZbYVNza.png
[9]: http://img1.tuicool.com/bIni2qn.png
[10]: http://img1.tuicool.com/mq6Nn2Z.png