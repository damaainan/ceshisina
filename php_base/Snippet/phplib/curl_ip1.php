<?php
//随机IP
function Rand_IP(){

    $ip2id= round(rand(600000, 2550000) / 10000); //第一种方法，直接生成
    $ip3id= round(rand(600000, 2550000) / 10000);
    $ip4id= round(rand(600000, 2550000) / 10000);
    //下面是第二种方法，在以下数据中随机抽取
    $arr_1 = array("218","218","66","66","218","218","60","60","202","204","66","66","66","59","61","60","222","221","66","59","60","60","66","218","218","62","63","64","66","66","122","211");
    $randarr= mt_rand(0,count($arr_1)-1);
    $ip1id = $arr_1[$randarr];
    return $ip1id.".".$ip2id.".".$ip3id.".".$ip4id;
}

//抓取页面内容
function Curl($url){
        $ch2 = curl_init();
        $user_agent = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36";//模拟windows用户正常访问
        curl_setopt($ch2, CURLOPT_URL, $url);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, array('X-FORWARDED-FOR:'.Rand_IP(), 'CLIENT-IP:'.Rand_IP()));
//追踪返回302状态码，继续抓取
        curl_setopt($ch2, CURLOPT_HEADER, true); 
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch2, CURLOPT_NOBODY, false);
        curl_setopt($ch2, CURLOPT_REFERER, 'https://www.baidu.com/s?wd=%E8%B4%BE%E4%BF%8A%E5%9B%AD&rsv_spt=1&rsv_iqid=0x814c9e0d0002da73&issp=1&f=8&rsv_bp=1&rsv_idx=2&ie=utf-8&rqlang=cn&tn=baiduhome_pg&rsv_enter=1&rsv_t=beae2WcBI%2BH4hMOzeYzHsw49zef%2Fud1XE%2Bs4FcVV6bPpkHD6QRhfsXziT7kNsQwpLhO0&gpc=stf%3D1548950400%2C1552319998%7Cstftype%3D2&tfflag=1');//模拟来路
        curl_setopt($ch2, CURLOPT_USERAGENT, $user_agent);
        $temp = curl_exec($ch2);
        curl_close($ch2);
        return $temp;
}