## (转)php cli命令 自定义参数传递


> 本文为转载，原文链接： [参考文章][0]

所有的PHP发行版，不论是编译自源代码的版本还是预创建的版本，都在默认情况下带有一个PHP可执行文件。这个可执行文件可以被用来运行命令行的PHP程序。  
要在你的系统上找到这个可执行文件，就要遵照下面的步骤：

1、Windows :放在PHP主安装目录下，文件名是php.exe或者（在老版本的PHP里）是php-cli.exe。  
2、Linux : 保存在PHP安装目录的bin/子目录下。

需要注意的是CLI模式和CGI模式运行时用的PHP.INI并非同一套配置，需要单独配置。  
不论是在哪一个操作系统里，你都需要对它进行测试，以保证它能够正常运行，方法是用-v参数调用它：

    shell> /path/php.exe -v
    PHP 5.0.0 (cli) (built: Jun 1 2005 18:32:10)
    Copyright (c) 1997-2004 The PHP Group
    Zend Engine v2.0.0, Copyright (c) 1998-2004 Zend Technologies

它应该会返回PHP的版本号。

使用CLI命令  
一个简单的PHP CLI程序，命名hello.php

```php
    <?php
    echo "Hello from the CLI";
    ?>
```
现在，试着在命令行提示符下运行这个程序，方法是调用CLI可执行文件并提供脚本的文件名：

    shell> /path/php.exe /example/hello.php
    Hello from the CLI

使用标准的输入和输出  
PHP CLI会定义三个常量，以便让在命令行提示符下与解释器进行交互操作更加容易。这些常量见下表

常量 说明  
STDIN 标准的输入设备  
STDOUT 标准的输出设备  
STDERR 标准的错误设备

你可以在自己的PHP脚本里使用这三个常量，以接受用户的输入，或者显示处理和计算的结果。

使用范例：

```php
    <?php
    // ask for input
    fwrite(STDOUT, "Enter your name: ");
     
    // get input
    $name = trim(fgets(STDIN));
     
    // write input back
    fwrite(STDOUT, "Hello, $name!");
    ?>
```

output:

    D:\>\wamp\bin\php\php5.3.0\php.exe  \tools\index.php
    Enter your name: kkk
    Hello, kkk!

     在这个脚本里，fwrite()函数首先会向标准的输出设备写一条消息，询问用户的姓名。然后它会把从标准输入设备获得的用户输入信息读取到一个PHP变量里，并它把合并成为一个字符串。然后就用fwrite()把这个字符串打印输出到标准的输出设备上。
    

命令行自定义变量1【$argv|$argc】  
在命令行里输入程序参数来更改其运行方式是很常见的做法。你也可以对CLI程序这样做。  
PHP CLI带有两个特殊的变量，专门用来达到这个目的：  
一个是$argv变量，它通过命令行把传递给PHP脚本的参数保存为单独的数组元素；  
另一个是$argc变量，它用来保存$argv数组里元素的个数。

使用范例：

```php
    <?php
    print_r($argv);
    ?>
```

output:
    
    D:\>\wamp\bin\php\php5.3.0\php.exe  \tools\index.php bac ddd
    Array
    (
        [0] => \tools\index.php
        [1] => bac
        [2] => ddd
    )

正如你可以从输出的结果看到的，传递给index.php的值会自动地作为数组元素出现在$argv里。要注意的是，$argv的第一个自变量总是脚本自己的名称。

注意：我们还可以用Console_Getopt PEAR类向PHP增加更加复杂的命令行参数。

命令行自定义变量2【使用Console_Getopt接收参数】

注意：这个变量仅在 register_argc_argv 打开时可用

    getopt($option, $longopts) // 第一个$option接收 -h vb 第二个参数接收 --require sss

使用范例

```php
    <?php
            $shortopts = "";
            $shortopts .= "f:";  // Required value
            $shortopts .= "v::"; // Optional value
            $shortopts .= "abc"; // These options do not accept values
            $longopts = array(
                "required:", // Required value
                "optional::", // Optional value
                "option", // No value
                "opt", // No value
            );
            $options = getopt($shortopts, $longopts);
            var_dump($options);
    ?> 
```

ouput:

    D:\>\wamp\bin\php\php5.3.0\php.exe  \tools\index.php -f "value for f" -v -a --re
    quired value --optional="optional value" --option will
    array(6) {
      ["f"]=>
      string(11) "value for f"
      ["v"]=>
      bool(false)
      ["a"]=>
      bool(false)
      ["required"]=>
      string(5) "value"
      ["optional"]=>
      string(14) "optional value"
      ["option"]=>
      bool(false)
    }

命令行变量3【使用CLI参数】

除了用命令行传递PHP脚本参数，还可以传递PHP CLI参数以更改其工作方式。

    参数 说明  
    -a 交互式运行Run interactively  
    -c path 从path读取php的.ini文件  
    -n 不用读取php的.ini文件就直接运行  
    -m 列出经过编译的模块  
    -i 显示有关PHP构建的信息  
    -l 检查PHP脚本的句法  
    -s 以彩色方式显示源代码  
    -w 显示去掉注释之后的源代码  
    -h 显示帮助

交互模式  
你还可以以交互方式使用PHP CLI，也就是输入命令，马上获得结果。  
要得到这种效果，只需要使用一个参数调用CLI可执行文件就行了，就像下面这样：

    shell> /path/to/php -a
    Interactive mode enabled
    ```php
    <?php
    echo mktime();
    1121187283
    echo 2+2;
    4
    exit();
    shell>

或者，你可以不使用-a参数就调用CLI可执行文件，直接输入完整的脚本或者代码段。

用<Ctrl>-D来结束代码段，并让CLI来执行它。见下面的例子：

    shell> /path/to/php
    <?php
    echo date("d-M-Y h:i:s", time());
    ?>

    
[0]: http://www.cnblogs.com/zcy_soft/archive/2011/12/10/2283437.html