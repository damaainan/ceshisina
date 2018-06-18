## 推荐一款 phpcs 插件规范 Laravel 代码 (规范从本地写代码到版本控制)

来源：[https://juejin.im/post/5b18fdeb6fb9a01e573c3cb3](https://juejin.im/post/5b18fdeb6fb9a01e573c3cb3)

时间 2018-06-07 17:42:25

 
我相信每个公司都有一套完备的代码规范标准，但标准是标准，如何能有效的让所有人遵守，那就要工具的辅助和实时提醒了。
 
如前端 vue 的大家基本都会使用`eslint`来约束我们的代码，一旦多一个空格都会提示你有问题，当`npm run dev`或者`npm run watch`就会提示你哪哪哪不符合规范。
 
在 Laravel 开发中，照样也有类似的工具，这也是本文的所要推荐的： **`phpcs`** 
 
在开始使用`phpcs`之前，我们简单来说说 Laravel 的代码规范标准
 
## Laravel 代码规范
 
Laravel follows the PSR-2 coding standard and the PSR-4 autoloading standard.
 
来自 Laravel 的说明：laravel.com/
 
几个代码规范的含义
 
 
* PSR-0 自动加载规范 (已弃用) 
* PSR-1 基础编码规范 
* PSR-2 编码风格规范 
* PSR-3 日志接口规范 
* PSR-4 自动加载规范 
* PSR-5 PHPDoc 标准 (未通过) 
* PSR-6 缓存接口规范 
* PSR-7 HTTP 消息接口规范 
* PSR-8 Huggable 接口 (未通过) 
* PSR-9 项目安全问题公示 (未通过) 
* PSR-10 项目安全上报方法 (未通过) 
* PSR-11 容器接口 
* PSR-12 编码规范扩充 
* PSR-13 超媒体链接 
* PSR-14 事件分发器 (未通过) 
* PSR-15 HTTP 请求处理器 
* PSR-16 缓存接口 
 
 
其实现在很多网站已经挂出 PSR-2 编码规范的说明了，推荐看下面这个：
 
[laravel-china.org/docs/psr/ps…][21]
 
 ![][0]
 
但我在实际使用时，除了能够按照上面说的规范来，还有一块重要的内容他们没提。
 
### 文件和类注释
 
 ![][1]
 
主要包含以上内容块：文件说明、PHP 版本号、还有就是按顺序的这五要素：(category, package, author, license, link)，而且这五要素排版要对齐哦，一般人我不告诉哦~~~
 
### 方法注释
 
 ![][2]
 
主要包含：方法说明、空一行、参数块 (类型、参数名、含义 —— 这个需要对齐)、空一行、最后`return`类型。
 
## 安装 phpcs
 
使用 phpcs 之前，还是需要先知道这个东西是什么吧？
 
PHP_CodeSniffer tokenizes PHP, JavaScript and CSS files and detects violations of a defined set of coding standards.
 
摘自： [github.com/squizlabs/P…][22]
 
主要包含两个工具：phpcs 和 phpcbf (这个之后再说)。phpcs 主要对`PHP`、`JavaScript`、`CSS`文件定义了一系列代码规范标准，如我们会使用的，也是 Laravel 使用的`PHP``PSR-2`标准，能够检测出不符合我们定义的代码规范的代码，并发出警告和错误，当然我们也可以设置报错的级别。
 
对于 phpcs 的使用，主要有两个途径：
 
 
* 在本地开发过程中，实时对我们的代码进行检测，让代码提交版本库时，就已经符合规范标准了； 
* 在服务器对提交的代码进行检测，如果不符合标准的，则不入代码库，打回要求修改。 
 
 
下面我们开始说说根据不同方法，如何安装 phpcs 工具的。
 
### composer
 
``` 
composer global require "squizlabs/php_codesniffer"
```
 
写 Laravel 代码的同学，对使用 composer 应该很熟悉了，这种方法比较推崇。但主要区分为是「全局安装」还是按「项目安装」。
 
这里我本人推荐采用「全局安装」，可以在各个 IDE 上直接填入全局安装的 phpcs 可执行路径。但如果你的版本库是使用「git」的话，那我推荐使用「项目安装」，下文就知道原因了。
  注：  我使用这种方式「全局安装」后，发现每回都关联不了「VSCode」，这个原因待查。
 
 ![][3]
 
### pear
 
安装 pear
 
``` 
curl -O https://pear.php.net/go-pear.phar
php -d detect_unicode=0 go-pear.phar
```
 
开始安装配置，
 
先选择 1 (change the Installation Base)；
 
输入：`/usr/local/pear`，
 
再选择 4 (change the Binaries directory)，
 
输入：`/usr/local/bin`![][4]
 
开始安装`PHP_CodeSniffer` 

``` 
pear install PHP_CodeSniffer
```
 
在 MacOS 系统下：
 
 ![][5]
 
在 Centos Linux 系统下安装效果：
 
 ![][6]
 
此方法比较有效果，而且也符合在多系统上尝试，比如本人同时在「Mac」和 「Linux」下都可以正常安装和使用。
 
*注：*我没在「Windows」环境下尝试，尚未知道效果。
 
### brew
 
``` 
brew install php-code-sniffer
```
 
 ![][7]
 
这种方法显然在「Mac」系统下有效了！
 
当然根据官网的文档，还有其他方法，欢迎大家去尝试：
 
具体可参考： [github.com/squizlabs/P…][22] 中的「Installation」部分。
 
## 使用 phpcs
 
无论是本地还是服务器，只要我们安装好了，自然就可以开始使用了。最直观也是最简单的方法莫过于用命令行的方式了，如：
 
``` 
phpcs php_path

// or

phpcs php_dir
```
 
但想到我们是用 IDE 写代码的，而且是希望实时看到效果的，所以下面尝试在几个 IDE 下看看如何使用。
 
### 安装 VSCode 插件
 
在插件界面，搜索：phpcs，安装即可。
 
 ![][8]
 
参考： [marketplace.visualstudio.com/items?itemN…][24]
 
配置插件
 
由于项目使用的是系统的 phpcs，所以需要在`user setting`中配置可执行路径和自己自定义的编写风格
 
 ![][9]
 
这时候我们去看看我们的代码界面，是不是有了`phpcs`的提示了：
 
![2018-05-10 09.29.08](http://ow20g4tgj.bkt.clouddn.com/2018-05-10-2018-05-10 09.29.08.gif)
 
### 安装 PhpStorm 插件
 
 ![][10]
 
 ![][11]
 
 ![][12]
 
 ![][13]
 
 ![][14]
 
直接看图，不需要做过多的说明了。
 
基本到此，phpcs 的插件就可以使用了。
 
## 版本检测规范
 
我们希望在团队项目代码提交版本库之前「pre-commit」就能检测 出不符合「PSR-2」 标准的代码文件。无论是 svn 或者 git，都能在「pre-commit」获取提交版本库的代码文件，然后再利用「phpcs」去检测每个文件是否符合规范。
 
### svn
 
由于每个 svn 在服务端都有对应 hooks 文件夹，可以在「pre-commit」时，验证代码的规范，直接上文件，比较好理解：
 
```sh
#!/bin/bash

LOG="/tmp/svn.log"
touch ${LOG}

REPOS="$1"
TXN="$2"
echo "REPOS: $REPOS" > ${LOG}
echo "TXN: $TXN" >> ${LOG}

SVNLOOK="/usr/bin/svnlook"
PHPCS="/usr/bin/phpcs"

# php file extension
PHP_EXT="php"

MSG_MIN_CHAR_NUM=3

MAX_PNG_SIZE=2048

PROHIBITED_FILES=(
)

TMP_DIR="/tmp/svn"
if [[ -d ${TMP_DIR} ]]; then
    rm -r ${TMP_DIR}
fi
mkdir -p ${TMP_DIR}

function check_php_syntax {
local php_file=$1
echo `${PHPCS} ${php_file} 2>&1`
}

function create_file {
local file_name=$1
# Create tmp file and copy content
tmp_file="${TMP_DIR}/${file_name}"
mkdir -p "$(dirname "${tmp_file}")" && touch "${tmp_file}"
${SVNLOOK} cat -t "${TXN}" "${REPOS}" "${file_name}" > ${tmp_file}
}

changed_info_str=`${SVNLOOK} changed -t "${TXN}" "${REPOS}"`
IFS=$'\n' read -rd '' -a changed_infos <<<"${changed_info_str}"

php_error_msg=""
for changed_info in "${changed_infos[@]}"; do
    # Prevent commiting file that contains space in its filename
    echo ${changed_info} >> ${LOG}
    operation=`echo ${changed_info} | awk '{print $1}'`
    if [[ ${operation} = "A" ]] && [[ `echo ${changed_info} | awk '{print NF}'` -gt 2 ]]; then
        echo "Please do not commit file that contains space in its filename!" 1>&2
        exit 1
    fi
    file_name=`echo ${changed_info} | awk '{print $2}'`
    echo "operation: ${operation}, file: ${file_name}, ext: ${ext}" >> ${LOG}

    # Check prohibit-commit files
    for prohibited_file in ${PROHIBITED_FILES[@]}; do
        if [[ ${file_name} = ${prohibited_file} ]]; then
            echo "${file_name} is not allowed to be changed!" 1>&2
            exit 1
        fi
    done

    ext=`echo ${file_name} | awk -F"." '{print $NF}'`

    if [[ ${operation} = "U" ]] || [[ ${operation} = "A" ]]; then
        tmp_file="${TMP_DIR}/${file_name}"

        # Check lua syntax
        if [[ ${ext} = ${PHP_EXT} ]]; then
            echo "Check syntax of ${tmp_file}" >> ${LOG}
            create_file ${file_name}
            error_msg=`check_php_syntax ${tmp_file}`
            if [[ `echo ${error_msg} | sed 's/\n//g'` != "" ]]; then
                php_error_msg="${php_error_msg}\n${error_msg}"
            fi
        fi
    fi
done

rm -r ${TMP_DIR}

if [[ ${php_error_msg} != "" ]]; then
    echo "php error: ${php_error_msg}" >> ${LOG}
        echo "Please fix the error in your php program:${php_error_msg}" 1>&2

    exit 1
fi

exit 0
```
 
 ![][15]
 
 ![][16]
 
这就是我们想要看到的效果了，无论 IDE 的实时提示，还是提交代码时的检测反馈，都会告诉我们哪里格式不符合规范了。
 
### git
 
这里主要参考 WickedReports/phpcs-pre-commit-hook [github.com/WickedRepor…][25] 的写法：
 
```sh
#!/bin/sh

PROJECT=`php -r "echo dirname(dirname(dirname(realpath('$0'))));"`
STAGED_FILES_CMD=`git diff --cached --name-only --diff-filter=ACMR HEAD | grep \\\\.php`

# Determine if a file list is passed
if [ "$#" -eq 1 ]
then
    oIFS=$IFS
    IFS='
    '
    SFILES="$1"
    IFS=$oIFS
fi
SFILES=${SFILES:-$STAGED_FILES_CMD}

echo "Checking PHP Lint..."
for FILE in $SFILES
do
    php -l -d display_errors=0 $PROJECT/$FILE
    if [ $? != 0 ]
    then
        echo "Fix the error before commit."
        exit 1
    fi
    FILES="$FILES $PROJECT/$FILE"
done

if [ "$FILES" != "" ]
then
    echo "Running Code Sniffer. Code standard PSR2."
    /usr/local/bin/phpcs --standard=PSR2 --colors --encoding=utf-8 -n -p $FILES
    if [ $? != 0 ]
    then
        echo "Fix the error before commit!"
        echo "Run"
        echo "  ./vendor/bin/phpcbf --standard=PSR2 $FILES"
        echo "for automatic fix or fix it manually."
        exit 1
    fi
fi

exit $?
```
 
我们把该文件内容写入「.git/hooks/pre-commit」中，然后再提交一个文件，看看运行效果。
 
在实验之前，我们先调用本地的`phpcs`插件，看看我们的一个文件代码的规范情况：
 
``` 
phpcs --standard=PSR2 --encoding=utf-8 -n -p app/Http/Controllers/ApplyController.php
```
 
运行结果提示：
 
``` 
E 1 / 1 (100%)



FILE: /Users/app/Http/Controllers/ApplyController.php
------------------------------------------------------------------------------------------------------
FOUND 4 ERRORS AFFECTING 3 LINES
------------------------------------------------------------------------------------------------------
 17 | ERROR | [x] Opening brace should be on a new line
 60 | ERROR | [x] Opening parenthesis of a multi-line function call must be the last content on the
    |       |     line
 62 | ERROR | [x] Multi-line function call not indented correctly; expected 12 spaces but found 16
 62 | ERROR | [x] Closing parenthesis of a multi-line function call must be on a line by itself
------------------------------------------------------------------------------------------------------
PHPCBF CAN FIX THE 4 MARKED SNIFF VIOLATIONS AUTOMATICALLY
------------------------------------------------------------------------------------------------------

Time: 87ms; Memory: 6Mb
```
 
主要报错在于：
  60 行： `output`函数
 
 ![][17]
 
和 17 行： 
 
 ![][18]
 
好了，我们执行`git commit`试试：
 
 ![][19]
 
接着我们把这几个不规范的地方改了之后，同样运行本地方法，返回结果：
 
``` 
phpcs --standard=PSR2 --encoding=utf-8 -n -p app/Http/Controllers/ApplyController.php
. 1 / 1 (100%)


Time: 44ms; Memory: 6Mb
```
 
我们再执行`git commit`试试：
 
 ![][20]
 
完美了！
  注：  「svn」和「git」的区别在于，svn 是放在服务器上做「pre-commit」检测的，而「git」是在本地本项目中的，这也是上文说的，如果你用 git 做版本库，推荐你用「composer」项目安装的方式安装工具。
 


[21]: https://link.juejin.im?target=https%3A%2F%2Flaravel-china.org%2Fdocs%2Fpsr%2Fpsr-2-coding-style-guide%2F1606
[22]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fsquizlabs%2FPHP_CodeSniffer
[23]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2Fsquizlabs%2FPHP_CodeSniffer
[24]: https://link.juejin.im?target=https%3A%2F%2Fmarketplace.visualstudio.com%2Fitems%3FitemName%3Dikappas.phpcs
[25]: https://link.juejin.im?target=https%3A%2F%2Fgithub.com%2FWickedReports%2Fphpcs-pre-commit-hook
[0]: https://img0.tuicool.com/nuUrqmj.jpg 
[1]: https://img2.tuicool.com/Jf6JJvA.jpg 
[2]: https://img1.tuicool.com/uY32Q3Z.jpg 
[3]: https://img0.tuicool.com/yQrEzeY.jpg 
[4]: https://img1.tuicool.com/yqM7ni6.jpg 
[5]: https://img1.tuicool.com/mQZbmqu.jpg 
[6]: https://img0.tuicool.com/iuqM3qU.jpg 
[7]: https://img1.tuicool.com/yUBbaqz.jpg 
[8]: https://img0.tuicool.com/aE32emm.jpg 
[9]: https://img2.tuicool.com/ZNbYBjZ.jpg 
[10]: https://img1.tuicool.com/mqERzuV.jpg 
[11]: https://img2.tuicool.com/AJFn2un.jpg 
[12]: https://img2.tuicool.com/FzInqyM.jpg 
[13]: https://img1.tuicool.com/yuUrQje.jpg 
[14]: https://img2.tuicool.com/7fqEbuq.jpg 
[15]: https://img0.tuicool.com/Yzey63i.jpg 
[16]: https://img1.tuicool.com/6n2URzR.jpg 
[17]: https://img2.tuicool.com/YFvINru.jpg 
[18]: https://img2.tuicool.com/VJR7vae.jpg 
[19]: https://img2.tuicool.com/mumEvuq.jpg 
[20]: https://img1.tuicool.com/INNniqZ.jpg 