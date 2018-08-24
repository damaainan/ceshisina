## 使用PHPCS+GIT钩子保障团队开发中代码风格一致性实践

来源：[https://zhuanlan.zhihu.com/p/41813339](https://zhuanlan.zhihu.com/p/41813339)

时间：发布于 2018-08-11


## 一、背景

笔者在6月份加入新团队，新团队这边刚组建起来，基础一些东西还处于待完善状态，比如笔者组内同学约定使用PSR-2的编码风格规范，但是并不是所有人都严格按照PSR-2来提交代码。

最大的原因就是口头的约束力极为有限，而团队中大家使用的编辑器不统一，有使用phpstorm，也有使用VS Code更有vim，而各种编辑器都有自己的格式化规则，因此代码风格统一是个问题；

 具体一点来说，当张三使用VS Code提交了一个代码文件，李四pull代码之后使用phpstorm进行格式化后再提交，代码风格发生变化提交到服务器，张三再pull代码，使用VS Code格式化，代码又一次发生变化；这样反反复复的改变，开发同学会觉得麻烦，代码审计同学也同样麻烦； 

 
在笔者上家公司的技术团队，会由架构组来处理类似的问题，于是这里笔者把上一个团队实现的方式照搬过来，同样在git的钩子上做文章，如果有人的代码不符合psr-2代码风格规范，通过git钩子将不其commit，并且给出具体行号和具体的原因，更方便的是提供一个快速格式化的命令。

## 二、实现概要

* 安装php-cs
* 配置php-cs
* 集成到编辑器
* git触发检测

## 三、安装PHP-CS

php-cs可以用来检测代码是否符合PSR-2规范，同时支持对不符合规范的代码自动格式化，让其转成PSR-2的编码风格。

## 3.1 安装composer

php-cs依赖于composer，所以笔者需要先安装composer，安装的方法有很多种，这里提供mac操作系统下两种安装方法

brew安装composer命令为：

```

brew install composer

```

手动安装composer命令为：

```

wget https://getcomposer.org/download/1.7.1/composer.phar && chmod 777 composer.phar  && mv composer.phar  /usr/local/bin/composer

```

## 3.2 安装PHP-CS

安装好composer之后，可以用composer快速安装php-cs，安装命令如下

```

composer global require "squizlabs/php_codesniffer=*"

```

当命令执行完成之后，会在笔者当前用户的主目录下创建一个  **.composer**  目录，在目录中包含了笔者需要的php-cs，此时笔者可以执行下方命令来验证是否安装成功

```

~/.composer/vendor/bin/phpcs  --help

```

当命令执行后，如果能看到下方的一些信息，那么就代表安装成功

```

-     Check STDIN instead of local files and directories
 -n    Do not print warnings (shortcut for --warning-severity=0)
 -w    Print both warnings and errors (this is the default)
 -l    Local directory only, no recursion
 -s    Show sniff codes in all reports
 -a    Run interactively
 -e    Explain a standard by showing the sniffs it includes
 -p    Show progress of the run
 -q    Quiet mode; disables progress and verbose output
 -m    Stop error messages from being recorded
       (saves a lot of memory, but stops many reports from being used)
 -v    Print processed files
 -vv   Print ruleset and token output
 -vvv  Print sniff processing information
 -i    Show a list of installed coding standards
 -d    Set the [key] php.ini value to [value] or [true] if value is omitted

```

## 3.3 全局使用

前面笔者使用验证的命令的路径太长，后续如果要使用是极为不方便的，所以笔者需要将这写路径加入到全局中，加入的命令如下

```

ln -s ~/.composer/vendor/bin/phpcs /usr/local/bin/phpcs
ln -s ~/.composer/vendor/bin/phpcbf /usr/local/bin/phpcbf

```

当执行完成之后，可以使用短命令来验证是否加入全局成功，可以用下方的命令

```

phpcs --help

```

执行成功之后，返回结果应该和上方完整路径返回的一致。

## 3.4 设置默认标准

phpcs默认的编码格式并不是php-cs，所以当不指定标准的时候，检测的结果并不准确，但每次都手动指定也挺麻烦，所以笔者可以设置一个默认标准，命令如下：

```

phpcs --config-set default_standard PSR2
phpcbf --config-set default_standard PSR2

```

## 3.5 PHPCS检测

现在笔者可以用phpcs来真实的试验了，笔者先准备一个PHP文件，文件里面的内容如下代码示例，可以看出这份代码并不符合PSR-2的风格规范

```php

<?php

function test_test(){
    echo 'daxia';
}

test();

```

## 通过PHP-CS检测编码风格,命令如下

```

phpcs /Users/tangqingsong/mycode/test.php

```

命令执行完成之后，可以看到如下代码提示，在提示中笔者能看到具体哪一行，提示级别，以及具体的提示原因

```

FILE: /Users/song/mycode/test.php
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
FOUND 2 ERRORS AND 1 WARNING AFFECTING 3 LINES
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 1 | WARNING | [ ] A file should declare new symbols (classes, functions, constants, etc.) and cause no other side effects, or it should execute logic with side effects, but should not do both. The first symbol is
   |         |     defined on line 3 and the first side effect is on line 8.
 3 | ERROR   | [x] Opening brace should be on a new line
 8 | ERROR   | [x] Expected 1 newline at end of file; 0 found
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
PHPCBF CAN FIX THE 2 MARKED SNIFF VIOLATIONS AUTOMATICALLY
-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

Time: 79ms; Memory: 4Mb

```

## 3.6 PHPCS检测

## 自动格式化编码风格命令

```

phpcbf /Users/tangqingsong/mycode/test.php

```

命令执行完成之后，可以看到如下返回提示，处理了哪一些文件，以及类型

```

PHPCBF RESULT SUMMARY
----------------------------------------------------------------------
FILE                                                  FIXED  REMAINING
----------------------------------------------------------------------
/Users/song/mycode/test.php                           2      1
----------------------------------------------------------------------
A TOTAL OF 2 ERRORS WERE FIXED IN 1 FILE
----------------------------------------------------------------------

Time: 68ms; Memory: 4Mb

```

## 再次使用PHP-CS检测

```

phpcs /Users/tangqingsong/mycode/test.php

```

执行完成之后，通过命令再次查看结果

```

FILE: /Users/song/mycode/test.php
----------------------------------------------------------------------------------------------------------------------------------------------
FOUND 0 ERRORS AND 1 WARNING AFFECTING 1 LINE
----------------------------------------------------------------------------------------------------------------------------------------------
 1 | WARNING | A file should declare new symbols (classes, functions, constants, etc.) and cause no other side effects, or it should execute
   |         | logic with side effects, but should not do both. The first symbol is defined on line 3 and the first side effect is on line 9.
----------------------------------------------------------------------------------------------------------------------------------------------

Time: 71ms; Memory: 4Mb

```

能看到最开始检测有三次不合格，但现在只剩下一处了；这里说一下为什么phpcbf没有帮完全处理呢，因为phpcbf只能处理代码风格等方式，而不能帮你处理里面的命名与代码实现规则，所以有少部分还需要人为去更正，但并不会太多。

## 四、编辑器编辑与配置

很少开发者只使用终端就开发代码，通常都会用到编辑器，因此笔者也需要把phpcs和编辑器进行结合

## 4.1 让编辑器使用PSR-2标准

* 设置->code style -> PHP 中选择风格为 psr1/2

![][1]

2. 设置->languages->php->code sniffer 中设置phpcs的路径

![][2]

3. 设置->Editor->Inspections展开点击右侧的PHP，勾选下面的两个PHP，选择使用PSR2

下面还有一处，也要选中

![][3]

现在笔者使用phpstorm的格式化，将会自动格式化成psr-2的风格。

## 4.2 集成phpcs

经过上面的操作，phpstorm代码格式化的规则基本与phpcs的规则基本一致了，但也有一小部分不一致，所以后面还要用到phpcs和phpcbf。

笔者如果每次都在终端去执行phpcs风格检测花费时间可不少，为了提高工作效率，可以在phpstorm集成phpcs检测规范的功能，设置路径:Tools->External Tools->添加-> (/usr/local/bin/phpcs )  ($FileDir$/$FileName$)

![][4]

## 4.3 集成phpcbf

如果每次都在终端去执行phpcbf格式化，还是会有一些麻烦，所以笔者也可以在phpstorm集成phpcbf自动格式化功能，设置路径:Tools->External Tools->添加-> (/usr/local/bin/phpcbf )  ($FileDir$/$FileName$)

![][5]

## 五、GIT配置篇

当前面一切准备就绪，笔者就可以在git钩子里面增加强制的策略了，git钩子脚本存放于项目下  **.git/hooks/**  文件夹下，按照下面的步骤笔者来添加一个commit事件。

## 5.1 新增钩子文件

在你的项目根目录下，使用vim命令或其他方式，新增一个文件   **./.git/hooks/pre-commit** ,然后把下面的脚本放进去，之后再保存。

```sh

#!/bin/sh
PHPCS_BIN=/usr/local/bin/phpcs
PHPCS_CODING_STANDARD=PSR2
PHPCS_FILE_PATTERN="\.(php)$"

FILES=$(git diff HEAD^..HEAD --stat)

if [ "$FILES" == "" ]; then
 exit 0
fi

for FILE in $FILES
do
 echo "$FILE" | egrep -q "$PHPCS_FILE_PATTERN"
 RETVAL=$?
 if [ "$RETVAL" -eq "0" ]
 then

     PHPCS_OUTPUT=$($PHPCS_BIN --standard=$PHPCS_CODING_STANDARD $FILE)
     PHPCS_RETVAL=$?

     if [ $PHPCS_RETVAL -ne 0 ];
     then
         echo $PHPCS_OUTPUT
         exit 1
     fi
 fi
done
exit 0

```

需要注意的是让这个文件有可执行权限，最直接的办法就是设置为777，参考命令如下：

```

chmod 777 .git/hooks/pre-commit

```

## 5.2 本地钩子

现在笔者故意让php代码风格不一致，然后使用git commit来提交，看看git是否会阻止提交，以下面这份代码为例

```php

<?php

function test_test(){
    echo 'daxia';
}

test();

```

可以很明显的看出来，这份代码没有按照驼峰命名法，大括号也没用换行的两处问题；把它保存在根目录名为test.php文件，然后执行git commit命令,如下

```

git add test.php && git commit . -m 'test'

```

命令执行后，git返回了如下信息，便终止了

```

FILE: /Users/song/mycode/work/xiaoyu/test.php
----------------------------------------------------------------------------------------------------------------------------------------------
FOUND 2 ERRORS AND 1 WARNING AFFECTING 3 LINES
----------------------------------------------------------------------------------------------------------------------------------------------
 1 | WARNING | [ ] A file should declare new symbols (classes, functions, constants, etc.) and cause no other side effects, or it should
   |         |     execute logic with side effects, but should not do both. The first symbol is defined on line 3 and the first side effect
   |         |     is on line 8.
 3 | ERROR   | [x] Opening brace should be on a new line
 8 | ERROR   | [x] Expected 1 newline at end of file; 0 found
----------------------------------------------------------------------------------------------------------------------------------------------
PHPCBF CAN FIX THE 2 MARKED SNIFF VIOLATIONS AUTOMATICALLY
----------------------------------------------------------------------------------------------------------------------------------------------

Time: 63ms; Memory: 4Mb

```

验证一下git是否commit成功，可以执行下面的命令：

```

git status

```

返回结果如下

```

位于分支 develop
您的分支与上游分支 'origin/develop' 一致。

要提交的变更：
  （使用 "git reset HEAD <文件>..." 以取消暂存）

    新文件：   test.php

```

说明笔者前面的命令只成功执行了   **git add .**   而后面commit则成功阻挡了。

## 5.3 服务端钩子

前面一个步骤笔者已经成功的在本地的commit钩子中阻挡了触发，但是任然有可能有伙伴会绕过，或者新项目没有部署等，导致可以最终提交上来的代码还是存在不符合psr-2风格，所以这个时候笔者就需要在服务端的push事件做一些处理。

这个时候笔者需要在服务器的钩子事件中新增一个，pre-receive 文件。

在服务端去配置的时候遇到了几个坑，后来笔者放弃了，有兴趣的可以留言或私信。

作者：汤青松

微信：songboy8888

[1]: ../img/v2-53743036cf7de6b4a2bd9b851c1923bc_r.jpg
[2]: ../img/v2-881eaa22ca09153daf8234f3f379bcb9_r.jpg
[3]: ../img/v2-bc935b1904b7cb8ec0f308a614f1df16_r.jpg
[4]: ../img/v2-76483c70319b9dc2bc05e4dfb516221e_r.jpg
[5]: ../img/v2-a03d3c127b3ba6624aeb34a96dff58bc_r.jpg