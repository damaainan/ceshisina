<?php
header('Content-Type:text/html; charset=UTF-8');
include "Snoopy.class.php";
include "phpQuery/phpQuery.php";
set_time_limit(0);
// 获取所有列表页 存储
$link = "http://www.17500.cn/ssq/all2018.php";
function getAllPage($link) {
//获取完整列表页
	$result = openUrl($link);
	phpQuery::newDocument($result); //实例化
	$ps = pq("#SF")->find("p");
	$hrefs = [];
	foreach ($ps as $vp) {
		$a = pq($vp)->find("a");
		foreach ($a as $va) {
			$href = pq($va)->attr("href");
			// echo $href, "<br/>";
			$hrefs[] = "http://www.17500.cn/ssq/" . $href;
		}
	}
	return $hrefs;
}

function openUrl($url) {
	$ch = curl_init();
	$timeout = 3000; // set to zero for no timeout
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$handles = curl_exec($ch);
	curl_close($ch);
	return $handles;
} //CURLOPT_REFERER
function dealSQL($arr) {
	$pdo = new PDO('mysql:host=localhost;dbname=caiji;charset=utf8', 'root', '');
	$pdo->exec('set names utf8');
	$stmt2 = $pdo->prepare("INSERT INTO lottery_link (href,status) VALUES(?,?) ;");
	$flag = 0;
	for ($i = 0, $len = count($arr); $i < $len; $i++) {
		$stmt2->bindParam(1, $arr[$i]);
		$stmt2->bindParam(2, $flag);
		$stmt2->execute();
		$nid = $pdo->lastInsertId();
	}
}
$hrefs = getAllPage($link);
dealSQL($hrefs);