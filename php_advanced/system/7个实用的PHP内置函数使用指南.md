# 7个实用的PHP内置函数使用指南

[舒铭][0]


本文给大家推荐了7个不经常被用到，但实际很实用，功能很强大的php内置函数，用好了，可以省去小伙伴们很多的时间的。

PHP有许多内置函数，其中大多数函数都被程序员广泛使用。但也有一些函数隐藏在角落，本文将向大家介绍7个鲜为人知，但用处非常大的函数。 没用过的程序员不妨过来看看。

1.highlight_string()

当需要在一个网站中展示PHP代码时，highlight_string()函数就变的非常有用了。该函数通过使用PHP语法高亮程序中定义的颜色，输出或返回给定的PHP代码的语法高亮版本。

示例：

    <?php
    highlight_string('<?php phpinfo(); ?>');
    ?>

2.str_word_count()

该函数必须要传递一个参数，根据参数类型返回单词的个数。如下面的所示：

    <?php
    $str = "How many words do I have?";
    echo str_word_count($str); //Outputs 6
    ?>

3.levenshtein()

该函数主要返回两个字符串之间的Levenshtein距离。Levenshtein 距离，又称编辑距离，指的是两个字符串之间，由一个转换成另一个所需的最少编辑操作次数。许可的编辑操作包括将一个字符替换成另一个字符，插入一个字符，删除一个字符。该函数对查找用户所提交的错别字非常有用。

示例：

    <?php
    $str1 = "carrot";
    $str2 = "carrrott";
    echo levenshtein($str1, $str2); //Outputs 2
    ?>

4.get_defined_vars()

该函数返回一个包含所有已定义变量列表的多维数组，这些变量包括环境变量、服务器变量和用户定义的变量。

示例：

    <?php
    print_r(get_defined_vars());

5.escapeshellcmd()

该函数用来避开字符串中的特殊符号，可以防止使用者耍花招来破解服务器系统。可以用本函数搭配exec() 或是system() 二个函数，这样可以减少网上使用者的恶意破坏行为。

示例：

    <?php
    $command = './configure '.$_POST['configure_options'];
    $escaped_command = escapeshellcmd($command);
    system($escaped_command);
    ?>

6.checkdate()

本函数可以用来检查日期是否有效，例如年为0至32767年、月为1至12月、日则随着月份及闰年变化。

示例：

    <?php
    var_dump(checkdate(12, 31, 2000));
    var_dump(checkdate(2, 29, 2001));
    //Output
    //bool(true)
    //bool(false)
    ?>

7.php_strip_whitespace()

该函数可以返回已删除PHP注释以及空白字符的源代码文件，这对实际代码数量和注释数量的对比很有用。

示例：

    <?php
    // PHP comment here
    /*
    * Another PHP comment
    */
    echo        php_strip_whitespace(__FILE__);
    // Newlines are considered whitespace, and are removed too:
    do_nothing();
    ?>

输出结果：

    <?php
    echo php_strip_whitespace(__FILE__); do_nothing(); 
    ?>

以上7个php的内置函数，小伙伴们你们用过几个？估计大多数人都没用过吧，实际此类内置函数还有挺多，这里先给大家介绍这7个，后续我们再补上其他（小编回去也要翻翻再总结，真心用的少啊）

[0]: https://www.zhihu.com/people/phpgod