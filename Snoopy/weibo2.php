<?php 
function getloginUser()
{

//定义登陆账号密码
         $array = array(0=>array('username'=>'username1',
                              'password'=>'password1'),
                1=>array('username'=>'usrname2',
                                     'password2'), 
                2=>array('username'=>'username3',
                         'password'=>'password3'));
         $key = rand(0,2);    //随机一个数
         $rst = $array[$key];
         return $rst;
}


function weibologin($username,$password)
{
        //******前置登录****//
     $time   = $this->get_js_timestamp();    //获取登陆的时间戳
     //调用新浪微博的前置接口
     $pre_login = 'http://login.sina.com.cn/sso/prelogin.php?entry=weibo&callback=sinaSSOController.preloginCallBack&su=&rsakt=mod&client=ssologin.js(v1.4.11)';  
    $this->snoopy->fetch($pre_login);
    $response = $this->snoopy->results;   //获取登陆结果
         
    $length     = strlen($response); 
    $left       = 0; 
    $right      = $length - 1; 
    while ( $left < $length )  
    if ( $response[$left] == '{' ) break; 
        else $left ++; 
    while ( $right > 0 ) 
        if ( $response[$right] == '}' ) break; 
            else $right --; 
    $response   = substr($response, $left, $right - $left + 1); 
    $info = array_merge(json_decode($response, TRUE), array('preloginTime'=>max($this->get_js_timestamp() - $time, 100),));
        
    //拼接参数      
    $feedbackurl    = $this->makeUrl('http://weibo.com/ajaxlogin.php', array( 
        'framelogin'        => 1,  
        'callback'          => 'parent.sinaSSOController.feedBackUrlCallBack',  
        )); 
        $datas  = array( 
            'encoding'          => 'UTF-8',  
            'entry'             => 'weibo',  
            'from'              => '',  
            'gateway'           => 1,  
            'nonce'             => $info['nonce'],  
            'prelt'             => $info['preloginTime'],  
            'pwencode'          => 'rsa2',  
            'returntype'        => 'META',  
            'rsakv'             => $info['rsakv'],  
            'savestate'         => 7,  
            'servertime'        => $info['servertime'],  
            'service'           => 'miniblog',  
            'sp'                => $this->encode_password($info['pubkey'], $password, $info['servertime'], $info['nonce']),  
            'ssosimplelogin'    => 1,  
            'su'                => $this->encode_username($username),  
            'url'               => $feedbackurl,  
            'useticket'         => 1,  
            'vsnf'              => 1,
            'pagerefer'         => 'http://weibo.com/a/download'            
        );
        
        $url  = $this->makeUrl('http://login.sina.com.cn/sso/login.php', array( 
            'client'    => 'ssologin.js(v1.4.2)',  
        ), FALSE);
                
                $agent = array();   //设置user_agent 该处自己定义
        $this->snoopy->agent = $agent[0];
        $this->snoopy->referer = "http://www.weibo.com";
        #$this->snoopy->rawheaders["X_FORWARDED_FOR"] = "180.149.135.239";      
        $this->snoopy->submit($url,$datas); 
        $response   = $this->snoopy->results;
        return $response;
}

//获取登陆的时间戳
function get_js_timestamp() 
 { 
     return time() * 1000 + rand(0, 999); 
}

//构造URL
function makeUrl($url, $info, $encode = TRUE) 
 { 
            if ( !is_array($info) || empty($info) ) return $url; 
            $components = parse_url($url); 
            if ( array_key_exists('query', $components) )  
                $query  = parse_str($components['query']); 
            else  
                $query  = array(); 
            if ( is_string($info) ) $info = parse_str($info); 
            $query      = array_merge($query, $info); 
            $query      = $encode 
                        ? http_build_query($query) 
                        : $this->http_build_query_no_encode($query); 
            $components['scheme']   = array_key_exists('scheme', $components) 
                                    ? $components['scheme'] . '://' 
                                    : ''; 
            $components['user']     = array_key_exists('user', $components) 
                                    ? $components['user'] . ':' . $components[HTTP_URL_PASS] . '@' 
                                    : ''; 
            $components['host']     = array_key_exists('host', $components) 
                                    ? $components['host'] 
                                    : ''; 
            $components['port']     = array_key_exists('port', $components) 
                                    ? ':' . $components['port'] 
                                    : ''; 
            $components['path']     = array_key_exists('path', $components) 
                                    ? '/' . ltrim($components['path'], '/') 
                                    : ''; 
            $components['query']    = $query  
                                    ? '?' . $query 
                                    : ''; 
            $components['fragment'] = array_key_exists('fragment', $components) 
                                    ? '#' . $components['fragment'] 
                                    : ''; 
            return sprintf('%s%s%s%s%s%s%s', $components['scheme'], $components['user'], $components['host'],  
                                        $components['port'], $components['path'],  
                                        $components['query'], $components['fragment']); 
}   

//构造用户名参数
function encode_username($username)
 { 
       return base64_encode(urlencode($username)); 
 }

