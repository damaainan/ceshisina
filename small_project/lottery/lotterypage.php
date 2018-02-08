<?php
header('Content-Type:text/html; charset=UTF-8');
include "Snoopy.class.php";
include "phpQuery/phpQuery.php";

// 抓取详细信息 存储

set_time_limit(0);
// $link = "http://www.17500.cn/ssq/details.php?issue=2016141";
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
	$total['qihao'] = intval($matches[0][0]);
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
	// var_dump($balls);

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
	// var_dump($pride);
	$total = array_merge($total, $b);

	$t4 = pq($ps)->find("table:eq(4) tr td");
	$str4 = pq($t4)->text();
	$arr4 = explode("元", $str4);
	$alls = $arr4[0];
	$pools = $arr4[1];
	$all = str_replace(',', '', explode("：", $alls)[1]);
	$pool = str_replace(',', '', explode("：", $pools)[1]);
	// echo $all, '**', $pool;
	$total['all'] = intval($all);
	$total['pool'] = intval($pool);
	preg_match('/[\d\s]{12,}/', $t4, $match);
	// var_dump($match);
	if (empty($match)) {
		$turn = [0, 0, 0, 0, 0, 0];
	} else {
		$turn = explode(' ', trim($match[0]));
	}
	$c = [];
	$turn = array_map('intto', $turn);
	list($c['rc1'], $c['rc2'], $c['rc3'], $c['rc4'], $c['rc5'], $c['rc6']) = $turn;
	$total = array_merge($total, $c);
	$total['address'] = trim($str4);
	$total['href'] = $link;
	// var_dump($total);
	return $total;

}
function intto($v) {
	return intval($v);
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
function getLink() {
	$pdo = new PDO('mysql:host=localhost;dbname=caiji;charset=utf8', 'root', '');
	$pdo->exec('set names utf8');
	$stmt2 = $pdo->prepare("SELECT href FROM lottery_link WHERE status=? LIMIT " . "0,500" . ";");
	$flag = 0;
	$count = 10;
	$stmt2->bindParam(1, $flag);
	// $stmt2->bindParam(2,$count);
	$stmt2->execute();
	$resu = $stmt2->fetchAll(PDO::FETCH_ASSOC);
	return $resu;
}
function dealSQL($arr) {
	$pdo = new PDO('mysql:host=localhost;dbname=caiji;charset=utf8', 'root', '');
	$pdo->exec('set names utf8');
	$stmt2 = $pdo->prepare("INSERT INTO lottery_data (qihao,time,r1,r2,r3,r4,r5,r6,blue,rc1,rc2,rc3,rc4,rc5,rc6,p1,p1n,p2,p2n,p3,p3n,p4,p4n,p5,p5n,p6,p6n,`all`,pool,address) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ;");
	$stmt3 = $pdo->prepare("UPDATE lottery_link SET status=? WHERE href=?;");
	$flag = 0;
	$status = 1;
	for ($i = 0, $len = count($arr); $i < $len; $i++) {
		$stmt2->bindParam(1, $arr[$i]['qihao']);
		$stmt2->bindParam(2, $arr[$i]['time']);
		$stmt2->bindParam(3, $arr[$i]['r1']);
		$stmt2->bindParam(4, $arr[$i]['r2']);
		$stmt2->bindParam(5, $arr[$i]['r3']);
		$stmt2->bindParam(6, $arr[$i]['r4']);
		$stmt2->bindParam(7, $arr[$i]['r5']);
		$stmt2->bindParam(8, $arr[$i]['r6']);
		$stmt2->bindParam(9, $arr[$i]['blue']);
		$stmt2->bindParam(10, $arr[$i]['rc1']);
		$stmt2->bindParam(11, $arr[$i]['rc2']);
		$stmt2->bindParam(12, $arr[$i]['rc3']);
		$stmt2->bindParam(13, $arr[$i]['rc4']);
		$stmt2->bindParam(14, $arr[$i]['rc5']);
		$stmt2->bindParam(15, $arr[$i]['rc6']);
		$stmt2->bindParam(16, $arr[$i]['p1']);
		$stmt2->bindParam(17, $arr[$i]['p1n']);
		$stmt2->bindParam(18, $arr[$i]['p2']);
		$stmt2->bindParam(19, $arr[$i]['p2n']);
		$stmt2->bindParam(20, $arr[$i]['p3']);
		$stmt2->bindParam(21, $arr[$i]['p3n']);
		$stmt2->bindParam(22, $arr[$i]['p4']);
		$stmt2->bindParam(23, $arr[$i]['p4n']);
		$stmt2->bindParam(24, $arr[$i]['p5']);
		$stmt2->bindParam(25, $arr[$i]['p5n']);
		$stmt2->bindParam(26, $arr[$i]['p6']);
		$stmt2->bindParam(27, $arr[$i]['p6n']);
		$stmt2->bindParam(28, $arr[$i]['all']);
		$stmt2->bindParam(29, $arr[$i]['pool']);
		$stmt2->bindParam(30, $arr[$i]['address']);
		$stmt2->execute();
		$nid = $pdo->lastInsertId();
		// var_dump($stmt2->errorInfo());
		if ($nid > 0) {
			$stmt3->bindParam(1, $status);
			$stmt3->bindParam(2, $arr[$i]['href']);
			$stmt3->execute();
		}
		echo $nid, "++";
	}
}

$links = getLink();
// var_dump($links);
$narr = [];
foreach ($links as $kk => $va) {
	echo $kk, "**";
	$narr[] = getPage($va['href']);
}
// var_dump($narr);
dealSQL($narr);
