## valgrind 检测 php-fpm 内存泄漏

_摘要：_ 最近线上服务器安装了一些扩展，导致 php-fpm 内存增长过快，虽然可以通过配置最大请求数和定时脚本来重启 php-fpm ，但是也抱着学习折腾的精神来学习下valgrind的使用。 下面的配置都是在我自己的服务器上测试，就是该博客运行的服务器上。 下载安装 wget http://valgr 

最近线上服务器安装了一些扩展，导致 php-fpm 内存增长过快，虽然可以通过配置最大请求数和定时脚本来重启 php-fpm ，但是也抱着学习折腾的精神来学习下valgrind的使用。 下面的配置都是在我自己的服务器上测试，就是该博客运行的服务器上。

## 下载安装

    wget http://valgrind.org/downloads/valgrind-3.12.0.tar.bz2
    tar -jxvf valgrind-3.12.0.tar.bz2
    cd valgrind-3.12.0
    ./autogen.sh
    ./configure
    make
    make intall
    

下载地址：[http://valgrind.org/downloads/current.html#current][0]  
可能需要升级automake和autoconf## 修改 php-fpm 启动命令

我我的脚本是/etc/init.d/php-fpm，需要做两个修改：在启动脚本中增加环境变量USE_ZEND_ALLOC=0以及将bin文件由原来的php-fpm文件修改为由valgrind启动，并将valgrind的日志重定向到日志文件中。

    + export USE_ZEND_ALLOC=0
    
    - php_fpm_BIN=${exec_prefix}/sbin/php-fpm
    + php_fpm_BIN="valgrind --leak-check=full --log-file=/data/log/valgrind-log-%p.log ${exec_prefix}/sbin/php-fpm"
    

## 重启 php-fpm

```shell
    [root@VM_132_97_centos log]# /etc/init.d/php-fpm restart
    Gracefully shutting down php-fpm . done
    Starting php-fpm  done
    [root@VM_132_97_centos log]#  ps afx|grep php-fpm.pid
    15694 pts/0    S+     0:00  |       \_ grep php-fpm.pid
    15677 ?        Ss     0:00 valgrind --log-file=/data/log/valgrind-log-%p.log /usr/local/php/sbin/php-fpm --daemonize --fpm-config /usr/local/php/etc/php-fpm.conf --pid /usr/local/php/var/run/php-fpm.pid
    15678 ?        S      0:00  \_ valgrind --log-file=/data/log/valgrind-log-%p.log /usr/local/php/sbin/php-fpm --daemonize --fpm-config /usr/local/php/etc/php-fpm.conf --pid /usr/local/php/var/run/php-fpm.pid
    15679 ?        S      0:00  \_ valgrind --log-file=/data/log/valgrind-log-%p.log /usr/local/php/sbin/php-fpm --daemonize --fpm-config /usr/local/php/etc/php-fpm.conf --pid /usr/local/php/var/run/php-fpm.pid
    15680 ?        S      0:00  \_ valgrind --log-file=/data/log/valgrind-log-%p.log /usr/local/php/sbin/php-fpm --daemonize --fpm-config /usr/local/php/etc/php-fpm.conf --pid /usr/local/php/var/run/php-fpm.pid
    15681 ?        S      0:00  \_ valgrind --log-file=/data/log/valgrind-log-%p.log /usr/local/php/sbin/php-fpm --daemonize --fpm-config /usr/local/php/etc/php-fpm.conf --pid /usr/local/php/var/run/php-fpm.pid
    15682 ?        S      0:00  \_ valgrind --log-file=/data/log/valgrind-log-%p.log /usr/local/php/sbin/php-fpm --daemonize --fpm-config /usr/local/php/etc/php-fpm.conf --pid /usr/local/php/var/run/php-fpm.pid
    15683 ?        S      0:00  \_ valgrind --log-file=/data/log/valgrind-log-%p.log /usr/local/php/sbin/php-fpm --daemonize --fpm-config /usr/local/php/etc/php-fpm.conf --pid /usr/local/php/var/run/php-fpm.pid
    [root@VM_132_97_centos log]# ll |grep valgrind
    -rw-r--r-- 1 root root      1090 12月  9 10:34 valgrind-log-15634.log
```

## 查看日志

```
    ==12378== Invalid read of size 8
    ==12378==    at 0x9CCCB5F: rndr_image (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CD859A: char_link (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CD64EF: parse_inline (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CD970A: parse_paragraph (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CDB500: parse_block (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CDC0ED: sd_markdown_render (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CCE690: php_sundown_markdon_render (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CCEE1B: zim_sundown_markdown_render (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x8124FF: zend_do_fcall_common_helper_SPEC (zend_vm_execute.h:550)
    ==12378==    by 0x811A97: execute_ex (zend_vm_execute.h:363)
    ==12378==    by 0xA0EFC5E: hp_execute_ex (xhprof.c:1664)
    ==12378==    by 0x812866: zend_do_fcall_common_helper_SPEC (zend_vm_execute.h:584)
    ==12378==  Address 0xd6ebc30 is 0 bytes inside a block of size 32 free'd
    ==12378==    at 0x4A07B16: free (vg_replace_malloc.c:529)
    ==12378==    by 0x9CCCB3D: rndr_image (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CD859A: char_link (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CD64EF: parse_inline (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CD970A: parse_paragraph (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CDB500: parse_block (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CDC0ED: sd_markdown_render (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CCE690: php_sundown_markdon_render (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CCEE1B: zim_sundown_markdown_render (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x8124FF: zend_do_fcall_common_helper_SPEC (zend_vm_execute.h:550)
    ==12378==    by 0x811A97: execute_ex (zend_vm_execute.h:363)
    ==12378==    by 0xA0EFC5E: hp_execute_ex (xhprof.c:1664)
    ==12378==  Block was alloc'd at
    ==12378==    at 0x4A0813C: malloc (vg_replace_malloc.c:298)
    ==12378==    by 0x9CCC9F5: rndr_image (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CD859A: char_link (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CD64EF: parse_inline (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CD970A: parse_paragraph (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CDB500: parse_block (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CDC0ED: sd_markdown_render (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CCE690: php_sundown_markdon_render (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x9CCEE1B: zim_sundown_markdown_render (in /usr/local/php/lib/php/extensions/no-debug-non-zts-20121212/sundown.so)
    ==12378==    by 0x8124FF: zend_do_fcall_common_helper_SPEC (zend_vm_execute.h:550)
    ==12378==    by 0x811A97: execute_ex (zend_vm_execute.h:363)
    ==12378==    by 0xA0EFC5E: hp_execute_ex (xhprof.c:1664)
```

[0]: http://valgrind.org/downloads/current.html?spm=5176.100239.blogcont66127.9.XmHMl1#current