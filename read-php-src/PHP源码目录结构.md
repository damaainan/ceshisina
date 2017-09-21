PHP源码目录结构

PHP的源码在结构上非常清晰。其代码根目录中主要包含了一些说明文件以及设计方案，并提供了如下子目录：

1. build —— 顾名思义，这里主要放置一些跟源码编译相关的文件，比如开始构建之前的buildconf脚本及一些检查环境的脚本等。
2. ext —— 官方的扩展目录，包括了绝大多数PHP的函数的定义和实现，如array系列，pdo系列，spl系列等函数的实现。 个人开发的扩展在测试时也可以放到这个目录，以方便测试等。
3. main —— 这里存放的就是PHP最为核心的文件了，是实现PHP的基础设施，这里和Zend引擎不一样，Zend引擎主要实现语言最核心的语言运行环境。
4. Zend —— Zend引擎的实现目录，比如脚本的词法语法解析，opcode的执行以及扩展机制的实现等等。
5. pear —— PHP 扩展与应用仓库，包含PEAR的核心文件。
6. sapi —— 包含了各种服务器抽象层的代码，例如apache的mod_php，cgi，fastcgi以及fpm等等接口。
7. TSRM —— PHP的线程安全是构建在TSRM库之上的，PHP实现中常见的*G宏通常是对TSRM的封装，TSRM(Thread Safe Resource Manager)线程安全资源管理器。
8. tests —— PHP的测试脚本集合，包含PHP各项功能的测试文件。
9. win32 —— 这个目录主要包括Windows平台相关的一些实现，比如sokcet的实现在Windows下和*Nix平台就不太一样，同时也包括了Windows下编译PHP相关的脚本。

