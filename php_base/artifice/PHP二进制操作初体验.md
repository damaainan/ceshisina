## PHP二进制操作初体验

来源：[http://www.miaoqiyuan.cn/p/php-buffer](http://www.miaoqiyuan.cn/p/php-buffer)

时间 2018-05-07 21:45:49

 
一个朋友 最近在做一个 硬件相关的项目。搞Web的，最擅长的也是最熟悉的 PHP来开发，使用了 workerman 框架。搞WEB的平常很少和 二进制打交道，他看到 文档 开头就蒙圈了。向他的大神（也就是我）来求救了。下面是 部分文档的截图，第一次看时 简直 头大阿。
 
![][0]
 
![][1]
 
我也很少 和 二进制打交道，作为”大神”的我，怎么能在”迷弟”面前表现出不会呢，只能硬着头皮上了，折腾了一晚上，终于造出来一个小轮子。代码已经测试没有问题了，另外 为了 让 这朋友 更加崇拜我，完成后，特意美化了一下代码。。。
 
直接写成了类，方便 整合，代码如下：
 
```php
<?php

/**
 * 文件：BufferService.class.php
 * 作者：mqycn
 * 博客：http://www.miaoqiyuan.cn
 * 源码：http://www.miaoqiyuan.cn/p/php-buffer
 * 说明：一个简单的PHP协议校验类
 */

class BufferService {

	private static $debugMode = false;

	private static function debug($fun, $info, $debug = null) {
		if ($debug === null) {
			$debug = self::$debugMode;
		}
		if ($debug === true) {
			echo str_replace(' ', ' ', "[$fun]：$info
");
		}
	}

	//数字 转 Buffers[] （之前的方法）
	public static function number_to_buffer($number, $debug = false) {
		if ($number < 0xff) {
			self::debug('number_to_buffer', "数字" . $number . '小于256 计算：' . $number, $debug);
			//如果小于一个字节，输出
			return array($number);
		}
		self::debug('number_to_buffer', "准备分析数字" . $number, $debug);
		$remainder = $number % 0x100;
		$quotient = ($number - $remainder) / 0x100;
		self::debug('number_to_buffer', "    余数：" . $remainder, $debug);
		self::debug('number_to_buffer', "    整商：" . $number, $debug);
		$arr = self::number_to_buffer($quotient, $debug);
		if ($remainder < 0) {
			throw new Exception('数字溢出(' . $number . ')');
		}
		$arr[] = $remainder;
		return $arr;
	}

	//二进制 求 ck_sum （也是之前的）
	public static function buffer_ck_sum($buffers = array()) {
		$ck_sum = 0;
		for ($pos = 0; $pos < count($buffers); $pos++) {
			self::debug('buffer_ck_sum', ' **第' . ($pos + 1) . '次计算求值** ');
			self::debug('buffer_ck_sum', '    计算前：' . $ck_sum . ', ' . self::number_to_hexstring($ck_sum));
			$ck_sum += $buffers[$pos];
			self::debug('buffer_ck_sum', '    计算后：' . $ck_sum . ', ' . self::number_to_hexstring($ck_sum));
			$ck_sum = $ck_sum % 0x100;
			self::debug('buffer_ck_sum', '    求余后：' . $ck_sum . ', ' . self::number_to_hexstring($ck_sum));
		}
		$ck_sum = 0xff - $ck_sum;
		self::debug('buffer_ck_sum', ' **最终结果** ：' . $ck_sum . ', ' . self::number_to_hexstring($ck_sum));
		return $ck_sum;
	}

	// Buffers[] 转 hex （还是原来的，写到函数里）
	public static function buffer_to_hexstring($buffers = array()) {
		$hex = '';
		$hex_string = '';
		foreach ($buffers as $val) {
			$hex = dechex($val);
			$hex_string .= strlen($hex) > 1 ? $hex : '0' . $hex;
		}
		return '0x' . strtoupper($hex_string);
	}

	// 通过HEX字符串 转 Buttfer[]
	public static function hexstring_to_buffer($str) {
		$buffers = array();
		for ($pos = 0; $pos < strlen($str); $pos += 2) {
			$buffers[] = hexdec(substr($str, $pos, 2));
		}
		return $buffers;
	}

	// 通过数字转换成 HEX字符串
	public static function number_to_hexstring($number, $debug = false) {
		$buffers = self::number_to_buffer($number, $debug);
		return self::buffer_to_hexstring($buffers);
	}

	// 获取输入参数
	public static function get_input($input) {
		if (is_array($input)) {
			$buffers = $input;
		} elseif (is_string($input)) {
			$buffers = self::hexstring_to_buffer($input, "string");
		} elseif (is_numeric($input)) {
			$buffers = self::number_to_buffer($input, "number");
		}
		return $buffers;
	}

	// 通过输入项目获取 ck_sum
	public static function get_ck_sum($input, $debug = null) {
		self::$debugMode = $debug;
		$buffers = self::get_input($input);
		self::debug('get_ck_sum', '转换成HEX字符串：' . self::buffer_to_hexstring($buffers));
		return self::buffer_ck_sum($buffers);
	}
	
	// 判断输入的数据是否通过校验
	public static function is_ck_sum($input, $debug = false) {
		self::$debugMode = $debug;
		$buffers = self::get_input($input);
		$ck_sum = self::get_ck_sum(array_slice($buffers, 0, count($buffers)-1), $debug);
		return $ck_sum == $buffers[count($buffers) - 1];
	}
}
```
 
使用非常简单，引用类后，直接通过 静态方式调用即可。
 
```php
<?php

require_once('BufferService.class.php');

//显示 数字转HEX计算过程
$hex_string = BufferService::number_to_hexstring(10597059, true); //0xA1B2C3
echo '
通过数字转HEX字符串: ' . $hex_string . '
-----
';

//传入数字方式计算 ck_sum，并显示计算过程
$ck_sum = BufferService::get_ck_sum(105970, true); //0xA1B2A
echo '
 **传入数字方式**  最终结算结果: ' . BufferService::number_to_hexstring($ck_sum) . '
-----
';

//通过二进制流计算sum
$buff = array(
	0xF0, 0x86, 0x8D, 0xBA,
	0x35, 0x3D, 0x10, 0x03,
	0x00, 0x6C, 0x67, 0xF6,
	0x35, 0x3D, 0x10, 0x03,
	0x00, 0x6C, 0x67, 0xF6,
	0x35, 0x3D, 0x10, 0x03,
	0x00, 0x6C, 0x67, 0xF6,
	0x35, 0x3D, 0x10, 0x03,
	0x00, 0x6C, 0x61, 0x46
);
$ck_sum = BufferService::get_ck_sum($buff);
echo '
 **Buffer方式**  array(' . join($buff, ',') . ') 最终结算结果: ' . BufferService::number_to_hexstring($ck_sum) . '
-----
';

$buff[] = 0x01;
echo '
 **Buffer判断是否通过校验**    array(' . join($buff, ',') . ') : ' . (BufferService::is_ck_sum($hex_string) ? '<b style="color:#090">通过** ' : '<b style="color:#C00">未通过** ' ) . '
-----
';

//通过HEX字符串计算sum
$hex_string = "7A78183A777922B57A7A7817A78183A777922B57A7A78183A777922B578183A7779229383A777922B5EF";
$ck_sum = BufferService::get_ck_sum($hex_string);
echo "
 **HEX字符串方式 **  $hex_string 最终计算结果: " . BufferService::number_to_hexstring($ck_sum) . '
-----
';

$hex_string .= '0B';
echo "
 **HEX字符串 判断是否通过校验**   $hex_string: " . (BufferService::is_ck_sum($hex_string) ? '<b style="color:#090">通过** ' : '<b style="color:#C00">未通过** ' ) . '
-----
';

$hex_string = BufferService::number_to_hexstring(0x7A78183A777922B7A78183A777922B5); // 异常处理：溢出
```
 
在线测试地址： [http://www.miaoqiyuan.cn/products/buffer.php][7] 下面是 测试脚本运行的截图和一些说明。
 
数字转HEX计算过程，后续创建token可以用到。另外所有的数字转换回 HEX字符串也会用到
 
![][2]
 
传入数字方式计算 ck_sum，并显示计算过程
 
![][3]
 
通过二进制流方式计算sum和验证和传入的数据是否可用
 
![][4]
 
通过HEX字符串计算sum和校验结果是否能通过
 
![][5]
 
防止因为传参错误，导致 溢出，计算出错误的 ck_sum，支持异常处理
 
![][6]
 


[7]: http://www.miaoqiyuan.cn/products/buffer.php
[0]: ../img/bUVBRzq.png 
[1]: ../img/rYbIZzR.png 
[2]: ../img/QJjyYbZ.png 
[3]: ../img/AzmYrqY.png 
[4]: ../img/im63mqV.png 
[5]: ../img/eeUrUfm.png 
[6]: ../img/uAjY3iQ.png 