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
	$t1=pq($ps)->find("table:eq(1) tr:eq(1)");
	$str11=pq($t1)->find("td:eq(0)")->text();
	$str12=pq($t1)->find("td:eq(1)")->text();
	var_dump($str11);
	var_dump($str12);

    $t2=pq($ps)->find("table:eq(2) tr:eq(1) td");
    $balls=[];
    foreach ($t2 as $v2) {
    	$str2=pq($v2)->text();
    	$balls[]=intval($str2);
    	// echo $str2,"<br/>";
    }
    var_dump($balls);

	$t3=pq($ps)->find("table:eq(3) tr");
	$pride=[];
	foreach ($t3 as $k3=> $v3) {
		if($k3==0)
			continue;
		$str31=pq($v3)->find("td:eq(1)")->text();
		$str32=pq($v3)->find("td:eq(2)")->text();
		$pride[]['rank']=intval(str_replace(',','',$str31));
		$pride[]['bound']=intval(str_replace(',','',$str32));
	}
	var_dump($pride);

	$t4=pq($ps)->find("table:eq(4) tr td");
	$str4=pq($t4)->text();
	var_dump($str4);

	
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