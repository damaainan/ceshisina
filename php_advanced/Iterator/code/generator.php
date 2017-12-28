<?php 
/**
 * 生成器示例
 */

class GeneratorCheat{
	/**
	 * 构造方法
	 */
	public function __construct(){
		//
	}

	public function dragonStrike($n){
		if($n <= 0){
			throw new \Exception('出现意外，请重新准备');
		}

		while(true){
			$n--;
			$yieldExpressionResult =(yield $n);//要加括号
			if($yieldExpressionResult){
				echo '击打一点生命值，当前对方还剩生命值：',$n,PHP_EOL,$yieldExpressionResult,PHP_EOL;
			}
		}
	}


}
//回去跑一下电脑

$n = 100;

$generatorCheat = new GeneratorCheat();

$ds = $generatorCheat->dragonStrike($n);
echo '神功准备[rewind()]',PHP_EOL;
$ds->rewind();

$statrtTime=microtime(true);

while($ds->current() >= 0){
	switch($ds->current()){
		case 0:
			echo '击溃对方！',PHP_EOL;
			break;
		case round($n / 2):
			$ds->send('加油！对方生命值下降一半');
			break;
		default:
			echo '击打一点生命值，当前对方还剩生命值：',$ds->current(),PHP_EOL;
	}

	$ds->next();
}

$endTime = microtime(true);
echo "击溃对方用时",bcsub($endTime,$statrtTime,4),'s',PHP_EOL;