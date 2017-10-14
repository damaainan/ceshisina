<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 16/10/29
 * Time: 16:57
 */
header("Content-type: text/html; charset=utf-8");
$filename = 'source.jpg';
$file     = file_get_contents($filename);
//数据块长度为9
$len = pack("N", 9);
//数据块类型标志为pHYs
$sign = pack("A*", "pHYs");
//X方向和Y方向的分辨率均为300DPI（1像素/英寸=39.37像素/米），单位为米（0为未知，1为米）
$data = pack("NNC", 72 * 39.37, 72 * 39.37, 0x01);
//CRC检验码由数据块符号和数据域计算得到
$checksum = pack("N", crc32($sign . $data));
$phys     = $len . $sign . $data . $checksum;
$pos      = strpos($file, "pHYs");
if ($pos > 0) {
//修改pHYs数据块
    $file = substr_replace($file, $phys, $pos - 4, 21);
} else {
//IHDR结束位置（PNG头固定长度为8，IHDR固定长度为25）
    $pos = 33;
//将pHYs数据块插入到IHDR之后
    $file = substr_replace($file, $phys, $pos, 0);
}
//打开文件准备写入
$filedata = fopen('dest.jpg', "w");
fwrite($filedata, $file); //写入
fclose($filedata); //关闭
echo "图片压缩成功！";
