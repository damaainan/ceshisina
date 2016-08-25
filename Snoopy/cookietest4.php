<?php
header('Content-Type:text/html; charset=UTF-8');
include("Snoopy.class.php");
include("phpQuery/phpQuery.php");

// 需要7个参数
// $cookie="_s_tentry=login.sina.com.cn;ALF=1503541705;Apache=9204201686661.691.1472005709430;Hm_lvt_cdc2220e7553b2a2cd949e1765e21edc=1466418850,1466472305;SCF=AjbVfK4Xdw2XgTYyQGOIRtCsFHf_smxmyXZval-aDwjo-v16MTP5ZdFT4JwozS4V3g_ZTmWzrQDv1CjWNuvuUcA.;SINAGLOBAL=8062468627467.752.1458205942141;SSOLoginState=1472005706;SUB=_2A256uXYaDeTxGedJ6FIZ8S3NzDiIHXVZz-DSrDV8PUNbmtBeLVCkkW8mJ37ioXkii6_2E9zViijrUhFctg..;SUBP=0033WrSXqPxfM725Ws9jqgMF55529P9D9WhOaEMKSk.Lxxebbn5-7OSC5JpX5KzhUgL.Fo2Ne05ReKepS0B2dJLoIpqLxKBLB.BLBoeLxKBLB.BLBoSNI0W9;SUHB=0bttthHR6LX4IJ;ULV=1472005709437:141:22:6:9204201686661.691.1472005709430:1471948664045;un=jiachunhui1988@sina.cn;UOR=news.ifeng.com,widget.weibo.com,login.sina.com.cn;wvr=6;YF-Page-G0=59104684d5296c124160a1b451efa4ac;YF-Ugrow-G0=5b31332af1361e117ff29bb32e4d8439;YF-V5-G0=ab4df45851fc4ded40c6ece473536bdd;";
$cookie = "_s_tentry=login.sina.com.cn;ALF=1503624485;Apache=7725523682311.178.1472092919096;Hm_lvt_cdc2220e7553b2a2cd949e1765e21edc=1466418850,1466472305;SCF=AjbVfK4Xdw2XgTYyQGOIRtCsFHf_smxmyXZval-aDwjoptsPGVQh8E7hPkvIvAH8YgjMz5BSfHzMj2Ii46OEkVU.;SINAGLOBAL=8062468627467.752.1458205942141;SSOLoginState=1472088486;SUB=_2A256ujn1DeTxGedJ6FIZ8S3NzDiIHXVZziw9rDV8PUNbmtBeLXTRkW9imrJfuV8eXc6bK72e1EjBWRO9hA..;SUBP=0033WrSXqPxfM725Ws9jqgMF55529P9D9WhOaEMKSk.Lxxebbn5-7OSC5JpX5KMhUgL.Fo2Ne05ReKepS0B2dJLoIpqLxKBLB.BLBoeLxKBLB.BLBoSNI0W9;SUHB=0laUeB0nkMLyN7;ULV=1472092919124:143:24:8:7725523682311.178.1472092919096:1472032743537;un=jiachunhui1988@sina.cn;UOR=news.ifeng.com,widget.weibo.com,spr_web_360_hao360_weibo_t001;wvr=6;YF-Page-G0=46f5b98560a83dd9bfdd28c040a3673e;YF-Ugrow-G0=1eba44dbebf62c27ae66e16d40e02964;YF-V5-G0=32427df11f152291036145f8d346cc49;";
$page = 1;
$href = "http://weibo.com/1730813174/profile?profile_ftype=1&is_all=1";
$domain = "100505";  // 100606
$id = "1005051730813174";  //1006062674868673
$script = "script_uri=/1730813174/profile";

$href2 = "http://weibo.com/p/aj/v6/mblog/mbloglist?ajwvr=6&" . $script . "&domain=" . $domain . "&is_all=1&profile_ftype=1&pagebar=0&id=" . $id;

getWeibo($cookie,$page,$href,$href2);

