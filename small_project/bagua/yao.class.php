<?php
/**
 * 负责三变生爻
 */

//用位变化来表示阴阳  

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
		// echo ($tian+$di),"\n";
		return $tian+$di;
	}
	public function sanbian($dayan){
		$one=$this->bian($dayan);
		$two=$this->bian($one);
		$three=$this->bian($two);
		return $three;
	}
	//三变生六爻
	public function liuyao($dayan){
		$arr=[];
		for($i=0;$i<6;$i++){
			$bian=$this->sanbian($dayan);
			$arr[]=$bian/4;
		}
		return $arr;
	}
	//六爻生卦象
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
	//本卦 变卦
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
	
	
	function choose($arr,$ben,$bian){
		require "ming.php";
		$revarr=$this->getRev($arr);
		$str1=implode($ben);
		$str2=implode($bian);
		$len=count($arr);
		switch ($len){
			case 0:
				return $ming[$str1][1];//用本卦的卦辞判断吉凶
				break;
			case 1:
				return $ming[$str1][0][$arr[0]];//用本卦变爻的爻辞判断吉凶
				break;
			case 2:
				return [$ming[$str1][0][$arr[0]],$ming[$str1][0][$arr[1]]];//用本卦两个变爻的爻辞判断吉凶，但以居上的一爻的爻辞为主
				break;
			case 3:
				return [$ming[$str1][1],$ming[$str2][1]];//用本卦及之卦的卦辞判断吉凶，但以本卦卦辞为主，之卦卦辞为辅
				break;
			case 4:
				return [$ming[$str2][$revarr[1]],$ming[$str2][$revarr[0]]];//用之卦的两个不变爻的爻辞判断吉凶，但以居下一爻的爻辞为主
				break;
			case 5:
				return $ming[$str2][$revarr[0]];//用之卦的一个不变爻的爻辞判断吉凶
				break;
			case 6:
				if($str1='111111'){
					return $ming['111111'][0][6];
				}elseif($str1='000000'){
					return $ming['000000'][0][6];
				}else{
					return $ming[$str2][1];
				}
				 //用之卦的卦辞判断吉凶（若六爻皆为老阳，则用《乾》卦的“用九文
			//辞判断吉凶；若六爻皆为老阴，则用《坤》卦的“用六”文辞判断吉凶。）
				break;
			
		}
	}
	//得位
	function checkWei(){}
	//得中
	function checkZhong(){}
	


	public function randD($max){//产生随机数  应该用更好的扩展
		$rand=mt_rand(1,$max-1);
		return $rand;
	}
	function getRev($arr){
		$rev=[];
		for($i=0;$i<6;$i++){
			if(!isset($arr[$i])){
				$rev[]=$i;
			}
		}
		return $rev;
	}
	public function guaxiang($dayan){
		$arr=$this->liuyao($dayan);//本卦
		$bengua=$this->xiang($arr);
		$resu=$this->bianyao($arr);//变卦
		$narr=$resu[0];
		$yaoci=$resu[1];
		$biangua=$this->xiang($narr);
		// 查 本卦 变卦的名称

		//选择
		$ci=$this->choose($yaoci,$bengua,$biangua);
		// var_dump($ci);
		//检查得位  得中
		$data=["ben"=>$arr,"bengua"=>$bengua,"bian"=>$narr,"biangua"=>$biangua,"yaoci"=>$yaoci,'ci'=>$ci];
		return $data;
		
	}

}
