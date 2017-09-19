# 正确使用PHP的Opcache加速模块

关注 2017.02.16 18:13  字数 1308 

前几天一个朋友问了 PHP 中的 Opcache ，自己就去看了线上服务器的相关配置，其中的一个参数让我思索了半天，所以就详细去了解这个模块。

Opcache 对于 PHP 开发人员来说，应该是比较熟悉的，PHP 在执行的时候需要由解析器将源码转换为 byte-code，对于同一个 PHP 文件来说，假如每次执行的时候就需要转换一次，那就太影响性能了，因此出现了很多的加速模块，而从 PHP 5.5 开始 Opcache 已经成为官方标准的模块。

假如你还是个新手，就不要去了解其它的加速模块了（比如 APC），因为既然是官方的，那么性能、稳定性、适配性各方面就不用太担心了，也有一些文章比较 APC cache 和 Opcache。

对于**应用层的开发者**来说，只要知道 Opcache 是一个 PHP 内置的加速模块就行，当 PHP 解析器在解析一个 PHP 文件的时候，假如该文件对应的 byte-code 存储在内存中，则省去了转换过程直接执行了；反之则会编译，并将编译后的 byte-code 存入到内存中（以文件名作为索引）。

那么用了 Opcache 性能有多大的提升呢（这是应用的本质），看了一些文章列举的测试，整体的脚本响应时间最少提升 10%（取决于不同的 PHP 开发框架和具体的应用），虽然提升仅仅是几毫秒，但是对于一个大网站来说，能够极大的降低负载并且也会提高并发请求数。

不管是通过源码还是包安装，默认 Opcache 安装后是开启的，相关的参数类似于：

    #/etc/php5/fpm/php.ini 
    
    validate_timestamps=1
    revalidate_freq=0
    memory_consumption=64
    max_accelerated_files=4000 
    opcache.fast_shutdown=0

validate_timestamps 这个参数很重要，假如等于 0，那么 PHP 解析器只要发现内存中有对应 PHP 文件的 byte-code 内容就会加载，反应灵敏的同学可能会想，假如开发者修改了其开发的 PHP 代码，那么这个 byte-code 不就是“脏”内容吗，确实是这样的（如何解决呢？）。

假如 validate_timestamps 等于 1，PHP 解析器从内存中获取某个 PHP 文件对应的  
byte-code，会通过一定的方法比较 byte-code 内容是不是最新的（读取文件系统），假如比较后发现 byte-code 已经过期，应该重新编译生成。  
需要注意，PHP 解析器不是每次都会去检查（一切为了效率），检查的频率取决于 revalidate_freq 参数（ 0 表示每次都检查）。

memory_consumption 这个参数很好理解，代表这块内存区开辟的大小，另外需要注意不同 PHP SAPI 内存区不是共享的，就是说同一个 PHP 文件，运行在命令行模式或者 PHP-FPM 模式下，对应的 byte-code 会存储在不同的内存区中。

max_accelerated_files 表示内存区最大能存储的 PHP 文件数量。

#### 如何了解 Opcache 的运行状况

上面主要说了 Opcache 的定义、安装、配置、简单的运作机制，对于开发者来说，了解这些就足够了，但是可进一步了解 Opcache 到底缓存了什么，PHP 提供了一些函数（opcache_get_configuration()和 and opcache_get_status()）获取配置信息和运行信息，比如了解那些文件被缓存了、使用了多少内存、内存命中率等等。

我截取了一段线上服务器的运行信息：

    Array
    (
        [opcache_enabled] => 1
        [cache_full] => 
        [restart_pending] => 
        [restart_in_progress] => 
        [memory_usage] => Array
            (
                [used_memory] => 16975736
                [free_memory] => 116769216
                [wasted_memory] => 472776
                [current_wasted_percentage] => 0.35224556922913
            )
    
        [opcache_statistics] => Array
            (
                [num_cached_scripts] => 340
                [num_cached_keys] => 1071
                [max_cached_keys] => 7963
                [hits] => 22016549
                [start_time] => 1486840022
                [last_restart_time] => 0
                [oom_restarts] => 0
                [hash_restarts] => 0
                [manual_restarts] => 0
                [misses] => 352
                [blacklist_misses] => 0
                [blacklist_miss_ratio] => 0
                [opcache_hit_rate] => 99.99840122822
            )
    
        [scripts] => Array
            (
                [/usrPincode.php] => Array
                    (
                        [full_path] => /usr/Pincode.php
                        [hits] => 0
                        [memory_consumption] => 323800
                        [last_used] => Sun Feb 12 06:16:14 2017
                        [last_used_timestamp] => 1486851374
                        [timestamp] => 1469520891
                    )
            )
    )

也有一些[工具][1]可以更直观的获取运行信息。

#### validate_timestamps 指令

其实写这篇文章，主要是为了了解这个指令，文档上建议生产环境不开启，开发环境不开启。  
不开启的意思就是不校验 PHP 文件最近有没有修改过，主要是为了效率（参考前面讲的），那么如何解决文件更新带来的问题呢（让它保持最新），有两种方法：

第一种方法就是调用 opcache_reset() 函数，第二种重启 PHP SPAI，比如运行service php5-fpm restart等。

但是上面两种方式都太凶残，假如开发的项目频繁上线，每次缓存区都要全部清空，可以使用opcache_invalidate()函数更新特定文件的缓存。

不管那种方式，为了清除失效的 byte-code，在部署代码的时候必然会有一些麻烦，可以写一些脚本来做到自动化更新缓存。

关闭这个指令的好处：

* 不用频繁读取文件系统（校验内存中的 byte-code 是不是最新的）
* 同个文件不会缓存多份（因为某个文件 byte-code 并不会因为过期而释放内存区）

另外推荐这篇[文章][2]，说的比较详细。


[1]: https://github.com/PeeHaa/OpCacheGUI
[2]: https://tideways.io/profiler/blog/fine-tune-your-opcache-configuration-to-avoid-caching-suprises