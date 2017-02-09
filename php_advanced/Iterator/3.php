<?php
//生成器的简单示例
function xrange($start, $limit, $step =1){
	for($i = $start; $i <= $limit; $i += $step){
		yield $i +1=> $i;// 关键字 yield 表明这是一个 generator
	}
}
// 我们可以这样调用
foreach(xrange(0,10,2)as $key => $value){
    printf("%d %d\n", $key, $value);
}