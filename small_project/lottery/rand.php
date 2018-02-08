<?php
//随机数模拟抽奖，看何时能抽中一次
header("Content-type:text/html; Charset=utf-8");
require_once "random_compat/lib/random.php";
$pdo = new PDO('mysql:host=localhost;dbname=caiji;charset=utf8', 'root', '');
$pdo->exec('set names utf8');
function getPride($pdo, $arr) {

	$stmt2 = $pdo->prepare("SELECT * FROM lottery_data WHERE r1=? AND r2=? AND r3=? AND r4=? AND r5=? AND r6=? AND blue=?;");

	$stmt2->bindParam(1, $arr['r1']);
	$stmt2->bindParam(2, $arr['r2']);
	$stmt2->bindParam(3, $arr['r3']);
	$stmt2->bindParam(4, $arr['r4']);
	$stmt2->bindParam(5, $arr['r5']);
	$stmt2->bindParam(6, $arr['r6']);
	$stmt2->bindParam(7, $arr['blue']);
	// $stmt2->bindParam(2,$count);
	$stmt2->execute();
	$resu = $stmt2->fetch(PDO::FETCH_ASSOC);
	return $resu;
}

function randNum() {
	$balls = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33];
	$luck = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16];
	$sballs = deal($balls);
	$sluck = deal($luck);
	$red = diff($balls, $sballs);
	$blue = diff($luck, $sluck);

	// var_dump($red);
	// var_dump($blue);
	list($p['r1'], $p['r2'], $p['r3'], $p['r4'], $p['r5'], $p['r6']) = $red;
	$p['blue'] = $blue[0];
	return $p;

}
function deal($arr) {
	$l = count($arr);
	$int = random_int(0, $l - 1); // 使用 random_compat 库生成的随机数
	// echo "rand===",$arr[$int],"<br/>";
	array_splice($arr, $int, 1);
	$arr = array_reverse($arr);
	$arr = array_reverse($arr);
	if ($l < 29) {
		return $arr;
	} else {
		$narr = deal($arr);
		return $narr;
	}
}
function diff($big, $small) {
	$narr = [];
	foreach ($big as $va) {
		if (!in_array($va, $small)) {
			$narr[] = $va;
		}
	}
	return $narr;
}
// randNum();

function init($pdo, $flag) {
//需要解决递归的层数问题
	$arr = randNum();
	$resu = getPride($pdo, $arr);
	$flag++;
	// echo "flag==",$flag,"\r\n";
	if ($resu == null) {
		// return init($flag);
		return function () use ($pdo, $flag) {
//use 闭包函数（匿名函数） 从父级作用域继承变量
			return init($pdo, $flag);
		};
	} else {
		// echo "flag==",$flag,"\r\n";
		// var_dump($resu);
		$sttr = $resu['id'] . '**' . $resu['qihao'] . '**' . $resu['time'] . '**' . $resu['r1'] . ' ' . $resu['r2'] . ' ' . $resu['r3'] . ' ' . $resu['r4'] . ' ' . $resu['r5'] . ' ' . $resu['r6'] . ' ' . $resu['blue'];
		$rst['result'] = $sttr;
		$rst['flag'] = $flag;
		return $rst;
	}
}
function trampoline($callback, $params) {
	$result = call_user_func_array($callback, $params);

	while (is_callable($result)) {
		$result = $result();
	}
	return $result;
}

// trampoline('init',array(0));
function make($pdo) {
	$result = [];
	for ($i = 0; $i < 50; $i++) {
		$result[] = trampoline('init', array($pdo, 0));
		echo $i, "**";
	}
	echo "\r\n";
	sqliteData($result);
}

//写一个sqlite的存储函数
function sqliteData($rst) {
	$db = new SQLite3('my.sqlite');
	foreach ($rst as $va) {
		$db->exec("insert into result (flag,result)values (" . $va['flag'] . ",'" . $va['result'] . "')");
	}
}
for ($k = 0; $k < 10; $k++) {
	make($pdo);
}
