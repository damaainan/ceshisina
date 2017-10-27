## [Xdebug文档（六） 分析PHP脚本][0] 

Posted on 2016-06-17 18:10 [龙翔天下][1] 

分析PHP脚本Xdebug内置分析器能让你找到脚本中的瓶颈并用额外的工具诸如KcacheGrind或WinCacheGrind工具可视化。

**介绍**

Xdebug分析器是分析PHP代码和判断瓶颈或确定代码哪里运行过慢需要使用加速器的强大分析器。Xdebug2的分析器输出信息以cachegrind兼容文件形成表现。这能让你使用优秀的KcacheGrind工具（Linux,KDE）来分析数据。如果你用Linux你可以在你安装管理器安装它。

Windows下也有预编译的QcacheGrind二进制安装包可用（QCacheGrind 是KcacheGrind不捆绑KDE的版本）

如果你用Mac系统，这里有介绍[如何安装QcacheGrind][4]。

Windows用户也可以选择使用WinCacheGrind。但其功能与KcacheGrind不一样所以本文不在此介绍该软件。Xdebug2.3出版时，WinCacheGrind当前仍不支持cachegrind压缩功能和文件。

如果你不使用KDE(或不想用)，kcachegrind包也携带perl脚本“ct_annotate”能分析跟踪文件输出ASCII。

开始分析

Php.ini内设置xdebug.profiler_enable设为1开启分析。该设置命令xdebug启用分析并写入到 [xdebug.profiler_output_dir][5]指定的目录中。而产生的文件名一般以”cachegrind.out”开头，然后结尾跟着以PHP（或apache）进程PID（进程ID）或包含初始调试脚本目录的crc32哈希值。确保你的xdebug.profiler_output_dir设置目录有足够的空间保存分析对复杂脚本分析生成庞大的分析数据。比如有500MB以上的复杂应用程序 [eZ Publish][6]。

你也可以通过 [xdebug.profiler_enable_trigger][7] 设为1手动开启分析器。当它设为1后，你能让分析器使用名为XDEBUG_PROFILE的变量利用到GET/POST 或者 COOKIE 值。 FireFox 2 扩展能用于开启debugger (参考 [HTTP Debug Sessions][8]) 也能用于使用该设置。为了触发器能合理地运行， [xdebug.profiler_enable][9] 需要设为0。

## 分析输出

当产生分析结果后，你就能利用 [KCacheGrind][10]打开它：

一旦打开了文件，KCacheGrind会有不同的面板提供充足的信息让你参考。左侧你能看到"Flat Profile"面板列出所有脚本函数并以花费 时间为序，还包括了它的子函数时间。第二列"Self"显示该函数（不包括子函数）花费的时间，第三列"Called"指被调用的频度，最后一列”functions”显示函数名称。Xdebug改变PHP内部函数名并在函数名加前缀“php::”，而引用文件也在用指定的方式处理。呼叫include命令都跟随“::”和引用的文件名. 左侧截图你能看到"include::/home/httpd/ez_34/v..." 和内存函数示例"php::mysql_query"。头两列的数量能以百分数表示总运行时间的占比（看示例）或绝对时间（1单元代表1/1.000.000之一秒）。你可以用右边的按钮来切换这两种模式。

右边的面板包含上层和下层两面板。上层面板显示哪个函数调用了当前选择的函数（截图中"eztemplatedesignresource->executecompiledtemplate). 下层面板显示已选择的函数调用的函数列表。

上层面板的Cost列显示当前选择的函数在列表内被调用时花费的时间。该数字在Cost列通常是100%。下方面板Cost列显示在列表中调用的函数所花费的时间。在这列的数字当中，你绝不看到达100%执行时间的函数。

"All Callers" 和 "All Calls"显示不仅仅是函数分别执行的直接调用而且还显示函数更多的上下级关系。截图中的上层面板显示当前选择的函数调用的所有函数列，直接和间接关系的其他函数都在堆栈中间。（翻译能力有限……原句是这样的：The upper pane in the screenshot on the left shows all functions calling the current selected one, both directly and indirectly with other functions inbetween them on the stack.）“Distance”列代表有多少函数呼叫在列表当中，而当前选择 的为（-1）。如果两函数间有不同的距离，此外会显示一范围值（例如：“5-24”）。圆括号中的数字代表平均值。下方面板也是类似显示，但不同的是它显示当前选择的函数调用的所有函数信息，不管是直接还是间接的。

## 相关设置

**xdebug.profiler_append**

> 类型: integer, 默认值: 0

> 设为1时，分析文件工作在新的请求下映射到相同的文件时（取决于xdebug.profiler_output_name）不会下覆盖分析结果，而是分析信息附加到尾部形成新的分析文件。

**xdebug.profiler_enable**

> 类型: integer, 默认值: 0

> 开启Xdebug的分析器能在[profile output directory][5]目录中创建分析文件。这些文件能被KcacheGrind读取而可视化分析数据。该设置不能使用ini_set()在脚本中设置。如果你要选择性开启分析器，可以使用 [xdebug.profiler_enable_trigger][7]设置设为1代替使用。

**xdebug.profiler_enable_trigger**

> 类型: integer, 默认值: 0

> 设为1时，你就能使用XDEBUG_PROFILE的GET/POST参数或设置XDEBUG_RPOFILE的cookie值触发分析文件的产生。这些写入分析数到预定义的目录中，为了不上分析文件在每次请求中产生，你需要设置[xdebug.profiler_enable][9] 为0值。访问触发器可能过[xdebug.profiler_enable_trigger_value][11]配置。

**xdebug.profiler_enable_trigger_value**

> 类型: string, 默认值: "", 始于 Xdebug > 2.3

> 如[xdebug.profiler_enable_trigger][7]所述，该设置用于限制谁能利用XDEBUG_PROFILE功能。当改变原本的空字符串默认值后，cookie,GET或POST参数值需要匹配共享秘密集合并随设置开启分析器。

**xdebug.profiler_output_dir**

> 类型: string, 默认值: /tmp

> 该目录为分析文件输出的地方，请确保运行PHP的账户能对该目录有写入权限。该设置不能用ini_set()在脚本中设置。

**xdebug.profiler_output_name**

> 类型: string, 默认值: cachegrind.out.%p

> 该设置决定分析文件的名称，可使用格式标识符指定，类似于sprintf() 和strftime()。有几种标识符可格式化文件名。详见参考[xdebug.trace_output_name][12]说明。

## 相关函数

**string xdebug_get_profiler_filename()**

> 返回当前保存的分析信息的文件名称。

[0]: http://www.cnblogs.com/xiwang6428/p/5594825.html
[1]: http://www.cnblogs.com/xiwang6428/
[2]: https://i.cnblogs.com/EditPosts.aspx?postid=5594825
[3]: #
[4]: http://www.tekkie.ro/computer-setup/how-to-install-kcachegrind-qcachegrind-on-mac-osx/
[5]: https://xdebug.org/docs/all_settings#profiler_output_dir
[6]: http://ez.no/
[7]: https://xdebug.org/docs/all_settings#profiler_enable_trigger
[8]: https://xdebug.org/docs/remote#firefox-ext
[9]: https://xdebug.org/docs/all_settings#profiler_enable
[10]: https://kcachegrind.github.io/
[11]: https://xdebug.org/docs/all_settings#profiler_enable_trigger_value
[12]: https://xdebug.org/docs/all_settings#trace_output_name