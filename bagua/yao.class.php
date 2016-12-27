<?php
/**
 * 负责三变生爻
 */

class yao {
	// private $dayan = 55;
	// //一变  二变  三变
	// private $tian;
	// private $di;
	// private $ren;
	public function bian($dayan){
		$tian=$this->randD($dayan);
		$di=$dayan-$tian-1;
		$tian=$tian-($tian%4===0?4:$tian%4);
		$di=$di-($di%4===0?4:$di%4);
		// echo ($tian+$di),"<br/>";
		return $tian+$di;
	}
	public function sanbian($dayan){
		$one=$this->bian($dayan);
		$two=$this->bian($one);
		$three=$this->bian($two);
		return $three;
	}
	public function liuyao($dayan){
		$arr=[];
		for($i=0;$i<6;$i++){
			$bian=$this->sanbian($dayan);
			$arr[]=$bian/4;
		}
		return $arr;
	}
	public function guaxiang($dayan){
		$arr=$this->liuyao($dayan);//本卦
		$bengua=$this->xiang($arr);
		$resu=$this->bianyao($arr);//变卦
		$narr=$resu[0];
		$yaoci=$resu[1];
		$biangua=$this->xiang($narr);
		//检查得位  得中
		$data=["ben"=>$arr,"bengua"=>$bengua,"bian"=>$narr,"biangua"=>$biangua,"yaoci"=>$yaoci];
		return $data;
		
	}
	function choose($arr){
		$len=count($arr);
		switch ($len){
			case 0:;//用本卦的卦辞判断吉凶
				break;
			case 1:;//用本卦变爻的爻辞判断吉凶
				break;
			case 2:;//用本卦两个变爻的爻辞判断吉凶，但以居上的一爻的爻辞为主
				break;
			case 3:;//用本卦及之卦的卦辞判断吉凶，但以本卦卦辞为主，之卦卦辞为辅
				break;
			case 4:;//用之卦的两个不变爻的爻辞判断吉凶，但以居下一爻的爻辞为主
				break;
			case 5:;//用之卦的一个不变爻的爻辞判断吉凶
				break;
			case 6:;//用之卦的卦辞判断吉凶（若六爻皆为老阳，则用《乾》卦的“用九文
			//辞判断吉凶；若六爻皆为老阴，则用《坤》卦的“用六”文辞判断吉凶。）
				break;
			
		}
	}
	function checkWei(){}
	function checkZhong(){}
	function bianyao($arr){
		$narr=[];
		$yaoci=[];
		foreach ($arr as $k=>$v) {
			if($v===6){
				$narr[]=7;
				$yaoci[]=$k;
			}elseif($v===9){
				$narr[]=8;
				$yaoci[]=$k;
			}else{
				$narr[]=$v;
			}
		}
		return [$narr,$yaoci];
	}
	function xiang($arr){
		$gua=[];
		foreach ($arr as $ke => $va) {
			if($va%2===0){
				$gua[]=1;
			}else{
				$gua[]=0;
			}
		}
		return $gua;
	}


	public function randD($max){
		$rand=mt_rand(1,$max-1);
		return $rand;
	}

}
$yao =new yao();
$one=$yao->guaxiang(49);
var_dump($one);
