# PHP伪随机数与真随机数 

* 2011/10/15
* [php][0], [伪随机数][1], [函数][2], [真随机数][3]
* [技术延伸][4]
* [评论][5]
* 5,751

首先需要声明的是，计算机不会产生绝对随机的随机数，计算机只能产生“伪随机数”。其实绝对随机的随机数只是一种理想的随机数，即使计算机怎样发展，它也不会产生一串绝对随机的随机数。计算机只能生成相对的随机数，即伪随机数。

伪随机数并不是假随机数，这里的“伪”是有规律的意思，就是计算机产生的伪随机数既是随机的又是有规律的。怎样理解呢？产生的伪随机数有时遵守一定的规律，有时不遵守任何规律；伪随机数有一部分遵守一定的规律；另一部分不遵守任何规律。比如“世上没有两片形状完全相同的树叶”，这正是点到了事物的特性，即随机性，但是每种树的叶子都有近似的形状，这正是事物的共性，即规律性。从这个角度讲，你大概就会接受这样的事实了：计算机只能产生伪随机数而不能产生绝对随机的随机数。

首先来了解一下真随机数和伪随机数的概念。

**真随机数发生器：**英文为：true random number generators ，简称为：TRNGs，是利用不可预知的物理方式来产生的随机数。

**伪随机数发生器：**英文为：pseudo-random number generators ，简称为：PRNGs，是计算机利用一定的算法来产生的。

对比一下两种办法产生的随机数的图片。

Random.org(利用大气噪音来生成随机数,而大气噪音是空气中的雷暴所产生的 )生成的随机位图：

[![伪随机和真随机](http://www.linuxde.net/wp-content/uploads/2011/10/randbitmap_true-300x300.png "伪随机和真随机")](http://www.linuxde.net/wp-content/uploads/2011/10/randbitmap_true.png)

Windows下 [PHP][0] 的rand()函数产生的随机图片：

[![伪随机和真随机](http://www.linuxde.net/wp-content/uploads/2011/10/randbitmap_computer-300x300.png "伪随机和真随机")](http://www.linuxde.net/wp-content/uploads/2011/10/randbitmap_computer.png)

很显然，后者伪随机数发生器产生的图片有这明显的条纹。

利用 php 的rand随机函数产生这张图片的代码为：

```php
    //需要开启gd库
    header("Content-type: image/png");
    $im = imagecreatetruecolor(512, 512)
    or die("Cannot Initialize new GD image stream");
    $white = imagecolorallocate($im, 255, 255, 255);
    for ($y=0; $y<512; $y++) {
        for ($x=0; $x<512; $x++) {
            if (rand(0,1) === 1) {
                imagesetpixel($im, $x, $y, $white);
            }
        }
    }
    imagepng($im);
    imagedestroy($im);
```

实际上也并不是所有的伪随机数发生器(PRNGs)效果都这么差的，只是恰好在Windows下的PHP的rand()函数是这样。如果是在 Linux 下 测试相同的代码的话，所产生的图片也看不出明显的条纹。在Windows下如果用mt_rand()函数替代rand()函数的话效果也会好很多。这是由 于mt_rand()用了Mersenne Twister(马其塞旋转)算法来产生随机数。PHP的文档还说：mt_rand() 可以产生随机数值的平均速度比 libc 提供的 rand() 快四倍。

另外，Linux内核（1.3.30以上）包括了一个随机数发生器/dev/random ，对于很多安全目的是足够的。

**下面是关于Linux的随机数发生器的原理介绍 ：**

Linux 操作系统提供本质上随机（或者至少具有强烈随机性的部件）的库数据。这些数据通常来自于设备驱动程序。例如，键盘驱动程序收集两个按键之间时间的信息，然后将这个环境噪声填入随机数发生器库。

随机数据存储在 熵池 ( linux内核维护了一个熵池用来收集来自设备驱动程序和其它来源的环境噪音。理论上，熵池中的数据是完全随机的，可以实现产生真随机数序列。为跟踪熵池中数据的随 机性，内核在将数据加入池的时候将估算数据的随机性，这个过程称作熵估算。熵估算值描述池中包含的随机数位数，其值越大表示池中数据的随机性越好。 ) 中，它在每次有新数据进入时进行“搅拌”。这种搅拌实际上是一种数学转换，帮助提高随机性。当数据添加到熵池中 后，系统估计获得了多少真正随机位。

测定随机性的总量是很重要的。问题是某些量往往比起先考虑时看上去的随机性小。例如，添加表示自从上次按键盘以来秒数的 32 位数实际上并没有提供新的 32 位随机信息，因为大多数按键都是很接近的。

从 /dev/random 中读取字节后，熵池就使用 MD5 算法进行密码散列，该散列中的各个字节被转换成数字，然后返回。

如果在熵池中没有可用的随机性位， /dev/random 在池中有足够的随机性之前等待，不返回结果。这意味着如果使用 /dev/random 来产生许多随机数，就会发现它太慢了，不够实用。我们经常看到 /dev/random 生成几十字节的数据，然后在许多秒内都不产生结果。

幸运的是有熵池的另一个接口可以绕过这个限制：/dev/urandom。即使熵池中没有随机性可用，这个替代设备也总是返回随机数。如果您取出许 多数而不给熵池足够的时间重新充满，就再也不能获得各种来源的合用熵的好处了；但您仍可以从熵池的 MD5 散列中获得非常好的随机数！这种方式的问题是，如果有任何人破解了 MD5 算法，并通过查看输出了解到有关散列输入的信息，那么您的数就会立刻变得完全可预料。大多数专家都认为这种分析从计算角度来讲是不可行的。然而，仍然认为 /dev/urandom 比 /dev/random 要“不安全一些”（并通常值得怀疑）。

Windows下没有/dev/random可用，但可以使用微软的“capicom.dll”所提供的CAPICOM.Utilities 对象。

以下是使用PHP时比用mt_rand()函数产生更好的伪随机数的一段例子代码：

```php
    <?php
    // get 128 pseudorandom bits in a string of 16 bytes
    
    $pr_bits = '';
    
    // Unix/Linux platform?
    $fp = @fopen('/dev/urandom','rb');
    if ($fp !== FALSE) {
        $pr_bits .= @fread($fp,16);
        @fclose($fp);
    }
    
    // MS-Windows platform?
    if (@class_exists('COM')) {
        try {
            $CAPI_Util = new COM('CAPICOM.Utilities.1');
            $pr_bits .= $CAPI_Util->GetRandom(16,0);
    
            // if we ask for binary data PHP munges it, so we
            // request base64 return value. We squeeze out the
            // redundancy and useless ==CRLF by hashing...
            if ($pr_bits) { 
                $pr_bits = md5($pr_bits,TRUE); 
            }
        } catch (Exception $ex) {
            // echo 'Exception: ' . $ex->getMessage();
        }
    }
    
    if (strlen($pr_bits) < 16) {
        // do something to warn system owner that
        // pseudorandom generator is missing
    }
```

所以PHP要产生真随机数 还是要调用外部元素来支持的！

[0]: http://www.linuxde.net/tag/php
[1]: http://www.linuxde.net/tag/%e4%bc%aa%e9%9a%8f%e6%9c%ba%e6%95%b0
[2]: http://www.linuxde.net/tag/%e5%87%bd%e6%95%b0
[3]: http://www.linuxde.net/tag/%e7%9c%9f%e9%9a%8f%e6%9c%ba%e6%95%b0
[4]: http://www.linuxde.net/category/technical_extension
[5]: http://www.linuxde.net/2011/10/1141.html#respond
