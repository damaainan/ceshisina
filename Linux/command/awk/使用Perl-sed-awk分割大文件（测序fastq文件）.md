## 使用Perl/sed/awk分割大文件（测序fastq文件）

来源：[http://www.bioinfo-scrounger.com/archives/582](http://www.bioinfo-scrounger.com/archives/582)

时间 2018-06-08 23:14:02


之前一段时间一直在用R，最近刚转变过来时差点都不会写了Perl了（有时觉得R的向量思维真的很棒！应用性极强~）

刚好遇到一个问题需要解决：将4000000行的测序fastq序列分割成10份，强行用Perl写了下。。下面是一些解决思路（代码比较简单，就不做注释了）

先用思路最简单的perl写法：读一行，打开写入句柄，print后关闭句柄，然后继续读下一行，那么代码如下：

```
#!/usr/bin/perl -w
use strict;

my $in  = shift @ARGV;

my $total_num = `wc -l < $in`;  #读取文件总行数
my $thread_counts = 10;         #需要分割成10份文件
my $size = int($total_num / ($thread_counts * 4)) + 1; #每个文件的大致行数

open my $fh, $in or die;
my $number = 1;
while (<$fh>){
    chomp;
    if ($. % ($size * 4 + 1) != 0){
        my $outfile = $in.".$number.tmp";
        open FILE, ">$outfile" or die;
        print FILE "$_\n";
        close FILE;
    }else{
        $number++;
    }
}
close $fh;
```

这种方法思路无脑，但效率及其低下！（等了好久。。）可以看看用时：

```
real    74m21.413s
user    4m26.035s
sys     63m57.729s
```

按照以前的经验来看，是由于每次打开关闭句柄浪费了过多的时间。接着是换一个思路：先打开多个句柄（减少每行都打开的所浪费的时间），然后再读一行，写一行，那么代码如下：

```
#!/usr/bin/perl -w
use strict;

my $in  = shift @ARGV;

my $total_num = `wc -l < $in`;
my $thread_counts = 10;
my $size = int($total_num / ($thread_counts * 4)) + 1;

my %handles;
foreach(1..$thread_counts){
    my $outfile = $in.".$_.tmp";
    open $handles{$_}, ">$outfile" or die;
}

open my $fh, $in or die;
my $number = 1;
while (<$fh>){
    chomp;
    if ($. % ($size * 4) != 0){
        print {$handles{$number}} "$_\n";
    }else{
        print {$handles{$number}} "$_\n";
        $number++;
    }
}
close $fh;
```

这代码刚开始写的时候遇到一个问题，由于用了`use strict`，所以Perl不允许`string`字符串作为句柄；比如我刚开始就是将OUT1,OUT2等字符型变量作为句柄，则报错：Can’t use string (“OUT1”) as a symbol ref while “strict refs”；后来在生信技能树Jimmy提醒下用了哈希来代替，如上代码所示；在`print`句柄时，记得用`{}`将句柄括起来，不然就像    [http://www.biotrainee.com/thread-1329-1-1.html][0]
中的`$fh{$F[0]}->print( "$_\n" )`这个方法相比上面那种时间快了很多

```
real    0m7.225s
user    0m4.819s
sys     0m2.040s
```

400万行用了7s，其实并不快，如果当测序数据有1亿行时则相当于需要3min，那么再换个bash脚本中调用sed命令来看看能否再快一点

```
#!/bin/bash

file=$1
count=$2

line=`wc -l <$file`
size=$((($line / ($count * 4) + 1) * 4))
for i in `seq 1 $count`;do
    s=$((($i-1)*$size+1))
    t=$(($i*$size))
    sed -n "${s}, ${t}p" $file >$file.$i.tmp
done
```

sed方法用时：

```
real    0m3.608s
user    0m2.930s
sys     0m0.463s
```

速度几乎提升了一倍，应该快了不少了！再看看awk的，一直都不怎么用awk，对其书写方式很不习惯，模仿了下网上的写法

```
#!/bin/bash

file=$1
count=$2

line=`wc -l <$file`
size=$((($line / ($count * 4) + 1) * 4))
awk -v sz=$size 'BEGIN{i=1}{ print $0 > FILENAME "." i ".tmp"; if (NR>=i*sz){close(FILENAME "." i ".tmp");i++}}' $file
```

awk方法用时：

```
real    0m2.080s
user    0m1.715s
sys     0m0.293s
```

结果很明显，以上几种中awk的速度算是相对最好的了~但是在不讲究速度情况下，我还是喜欢perl。。。

本文出自于    [http://www.bioinfo-scrounger.com][1]
转载请注明出处



[0]: http://www.biotrainee.com/thread-1329-1-1.html
[1]: http://www.bioinfo-scrounger.com