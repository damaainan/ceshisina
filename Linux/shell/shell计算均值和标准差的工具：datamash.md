# shell计算均值和标准差的工具：datamash

 [首页][0]  [分类][1]  [标签][2]  [留言][3]  [关于][4]  [订阅][5]  2016-05-08 | 分类 [linux][6] | 标签 [linux][7] ## 前言

- - -

shell下经常需要处理数据，需要计算均值和标准差，最近在分析EXT4的r_await的分布情况，需要计算下在一定的读写模式下，块设备的读延迟分布。 这已不是第一次有类似的需求了，每次都要awk写一坨处理脚本，感觉不爽。

其实我要的东西比较简单， 就是读取多笔记录，通过管道传递给一个工具，该工具就可以将指定字段的均值和标准差计算出来。

    ...
    
    sdb               0.00     0.00   21.00   93.00     4.02    22.88   483.23     0.31    2.70    9.52    1.16   0.88  10.00
    sdb               0.00     0.00   56.00   64.00    12.03    16.00   478.40     1.91   15.90   32.57    1.31   3.67  44.00
    sdb               0.00     0.00   55.00  100.00    12.03    25.00   489.24     0.78    5.01   11.42    1.48   1.45  22.40
    sdb               0.00     0.00   20.00   87.00     4.02    21.52   488.75     0.34    3.18   11.40    1.29   0.86   9.20
    sdb               0.00   166.00   34.00    4.00     8.01     0.66   467.37     0.22    5.79    6.47    0.00   1.58   6.00
    sdb               0.00     0.00   42.00  176.00     8.04    35.61   410.06     0.55    2.51   10.67    0.57   0.70  15.20
    sdb               0.00     0.00   22.00  128.00     4.02    32.00   491.84     0.50    3.07   13.09    1.34   1.07  16.00
    sdb               0.00     0.00   21.00   68.00     4.02    17.00   483.69     0.51    5.89   20.38    1.41   1.26  11.20
    sdb               0.00     0.00   36.00   64.00     8.02    16.00   491.84     0.49    4.92   10.56    1.75   1.20  12.00
    sdb               0.00   191.00   26.00  424.00     5.02    18.24   105.87     1.63    3.40   25.23    2.07   1.29  58.00
    sdb               0.00     0.00   30.00    9.00     7.01     2.25   486.15     0.32    9.23   11.33    2.22   1.64   6.40
    sdb               0.00     0.00    5.00  173.00     0.02    42.75   492.05     1.06    6.04  176.80    1.11   3.53  62.80
    sdb               0.00     0.00   37.00    4.00     8.02     1.00   450.37     0.38    9.17   10.16    0.00   2.34   9.60
    sdb               0.00     0.00   36.00   97.00     8.02    16.12   371.73     0.60    4.51   11.56    1.90   0.84  11.20
    sdb               0.00   163.00    0.00  155.00     0.00    38.40   507.41     0.05    0.34    0.00    0.34   0.03   0.40
    sdb               0.00     0.00   55.00  126.00    12.03    31.25   489.72     0.72    3.98   11.27    0.79   1.06  19.20
    sdb               0.00     0.00   34.00    0.00     8.01     0.00   482.35     0.39   11.41   11.41    0.00   2.12   7.20
    sdb               0.00     0.00   19.00   84.00     4.01    21.00   497.32     0.28    2.72   12.84    0.43   0.58   6.00
    sdb               0.00     0.00   54.00  112.00    12.02    28.00   493.78     1.52    9.16   26.96    0.57   1.71  28.40
    
    ...
    

上述内容是iostat -mx /dev/sd[bc] 1观察一段时间的输出，其中grep sdb 的部分，我关心的内容是第11列，即r_await的均值和方差。

    Device:         rrqm/s   wrqm/s     r/s     w/s    rMB/s    wMB/s avgrq-sz avgqu-sz   await r_await w_await  svctm  %util
    

一直用awk处理，但是总觉的不方便，不能随心所欲，下一次还的写awk，本来想自己写一个tool来计算均值和方差，但是今天搜了一下，发现了宝贝 datamash，这个工具是GNU的，Linux和Mac都有这个工具

## datamash 安装

安装的话，可以源码安装，也可以apt安装：

    wget http://ftp.gnu.org/gnu/datamash/datamash-1.1.0.tar.gz
    tar -xzf datamash-1.1.0.tar.gz
    cd datamash-1.1.0
    ./configure
    make
    make check
    sudo make install
    

    wget http://files.housegordon.org/datamash/bin/datamash_1.0.6-1_amd64.deb
    sudo dpkg -i datamash_1.0.6-1_amd64.deb
    

本来应该是apt-get install datamash，但是好像源里面没有这个包，Mac比较简单，

    brew install datamash
    

## datamash使用

还是我们的例子，有了这个工具，我们就可以计算均值和方差了：

    root@node4:~/bean# grep sdb n_186.80  | datamash -W mean 11 sstdev 11
    20.843  30.743196441241
    

注意啊，一个或者多个空格，如果需要被当成单个空格来对待，就必须加上-W选项，我开始也吃了这个亏，如下：

    root@node4:~/bean# grep sdb n_186.80  |awk '{print $11}' |datamash mean 1 sstdev 1
    20.843  30.743196441241
    root@node4:~/bean# grep sdb n_186.80  |datamash mean 11 sstdev 11
    datamash: invalid numeric input in line 1 field 11: ''
    root@node4:~/bean#
    

之所以报这个错误，就是因为没有-W选项，导致多个空格并没有当成一个独立的分隔符。

当然了，可以通过 -t选项来指定分隔符。

    printf '1,10,,100\n' | datamash -t, sum 4
    100
    

事实上，datamash的能力远不止于此，我们都用过数据库，知道SQL中有group by的功能，datamash 也有类似 的功能：

    $ cat scores.txt
    Name        Subject          Score
    Bryan       Arts             68
    Isaiah      Arts             80
    Gabriel     Health-Medicine  100
    Tysza       Business         92
    Zackery     Engineering      54
    
    ...
    

注意第二列是科目名称，我们可以计算不同科目的平均分和标准差：

    $ datamash --sort --headers --group 2 mean 3 sstdev 3 < scores.txt
    GroupBy(Subject)   mean(Score)   sstdev(Score)
    Arts               68.9474       10.4215
    Business           87.3636       5.18214
    Engineering        66.5385       19.8814
    Health-Medicine    90.6154       9.22441
    Life-Sciences      55.3333       20.606
    Social-Sciences    60.2667       17.2273
    

出了均值和标准差，还可获取最大最小

* sum sum the of values
* min minimum value
* max maximum value
* absmin minimum of the absolute values
* absmax maximum of the absolute values

更多高阶的用法就参考[官方文档][8]吧

[0]: http://bean-li.github.io/
[1]: http://bean-li.github.io/categories/
[2]: http://bean-li.github.io/tags/
[3]: http://bean-li.github.io/guestbook/
[4]: http://bean-li.github.io/about/
[5]: http://bean-li.github.io/feed/
[6]: http://bean-li.github.io/categories/#linux
[7]: http://bean-li.github.io/tags/#linux
[8]: https://www.gnu.org/software/datamash/manual/datamash.html#Field-Delimiters