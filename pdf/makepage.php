<?php

header('content-type:text/html;charset=utf8');

$list=$_POST['list'];
$book=$_POST['book'];
$qihao=$_POST['qihao'];
$name=$_POST['name'];
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
	//book需要去除符号
	// $book=str_replace('',$book);
$nbook=iconv('utf-8','gbk',$book);
$dir=__DIR__.'/'.$nbook;
if(!is_dir($dir)){
	mkdir($dir);
}
//$name 去掉特殊字符 防止不能转化
$name=str_replace('\x2764','',$name);
$boname=iconv('utf-8','gbk', $qihao.'-'.$name.'.pdf');
$bookname=$dir.'/'.$boname;
// 遍历目录 寻找文件
$farr=scandir($dir);
if(in_array($boname, $farr)){
	echo 2;
	die;
}

// $url="http://st.hujiang.com/mag/1426491012/";	
$result=openUrl($list);	
// echo $arr1[0];	
$result=explode('<body>',$result);
$result=explode('</body>',$result[1]);
$str=$result[0];
$arr=explode('<div class="cont-c">',$str);
$arr=explode('<!--附件-->',$arr[1]);
  
$cont='<div>'.$arr[0];//截取到的内容
$cont=str_replace('宋体','',$cont);
//需要删除里面的字体文件  还是得使用正则
$pat='/(?:font-family\:\s(.*?)\;)/';
$matches= array();
preg_match_all($pat, $cont, $matches);
$len=count($matches[0]);
$str=$cont;
for($i=0;$i<$len;$i++){
  $str=str_replace($matches[0][$i],'',$str);
}
$cont=$str;
// var_dump($cont);die;
/*******************************生成PDF的部分**********************************/
//实例化 
require_once('./tcpdf/tcpdf.php'); 
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false); 
 
// 设置文档信息 
$pdf->SetCreator('Helloweba'); 
$pdf->SetAuthor('yue'); 
$pdf->SetTitle('Welcome to hujiang.com!'); 
$pdf->SetSubject('TCPDF Tutorial'); 
$pdf->SetKeywords('TCPDF, PDF, PHP'); 
 
// 设置页眉和页脚信息 
$pdf->SetHeaderData('', 30, '沪江论坛社刊', $book,  
      array(0,64,255), array(0,64,128)); 
$pdf->setFooterData(array(0,64,0), array(0,64,128)); 
 
// 设置页眉和页脚字体 
$pdf->setHeaderFont(Array('stsongstdlight', '', '10')); 
$pdf->setFooterFont(Array('helvetica', '', '8')); 
 
// 设置默认等宽字体 
$pdf->SetDefaultMonospacedFont('courier'); 
 
// 设置间距 
$pdf->SetMargins(15, 20, 15); 
$pdf->SetHeaderMargin(5); 
$pdf->SetFooterMargin(10); 
 
// 设置分页 
$pdf->SetAutoPageBreak(TRUE, 25); 
 
// set image scale factor 
$pdf->setImageScale(1); 
 
// set default font subsetting mode 
$pdf->setFontSubsetting(true); 
 
//设置字体 
$pdf->SetFont('stsongstdlight', 'I', 12); 
 
$pdf->AddPage(); 
 
$html = $cont;

$pdf->writeHTML($html, true, false, true, false, '');

//输出PDF 
// __DIR__.'/'.$book;   $dir.'/'.
$pdf->Output($bookname,'F'); 
// $pdf->Output(__DIR__.'/1.pdf','F'); 
// 遍历目录 寻找文件
$farr=scandir($dir);
if(in_array($boname, $farr)){
	echo 1;
}