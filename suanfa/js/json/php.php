<?php
header("Content-type:text/html; Charset=utf-8");
$data=file_get_contents("ti.txt");
$arr=explode("\r\n",$data);
$jarr=[];
for($i=0;$i<200;$i+=2){
	$q=explode('.',$arr[$i]);
	$str='{"question":"'.$q[1].'",';
	$sarr=preg_split('/[A-C]\./',$arr[$i+1]);
	$answer='"subject":[{'.'"A":"'.$sarr[1].'",'.'"B":"'.$sarr[2].'",'.'"C":"'.$sarr[3].'"}],"answer":""}';
	$str=$str.$answer;
	$jarr[]=$str;
}
$json=implode(',',$jarr);
echo "[".$json."]";