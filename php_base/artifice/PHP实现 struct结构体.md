## PHP实现 struct结构体

来源：[http://www.miaoqiyuan.cn/p/php-struct](http://www.miaoqiyuan.cn/p/php-struct)

时间 2018-05-10 12:13:31


还是那个朋友，在用 Workman 开发 TCP协议 的项目。TCP客户端发过来的流，整数啥的好解析，遇到解析小数蒙圈了。百度一下，竟然没找出来方法，最终使用unpack解决。

直接写，真头疼，很容易出错，模仿C语言的struct自己造了一个车子，这应该是最优雅的解决方案了（自我陶醉…）。

封装成了类，代码如下：

```php
<?php
	
/**
 * 类名：StructPHP
 * 作者：mqycn
 * 博客：http://www.miaoqiyuan.cn
 * 源码：http://www.miaoqiyuan.cn/p/php-struct
 * 说明：PHP实现Struct，基于 pack/unpack
 *      官方文档： http://php.net/manual/zh/function.pack.php
 *      数据类型：
 *        a - NUL 填充的字符串
 *        A - SPACE 填充的字符串
 *        h - 十六进制字符串，低位在前
 *        H - 十六进制字符串，高位在前
 *        c - signed char
 *        C - unsigned char
 *        s - signed short（总是16位, machine 字节顺序）
 *        S - unsigned short（总是16位, machine 字节顺序）
 *        n - unsigned short（总是16位, big endian 字节顺序）
 *        v - unsigned short（总是16位, little endian 字节顺序）
 *        i - signed integer（取决于machine的大小和字节顺序）
 *        I - unsigned integer（取决于machine的大小和字节顺序）
 *        l - signed long（总是32位, machine 字节顺序）
 *        L - unsigned long（总是32位, machine 字节顺序）
 *        N - unsigned long（总是32位, big endian 字节顺序）
 *        V - unsigned long（总是32位, little endian 字节顺序）
 *        f - float（取决于 machine 的大小和表示）
 *        d - double（取决于 machine 的大小和表示）
 *        x - NUL 字节
 *        X - 备份一个字节
 *        Z - NUL 填充的字符串
 *        @ - NUL 填充绝对位置
 */
	
class StructPHP{

	public static function decode($struct = array(), $bin = ''){
		$format = '';
		foreach( $struct as $key => $val ){
			$format .= '/' . $val . (is_numeric($key) ? '' : $key);
		}
		$format = substr($format, 1);
		return unpack($format, $bin);
	}

	public static function encode($struct = array(), $data = array() ){
		if( !is_array($struct) || !is_array($data) || count($struct) == 0 || count($struct) <> count($data) ){
			throw new Exception('结构体与数据长度不对应');
		}
		$bin = '';
		foreach( $struct as $key => $val ){
			$bin .= pack($val, $data[$key]);
		}
		return $bin;
	}
}
```

测试下，没有问题，测试代码如下：

```php
<?php
require('struct.php');

//测试 结构体
$struct = array(
	'char' => 'C',
	'long' => 'L',
	'float' => 'f',
	'double' => 'd'
);
$data = array(
	'char' => 65,
	'long' => 77068320,
	'float' => 77.068320,
	'double' => 7706.8320,
);
$bin = StructPHP::encode($struct, $data);
$message = StructPHP::decode($struct, $bin);
var_dump($bin, $message);
/**
 *   string(17) "A 鴹?欱F扼継@"
 *   array(4) {
 *     ["char"]=>
 *     int(65)
 *     ["long"]=>
 *     int(77068320)
 *     ["float"]=>
 *     float(77.068321228027)
 *     ["double"]=>
 *     float(7706.832)
 *   }
*/


//不指定key（不建议，可以编码，解析时存在bug，可以通过 $struct 解析）
$struct2 = array('C', 'L', 'f', 'd');
$data2 = array(65, 77068320, 77.068320, 7706.8320);
$bin2 = StructPHP::encode($struct2, $data2);
var_dump($bin2, $bin === $bin2);
/**
 *   string(17) "A 鴹?欱F扼継@"
 *   bool(true)
*/

//不指定key，解析时可以通过指定key的 struct 解析
$message2 = StructPHP::decode($struct, $bin2);
var_dump($message2, $message == $message2);
/**
 *   array(4) {
 *     ["char"]=>
 *     int(65)
 *     ["long"]=>
 *     int(77068320)
 *     ["float"]=>
 *     float(77.068321228027)
 *     ["double"]=>
 *     float(7706.832)
 *   }
 *   bool(true)
*/
```

稍微封装下，就可以运用于实际项目中了

```php
<?php
require('struct.php');

function get_struct( $type = '00' ){
	$type = strtoupper($type);
	
	//协议头
	$struct = array(
		'header' => 'H8',
		'type' => 'H2'
	);
	
	switch( $type ){
		case "00": //直接返回消息头
			break;
		
		case "F2": //心跳包
			/**
			 * u16 Bat_volt -/- 电池电量4
			 * U32 Step_num 记步数据上海欧孚通信技术有限1
			 * U8 Signal_strength 信号
			 */
			$struct['bat_volt'] = 's';
			$struct['setp_num'] = 'L';
			$struct['bat_volt'] = 's';
			break;

		case "03": //GPS上报
			/**
			* 8 Double lon -/- longitude
			* 8 Double lat latitude
			* 1 U8 north_south #N or S
			* 1 U8 east_west #E or W
			* 1 U8 status #A or V
			* 4 U32 Timestamp 时间戳
			*/
			$struct['lon'] = 'd';
			$struct['lat'] = 'd';
			$struct['north_south'] = 'C';
			$struct['east_west'] = 'C';
			$struct['status'] = 'C';
			$struct['timestamp'] = 'L';
			
			break;
			
		default:
			echo("\n\nUNKNOW MESSAGE TYPE:" . $message['type'] . "\n\n");
			break;
	}
	if( $type != '00' ){
		//如果指定了协议，则存在签名
		$struct['ck_sum'] = 'H2';
	}
	return $struct;
}

function message_decode( $bin ){
	//分析 协议头
	$struct = get_struct();
	$message = StructPHP::decode($struct, $bin);
	
	//分析协议
	$struct = get_struct($message['type']);
	return StructPHP::decode($struct, $bin);
}

//测试：解析包
var_dump(message_decode("\xBD\xBD\xBD\xBD\xF2\xA4\x01\x4\x00\x10\x00\x0\x33"));
/**
 *   array(5) {
 *     ["header"]=>
 *     string(8) "bdbdbdbd"
 *     ["type"]=>
 *     string(2) "f2"
 *     ["bat_volt"]=>
 *     int(420)
 *     ["setp_num"]=>
 *     int(1048580)
 *     ["ck_sum"]=>
 *     string(2) "00"
 *   }
 */
 
var_dump(message_decode("\xBD\xBD\xBD\xBD\x03\x64\x7D\xF0\xC7\xDA\x95\x5D\x40\x1B\x96\x19\x49\x95\x8D\x41\x40\x4E\x45\x41\xC2\x6D\xF2\x5A\x5F"));
/**
 *   array(9) {
 *     ["header"]=>
 *     string(8) "bdbdbdbd"
 *     ["type"]=>
 *     string(2) "03"
 *     ["lon"]=>
 *     float(118.34147833333)
 *     ["lat"]=>
 *     float(35.106118333333)
 *     ["north_south"]=>
 *     int(78)
 *     ["east_west"]=>
 *     int(69)
 *     ["status"]=>
 *     int(65)
 *     ["timestamp"]=>
 *     int(1525837250)
 *     ["ck_sum"]=>
 *     string(2) "5f"
 *   }
 */
```


