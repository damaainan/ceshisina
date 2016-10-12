<?php
header('content-type:text/html;charset=utf8');
function openUrl($url)
	{
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
	}
// $url="http://st.hujiang.com/mag/1426491012/";	
$url=$_POST['url'];	
$result=openUrl($url);	
$pat='/<span class=\"title_text\" id=\"magTitle\">(.*?)<\/span>/';
preg_match($pat,$result,$match);
$title=$match[1];
// <span class="title_text" id="magTitle">橄榄的漂流美利坚</span>
$arr=explode("<div class=\"panel mag-list\">",$result);
$arr1=explode("<div class=\"main_sidebar\">",$arr[1]);
// echo $arr1[0];	
echo json_encode(array('data'=>$arr1[0],'title'=>$title));	
// st.hujiang.com