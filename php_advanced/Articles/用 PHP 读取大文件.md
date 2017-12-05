# 如何在不会导致服务器宕机的情况下，用 PHP 读取大文件

 时间 2017-12-04 19:21:31 

原文[https://www.oschina.net/translate/performant-reading-big-files-php][1]



作为PHP开发人员，我们并不经常需要担心内存管理。PHP 引擎在我们背后做了很好的清理工作，短期执行上下文的 Web 服务器模型意味着即使是最潦草的代码也不会造成持久的影响。

很少情况下我们可能需要走出这个舒适的地方 —— 比如当我们试图在一个大型项目上运行 Composer 来创建我们可以创建的最小的 VPS 时，或者当我们需要在一个同样小的服务器上读取大文件时。

![][3]

后面的问题就是我们将在本教程中深入探讨的。

_在 [GitHub][4]__上可以找到本教程的源码。_

## 衡量成功的标准 

确保我们对代码进行任何改进的唯一方法是测试一个不好的情况，然后将我们修复之后的测量与另一个进行比较。换句话说，除非我们知道“解决方案”对我们有多大的帮助（如果有的话），否则我们不知道它是否真的是一个解决方案。

这里有两个我们可以关系的衡量标准。首先是CPU使用率。我们要处理的进程有多快或多慢？第二是内存使用情况。脚本执行时需要多少内存？这两个通常是成反比的 - 这意味着我们可以以CPU使用率为代价来降低内存使用，反之亦然。

在一个异步执行模型（如多进程或多线程的PHP应用程序）中，CPU和内存的使用率是很重要的考量因素。在传统的PHP架构中，当任何一个值达到服务器的极限时，这些通常都会成为问题。

测量PHP内的CPU使用率是不切实际的。如果这是你要关注的领域，请考虑在Ubuntu或MacOS上使用类似top的工具。对于Windows，请考虑使用Linux子系统，以便在Ubuntu中使用top。

为了本教程的目的，我们将测量内存使用情况。我们将看看在“传统”的脚本中使用了多少内存。我们将执行一些优化策略并对其进行度量。最后，我希望你能够做出一个有经验的选择。

我们查看内存使用多少的方法是：

    // formatBytes is taken from the php.net documentation
    
    memory_get_peak_usage();
    
    function formatBytes($bytes, $precision = 2) {
        $units = array("b", "kb", "mb", "gb", "tb");
    
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
    
        $bytes /= (1 << (10 * $pow));
    
        return round($bytes, $precision) . " " . $units[$pow];
    }

我们将在脚本的最后使用这些函数，以便我们能够看到哪个脚本一次使用最大的内存。

### 我们的选择是什么？ 

这里有很多方法可以有效地读取文件。但是也有两种我们可能使用它们的情况。我们想要同时读取和处理所有数据，输出处理过的数据或根据我们所读取的内容执行其他操作。我们也可能想要转换一个数据流，而不需要真正访问的数据。

让我们设想一下，对于第一种情况，我们希望读取一个文件，并且每10,000行创建一个独立排队的处理作业。我们需要在内存中保留至少10000行，并将它们传递给排队的工作管理器（无论采取何种形式）。

对于第二种情况，我们假设我们想要压缩一个特别大的API响应的内容。我们不在乎它的内容是什么，但我们需要确保它是以压缩形式备份的。

在这两种情况下，如果我们需要读取大文件，首先，我们需要知道数据是什么。第二，我们并不在乎数据是什么。让我们来探索这些选择吧...

## 逐行读取文件 

有许多操作文件的函数，我们把部分结合到一个简单的文件阅读器中(封装为一个方法)：

    // from memory.php
    
    function formatBytes($bytes, $precision = 2) {
        $units = array("b", "kb", "mb", "gb", "tb");
    
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
    
        $bytes /= (1 << (10 * $pow));
    
        return round($bytes, $precision) . " " . $units[$pow];
    }
    
    print formatBytes(memory_get_peak_usage());

    // from reading-files-line-by-line-1.php
    
    function readTheFile($path) {
        $lines = [];
        $handle = fopen($path, "r");
    
        while(!feof($handle)) {
            $lines[] = trim(fgets($handle));
        }
    
        fclose($handle);
        return $lines;
    }
    
    readTheFile("shakespeare.txt");
    
    require "memory.php";

我们读取一个文本文件为莎士比亚全集。文件大小为5.5MB，内存占用峰值为12.8MB。现在让我们用一个生成器来读取每一行：

    // from reading-files-line-by-line-2.php
    
    function readTheFile($path) {
        $handle = fopen($path, "r");
    
        while(!feof($handle)) {
            yield trim(fgets($handle));
        }
    
        fclose($handle);
    }
    
    readTheFile("shakespeare.txt");
    
    require "memory.php";

文本文件大小不变，但内存使用峰值只是393KB。即使我们能把读取到的数据做一些事情也并不意味着什么。也许我们可以在看到两条空白时把文档分割成块，像这样：

    // from reading-files-line-by-line-3.php
    
    $iterator = readTheFile("shakespeare.txt");
    
    $buffer = "";
    
    foreach ($iterator as $iteration) {
        preg_match("/\n{3}/", $buffer, $matches);
    
        if (count($matches)) {
            print ".";
            $buffer = "";
        } else {
            $buffer .= $iteration . PHP_EOL;
        }
    }
    
    require "memory.php";

 猜到我们使用了多少内存吗？我们把文档分割为1216块，仍然只使用了459KB的内存，这是否让你惊讶？考虑到生成器的性质，我们使用的最多内存是使用在  迭代中  我们需要存储的最大文本块。在本例中，最大的块为101985字符。

我已经撰写了 [使用生成器提示性能][5] 和 [Nikita Popov的迭代器库][6] ，如果你感兴趣就去看看吧！ 

生成器还有其它用途，但是最明显的好处就是高性能读取大文件。如果我们需要处理这些数据，生成器可能是最好的方法。

### 管道间的文件

在我们不需要处理数据的情况下，我们可以把文件数据传递到另一个文件。通常被称为管道（大概是因为我们看不到除了两端的管子里面，当然，它也是不透明的），我们可以通过使用流方法实现。让我们先写一个脚本从一个文件传到另一个文件。这样我们可以测量内存的占用情况：

    // from piping-files-1.php
    
    file_put_contents(
        "piping-files-1.txt", file_get_contents("shakespeare.txt")
    );
    
    require "memory.php";

不出所料，这个脚本使用更多的内存来进行文本文件复制。这是因为它读取(和保留)文件内容在内存中，直到它被写到新文件中。对于小文件这种方法也许没问题。当为更大的文件时，就 捉襟见肘了…

让我们尝试用流(管道)来传送一个文件到另一个：

    // from piping-files-2.php
    
    $handle1 = fopen("shakespeare.txt", "r");
    $handle2 = fopen("piping-files-2.txt", "w");
    
    stream_copy_to_stream($handle1, $handle2);
    
    fclose($handle1);
    fclose($handle2);
    
    require "memory.php";

这段代码稍微有点陌生。我们打开了两文件的句柄，第一个是只读模式，第二个是只写模式，然后我们从第一个复制到第二个中。最后我们关闭了它，也许使你惊讶，内存只占用了393KB 

这似乎很熟悉。像代码生成器在存储它读到的每一行代码？那是因为第二个参数 fgets 规定了每行读多少个字节（默认值是-1或者直到下一行为止）。

第三个参数 stream_copy_to_stream 和第二个参数是同一类参数（默认值相同），  stream_copy_to_stream 一次从一个数据流里读一行，同时写到另一个数据流里。 它跳过生成器只有一个值的部分（因为我们不需要这个值）。 

这篇文章对于我们来说可能是没用的，所以让我们想一些我们可能会用到的例子。假设我们想从我们的CDN中输出一张图片，作为一种重定向的路由应用程序。我们可以参照下边的代码来实现它：

    // from piping-files-3.php
    
    file_put_contents(
        "piping-files-3.jpeg", file_get_contents(
            "https://github.com/assertchris/uploads/raw/master/rick.jpg"
        )
    );
    
    // ...or write this straight to stdout, if we don't need the memory info
    
    require "memory.php";

设想一下，一个路由应用程序让我们看到这段代码。但是，我们想从CDN获取一个文件，而不是从本地的文件系统获取。我们可以用一些其他的东西来更好的替换 file_get_contents （就像 [Guzzle][7] ），即使在引擎内部它们几乎是一样的。

图片的内存大概有581K。现在，让我们来试试这个

    // from piping-files-4.php
    
    $handle1 = fopen(
        "https://github.com/assertchris/uploads/raw/master/rick.jpg", "r"
    );
    
    $handle2 = fopen(
        "piping-files-4.jpeg", "w"
    );
    
    // ...or write this straight to stdout, if we don't need the memory info
    
    stream_copy_to_stream($handle1, $handle2);
    
    fclose($handle1);
    fclose($handle2);
    
    require "memory.php";

内存使用明显变少（大概 **400K** ），但是结果是一样的。如果我们不关注内存信息，我们依旧可以用标准模式输出。实际上，PHP提供了一个简单的方式来完成： 

    $handle1 = fopen(
        "https://github.com/assertchris/uploads/raw/master/rick.jpg", "r"
    );
    
    $handle2 = fopen(
        "php://stdout", "w"
    );
    
    stream_copy_to_stream($handle1, $handle2);
    
    fclose($handle1);
    fclose($handle2);
    
    // require "memory.php";

还有其它一些流，我们可以通过管道来写入和读取（或只读取/只写入）：

* php://stdin (只读)
* php://stderr (只写, 如php://stdout)
* php://input (只读) 这使我们能够访问原始请求体
* php://output (只写) 让我们写入输出缓冲区
* php://memory 和 php://temp (读-写) 是我们可以临时存储数据的地方。 不同之处在于一旦它变得足够大 php://temp 会将数据存储在文件系统中，而 php://memory 将一直持存储在内存中直到资源耗尽。

还有一个我们可以在stream上使用的技巧，称为 **过滤器** 。它们是一种中间的步骤，提供对stream数据的一些控制，但不把他们暴露给我们。想象一下，我们 会使用Zip扩展名 来压缩我们的shakespeare.txt文件。 

    // from filters-1.php
    
    $zip = new ZipArchive();
    $filename = "filters-1.zip";
    
    $zip->open($filename, ZipArchive::CREATE);
    $zip->addFromString("shakespeare.txt", file_get_contents("shakespeare.txt"));
    $zip->close();
    
    require "memory.php";

这是一小段整洁的代码，但它测量内存占用在10.75MB左右。使用过滤器的话，我们可以减少内存：

    // from filters-2.php
    
    $handle1 = fopen(
        "php://filter/zlib.deflate/resource=shakespeare.txt", "r"
    );
    
    $handle2 = fopen(
        "filters-2.deflated", "w"
    );
    
    stream_copy_to_stream($handle1, $handle2);
    
    fclose($handle1);
    fclose($handle2);
    
    require "memory.php";

此处，我们可以看到名为php://filter/zlib.deflate的过滤器，它读取并压缩资源的内容。我们可以在之后将压缩数据导出到另一个文件中。这仅使用了 **896KB** . 

我知道这是不一样的格式，或者制作zip存档是有好处的。你不得不怀疑：如果你可以选择不同的格式并节省约12倍的内存，为什么不选呢？

为了解压此数据，我们可以通过执行另一个zlib filter将压缩后的数据还原：

    // from filters-2.php
    
    file_get_contents(
        "php://filter/zlib.inflate/resource=filters-2.deflated"
    );

Streams have been extensively covered in Stream在“ [理解PHP中的流][8] ”和“ [U高效使用PHP中的流][9] ”中已经被全面介绍了。如果你喜欢一个完全不同的视角，可以阅读一下。


[1]: https://www.oschina.net/translate/performant-reading-big-files-php
[3]: https://img2.tuicool.com/nEFVZvZ.jpg
[4]: https://github.com/sitepoint-editors/sitepoint-performant-reading-of-big-files-in-php
[5]: https://www.sitepoint.com/memory-performance-boosts-with-generators-and-nikiciter/
[6]: https://github.com/nikic/iter
[7]: http://docs.guzzlephp.org/en/stable/
[8]: https://www.sitepoint.com/%EF%BB%BFunderstanding-streams-in-php/
[9]: https://www.sitepoint.com/using-php-streams-effectively/