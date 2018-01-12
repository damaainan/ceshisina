## PHP 一行代码删除目录下所有文件

想必很多人都会写几行甚至几十行代码使其列出所有文件变为数组进行删除，但是glob函数分分钟解决问题！

> **例子 1**

    <?php
    print_r(glob("*.txt"));
    ?>

> **输出类似：**

    Array
    (
    [0] => target.txt
    [1] => source.txt
    [2] => test.txt
    [3] => test2.txt
    )

> **例子 2**

    <?php
    print_r(glob("*.*"));
    ?>

> **输出类似：**

    Array
    (
    [0] => contacts.csv
    [1] => default.php
    [2] => target.txt
    [3] => source.txt
    [4] => tem1.tmp
    [5] => test.htm
    [6] => test.ini
    [7] => test.php
    [8] => test.txt
    [9] => test2.txt
    )

> **删除目录下所有文件**

    array_map('unlink', glob('*'));

