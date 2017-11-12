<?php

/*Bloom Filter算法来去重过滤。

介绍下Bloom Filter的基本处理思路：申请一批空间用于保存0 1信息，再根据一批哈希函数确定元素对应的位置，如果每个哈希函数对应位置的值为全部1，说明此元素存在。相反，如果为0，则要把对应位置的值设置为1。由于不同的元素可能会有相同的哈希值，即同一个位置有可能保存了多个元素的信息，从而导致存在一定的误判率。

如果申请空间太小，随着元素的增多，1会越来越多，各个元素冲突的机会越来越来大，导致误判率会越来越大。另外哈希函数的选择及个数上也要平衡好，多个哈希函数虽然可以提供判断的准确性，但是会降低程序的处理速度，而哈希函数的增加又要求有更多的空间来存储位置信息。

Bloom-Filter的应用。
Bloom-Filter一般用于在大数据量的集合中判定某元素是否存在。例如邮件服务器中的垃圾邮件过滤器。在搜索引擎领域，Bloom-Filter最常用于网络蜘蛛(Spider)的URL过滤，网络蜘蛛通常有一个 URL列表，保存着将要下载和已经下载的网页的URL，网络蜘蛛下载了一个网页，从网页中提取到新的URL后，需要判断该URL是否已经存在于列表中。此时，Bloom-Filter算法是最好的选择。
比如说，一个象 Yahoo,Hotmail 和 Gmai 那样的公众电子邮件（email）提供商，总是需要过滤来自发送垃圾邮件的人（spamer）的垃圾邮件。一个办法就是记录下那些发垃圾邮件的 email 地址。由于那些发送者不停地在注册新的地址，全世界少说也有几十亿个发垃圾邮件的地址，将他们都存起来则需要大量的网络服务器。

布隆过滤器是由巴顿.布隆于一九七零年提出的。它实际上是一个很长的二进制向量和一系列随机映射函数。我们通过上面的例子来说明起工作原理。

假定我们存储一亿个电子邮件地址，我们先建立一个十六亿二进制（比特），即两亿字节的向量，然后将这十六亿个二进制位全部设置为零。对于每一个电子邮件地址 X，我们用八个不同的随机数产生器（F1,F2, ...,F8） 产生八个信息指纹（f1, f2, ..., f8）。再用一个随机数产生器 G 把这八个信息指纹映射到 1 到十六亿中的八个自然数 g1, g2, ...,g8。现在我们把这八个位置的二进制位全部设置为一。当我们对这一亿个 email 地址都进行这样的处理后。一个针对这些 email 地址的布隆过滤器就建成了。（见下图） 现在，让我们看看如何用布隆过滤器来检测一个可疑的电子邮件地址 Y 是否在黑名单中。我们用相同的八个随机数产生器（F1, F2, ..., F8）对这个地址产生八个信息指纹 s1,s2,...,s8，然后将这八个指纹对应到布隆过滤器的八个二进制位，分别是 t1,t2,...,t8。如果 Y 在黑名单中，显然，t1,t2,..,t8 对应的八个二进制一定是一。这样在遇到任何在黑名单中的电子邮件地址，我们都能准确地发现。
布隆过滤器决不会漏掉任何一个在黑名单中的可疑地址。但是，它有一条不足之处。也就是它有极小的可能将一个不在黑名单中的电子邮件地址判定为在黑名单中，因为有可能某个好的邮件地址正巧对应八个都被设置成一的二进制位。好在这种可能性很小。我们把它称为误识概率。在上面的例子中，误识概率在万分之一以下。
布隆过滤器的好处在于快速，省空间。但是有一定的误识别率。常见的补救办法是在建立一个小的白名单，存储那些可能别误判的邮件地址。

 */

// 使用php程序来描述上面的算法

$set = array(1, 2, 3, 4, 5, 6);
// 判断5是否在$set 中

$bloomFiter = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

// 通过某种算法改变$bloomFiter 中位数组表示集合,这里我们使用简单的算法,把集合中对应的value 对应到bloom中的位置变成1

