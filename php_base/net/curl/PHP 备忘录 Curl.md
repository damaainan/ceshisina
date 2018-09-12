# PHP 备忘录 Curl（重中之重的是代理设置以及拿来即用的并发函数实例）

 时间 2016-11-30 18:31:43  简书

原文[http://www.jianshu.com/p/82b662376cc7][1]


## 常用选项
```php
// HTTP-HEADER 头
$arrHearders = [
    'Accept-Language:zh-CN,zh;q=0.8',
    'Connection:keep-alive',
];

$strUrl = '请求地址';

$ch = curl_init();

// 设置请求 User-Agent， 值是字符串
curl_setopt($ch, CURLOPT_USERAGENT, $strUserAgent);

// 设置请求 HTTP-HEADER 头，值是数组
curl_setopt($ch, CURLOPT_HTTPHEADER, $arrHearders);

// 禁止 SSL 验证
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// 请求的 URL 地址
curl_setopt($ch, CURLOPT_URL, $strUrl);

// curl_getinfo 结果里面增加请求的 Headers 信息
curl_setopt($ch, CURLINFO_HEADER_OUT, true);

// cURL 函数执行的最长秒数
curl_setopt($ch, CURLOPT_TIMEOUT, 300);

// 在尝试连接时等待的秒数。设置为0，则无限等待
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);

// 执行结果中是否包含响应的 Headers 头信息
curl_setopt($ch, CURLOPT_HEADER, false);

// curl_exec 执行的结果不自动打印出来
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// 执行
$result = curl_exec($ch);

// 获得执行的的信息
$arrCurlExecInfo = curl_getinfo($ch);

// 关闭 curl 
curl_close($ch);

var_dump($arrCurlExecInfo);

if (!isset($arrCurlExecInfo['http_code'])
    || 200 != $arrCurlExecInfo['http_code']) {
    die('请求失败');
}

if (empty($result)) {
    die('请求结果为空');
}

var_dump($result);
```
## POST 请求
```php
// 设置为 POST 请求
curl_setopt($ch, CURLOPT_POST, 1);

// POST 请求的数据. 如果数据是 URL-encoded 字符串时, 数据会被编码成 application/x-www-form-urlencoded. 如果数据是 Array 数组, 数据编码成 multipart/form-data
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
```
## COOKIE 相关
```php
// 一部分是自行设定 COOKIE 值
curl_setopt($ch, CURLOPT_COOKIE, '设置请求中的 COOKIE 部分'); 

// 另外一部分是先访问一次，拿到 COOKIE 保存到指定文件，然后再次访问的时候从文件当中读取出来
curl_setopt($ch, CURLOPT_COOKIEJAR, '指定保存到的文件'); // 把访问得到的 COOKIE 保存到指定文件里面
curl_setopt($ch, CURLOPT_COOKIEFILE, '保存 COOKIE 的文件路径名称'); // 从指定文件当中读取 COOKIE
```
## 代理相关
```php
// 代理协议  CURLPROXY_HTTP (默认值，代理为 HTTP、HTTPS 都设置此值)、 CURLPROXY_SOCKS4、 CURLPROXY_SOCKS5、 CURLPROXY_SOCKS4A、CURLPROXY_SOCKS5_HOSTNAME
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

// 代理地址
curl_setopt($ch, CURLOPT_PROXY, $strProxyServer);

// 代理端口号，也可以写在代理地址里面
curl_setopt($ch, CURLOPT_PROXYPORT, $strProxyPort);

// 代理的用户名和密码
curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$strProxyUser:$strProxyPassWord");
```
## 批处理相关
```php
/**
* 调用方法时传入 url 数组，返回 curl_info 信息、错误信息、执行结果信息
* @param  arr $arrUrls      url 数组
* @retunr arr $arrResponses 返回 curl 执行情况、错误信息、返回结果的多维数据
* [
*    '$arrUrls键名' => [
*        'curl_info'     => curl 执行情况信息,
*        'curl_error'    => curl 执行错误信息,
*        'curl_results'  => curl 执行执行结果,
*     ],
*    '$arrUrls键名' => [
*        'curl_info'     => curl 执行情况信息,
*        'curl_error'    => curl 执行错误信息,
*        'curl_results'  => curl 执行执行结果,
*     ],
*     ......
* ]
*/

function curl_multi_task($arrUrls) {

    // 创建批处理 cURL 句柄
    $mh = curl_multi_init();

    $responsesKeyMap = [];

    $arrResponses = [];

    // 添加 Curl 批处理会话 
    foreach ($arrUrls as $urlsKey=>$strUrlVal) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $strUrlVal);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($mh, $ch);
        $strCh = (string) $ch;
        $responsesKeyMap[$strCh] = $urlsKey;
    }

    // 批处理执行
    $active = null;

    do {

        $mrc = curl_multi_exec($mh, $active);

    } while (CURLM_CALL_MULTI_PERFORM == $mrc);

    while ($active && CURLM_OK == $mrc) {

        if (-1 == curl_multi_select($mh)) {
            usleep(100);
        }

        do {

            $mrc = curl_multi_exec($mh, $active);

            if (CURLM_OK == $mrc) {
                while ($multiInfo = curl_multi_info_read($mh)) {
                    $curl_info    = curl_getinfo($multiInfo['handle']);
                    $curl_error   = curl_error($multiInfo['handle']); 
                    $curl_results = curl_multi_getcontent($multiInfo['handle']);
                    $strCh       = (string) $multiInfo['handle'];
                    $arrResponses[$responsesKeyMap[$strCh]] = compact('curl_info', 'curl_error', 'curl_results');
                    curl_multi_remove_handle($mh, $multiInfo['handle']);
                    curl_close($multiInfo['handle']);
                }
            }

        } while (CURLM_CALL_MULTI_PERFORM == $mrc);
    }

    // 关闭资源
    curl_multi_close($mh);

    return $arrResponses;
}
```
附：

* [PHP 的 file_get_contents 函数访问页面][5]
* [Wget 代理方式的命令格式][6]


[1]: http://www.jianshu.com/p/82b662376cc7
[5]: http://www.jianshu.com/p/88aa3c16959c
[6]: http://www.jianshu.com/p/48e0b192134c