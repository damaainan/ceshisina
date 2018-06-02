## PHP解析纯真IP数据库（qqwry.dat）

来源：[http://www.miaoqiyuan.cn/p/php-qqwry-struct](http://www.miaoqiyuan.cn/p/php-qqwry-struct)

时间 2018-05-11 21:34:11


这是一个 StructPHP 的教程帖，使用它 解析 qqwry.dat。关于 qqwry.dat 数据结构的分析，请参考：http://www.jb51.net/article/17197_2.htm。，

StructPHP，仅有 20 多行代码，自我感觉不仅仅仅是对 unpack 和 pack 进行了封装，它是一种全新的编码方案。通过这种方法，可以简化 二进制转换的开发过程，使用代码更加易懂。

测试前需要先引入 StructPHP (http://www.miaoqiyuan.cn/p/php-struct) 类，全部的代码如下：

```php
<?php

header("Content-type:text/plain");

require 'struct.php';

// 修复数字格式，24位int
function hex_to_int24($hex) {
	return unpack('L', pack('H8', $hex . '00'))[1];
}
// 二进制 转 字符串
function binstr_to_string($binstr) {
	$arr = binstr_to_string_array($binstr);
	return $arr[0];
}
// 二进制 转 字符串数组
function binstr_to_string_array($binstr) {
	$arr = explode("\00", $binstr);
	return $arr;
}

//qqwry.dat请到 纯真官网下载
$qqwry_data = file_get_contents('./qqwry.dat');

//文件头
$head_struct = array(
	'start' => 'L', //第一条索引偏移
	'end' => 'L', //最后一条索引偏移
);
$head_struct_len = 8;

//索引区记录定义
$index_struct = array(
	'ip' => 'L', //IP地址（4个字节）
	'pos' => 'H6', //地址记录偏移(3个字节，后边需补0)
);
$index_struct_len = 7;

//IP记录格式
$ip_struct = array(
	'ip' => 'L', //IP地址
	'type' => 'H2', //偏移类型
	'pos' => 'H6', //偏移值
);
$ip_struct_len = 8;

//国家和地址定义
$area_struct = array(
	'county_type' => 'H2', //国家偏移类型
	'county_pos' => 'H6', //国家偏移
	'address_type' => 'H2', //地址偏移类型
	'address_pos' => 'H6', //地址偏移
);
$area_struct_len = 8;

//读取头
$head_binstr = substr($qqwry_data, 0, $head_struct_len);
$head_data = StructPHP::decode($head_struct, $head_binstr);
for ($index_pos = $head_data['start']; $index_pos < $head_data['end']; $index_pos += 7) {

	//最终返回的数据
	$data = array();

	//索引数据
	$index_binstr = substr($qqwry_data, $index_pos, $index_struct_len);
	$index_data = StructPHP::decode($index_struct, $index_binstr);
	$ip_pos = hex_to_int24($index_data['pos']);
	$data['ip_start'] = long2ip($index_data['ip']);
	$data['ip_pos'] = $ip_pos;

	//IP数据提取
	$ip_binstr = substr($qqwry_data, $ip_pos, $ip_struct_len);
	$ip_data = StructPHP::decode($ip_struct, $ip_binstr);
	$area_pos = hex_to_int24($ip_data['pos']);
	$data['ip_end'] = long2ip($ip_data['ip']);
	$data['area_type'] = $ip_data['type'];
	$data['area_pos'] = $area_pos;

	if (in_array($ip_data['type'], array('01', '02'))) {
		//如果不是简单模式，解析 国家和地址定义
		$area_binstr = substr($qqwry_data, $area_pos, $area_struct_len);
		$area_data = StructPHP::decode($area_struct, $area_binstr);
	}

	switch ($ip_data['type']) {
	case '01': //模式1, [国家+地区偏移]
		$data['county_type'] = $area_data['county_type'];
		switch ($area_data['county_type']) {
		case '01':
			//实际测试中，没有这类数据
			throw new Exception("数据结构更改[area_type:01, county_type:01]");
			break;
		case '02':

			//国家的处理方式都是一样的
			$county_pos = hex_to_int24($area_data['county_pos']);
			$data['county'] = binstr_to_string(substr($qqwry_data, $county_pos, 100));
			switch ($area_data['address_type']) {

			case '01':
				//实际测试中，没有这类数据
				throw new Exception("数据结构更改[area_type:01, county_type:02, address_type:01]");
				break;
			case '02':
				/**
				 * 混和情况1：[地区偏移]
				 *                |
				 *       [国家偏移][地址偏移]
				 *          |          |
				 *       [国家\0]   [地址\0]
				 * http://www.miaoqiyuan.cn/images/uploads/2018/05/010202.gif
				 */
				$address_pos = hex_to_int24($area_data['address_pos']);
				$data['address'] = binstr_to_string(substr($qqwry_data, $address_pos, 100));
				break;
			default:
				/**
				 * 混和情况2：[地区偏移]
				 *                |
				 *       [国家偏移][地址\0]
				 *            |
				 *         [国家\0]
				 * http://www.miaoqiyuan.cn/images/uploads/2018/05/0102.gif
				 */
				$data['address'] = binstr_to_string(substr($qqwry_data, $area_pos + $area_struct_len, 100));
				break;
			}
			break;
		default:
			/**
			 * 重定向模式1：[地区偏移]
			 *                |
			 *         [国家\0][地址\0]
			 * http://www.miaoqiyuan.cn/images/uploads/2018/05/0100.gif
			 */
			$data['county_type'] = '00'; //类型用00表示

			$areas_arr = binstr_to_string_array(substr($qqwry_data, $area_pos, 100));
			$data['county'] = $areas_arr[0];
			$data['address'] = isset($areas_arr[1]) ? $areas_arr[1] : '';
			break;
		}
		break;
	case '02':
		/**
		 *  重定向模式2：[地区偏移][地址\0]
		 *                  |
		 *               [国家\0]
		 * http://www.miaoqiyuan.cn/images/uploads/2018/05/02.gif
		 */
		$data['county'] = binstr_to_string(substr($qqwry_data, $area_pos, 100));
		$data['address'] = binstr_to_string(substr($qqwry_data, $ip_pos + $ip_struct_len, 100));
		break;
	default:
		/**
		 * IP记录的最简单形式：[IP地址(4字节)][国家\0][地址\0]
		 * http://www.miaoqiyuan.cn/images/uploads/2018/05/00.gif
		 */
		$data['area_type'] = '00'; //类型用00表示
		$areas_binstr = substr($qqwry_data, $ip_pos + $ip_struct_len, 100);

		$areas_arr = binstr_to_string_array($areas_binstr);
		$data['county'] = $areas_arr[0];
		$data['address'] = isset($areas_arr[1]) ? $areas_arr[1] : '';
		break;
	}

	print_r($data);
}
```


