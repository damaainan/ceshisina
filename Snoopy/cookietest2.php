<?php
header('Content-Type:text/html; charset=UTF-8');
include("Snoopy.class.php");
include("phpQuery/phpQuery.php");

// 需要六个参数
$cookie="_s_tentry=login.sina.com.cn;ALF=1503541705;Apache=9204201686661.691.1472005709430;Hm_lvt_cdc2220e7553b2a2cd949e1765e21edc=1466418850,1466472305;SCF=AjbVfK4Xdw2XgTYyQGOIRtCsFHf_smxmyXZval-aDwjo-v16MTP5ZdFT4JwozS4V3g_ZTmWzrQDv1CjWNuvuUcA.;SINAGLOBAL=8062468627467.752.1458205942141;SSOLoginState=1472005706;SUB=_2A256uXYaDeTxGedJ6FIZ8S3NzDiIHXVZz-DSrDV8PUNbmtBeLVCkkW8mJ37ioXkii6_2E9zViijrUhFctg..;SUBP=0033WrSXqPxfM725Ws9jqgMF55529P9D9WhOaEMKSk.Lxxebbn5-7OSC5JpX5KzhUgL.Fo2Ne05ReKepS0B2dJLoIpqLxKBLB.BLBoeLxKBLB.BLBoSNI0W9;SUHB=0bttthHR6LX4IJ;ULV=1472005709437:141:22:6:9204201686661.691.1472005709430:1471948664045;un=jiachunhui1988@sina.cn;UOR=news.ifeng.com,widget.weibo.com,login.sina.com.cn;wvr=6;YF-Page-G0=59104684d5296c124160a1b451efa4ac;YF-Ugrow-G0=5b31332af1361e117ff29bb32e4d8439;YF-V5-G0=ab4df45851fc4ded40c6ece473536bdd;";
$page=1;
$href="http://weibo.com/commandlinefu?profile_ftype=1&is_all=1";

$domain="100606";  // 100606
$id="1006062674868673";  //1006062674868673  1005051730813174
$href2="http://weibo.com/p/aj/v6/mblog/mbloglist?ajwvr=6&script_uri=/commandlinefu&domain=".$domain."&is_all=1&profile_ftype=1&pagebar=0&id=".$id;

$total=[];

for($i=1;$i<=$page;$i++){
   if($i==1){
   	$url=$href."#_0";
   }else{
   	$url=$href."&is_search=0&visible=0&is_tag=0&page=".$i."#feedtop";
   }
   $liarr=dealmain($url,$cookie);
   var_dump($liarr); 
   //处理数据  存入数据库
          
   for($j=0;$j<2;$j++){
	   	$url=$href2."&page=".$i."&pre_page=".$i."&pagebar=".$j;	
	   	$arr=dealjs($url,$cookie);
	   	var_dump($arr);
	   	// $total=array_merge($total,$arr);
   }
}


function init($cookie){
	$snoopy = new Snoopy();
	$snoopy->agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5 FirePHP/0.2.1";//这项是浏览器信息，前面你用什么浏览器查看cookie，就用那个浏览器的信息(ps:$_SERVER可以查看到浏览器的信息)
	$snoopy->referer = "http://weibo.com/1730813174/profile?rightmod=1&wvr=6&mod=personnumber&is_all=1kkk";
	$snoopy->expandlinks = true;
	$snoopy->rawheaders["COOKIE"]=$cookie;
	return $snoopy;
}
function dealHtml($html){
	$listarr=[];
	phpQuery::newDocument($html);
	$resu=pq(".WB_feed_detail>.WB_detail");
	foreach($resu as $v){
		$data=[];
		//分为转发 和 原创 两种情况
		
		$oname=pq($v)->find(".WB_info a:first")->text();
		$otime=pq($v)->find(".WB_from a:first")->attr("title");
		$ocont=pq($v)->find(".WB_text:first")->html();
		
		$data['oname']=trim($oname);
		$data['otime']=$otime;
		$data['ocont']=trim($ocont);
		$data['isOriginal']=1;
		if(pq($v)->find(".WB_feed_expand")->text()!=''){
			$data['isOriginal']=0;//转发
			$rcont=pq($v)->find(".WB_feed_expand")->find(".WB_expand .WB_text")->html();
			$data['rcont']=trim($rcont);
			$rname=pq($v)->find(".WB_feed_expand")->find(".WB_expand .WB_info a:first")->text();
			$data['rname']=trim($rname);
			$data['rtime']=pq($v)->find(".WB_feed_expand")->find(".WB_expand .WB_from a:first")->attr("title");
		}	

		$listarr[]=$data;//一维数组
	}
	phpQuery::$documents = array();
	// var_dump($listarr);
	return $listarr;
}
function dealmain($url,$cookie){
	$snoopy=init($cookie);
	$snoopy->fetch($url);
    $str=$snoopy->results;
	$html=unicode_decode($str);

// echo $html;
// 处理直接拿到的数据
	$pat='/<script>(.*?)<\/script>/i';
	preg_match_all($pat, $html, $match);
	$listarr=array();
	// var_dump($match[1]);
	foreach($match[1] as $ke=>$va){
		// echo $va;
		$str=str_replace('FM.view','',$va);
		$str=ltrim($str,'(');
		$str=rtrim($str,';');
		$str=rtrim($str,')');
        $darr=json_decode($str,true);
        
        if(isset($darr['html'])){
        	//可以简化为一个函数
        	$larr=dealHtml($darr['html']);
        	$listarr=array_merge($listarr,$larr);
        	// echo $ke."***<br/>";
        	/* phpQuery::newDocument($darr['html']);
        	 $resu=pq(".WB_detail>.WB_text");
			foreach($resu as $v){
				$larr=array();
				$name=pq($v)->text();
					$listarr[]=$name;//一维数组
			}
			// var_dump($listarr);
			if(empty($listarr)){
				continue;
			}else{
                break;
			}*/
        }
        // var_dump($darr->html);
	}
	// phpQuery::$documents = array();
	// var_dump($listarr);
	return $listarr;
}




function dealjs($url,$cookie){
    $snoopy=init($cookie);
	$snoopy->fetch($url);
	$str=$snoopy->results;
	$str=str_replace('{"code":"100000","msg":"","data":"','',$str);
	$str=str_replace('/div>"}','',$str);
	$str=str_replace('\/','/',$str);
	$str=str_replace('\"','"',$str);
	$str=str_replace('\n','',$str);
	$str=str_replace('\r','',$str);
	$str=str_replace('\t','',$str);
	$html=unicode_decode($str);

// 处理js的数据
	$listarr=dealHtml($html);
	// phpQuery::newDocument($html);
	// $resu=pq(".WB_detail>.WB_text");
	// $listarr=array();

	// 	foreach($resu as $v){
	// 		$larr=array();
	// 		$name=pq($v)->text();
	// 			$listarr[]=$name;//一维数组
	// 	}
		
	// phpQuery::$documents = array();
	return $listarr;

}

function unicode_decode($name)
{
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches))
    {
        for ($j = 0; $j < count($matches[0]); $j++)
        {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0)
            {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name=str_replace($str,$c,$name);
            }
        }
    }
    return $name;
}