// 算法如下

foreach ($set as $key) {

    $bloomFiter[$key] = 1;
}

var_dump($bloomFiter);

//此时 $bloomFiter = array(1,1,1,1,1,1);

//判断是否在集合中

if ($bloomFiter[9] == 1) {
    echo '在set 中';
} else {
    echo '不在set 中';
}

// 上面只是一个简单的例子,实际上哈希算法需要好几个,但另一方面，如果哈希函数的个数少，那么位数组中的0就多

class BloomFilter
{

    public function __construct($hash_func_num = 1, $space_group_num = 1)
    {
        $max_length = pow(2, 25);
        $binary     = pack('C', 0);

        //1字节占用8位
        $this->one_num = 8;

        //默认32m*1
        $this->space_group_num  = $space_group_num;
        $this->hash_space_assoc = array();

        //分配空间
        for ($i = 0; $i < $this->space_group_num; $i++) {
            $this->hash_space_assoc[$i] = str_repeat($binary, $max_length);
        }

        $this->pow_array = array(
            0 => 1,
            1 => 2,
            2 => 4,
            3 => 8,
            4 => 16,
            5 => 32,
            6 => 64,
            7 => 128,
        );
        $this->chr_array = array();
        $this->ord_array = array();
        for ($i = 0; $i < 256; $i++) {
            $chr                   = chr($i);
            $this->chr_array[$i]   = $chr;
            $this->ord_array[$chr] = $i;
        }

        $this->hash_func_pos = array(
            0 => array(0, 7, 1),
            1 => array(7, 7, 1),
            2 => array(14, 7, 1),
            3 => array(21, 7, 1),
            4 => array(28, 7, 1),
            5 => array(33, 7, 1),
            6 => array(17, 7, 1),
        );

        $this->write_num = 0;
        $this->ext_num   = 0;

        if (!$hash_func_num) {
            $this->hash_func_num = count($this->hash_func_pos);
        } else {
            $this->hash_func_num = $hash_func_num;
        }
    }

    public function add($key)
    {
        $hash_bit_set_num = 0;
// 离散key
        $hash_basic = sha1($key);
//  截取前4位,然后十六进制转换为十进制
        $hash_space = hexdec(substr($hash_basic, 0, 4));
//  取模
        $hash_space = $hash_space % $this->space_group_num;

        for ($hash_i = 0; $hash_i < $this->hash_func_num; $hash_i++) {
            $hash          = hexdec(substr($hash_basic, $this->hash_func_pos[$hash_i][0], $this->hash_func_pos[$hash_i][1]));
            $bit_pos       = $hash >> 3;
            $max           = $this->ord_array[$this->hash_space_assoc[$hash_space][$bit_pos]];
            $num           = $hash - $bit_pos * $this->one_num;
            $bit_pos_value = ($max >> $num) & 0x01;
            if (!$bit_pos_value) {
                $max                                           = $max | $this->pow_array[$num];
                $this->hash_space_assoc[$hash_space][$bit_pos] = $this->chr_array[$max];
                $this->write_num++;
            } else {
                $hash_bit_set_num++;
            }
        }
        if ($hash_bit_set_num == $this->hash_func_num) {
            $this->ext_num++;
            return true;
        }
        return false;
    }

    public function getStat()
    {
        return array(
            'ext_num'   => $this->ext_num,
            'write_num' => $this->write_num,
        );
    }
}

//test
//取6个哈希值，目前是最多7个
$hash_func_num = 6;

//分配1个存储空间，每个空间为32M，理论上是空间越大误判率越低，注意php.ini中可使用的内存限制
$space_group_num = 1;

$bf = new bloom_filter($hash_func_num, $space_group_num);

$list = array(
    'http://test/1',
    'http://test/2',
    'http://test/3',
    'http://test/4',
    'http://test/5',
    'http://test/6',
    'http://test/1',
    'http://test/2',
);
foreach ($list as $k => $v) {

    if ($bf->add($v)) {
        echo $v, "\n";
    }
}
print_r($bf->getStat());
