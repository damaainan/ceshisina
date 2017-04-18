<?php
header("Content-type:text/html; Charset=utf-8");
require_once('phpQuery/phpQuery.php');
set_time_limit(0);
class Kuwo{

    public function init($link){
        $singer=$this->makeFile($link);
        if($singer==="0000"){
            echo "<script>alert('目标已存在');</script>";
            return;
        }else{
            $arr=$this->getAllPage($link);
            // var_dump($arr);exit;
            $this->urlList($arr);
            rename("urls.txt",$singer."/url.txt");
        }
    }
    private function makeFile($link){
        $link2=$link."=";
        $pat='/name=(.*?)=/';
        $matches= array();
        preg_match_all($pat, $link2, $matches);
        // var_dump($matches);
        $singer=$matches[1][0];
        $singer=iconv("utf-8",'gbk',$singer);
        if(!file_exists($singer)){
            mkdir($singer);
            return $singer;
        }else{
            return "0000";
        }
    }
	private function getAllPage($link){//获取完整列表页
		$result=$this->openUrl($link);
		phpQuery::newDocument($result);  //实例化
        // $artistid=pq("#artisContent .artistTop")->attr("data-artistid");
		// $pageNum=pq("#song .listMusic")->attr("data-page");
		// $rn=pq("#song .listMusic")->attr("data-rn");
       $artistid=pq("#artistContent")->find(".artistTop")->attr("data-artistid");
       $pagenum=pq("#song")->find(".page")->attr("data-page");
       $total=[];
       for($i=0;$i<$pagenum;$i++){
        $url="http://www.kuwo.cn/artist/contentMusicsAjax?artistId=3247&pn=".$i."&rn=15";
        $arr=$this->getAllLink($url);
        $total=array_merge($total,$arr);
       }
		return $total;

	}
    private function getAllLink($url){
        $result=$this->openUrl($url);
        // var_dump($url);
        phpQuery::newDocument($result); 
        $linka=pq(".listMusic .onLine")->find(".name a");
        $arr=[];
        foreach ($linka as $k => $va) {
            $link=pq($va)->attr("href");
            $arr[]="http://www.kuwo.cn".$link;
        }
        // var_dump($arr);exit;

        phpQuery::$documents = array();
        return $arr;
    }
    private function openUrl($url)
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
	}//CURLOPT_REFERER
    private function urlList($arr){
        $file="urls.txt";
        touch($file);
        $str='';
        foreach ($arr as $ak => $av) {
            $str.=$av."\r\n";
        }
        file_put_contents($file, $str);

    }
    


}//end class

$kuwo= new Kuwo();
// $link="http://www.kuwo.cn/artist/content?name=水瀬いのり";
$link=isset($_POST['name'])?$_POST['name']:'';
if($link){
    $kuwo->init($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>酷我音乐歌手专辑抓取</title>
</head>
<body>
    <form  action="" method="post">
        <input type="text" name="name" value="">
        <button type="submit" name="button">提交</button>
    </form>
</body>
</html>
