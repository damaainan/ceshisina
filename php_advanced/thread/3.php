<?php
 namespace Home\Controller;
 class test extends \Thread {
	 public $url;
	 public $result;
	 public function __construct($url) {
		  $this->url = $url;
	 }
	 public function run() {
		  if ($this->url) {
				$this->result = model_http_curl_get($this->url);
		  }
	 }
 }
 function model_http_curl_get($url) {
	 $curl = curl_init();  
	 curl_setopt($curl, CURLOPT_URL, $url);  
	 curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  
	 curl_setopt($curl, CURLOPT_TIMEOUT, 5);  
	 curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)');  
	 $result = curl_exec($curl);  
	 curl_close($curl);  
	 return $result;  
 }
 for ($i = 0; $i < 10; $i++) {
	 $urls[] = 'http://www.baidu.com/s?wd='. rand(10000, 20000);
 }
 /* 多线程速度测试 */
$t = microtime(true);
 foreach ($urls as $key=>$url) {
	 $workers[$key] = new test($url);
	 $workers[$key]->start();
 }
 foreach ($workers as $key=>$worker) {
	 while($workers[$key]->isRunning()) {
		  usleep(100);  
	 }
	 if ($workers[$key]->join()) {
		  dump($workers[$key]->result);
	 }
 }
$e = microtime(true);
echo "多线程耗时：".($e-$t)."秒<br>";  
 /* 单线程速度测试 */
$t = microtime(true);
 foreach ($urls as $key=>$url) {
	 dump(model_http_curl_get($url));
 }
$e = microtime(true);
echo "For循环耗时：".($e-$t)."秒<br>";