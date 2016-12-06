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
	$ps = pq("center");
	$t1 = pq($ps)->find("table:eq(1) tr:eq(1)");
	$str11 = pq($t1)->find("td:eq(0)")->text();
	$str12 = pq($t1)->find("td:eq(1)")->text();
	preg_match_all('/\d+/', $str11, $matches);
	preg_match_all('/[\d-]+/', $str12, $matches2);
	// var_dump($str11);
	// var_dump($matches[0][0]);
	$total['qihao'] = $matches[0][0];
	$total['time'] = $matches2[0][0];
	// var_dump($matches2[0][0]);
	// var_dump($str12);

	$t2 = pq($ps)->find("table:eq(2) tr:eq(1) td");
	$balls = [];
	foreach ($t2 as $v2) {
		$str2 = pq($v2)->text();
		$balls[] = intval($str2);
		// echo $str2,"<br/>";
	}
	$a = [];
	list($a['r1'], $a['r2'], $a['r3'], $a['r4'], $a['r5'], $a['r6'], $a['blue']) = $balls;
	$total = array_merge($total, $a);
	var_dump($balls);

	$t3 = pq($ps)->find("table:eq(3) tr");
	$pride = [];
	foreach ($t3 as $k3 => $v3) {
		if ($k3 == 0) {
			continue;
		}

		$str31 = pq($v3)->find("td:eq(1)")->text();
		$str32 = pq($v3)->find("td:eq(2)")->text();
		$pride[] = intval(str_replace(',', '', $str31));
		$pride[] = intval(str_replace(',', '', $str32));
	}
	$b = [];
	list($b['p1'], $b['p1n'], $b['p2'], $b['p2n'], $b['p3'], $b['p3n'], $b['p4'], $b['p4n'], $b['p5'], $b['p5n'], $b['p6'], $b['p6n']) = $pride;
	var_dump($pride);
	$total = array_merge($total, $b);

	$t4 = pq($ps)->find("table:eq(4) tr td");
	$str4 = pq($t4)->text();
	$arr4 = explode("元", $str4);
	$all = str_replace(',', '', (explode("：", $arr4[0]))[1]);
	$pool = str_replace(',', '', (explode("：", $arr4[1]))[1]);
	// echo $all, '**', $pool;
	$total['all'] = $all;
	$total['pool'] = $pool;
	var_dump($total);
	// preg_match_all('', $arr4[2], $match);
	// $arr41 = explode("。", $arr4[2]);
	// var_dump($arr4);
	// var_dump($arr41);
	// var_dump($str4);

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