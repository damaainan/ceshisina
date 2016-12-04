<?php
header('Content-Type:text/html; charset=UTF-8');
include "Snoopy.class.php";
include "phpQuery/phpQuery.php";
set_time_limit(0);
$link = "http://www.17500.cn/ssq/details.php?issue=2016141";
function getPage($link) {
//获取完整列表页
	$result = openUrl($link);
	phpQuery::newDocument($result); //实例化
	$ps = pq("center>table>tbody>tr>td")->find("table:eq(0)")->text();
	// $str1 = pq($ps)->find("table:eq(0)")->text();
	var_dump($ps);
	// var_dump($str1);
	$hrefs = [];
	// foreach ($ps as $vp) {
	//     $a = pq($vp)->find("a");
	//     foreach ($a as $va) {
	//         $href = pq($va)->attr("href");
	//         echo $href, "<br/>";
	//         $hrefs[] = $href;
	//     }
	// }
	// return $hrefs;
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
getPage($link);