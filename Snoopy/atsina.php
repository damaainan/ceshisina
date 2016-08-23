<?php
/**
 * 用于模拟新浪微博登录! by CJ ( http://www.summerbluet.com )
 */

/** 定义项目路径 */
define('PROJECT_ROOT_PATH' , dirname(__FILE__));
define('COOKIE_PATH' , PROJECT_ROOT_PATH );

// 通用时间戳
define('TIMESTAMP', time());

// 出现问题的时候可以开启, 调试用的, 会在当前文件夹下面创建 LOG 文件
define('DEBUG', false);

/** 用来做模拟登录的新浪帐号 */
$username = "jiachunhui1988@sina.cn";
$password = "201251231134";

/* Fire Up */
$weiboLogin = new weiboLogin( $username, $password );
exit($weiboLogin->showTestPage( 'http://weibo.com/at/comment' ));

class weiboLogin {

    private $cookiefile;
    private $username;
    private $password;

    function __construct( $username, $password )
    {
        ( $username =='' ||  $password=='' ) && exit( "请填写用户名密码" );

        $this->cookiefile = COOKIE_PATH.'/cookie_sina_'.substr(base64_encode($username), 0, 10);
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * CURL请求
     * @param String $url 请求地址
     * @param Array $data 请求数据
     */
    function curlRequest($url, $data = false)
    {
        $ch = curl_init();

        $option = array(
                            CURLOPT_URL => $url,
                            CURLOPT_HEADER => 0,
                            CURLOPT_HTTPHEADER => array('Accept-Language: zh-cn','Connection: Keep-Alive','Cache-Control: no-cache'),
                            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.79 Safari/537.1",
                            CURLOPT_FOLLOWLOCATION => TRUE,
                            CURLOPT_MAXREDIRS => 4,
                            CURLOPT_RETURNTRANSFER => TRUE,
                            CURLOPT_COOKIEJAR => $this->cookiefile,
                            CURLOPT_COOKIEFILE => $this->cookiefile
                        );

        if ( $data ) {
            $option[CURLOPT_POST] = 1;
            $option[CURLOPT_POSTFIELDS] = $data;
        }

        curl_setopt_array($ch, $option);
        $response = curl_exec($ch);

        if (curl_errno($ch) > 0) {
            exit("CURL ERROR:$url " . curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    /**  @desc CURL 模拟新浪登录 */
    function doSinaLogin()
    {
        // Step 1 : Get tickit
        $preLoginData = $this->curlRequest('http://login.sina.com.cn/sso/prelogin.php?entry=weibo&callback=sinaSSOController.preloginCallBack&su=' .
                base64_encode($this->username) . '&client=ssologin.js(v1.3.16)');
        preg_match('/sinaSSOController.preloginCallBack\((.*)\)/', $preLoginData, $preArr);
        $jsonArr = json_decode($preArr[1], true);

        $this->debug('debug_1_Tickit', $preArr[1]);

        if (is_array($jsonArr)) {
            // Step 2 : Do Certification
            $postArr = array( 'entry' => 'weibo',
                    'gateway' => 1,
                    'from' => '',
                    'vsnval' => '',
                    'savestate' => 7,
                    'useticket' => 1,
                    'ssosimplelogin' => 1,
                    'su' => base64_encode(urlencode($this->username)),
                    'service' => 'miniblog',
                    'servertime' => $jsonArr['servertime'],
                    'nonce' => $jsonArr['nonce'],
                    'pwencode' => 'wsse',
                    'sp' => sha1(sha1(sha1($this->password)) . $jsonArr['servertime'] . $jsonArr['nonce']),
                    'encoding' => 'UTF-8',
                    'url' => 'http://weibo.com/ajaxlogin.php?framelogin=1&callback=parent.sinaSSOController.feedBackUrlCallBack',
                    'returntype' => 'META');

            $loginData = $this->curlRequest('http://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.3.19)', $postArr);

            $this->debug('debug_2_Certification_raw', $loginData);

            // Step 3 : SSOLoginState
            if ($loginData) {

                $matchs = $loginResultArr  =array();
                preg_match('/replace\(\'(.*?)\'\)/', $loginData, $matchs);

                $this->debug('debug_3_Certification_result', $matchs[1]);

                $loginResult = $this->curlRequest( $matchs[1] );
                preg_match('/feedBackUrlCallBack\((.*?)\)/', $loginResult, $loginResultArr);

                $userInfo = json_decode($loginResultArr[1],true);

                $this->debug('debug_4_UserInfo', $loginResultArr[1]);
            } else {
                exit('Login sina fail.');
            }
        } else {
            exit('Server tickit fail');
        }
    }

    /**  测试登录情况, 调用参考 */
    function showTestPage( $url ) {
        $file_holder = $this->curlRequest( $url );

        // 如果未登录情况, 登录后再尝试
        $isLogin = strpos( $file_holder, 'class="user_name"');
        if ( !$isLogin ){
            unset($file_holder);
            $this->doSinaLogin();
            $file_holder = $this->curlRequest( $url );
        }
        return $file_holder ;
    }

    /**  调试 */
    function debug( $file_name, $data ) {
        if ( DEBUG ) {
            file_put_contents( $file_name.'.txt', $data );
        }
    }

}
