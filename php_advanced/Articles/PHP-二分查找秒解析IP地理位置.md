# PHP-二分查找秒解析IP地理位置

 时间 2017-12-04 10:32:20  

原文[http://www.jianshu.com/p/a8f75501d2e3][1]


通过二分查找的方法，我们可以在1秒内从19万条IP信息中找到我们所需要的IP，这大大地提高了查找速度。   
资料准备：CHINA_IP_INFO.txt (这里面包含了中国所有的IP网段和实际地理位置的关系，可以从 [yoooknight.github][4] 下载）   
参考文章：   
[PHP实现文本快速查找 - 二分查找法][5]

设计思路 

第一步、需要将IP转换成一个整数类型

第二步、根据IP文件(CHINA_IP_INFO.txt)生成一个索引文件

索引文件分成3部分：

![][6]

1. head:存储IP索引的起始偏移量和结束偏移量：起始偏移量和结束偏移量各占4byte，共8byte
1. data:存储IP的详细信息，长度根据CHINA_IP_INFO的具体数据大小决定，结束时用`\x00`提示
1. index:索引信息，起始IP的值和详细信息的位置偏移量，各占4个字节，共8个字节

第三步、使用二分查找法对比查找IP和IP文件中的IP范围

根据第二步中存储的索引文件，查询顺序应该是：`head->index->data` 

1. 就是说在查询的时候先会查询 **head** 中的内容，获取到起始和结束索引的位置偏移量
1. 然后根据这个偏移量读取到IP的索引，再通过查询ip和起始IP进行对比
1. 如果成功匹配就可以根据index中的位置偏移量读取具体的ip信息。

二分查找 

又被成为折半查找,它充分利用了元素间的次序关系，采用分治策略，可在最坏的情况下用`O(log n)`完成搜索任务。它的基本思想是，将n个元素分成个数大致相同的两半，取`a[n/2]`与欲查找的x作比较，如果`x=a[n/2]`则找到x，算法终止。

在这里我们有一点不同，就是我们查找的是一个范围，而不是固定的值，也就是说，我们这里的判断条件应该是先比较是否大于起始IP，再比较是否小于结束IP

流程图如下：

![][7]


完整代码如下：
```php
<?php
/**
 * Created by PhpStorm.
 * User: WangSong
 * Date: 2017/11/27 0027
 * Time: 15:02
 */

error_reporting(0);

class SearchIP
{
    private $forceReForm        = False; /* 是否强制重新生成索引文件 */
    private $filename           = "/var/caploudrc/rmsc/CHINA_IP_INFO.txt"; /* 保存IP地址的文件 */
    private $head               = array(); /* 0起始IP的文件开始位置，1起始IP的文件结束位置 */
    private $index              = array(); /* ... */
    private $data               = array(); /* 数据信息的位置 */
    private $start_data_offset  = 8; /* 起始偏移量 */
    private $index_len          = 0; /* 索引长度 */
    CONST  READ_64bit_OFFSET    = 9;
    CONST  READ_32bit_OFFSET    = 5;
    CONST  EVERY_INDEX_OFFSET   = 8;

    /**
     * @description 初始化文件
     *
     * @param $filename
     * @param $forceReForm
     */
    public function __construct($filename = "", $forceReForm = False)
    {
        self::SP_debug("construct", "1.     开始初始化脚本...");
        # 变量赋值
        $this->filename = empty($filename) ? $this->filename : $filename;
        $this->forceReForm = $forceReForm;
        $this->formatFile = dirname(__FILE__) ."/" . md5($this->filename);

        # 若强制重新生成索引标志为真或者不存在索引文件，则重新生成
        if ($this->forceReForm || !file_exists($this->formatFile)) {
            $this->formatFile();
        }
    }

    /**
     * @description  格式化文件
     *
     */
    private function formatFile()
    {
        self::SP_debug("formatFile", "1.1    正在重新生成索引文件...");
        //读源文件，写入到新的索引文件
        $readfd = fopen($this->filename, 'rb');
        $writefd = fopen($this->formatFile.'_tmp', 'wb+');
        if ($readfd === false || $writefd === false) {
            return false;
        }

        while (!feof($readfd)) {
            $line = fgets($readfd);
            if (empty($line))  continue;
            $line_items = explode("\t", $line);

            # 将起始IP转换为数字
            if (preg_match('/\d+\.\d+\.\d+\.\d+/', $line_items[0])){
                $start_ip = $this->ip2int($line_items[0]);
            } else {
                $start_ip = intval($line_items[0]);
            }

            # 将结束IP转换为数字
            if (preg_match('/\d+\.\d+\.\d+\.\d+/', $line_items[1])){
                $line_items[1] = pack("L", $this->ip2int($line_items[1]));
            } else {
                $line_items[1] = pack("L", intval($line_items[1]));
            }

            # 删除起始IP
            unset($line_items[0]);

            # 1. 构造索引内容ip+该ip对应数据所存储的偏移量
            # 2. 头索引：索引内容 的偏移量，所以每次起始的数据偏移量要增加数据的长度
            $tmp_index_offset = pack("LL",$start_ip,$this->start_data_offset); # 8byte

            $this->index_len = $this->index_len + strlen($tmp_index_offset);
            array_push($this->index, $tmp_index_offset);

            $tmp_data = implode("\t", $line_items) . '\x00';
            array_push($this->data, $tmp_data);
            $this->start_data_offset = $this->start_data_offset + strlen($tmp_data);
        }

        self::SP_debug("formatFile", "1.2    索引头部开始生成...");
        array_push($this->head, pack("L", $this->start_data_offset));
        array_push($this->head, pack("L", $this->index_len + $this->start_data_offset - 8));

        # 将数据写到临时文件中
        self::SP_debug("formatFile", "1.3    开始写入索引头...");
        $this->write_array($writefd, $this->head);
        self::SP_debug("formatFile", "1.4    开始写入数据...");
        $this->write_array($writefd, $this->data);
        self::SP_debug("formatFile", "1.5    开始写入数据索引...");
        $this->write_array($writefd, $this->index);

        echo "\n reformat ok\n";
        fclose($readfd);
        fclose($writefd);
        rename($this->formatFile.'_tmp', $this->formatFile);

        self::SP_debug("formatFile", "1.6    索引文件已经生成...");
        return True;
    }

    /**
     * @description 查找文件
     *
     * @param string $ip
     *
     * @return bool
     */
    public function search($ip = "")
    {
        self::SP_debug("search", "2.     开始查找IP信息...");
        $output = array("valid"=>False, "info"=>array(), "error_msg"=>"");
        $fd = fopen($this->formatFile, "rb");
        $search_int_ip = $this->ip2int($ip);

        self::SP_debug("search", "2.1    开始读取索引文件的头信息...");
        # 1. 读取head里面的偏移量信息
        $head = unpack("Lleft/Lright", fgets($fd, 9));
        $left = $head['left'];
        $right = $head['right'];

        while ($left <= $right) {
            # 计算索引个数
            $index_count = ($right-$left+self::EVERY_INDEX_OFFSET)/self::EVERY_INDEX_OFFSET;
            $index_middle = intval($index_count/2) < 1 ? 1 : intval($index_count/2);
            $offset_middle = $left + ($index_middle - 1) * self::EVERY_INDEX_OFFSET;

            self::SP_debug("search", "2.1.1    当前还剩下{$index_count}个节点IP信息...");
            self::SP_debug("search", "2.1.2    当前将要二分查询第{$index_middle}个节点IP信息...");


            fseek($fd, $offset_middle, SEEK_SET);
            # 获取起始IP和详细信息的偏移量
            # 在这里读取的时候要用fread,fget读取会出错
            $info = unpack("Ltmp_ip/Ltmp_offset", fread($fd, self::READ_64bit_OFFSET));
            $start_ip = $info['tmp_ip'];
            self::SP_debug("search", "2.1.3    读取该节点IP信息的起始IP：{$start_ip}-" . $this->int2ip($start_ip));

            fseek($fd, $info['tmp_offset'], SEEK_SET);
            # 读取结束IP的值
            fseek($fd, $info['tmp_offset'], SEEK_SET);
            $end_ip = unpack("Lip", fgets($fd, self::READ_32bit_OFFSET))['ip'];
            self::SP_debug("search", "2.1.4    读取该节点IP信息的结束IP：{$end_ip}-" . $this->int2ip($end_ip));

            $info_detail= fgets($fd);
            if ($search_int_ip < $start_ip) {
                self::SP_debug("search", "2.1.5    查询IP在节点IP的左边\n");
                $right = $offset_middle - self::EVERY_INDEX_OFFSET;
            } elseif ($search_int_ip > $end_ip){
                self::SP_debug("search", "2.1.5    查询IP在节点IP的右边\n");
                $left = $offset_middle + self::EVERY_INDEX_OFFSET;
            } else {
                self::SP_debug("search", "2.1.5    查询IP存在于该节点IP中\n");
                $output['valid'] = True;
                $output['info'] = explode("\t", $info_detail);
                $output['info'][count($output['info']) - 1] =
                    trim($output['info'][count($output['info']) - 1], PHP_EOL);
                unset($output['info'][0]);
                goto final_out;
            }
        }

        fclose($fd);
        $output['valid'] = False;
        $output['info'] = "NO IP FOUND";
        self::SP_debug("search", "2.2.5    没有查询到该IP有关信息\n");

        final_out:
        return $output;
    }

    /**
     * @description 将数组数据写入到二进制文本中
     *
     * @param Resource $fh      文本句柄
     * @param array $arr_data   需要写入的数组
     */
    private function write_array($fh, $arr_data)
    {
        foreach($arr_data as $data) {
            fwrite($fh, $data);
        }
    }

    /**
     * @description 将IP地址转换为整型
     *
     * @param string $ip
     *
     * @return int
     */
    private function ip2int($ip)
    {
        $ipArray = explode(".", $ip);
        $int_ip = intval($ipArray[0]) * 0x1000000 +
            intval($ipArray[1]) * 0x10000 +
            intval($ipArray[2]) * 0x100 +
            intval($ipArray[3]);

        return intval($int_ip);
    }

    /**
     * @description 将int类型的ip转换为4位的ip地址
     *
     * @param $int_ip
     *
     * @return string $ip
     */
    private function int2ip($int_ip)
    {
        $ip_4 = $int_ip >> 24;
        $ip_3 = ($int_ip - ($ip_4 << 24)) >> 16;
        $ip_2 = ($int_ip - ($ip_4 << 24) - ($ip_3 << 16)) >> 8;
        $ip_1 = ($int_ip - ($ip_4 << 24) - ($ip_3 << 16) - ($ip_2 << 8)) >> 0;

        $ip = $ip_4 . "." . $ip_3 . "." . $ip_2 . ".". $ip_1;
        return $ip;
    }

    /**
     * @description 日志记录
     */
    static private function SP_debug($func_name, $msg)
    {
        if ($debug_flag = True) {
            $info = "[ " . date("Y-m-d H:i:s") . " ] [ {$func_name} ]" . $msg . "\n";
            file_put_contents("/tmp/www", $info, FILE_APPEND);
        }
    }
}


//==============================TEST================================
# 1.0.8.0   1.0.15.255
//$a = new SearchIP();
//$m = $a->search("1.1.9.22"); # 康王南路   78号
//$m = $a->search("1.1.5.22"); # 八一七中路  649号
//$m = $a->search("1.48.165.22"); # 安顺市 西秀区 黉学坝路
//$m = $a->search("1.0.9.22");   # 广东省  广州市 荔湾区 康王南路    78号
//$m = $a->search("192.168.1.172");
//$m = $a->ip2int("172.16.5.226");
//var_dump($m);
//var_dump($a->int2ip($m));
```

[1]: http://www.jianshu.com/p/a8f75501d2e3

[4]: https://link.jianshu.com?t=https://github.com/yoooknight/PHP
[5]: https://www.jianshu.com/p/2fca5167ffbe
[6]: ./img/yuq2Mv3.png
[7]: ./img/i6vUVfu.png