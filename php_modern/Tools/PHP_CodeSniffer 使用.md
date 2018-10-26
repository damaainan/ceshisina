# PHP_CodeSniffer 使用攻略

 时间 2016-06-02 22:52:58  

原文[http://searchp.cc/20160602.html][1]


## 目录

## 安装 PHP_CodeSniffer

## 安装 phpcs

phpcs 是 PHP 代码规范的检测工具。

    # 下载
    $ curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar
    # 加入到命令目录
    $ mv phpcs.phar /usr/local/bin/phpcs
    # 赋予执行权限
    $ sudo chmod +x /usr/local/bin/phpcs
    # 检验是否成功
    $ phpcs -h
    

## 安装 phpcbf

phpcbf 是 PHP 代码规范的修复工具。

    # 下载
    $ curl -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcbf.phar
    # 加入到命令目录
    $ mv phpcbf.phar /usr/local/bin/phpcbf
    # 赋予执行权限
    $ sudo chmod +x /usr/local/bin/phpcbf
    #  检验是否成功
    $ phpcbf -h
    

## 使用 phpcs

## phpcs 配置

1.查看详细配置。使用命令： phpcs --config-show （下面是我当前的配置） 

    colors:                  1
    default_standard:        PSR2
    encoding:                utf-8
    error_severity:          1
    ignore_errors_on_exit:   1
    ignore_warnings_on_exit: 1
    report_format:           summary
    report_width:            auto
    severity:                1
    show_progress:           1
    show_warnings:           1
    tab_width:               4
    warning_severity:        8
    

2.设置默认的编码标准。（这个很重要，建议使用 PSR2 的标准） 

    # 查看配置
    $ phpcs -i
    The installed coding standards are MySource, PEAR, PHPCS, PSR1, PSR2, Squiz and Zend
    
    # 设置编码标准为 PSR2
    $ phpcs --config-set default_standard PSR2
    

3.隐藏警告。（当然，对于强迫症来说，警告都是不允许的，非强迫症患者可以使用此配置项）

    # 隐藏警告提醒
    $ phpcs --config-set show_warnings 0
    # 开启警告提醒
    $ phpcs --config-set show_warnings 1

4.显示检查进程。（如果项目需要检查的文件较多可以开启这个）

    # 显示检查进程
    $ phpcs --config-set show_progress 1
    # 关闭进程显示
    $ phpcs --config-set show_progress 0

5.显示颜色。 (给自己点颜色看看哈)

    # 显示颜色
    $ phpcs --config-set colors 1
    # 关闭颜色显示
    $ phpcs --config-set colors 0

6.修改错误和警告等级

    # 显示所有的错误和警告
    $ phpcs --config-set severity 1
    # 显示所有的错误，部分警告 注意等级可有从 5-8 5 的警告显示会更多，8 的更少
    $ phpcs --config-set error_severity 1
    $ phpcs --config-set warning_severity 5

7.设置默认编码

    # 设置 utf-8
    $ phpcs --config-set encoding utf-8

8.设置 tab 的宽度

    # tab 为 4 个空格
    $ phpcs --config-set tab_width 4
    # 也可以对单独文件生效
    $ phpcs --tab-width=0 /path/to/code
    

## 代码验证

1.校验单个文件

    # 校验单个文件
    $ phpcs filename
    

2.校验目录（如：整个项目）

    ＃ 校验目录 注意这个时候别因为 linux 学的太好加个 -R 哈。
    $ phpcs /path/dir
    

### 结果分析

结果展现的形式：

full, xml, checkstyle, csv, json, emacs, source, summary, diff, svnblame, gitblame, hgblame or notifysend

指定展现形式：

    # 汇总的形式
    phpcs --report=summary test01.php
    
    # json 形式 （个人觉得这个形式更清晰）
    phpcs --report=json test01.php
    

## 修复代码

## 使用 phpcbf

覆盖式修复

    # 直接覆盖
    $ phpcbf /path/code
    

生成中间文件

    # 生成新文件
    $ phpcbf /path/to/code --suffix=.fixed
    

## 使用 diff

    # 以 test.php 为例 会生成 test.php.diff 文件
    $ phpcs --report-diff=test.php.diff test01.php
    

## 总结

记住，这只是一个工具。但是， 工欲善其事，必先利其器 。这里可以打个小广告利器 里面是有很多好用的工具的。 

如果，你需要应用到团队，需要看你团队使用的什么框架。然后，根据框架适当的调整一下配置的 **错误等级** 和 **警告等级** 。 

## 参考链接

1. [PHP_CodeSniffer_wiki][4]

2. [Configuration Options][5]


[1]: http://searchp.cc/20160602.html

[4]: https://github.com/squizlabs/PHP_CodeSniffer/wiki
[5]: https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options#ignoring-warnings-when-generating-the-exit-code