function getWeibo($cookie,$page,$href,$href2){
    for ($i = 1; $i <= $page; $i++) {
        $liarr = [];
        if ($i == 1) {
            $url = $href . "#_0";
        } else {
            $url = $href . "&is_search=0&visible=0&is_tag=0&page=" . $i . "#feedtop";
        }
        $liarr = dealmain($url, $cookie);
        // var_dump($liarr);
        //处理数据  存入数据库
        // dealSQL($liarr);
        for ($j = 0; $j < 2; $j++) {
            $url = $href2 . "&page=" . $i . "&pre_page=" . $i . "&pagebar=" . $j;
            $arr = dealjs($url, $cookie);
            $liarr = array_merge($liarr, $arr);
            // var_dump($arr);
            // dealSQL($arr);
        }
        dealSQL($liarr);
        echo $i . "***<br/>";
    }
}

function dealSQL($arr)
{
    $pdo = new PDO('mysql:host=localhost;dbname=caiji;charset=utf8', 'root', '');
    $pdo->exec('set names utf8');
    $stmt2 = $pdo->prepare("INSERT INTO weibo (author,createTime,content,media,isOriginal,rAuthor,rCreateTime,rContent,rMedia) VALUES(?,?,?,?,?,?,?,?,?) ;");
    for ($i = 0, $len = count($arr); $i < $len; $i++) {
        $stmt2->bindParam(1, $arr[$i]['author']);
        $stmt2->bindParam(2, $arr[$i]['createTime']);
        $stmt2->bindParam(3, $arr[$i]['content']);
        $stmt2->bindParam(4, $arr[$i]['media']);
        $stmt2->bindParam(5, $arr[$i]['isOriginal']);
        if ($arr[$i]['isOriginal'] == 0) {
            $stmt2->bindParam(6, $arr[$i]['rAuthor']);
            $stmt2->bindParam(7, $arr[$i]['rCreateTime']);
            $stmt2->bindParam(8, $arr[$i]['rContent']);
            $stmt2->bindParam(9, $arr[$i]['rMedia']);
        } else {
            $flag = '';
            $stmt2->bindParam(6, $flag);
            $stmt2->bindParam(7, $flag);
            $stmt2->bindParam(8, $flag);
            $stmt2->bindParam(9, $flag);
        }
        $stmt2->execute();
        $nid = $pdo->lastInsertId();
    }
}

function init($cookie)
{
    $snoopy = new Snoopy();
    $snoopy->agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5 FirePHP/0.2.1";//这项是浏览器信息，前面你用什么浏览器查看cookie，就用那个浏览器的信息(ps:$_SERVER可以查看到浏览器的信息)
    $snoopy->referer = "http://weibo.com/1730813174/profile?rightmod=1&wvr=6&mod=personnumber&is_all=1kkk";
    $snoopy->expandlinks = true;
    $snoopy->rawheaders["COOKIE"] = $cookie;
    return $snoopy;
}

function dealmain($url, $cookie)
{
    $snoopy = init($cookie);
    $snoopy->fetch($url);
    $str = $snoopy->results;
    $html = unicode_decode($str);
    // echo $html;
    // 处理直接拿到的数据
    $pat = '/<script>(.*?)<\/script>/i';
    preg_match_all($pat, $html, $match);
    $listarr = array();
    // var_dump($match[1]);
    foreach ($match[1] as $ke => $va) {
        // echo $va;
        $str = str_replace('FM.view', '', $va);
        $str = ltrim($str, '(');
        $str = rtrim($str, ';');
        $str = rtrim($str, ')');
        $darr = json_decode($str, true);
        if (isset($darr['html'])) {
            //可以简化为一个函数
            $larr = dealHtml($darr['html']);
            $listarr = array_merge($listarr, $larr);
        }
    }
    return $listarr;
}

function dealjs($url, $cookie)
{
    $snoopy = init($cookie);
    $snoopy->fetch($url);
    $str = $snoopy->results;
    $str = str_replace('{"code":"100000","msg":"","data":"', '', $str);
    $str = str_replace('/div>"}', '', $str);
    $str = str_replace('\/', '/', $str);
    $str = str_replace('\"', '"', $str);
    $str = str_replace('\n', '', $str);
    $str = str_replace('\r', '', $str);
    $str = str_replace('\t', '', $str);
    $html = unicode_decode($str);
    // 处理js的数据
    $listarr = dealHtml($html);
    return $listarr;
}

