## PHP CURL方式使用代理访问网站

来源：[https://www.swoole.org.cn/index.php/archives/339/](https://www.swoole.org.cn/index.php/archives/339/)

时间 2018-12-28 17:19:40


抓取接口数据 但对方网站有限速规则 , 为了防止被限制 使用

```php
curl_setopt ($ch, CURLOPT_URL, $requestUrl);
  
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
  
curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  
curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
  
curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1"); //代理服务器地址
  
curl_setopt($ch, CURLOPT_PROXYPORT, 80); //代理服务器端口
  
//curl_setopt($ch, CURLOPT_PROXYUSERPWD, ":"); //http代理认证帐号，名称:pwd的格式
  
curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用http代理模式

curl_setopt($ch, CURLOPT_USERAGENT, 'curl); //设置用户代理
```

最后更新于2018-12-28  09:19:40 并被添加「curl」标签，已有 3 位童鞋阅读过。

