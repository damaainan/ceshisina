# PHP 与 UTF-8的最佳实践

2016.12.10 07:01  字数 1257 

[《PHP中的字符串、编码、UTF-8》][1]一文中描述了一些列的基础知识，比较枯燥，现在来说点有用的——PHP 字符串处理的最佳实践，本文是“PHP、字符串、编码、UTF-8”相关知识的第二部分。先说结论——**在 PHP 中的各个方面使用 UTF-8**编码。

PHP 语言层面是不支持 Unicode字符集的，但可以通过 UTF-8 编码能处理大部分问题。 

最佳实践就是明确知道输入编码（不知道就检测），内部统一转换为 UTF-8 编码，输出编码也统一是 UTF-8编码。

#### PHP 层面如何处理 UTF-8

当操作 Unicode 字符集的时候，请务必安装 mbstring 扩展，并使用相应的函数代替原生的字符串函数。举个例子，一个文件编码为 UTF-8 的 PHP 代码，假如使用 strlen() 函数是错误的，请使用 mb_strlen() 函数代替。

mbstring 扩展大部分的函数都需要基于一个编码（内部编码）来处理，请务必统一使用 UTF-8 编码，这个大部分可以在 PHP.INI 中配置。

从 PHP 5.6 开始，default_charset 配置可以替换为 mbstring.http_input，mbstring.http_output 。  
另外一个重要的配置就是 mbstring.language，这个默认值是 Neutral（UTF-8）。

注意文件编码和 mbstring 扩展的内部编码不是同一个概念。

概括的说来：

* PHP.INI 中涉及到 mbstring 扩展的部分尽量使用 UTF-8。
* 请用 mbstring 扩展函数代替原生字符串操作函数。
* 在使用相关函数的时候，请务必了解你操作的字符的编码是什么，在使用对应函数的时候，显示的写上 UTF-8 编码参数，比如 htmlentities() 函数的第三个参数显示写上 UTF-8。

#### 文件 IO 操作 如何处理 UTF-8

这里举个例子，假如你要打开一个文件，但是不知道文件内容是什么编码的，那么如何处理呢？  
最佳实践就是，在打开的时候统一转换成 UTF-8，修改内容后就再转回原来的编码并保存到文件。看代码把：

    if ( mb_internal_encoding()!="UTF-8") {
            mb_internal_encoding("UTF-8");
    }
    $file = "file.txt"; //一个编码为gbk的中文文件
    $str= file_get_contents($file);
    //不管来源是什么编码，统一显示的时候转换为 UTF-8
    if (mb_check_encoding($str, "GBK")) {
        $str =  mb_convert_encoding($str, "UTF-8", "GBK");
        $str ="修改内容";
        $str =  mb_convert_encoding($str, "GBK", "UTF-8"); //原样转回去
        file_put_contents($file, $str);
    }

#### Mysql 和 UTF-8 的最佳实践

这个相对简单，首先保证你的 Mysql 都是 UTF-8。然后 Mysql 客户端连接的时候也保持 UTF-8，具体到 PHP 中，就是 imysql 或者 PDO 扩展连接 Mysql 的时候都设置 UTF-8 作为连接编码，二边保持一致，一般就不会遇到问题。

有兴趣可以看看这篇[文章][2]

#### 浏览器和 UTF-8 的最佳实践

这个也比较简单，就是你的输出内容假如是网页，那么你的字符串处理输出最总请保持为 UTF-8 ；同时 PHP.INI 中也明确设定 default_charset 为 UTF-8；HTML 的 Meta Tag 也明确标识为 UTF-8。

现在万事大吉了吗，并没有，虽然服务器和浏览器让用户使用 UTF-8 编码，但是用户的行为并没有约束性，他可能输入的是其他编码的字符，或者上传的文件名是其他编码的字符，那么怎么办呢？可以通过 mb_http_input() 和 mb_check_encoding() 函数来检测用户的编码，然后内部转换为 UTF-8。确保在任何一个层面，最终处理的是 UTF-8 编码。换句话说，需要手段能够知晓你的输入是什么编码的，处理完成后控制输出的编码是 UTF-8。

不建议使用 mbstring.encoding_translation 指令 和 mb_detect_encoding() 函数。折磨我半天。

#### 操作系统和 UTF-8 的最佳实践

由于操作系统的原因，PHP 处理 Unicode 文件名的时候会有不同的处理机制。  
在 Linux 中，文件名始终是 UTF-8 编码的，而在中文 Windows 环境下，文件名始终是 GBK 编码的，记住这一点就可以了。

通过例子说明下:

    //命令行程序函数，运行在中文版 Windows 10 操作系统 ，文件编码为 UTF-8
    
    function filenameexample() {
        $filename = "测试.txt" ;
        $gbk_filename = iconv("UTF-8","GBK",$filename);
        file_put_contents($gbk_filename, "测试");
        echo file_get_contents($gbk_filename);
    }
    
    function scandirexample() {
        $arr = scandir("./tmp");
        foreach ($arr as $v) {
            if ($v == "." || $v =="..")
                continue ;
            $filename = iconv( "GBK","UTF-8",$v ) ;
            $content = file_get_contents("./tmp/" . $v );
        }
    }

假如不想写写兼容 Windows 和 linux 的程序，可以对文件名进行 urlencode 编码，比如：

     function urlencodeexample() {
        $filename = "测试2.txt" ;
        $urlencodefilename = urlencode($filename) ;
        file_put_contents($urlencodefilename, "测试");
        echo file_get_contents($urlencodefilename);
     }

在用 PHP 通过 header() 函数下载文件的时候，也要考虑浏览器和操作系统（大部分人使用的是 Windows），对于 Chrome 来说，输出的文件名编码可以是 UTF-8，Chrome 会自动将文件名转换为 GBK 编码。  
而对于低版本的 IE 来说，它继承了操作系统的环境，所以下载文件名假如包含中文字符必须转码为 GBK 编码，否则下载的时候用户看到的是乱码文件名。通过代码来说明：

    $agent=$_SERVER["HTTP_USER_AGENT"];
    if(strpos($agent,'MSIE')!==false  ｛
        $filename = iconv("UTF-8","GBK","附件.txt");
        header("Content-Disposition: attachment; filename=\"$filename\"");
    ｝


[1]: http://www.jianshu.com/p/aeb3b15e024e
[2]: https://www.toptal.com/php/a-utf-8-primer-for-php-and-mysql