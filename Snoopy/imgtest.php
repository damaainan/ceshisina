<?php
// 加载Snoopy类
include 'Snoopy.class.php';
// 实例化一个对象
$snoopy = new Snoopy();
// 要抓取的网页
$sourceURL = "http://mzitu.com/";
// 获得网页的链接
$snoopy->fetchlinks($sourceURL);
// 得到网页链接的结果
$a = $snoopy->results;
// 匹配的正则
$re = "/d+.html$/";
// 过滤获取指定的文件地址请求
foreach ($a as $tmp) {
    if (preg_match($re, $tmp)) {
        $aa=$tmp;
    }
}
getImgURL($aa);
function getImgURL($siteName) {
    $snoopy = new Snoopy();
    $snoopy->fetch($siteName);
    // 获取过滤后的页面的内容
    $fileContent = $snoopy->results;
    // 匹配图片的正则表达式
    $reTag = "/<img[^s]+src=\"(http\:\/\/[^\"\]+).(jpg|png|gif|jpeg)\"[^\/]*\/>/i";
    if (preg_match($reTag, $fileContent)) {
        // 过滤图片
        $ret = preg_match_all($reTag, $fileContent, $matchResult);
        for ($i = 0, $len = count($matchResult[1]); $i < $len; ++$i) {
            saveImgURL($matchResult[1][$i], $matchResult[2][$i]);
        }
    }
}
function saveImgURL($name, $suffix) {
    // 消息输出
    $url = $name.".".$suffix;
    echo "请求的图片地址：".$url."<br/>";

    // 图片保存地址
    $imgSavePath = "D:/123/images/";
    // 产生一个随机的文件名
    $imgId =mt_rand();
    if ($suffix == "gif") {
        // 根据图片类型，放入不同的文件夹下面
        $imgSavePath .= "emotion";
    } else {
        $imgSavePath .= "topic";
    }

    // 组装要保存的文件名
    $imgSavePath .= ("/".$imgId.".".$suffix);
    if (is_file($imgSavePath)) {
        // 判断文件名是否存在，存在则删除
        unlink($imgSavePath);
        echo "<p style='color:#f00;'>文件".$imgSavePath."已存在，将被删除</p>";
    }

    // 读取网络文件
    $imgFile = file_get_contents($url);
    // 写入到本地
    $flag = file_put_contents($imgSavePath,$imgFile);
    if ($flag) {
        echo "<p>文件".$imgSavePath."保存成功</p>";
    }
}
