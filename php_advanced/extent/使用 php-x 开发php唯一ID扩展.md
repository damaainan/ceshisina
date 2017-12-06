# 使用 php-x 开发php唯一ID扩展

 时间 2017-09-19 13:21:04  

原文[http://www.jianshu.com/p/38c9764d761e][1]


date: 2017-9-17 19:48:13

title: 使用 php-x 开发php唯一ID扩展

唯一ID 相关资源:

* [daydaygo - 唯一ID生成原理与phper的深度思考][4]
* [分布式id生成方案概述][5]
* [snowflake升级版全局id生成][6]

php 扩展开发相关资源:

* [信海龙 - php7 扩展开发教程][7]
* [zend api][8]
* [PHP-CPP: A C++ library for developing PHP extensions][9]
* [php-x wiki][10]
* [ranggo - 如何基于 PHP-X 快速开发一个 PHP 扩展][11]

整体体验下来, 韩老大的 php-x 在易用性上完胜, 毕竟官方 wiki 目前给出的 api 非常少, 稍微阅读一下, 配合源码中给出的 example, 就能轻松上手. 

唯一 id 设计归纳:

* 唯一性
* 时间相关: 添加时间戳, 常用秒级和毫秒级
* 粗略有序: 数据库的自增 id 是一个参考思路, 但完全自增也会导致攻击者可以根据已有 id 构造其他 id
* 可反解
* 分布式: 可选, 在性能要求比较高的场景下, 可以增加 machine_id 或者 worker_id 来达到分布式的效果, 比如使用 48bit MAC 地址

技术难点: sequence 在生成的时候, 考虑到并发, 需要加锁, **自旋锁** 是可选的方案. 

## show the code

由于 c++ 已经荒废多年, 写起来确实不顺手, 所以实现了一版简单的, 权当抛砖引玉:

采用 Simple flake: timestamp + sequence

uid(64bit) = timestamp(秒级, 32bit) + sequence(随机数, 32bit)* 原版 c++ 代码

    #include <iostream>
    #include <ctime>
    #include <cstdlib>
    #include <cinttypes>
    using namespace std;
    
    int main()
    {
        uint64_t data;
        uint64_t timestamp = (unsigned)time(NULL); // 秒级时间戳, 32bit
        cout << time(NULL) << endl;
        srand(timestamp);
        uint64_t sequence = rand() % 4294967295; // 32bit
        data = timestamp << 32
            | sequence;
        cout << timestamp << '\t' << sequence << '\t' << data << endl;
    
        // 1505655347   31225   6466740474412562937
        data = 6466740474412562937;
        timestamp = data >> 32;
        sequence = data & 0xfffffff;
        cout << timestamp << '\t' << sequence << '\t' << data << endl;
        return 0;
    }

* php-x

最开始使用 uint64_t , 要和 Variant 进行强制类型转换, 下面是第二版 

字符串要使用双引号, 这个地方坑了好久

    // cpp_atom.cc
    #include "phpx.h"
    #include <iostream>
    #include <ctime>
    #include <cstdlib>
    
    using namespace php;
    using namespace std;
    
    PHPX_FUNCTION(cpp_atom_generate)
    {
        long data;
        long timestamp = (unsigned)time(NULL);
        srand(timestamp);
        int sequence = rand() % 4294967295; // 32bit
        data = timestamp << 32
            | sequence;
        // cout<< data << endl;
        retval = data;
    }
    
    PHPX_FUNCTION(cpp_atom_explain)
    {
        Variant key = args[0];
        long data = key.toInt();
        // sscanf(key.toCString(), "%llu", &data); // 另一个类型转换的方式
        int timestamp = data >> 32;
        int sequence = data & 0xfffffff;
        // cout << data << '\t' << timestamp << '\t' << sequence << endl;
        Array arr;
        arr.set("ts", timestamp);
        arr.set("seq", sequence);
    
        retval = arr;
    }
    
    PHPX_EXTENSION()
    {
        Extension *extension = new Extension("cpp_atom", "0.0.1");
        extension->registerFunction(PHPX_FN(cpp_atom_generate));
        extension->registerFunction(PHPX_FN(cpp_atom_explain));
    
        extension->info(
        {
            "cpp_atom support", "enabled"
        },
        {
            { "author", "daydaygo" },
            { "version", extension->version },
            { "date", "2017-9-17 21:24:46" },
        });
    
        return extension;
    }

* Makefile

```
    PHP_INCLUDE = `php-config --includes`
    PHP_LIBS = `php-config --libs`
    PHP_LDFLAGS = `php-config --ldflags`
    PHP_INCLUDE_DIR = `php-config --include-dir`
    PHP_EXTENSION_DIR = `php-config --extension-dir`
    
    cpp_atom.so: cpp_atom.cc
        c++ -DHAVE_CONFIG_H -g -o cpp_atom.so -O0 -fPIC -shared cpp_atom.cc -std=c++11 -lphpx ${PHP_INCLUDE} -I${PHP_INCLUDE_DIR}
    install: cpp_atom.so
        cp cpp*.so ${PHP_EXTENSION_DIR}/
    clean:
        rm *.so
```

* test case

测试时建议将代码中的 cout 部分的调试代码加上, 用来对比, 第一版强制类型转换的时候, 就遇到 (int)data 这个将 64位整形转为 32位整形 

    <?php
    var_dump(cpp_atom_generate());
    var_dump(cpp_atom_explain(6466751737141169189));

无论是 php-x 还是 php-cpp 这样的项目, 都是在 zend-api 上面多封装了一层, 有兴趣还是可以去翻翻 zend-api 看看. 

当然, 真要写扩展, 还是要 c/c++ 功底的.


[1]: http://www.jianshu.com/p/38c9764d761e

[4]: http://www.jianshu.com/p/ea8e29a624bd
[5]: https://segmentfault.com/a/1190000010978305
[6]: http://hacloud.club/2017/09/09/snowflake
[7]: http://www.bo56.com/php7%E6%89%A9%E5%B1%95/
[8]: http://www.yesky.com/imagesnew/software/php/zh/zend.html
[9]: http://www.php-cpp.com/
[10]: https://wiki.swoole.com/wiki/index/prid-15
[11]: https://segmentfault.com/a/1190000011111074