function dealHtml($html)
{
    $listarr = [];
    phpQuery::newDocument($html);
    $resu = pq(".WB_feed_detail>.WB_detail");

    foreach ($resu as $v) {
        $data = [];
        //分为转发 和 原创 两种情况

        $oname = pq($v)->find(".WB_info a:first")->text();
        $otime = pq($v)->find(".WB_from a:first")->attr("title");
        $ocont = pq($v)->find(".WB_text:first")->html();
        $ocont = dealTotal($ocont);
        $data['author'] = trim($oname);
        $data['createTime'] = $otime;
        $data['content'] = trim($ocont);
        $data['isOriginal'] = 1;
        if (pq($v)->find(".WB_feed_expand")->text() != '') {
            $data['isOriginal'] = 0;//转发
            $rcont = pq($v)->find(".WB_feed_expand")->find(".WB_expand .WB_text")->html();
            $rcont = dealTotal($rcont);
            $data['rContent'] = trim($rcont);
            $rname = pq($v)->find(".WB_feed_expand")->find(".WB_expand .WB_info a:first")->attr("title");
            $data['rAuthor'] = trim($rname);
            $data['rCreateTime'] = pq($v)->find(".WB_feed_expand")->find(".WB_expand .WB_from a:first")->attr("title");
            $rmedia = pq($v)->find(".WB_feed_expand")->find(".WB_expand .WB_media_wrap")->html();
            //处理其中的图片
            $rmedia = dealTotal($rmedia);
            $data['rMedia'] = $rmedia;
        }
        if (pq($v)->parents(".WB_detail")->children(".WB_media_wrap")->text() != '') {
            $media = pq($v)->find(".WB_media_wrap")->html();
            $media = dealTotal($media);
            $data['media'] = $media;

        }
        $listarr[] = $data;
    }

    //一维数组
    // var_dump($listarr);
    return $listarr;
}

function unicode_decode($name)
{
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches)) {
        for ($j = 0; $j < count($matches[0]); $j++) {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0) {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $c = chr($code) . chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $c);
                $name = str_replace($str, $c, $name);
            }
        }
    }
    return $name;
}

function dealTotal($html)
{
    $html = dealHtmlTags($html);
    $html = dealImg($html);
    $html = dealHref($html);
    return $html;
}

function dealImg($html)
{
    $pat = '/<img(.*?)>/i';
    $match = [];
    preg_match_all($pat, $html, $match);
    $patt = '/src="(.*?)"/i';
    for ($i = 0, $len = count($match[0]); $i < $len; $i++) {
        preg_match($patt, $match[0][$i], $mat);
        $search = ['thumb180', 'orj480'];
        $src = str_replace($search, 'mw690', $mat[0]);
        $src = "<img " . $src . ">";
        // var_dump($src);
        $html = str_replace($match[0][$i], $src, $html);
    }
    return $html;
}

function dealHref($html)
{
    $pat = '/<a(.*?)>/i';
    $match = [];
    preg_match_all($pat, $html, $match);
    $patt = '/href="(.*?)"/i';
    for ($i = 0, $len = count($match[0]); $i < $len; $i++) {
        preg_match($patt, $match[0][$i], $mat);
        if ($mat[1] == "javascript:void(0)") {
            $html = str_replace($match[0][$i], '<a>', $html);
            continue;
        }
        $src = "<a " . $mat[0] . ">";
        $html = str_replace($match[0][$i], $src, $html);
    }
    return $html;
}

function dealHtmlTags($html)
{
    $html = preg_replace('/class="(.*?)"/i', '', $html);
    $html = preg_replace('/<li(.*?)>/i', '', $html);
    $html = preg_replace('/<\/li>/i', '', $html);
    $html = preg_replace('/<ul(.*?)>/i', '', $html);
    $html = preg_replace('/<\/ul>/i', '', $html);
    $html = preg_replace('/<div(.*?)>/i', '', $html);
    $html = preg_replace('/<\/div>/i', '', $html);
    $html = preg_replace('/<span(.*?)>/i', '', $html);
    $html = preg_replace('/<\/span>/i', '', $html);
    $html = preg_replace('/<!--(.*?)-->/i', '', $html);
    $html = trim($html);
    return $html;
}