## 查看php-fpm内存占用命令



查看PHP-FPM内存占用的几个有用小命令，记录如下：

## 1.查看每个FPM的内存占用：

    ps -ylC php-fpm --sort:rss

当然，在后后面加 `| wc -l`可查看系统当前FPM总进程数

单个进程占用23M内存大小；

    # ps -ylC php-fpm --sort:rss

    S UID PID PPID C PRI NI RSS SZ WCHAN TTY TIME CMD
    
    S 0 627 1 0 80 0 848 6205 ep_pol ? 00:01:09 php-fpm
    
    S 501 6685 627 0 80 0 23392 10858 skb_re ? 00:01:21 php-fpm
    
    S 501 6684 627 0 80 0 23536 10808 skb_re ? 00:01:17 php-fpm
    
    S 501 6915 627 0 80 0 24752 10911 skb_re ? 00:01:12 php-fpm
    
    # ps -ylC php-fpm --sort:rss | wc -l
    
    5

## 2.查看PHP-FPM在你的机器上的平均内存占用：

**命令如下：**

    ps --no-headers -o "rss,cmd" -C php-fpm | awk '{ sum+=$1 } END { printf ("%d%s\n", sum/NR/1024,"M") }'

**平均内存为17M大小；**

    # ps --no-headers -o "rss,cmd" -C php-fpm | awk '{ sum+=$1 } END { printf ("%d%s\n", sum/NR/1024,"M") }'

    17M


