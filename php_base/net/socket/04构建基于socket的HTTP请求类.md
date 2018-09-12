# 【PHPsocket编程专题(实战篇③)】构建基于socket的HTTP请求类

PHP- - -

该代码是两年前写的,现在看起来有点渣了,仅仅是提供一个思路,现在做一些Api开发的时候官方会有一些SDK,这些SDK其实原理都是通过socket来通讯的,事实我个人主张用curl更方便,当然前提是你的主机上的PHP安装了此扩展

    

```php
<?php
class Http{
  const CRLF = "\r\n";
  //把要拼接的内容放在数组里面最后用array_merge和import函数来拼接;
  private $line = array(); //请求行
  private $url = ''; //url;
  private $head = array(); //请求的主体;
  private $host = array(); //请求头的主机信息;
  private $urlInfo = array(); //pathInfo地址栏的url信息;
  private $query = ""; //pathInfo里面的query信息;
  private $body = array();
  private $fo = null; //socket资源;
  private $errno = -1; //socket资源打开的错误代码;
  private $errstr = "";//socket资源打开的错误描述;
  //public $response = "";//返回的响应字符串;
  public function __construct($url){
    $this->contact($url);
  }
  private function setLine($method){ //设置请求行
    $path = isset($this->urlInfo['path']) ? $this->urlInfo['path'] : "/";
    $this->line[] = $method . ' ' . $path . $this->query . ' ' . "HTTP/1.1";
  }
  public function setHead($content){ //设置请求头
    $this->head[] = $content;
  }
  private function setBody($data){ //请求主体
    $bodystr = '';//请求主体的内容;
    if (is_array($data)) {
      $bodystr = http_build_query($data);
    } else {
      $bodystr = $data;
    }
    $this->body[] = $bodystr;
  }
  private function contact($url){ //链接资源句柄
    $this->urlInfo = parse_url($url);
    $this->host[] = "Host: " . $this->urlInfo['host'];
    if (isset($this->urlInfo['query'])) {
      $this->query = "?" . $this->urlInfo['query'];
    } else {
      $this->query = "";
    }
    $port = isset($this->urlInfo['port']) ? $urlInfo['port'] : 80;//端口;
    $this->fo = fsockopen($this->urlInfo['host'] , $port , $this->errno , $this->errstr , 2);
  }
  public function post($data){ //发送post请求
    //这里的$data有可能是array的数组,也有可以能是key1=value1&key2=value2这样的字符串;
    $this->setLine("POST");
    $this->setHead("Content-Type: application/x-www-form-urlencoded");//注意这段话的大小写;
    $this->setBody($data);
    $strlen = strlen($this->body[0]);
    $this->setHead("Content-length: " . $strlen);
    $result = array_merge($this->line , $this->host , $this->head , array("") , $this->body , array(""));
    $request = implode(self::CRLF , $result);
    return $this->response($request);
  }
  public function get(){ //发送get请求
    $this->setLine("GET");
    $result = array_merge($this->line , $this->host , array("") , array(""));//特别注意,这个地方要空行再空行;
    $request = implode(self::CRLF , $result);
    return $this->response($request);
  }
  public function response($requestStr){//获取的响应资源;
    fputs($this->fo , $requestStr);
    $result = "";
    while (!feof($this->fo)) {
      $result .= fread($this->fo , 1024);
    }
    $this->close();
    return $result;
  }
  public function close(){
    fclose($this->fo);
  }
}
```

核心函数:

    

    fsockopen(主机名称，端口号码，错误号的接受变量，错误提示的接受变量，超时时间)

通过fsockopen就可以打开一个socket通道,这时候可以使用fwrite或者fputs函数中的任意一个,把http的请求格式发给fsockopen()打开的句柄,这时候一个socket模拟的http请求就完成了.但是还得通过fread函数把响应头给取回来;

