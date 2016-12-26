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
		$narr=$this->bianyao($arr);//变卦
		$biangua=$this->xiang($narr);
		//检查得位  得中
		$data=["ben"=>$arr,"bengua"=>$bengua,"bian"=>$narr,"biangua"=>$biangua];
		return $data;
		
	}
	function bianyao($arr){
		$narr=[];
		foreach ($arr as $v) {
			if($v===6){
				$narr[]=7;
			}elseif($v===9){
				$narr[]=8;
			}else{
				$narr[]=$v;
			}
		}
		return $narr;
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
