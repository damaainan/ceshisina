<?php 

function proxyIp($url, $data){
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
    curl_setopt($ch, CURLOPT_PROXY, ''.$data['ip'].''); //代理服务器地址
    curl_setopt($ch, CURLOPT_PROXYPORT, $data['port']); //代理服务器端口
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_URL, $url); //设置链接
    // curl_setopt($ch, CURLOPT_HTTPHEADER, $data['header']);
    curl_setopt($ch, CURLOPT_USERAGENT, $data['ua']);
    curl_setopt($ch, CURLOPT_REFERER, $data['refer']);
    $response = curl_exec($ch); //接收返回信息
    var_dump($response);
}

function getFakeData(){
    $ip = [
        '117.242.145.103:8080',
        '165.138.225.250:8080',
        '31.24.238.11:8080',
        '194.116.198.212:3128',
        '165.227.100.201:80',
        '110.76.148.49:8080',
        '103.218.24.129:8080',
        '188.32.110.74:8888',
        '178.223.83.160:8080',
        '101.128.73.201:8080',
        '103.80.116.22:8080',
        '202.182.59.93:8080',
        '36.80.123.180:8080',
        '47.88.193.14:8081',
        '77.37.147.94:8081',
        '202.92.199.66:8080',
        '203.222.16.183:80',
        '36.76.97.62:3128',
        '186.92.139.167:8080',
        '45.251.37.201:9999',
        '103.26.246.22:8080',
        '178.62.28.110:8118',
        '69.195.129.219:1080',
        '62.81.76.18:8080',
        '117.239.240.202:53281',
        '138.19.213.172:80',
        '103.14.26.22:8080',
        '36.67.7.33:42619',
        '177.125.187.116:8080',
        '115.69.217.10:3128',
        '103.206.255.58:8080',
        '154.72.48.214:8080',
        '186.224.65.233:6006',
        '103.218.26.225:8080',
        '139.59.175.229:8118',
        '178.62.7.53:8118',
        '46.101.55.122:8118',
        '125.27.16.122:8080',
        '62.33.159.116:8080',
        '36.76.119.136:3128',
        '45.76.196.180:8080',
        '5.148.150.155:8080',
        '217.219.246.191:8080',
        '217.182.12.168:1081',
        '115.84.179.231:3128',
        '188.230.19.33:8080',
        '78.111.92.59:8080',
        '176.31.100.130:3128',
        '190.202.24.66:3128',
        '85.10.236.171:1080',
        '85.10.53.92:8080',
        '173.214.162.41:8080',
        '91.247.235.28:42619',
        '98.234.65.47:80',
        '103.194.232.163:53281'
    ];
    $ua = [
        'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3322.4 Safari/537.36',
        "Firefox 4.0.1 – MAC"=>"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",  
        "Firefox 4.0.1 – Windows"=>"Mozilla/5.0 (Windows NT 6.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",  
        "Opera 11.11 – MAC"=>"Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.8.131 Version/11.11",  
        "Opera 11.11 – Windows"=>"Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11",  
        "Chrome 17.0 – MAC"=>"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_0) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11", 

    ];
    $refer = ['https://tieba.baidu.com/index.html'];
    $header = [];
    foreach ($ip as $va) {
        $arr = explode(':', $va);
        $ipa = $arr[0] ;
        $port = $arr[1];
        $data = [
            'ip' => $ipa,
            'port' => intval($port),
            // 'header' => array_rand($header),
            'ua' => array_rand($ua),
            'refer' => array_rand($refer)
        ];
        $datas[] =$data;
    }
    return $datas;
}
$datas = getFakeData();
foreach ($datas as $key => $val) {
    // sleep(1);
    echo $key;
    $url = '';
    proxyIp($url, $val);
}