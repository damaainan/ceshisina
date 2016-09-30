<?php
header("Content-type:text/html; Charset=utf-8");
require_once('phpQuery/phpQuery.php');
set_time_limit(0);
class Kuwo{
	private function getAllPage($link){//获取完整列表页
		$result=$this->openUrl($link);
		phpQuery::newDocument($result);  //实例化
        // $artistid=pq("#artisContent .artistTop")->attr("data-artistid");
		// $pageNum=pq("#song .listMusic")->attr("data-page");
		// $rn=pq("#song .listMusic")->attr("data-rn");
        $urls=[];
        $album=pq("#album ul")->find("li");
        foreach ($album as $ak => $av) {
            $href=pq($av)->find(".cover a")->attr("href");
            $cover=pq($av)->find(".cover a img")->attr("src");
            $albumName=pq($av)->find(".name a")->text();
            $resu=$this->openUrl($href);
    		phpQuery::newDocument($resu);
            $rst=pq(".list ul")->find("li");
            foreach ($rst as $rk => $rv) {
                $songLink=pq($rv)->find(".m_name a")->attr("href");
                $urls[$albumName][]=$songLink;
            }
        }
		return $urls;

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
            foreach ($av as $k => $v) {
                $str.=$v."\r\n";
            }
        }
        file_put_contents($file, $str);

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
    public function init($link){
        $singer=$this->makeFile($link);
        if($singer==="0000"){
            echo "<script>alert('目标已存在');</script>";
            return;
        }else{
            $arr=$this->getAllPage($link);
            $this->urlList($arr);
            rename("urls.txt",$singer."/url.txt");
        }
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