function http_build_query_no_encode($datas)
{ 
    $r  = array(); 
    foreach ( $datas as $k => $v )  
         $r[]    = $k . '=' . $v; 
    return implode('&', $r); 
} 

//使用Nodejs构造password参数   
function encode_password($pub_key, $password, $servertime, $nonce)
{ 
        #这里是要用nodejs执行新浪的js文件 
        $response   = `/usr/local/bin/node /alidata/www/htdocs/weiboproject/html/js/sinasso.js "$pub_key" "$servertime" "$nonce" "$password"`; 
        return substr($response, 0, strlen($response) - 1); 
}







public function lastlogin()
{
         
         $userarr = $this->getloginUser();
         $username = $userarr['username'];
         $password = $userarr['password'];
         $response = $this->weibologin($username,$password);
         
         #$response = 'http://weibo.com/ajaxlogin.php?framelogin=1&callback=parent.sinaSSOController.feedBackUrlCallBack&ssosavestate=1389534895&ticket=ST-MTg0MDg3OTgzNQ==-1386942895-gz-63F61508457F4B8D9B339477197EAA51&retcode=0';
         $sign       = 'location.replace(\''; 
        $response   = substr($response, strpos($response, $sign) + strlen($sign));
        
        $responsearr = explode('location.replace("',$response);
        
        if(count($responsearr) == 1)
        {
           $responsearr2 = explode("')",$responsearr[0]);
        }else if(count($responsearr) == 2)
        {
             $responsearr2 = explode('"',$responsearr[1]);
        }
        $location = $responsearr2[0];
        
    //登陆失败  
        $reasonarr = explode("reason=",$location);
        if(count($reasonarr) == 2)
        {
            $reasonstr = urldecode($reasonarr[1]);   //此处可以获取模拟登陆失败的原因
            return false;
        }
        
        $response = curl_request($location);      //登陆，将登陆成功的cookie写入
        $length     = strlen($response); 
        $left       = 0; 
        $right      = $length - 1; 
        while ( $left < $length )  
            if ( $response[$left] == '{' ) break; 
            else $left ++; 
        while ( $right > 0 ) 
            if ( $response[$right] == '}' ) break; 
            else $right --; 
        $response   = substr($response, $left, $right - $left + 1); 
        $rst = json_decode($response, true);
        //print_r($rst);
        //exit;
        return $rst;
     }

//通过curl写入cookie
//****抓取内容****//
     define('REQUEST_METHOD_GET',                'GET'); 
    define('REQUEST_METHOD_POST',               'POST'); 
    define('REQUEST_METHOD_HEAD',               'HEAD'); 

    define('COOKIE_FILE',                       '/tmp/sina.login.cookie');
function curl_switch_method($curl, $method) { 
    switch ( $method) { 
        case REQUEST_METHOD_POST: 
            curl_setopt($curl, CURLOPT_POST, TRUE); 
            break; 
        case REQUEST_METHOD_HEAD: 
            curl_setopt($curl, CURLOPT_NOBODY, TRUE); 
            break; 
        case REQUEST_METHOD_GET: 
        default: 
            curl_setopt($curl, CURLOPT_HTTPGET, TRUE); 
            break; 
    } 
    } 
    function curl_set_headers($curl, $headers) { 
    if ( emptyempty($headers) ) return ; 
    if ( is_string($headers) )  
        $headers    = explode("\r\n", $headers); 
    #类型修复 
    foreach ( $headers as &$header )  
        if ( is_array($header) )  
            $header = sprintf('%s: %s', $header[0], $header[1]); 
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); 
    } 
    function curl_set_datas($curl, $datas) { 
    if (empty($datas) ) return ; 
    curl_setopt($curl, CURLOPT_POSTFIELDS, $datas); 
    } 
    function curl_request($url, $method = REQUEST_METHOD_GET, $datas = NULL, $headers = NULL) { 
    static  $curl; 
    if ( !$curl ) 
        $curl   = curl_init(); 
    curl_switch_method($curl, $method); 
    curl_setopt($curl, CURLOPT_URL,                     $url); 
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,          TRUE); 
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION,          TRUE); 
    curl_setopt($curl, CURLOPT_AUTOREFERER,             TRUE); 
    curl_setopt($curl, CURLOPT_COOKIEJAR,               COOKIE_FILE); 
    curl_setopt($curl, CURLOPT_COOKIESESSION,           TRUE); 
    if ( $datas )  
        curl_set_datas($curl, $datas); 
    if ( $headers)  
        curl_set_headers($curl, $headers); 
    $response   = curl_exec($curl); 
    if ( $errno = curl_errno($curl) ) {
         #print_r($errno);
        error_log(sprintf("%10d\t%s\n", $errno, curl_error($curl)), 3, '://stderr'); 
        return FALSE; 
    } 
    return $response; 
}

// 运用方法直接调用lastlogin方法，就可以进行后续逻辑处理了。