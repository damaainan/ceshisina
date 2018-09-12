# php正则HTML标签、属性等

 时间 2017-09-21 17:30:43  

原文[http://www.uedbox.com/php-regexp-html/]

```php
$str=preg_replace("/\s+/", " ", $str); //过滤多余回车
$str=preg_replace("/<[ ]+/si","<",$str); //过滤<__("<"号后面带空格)
   
$str=preg_replace("/<\!--.*?-->/si","",$str); //注释
$str=preg_replace("/<(\!.*?)>/si","",$str); //过滤DOCTYPE
$str=preg_replace("/<(\/?html.*?)>/si","",$str); //过滤html标签
$str=preg_replace("/<(\/?head.*?)>/si","",$str); //过滤head标签
$str=preg_replace("/<(\/?meta.*?)>/si","",$str); //过滤meta标签
$str=preg_replace("/<(\/?body.*?)>/si","",$str); //过滤body标签
$str=preg_replace("/<(\/?link.*?)>/si","",$str); //过滤link标签
$str=preg_replace("/<(\/?form.*?)>/si","",$str); //过滤form标签
$str=preg_replace("/cookie/si","COOKIE",$str); //过滤COOKIE标签
   
$str=preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si","",$str); //过滤applet标签
$str=preg_replace("/<(\/?applet.*?)>/si","",$str); //过滤applet标签
   
$str=preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si","",$str); //过滤style标签
$str=preg_replace("/<(\/?style.*?)>/si","",$str); //过滤style标签
   
$str=preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si","",$str); //过滤title标签
$str=preg_replace("/<(\/?title.*?)>/si","",$str); //过滤title标签
   
$str=preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si","",$str); //过滤object标签
$str=preg_replace("/<(\/?objec.*?)>/si","",$str); //过滤object标签
   
$str=preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si","",$str); //过滤noframes标签
$str=preg_replace("/<(\/?noframes.*?)>/si","",$str); //过滤noframes标签
   
$str=preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si","",$str); //过滤frame标签
$str=preg_replace("/<(\/?i?frame.*?)>/si","",$str); //过滤frame标签
   
$str=preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si","",$str); //过滤script标签
$str=preg_replace("/<(\/?script.*?)>/si","",$str); //过滤script标签
$str=preg_replace("/javascript/si","Javascript",$str); //过滤script标签
$str=preg_replace("/vbscript/si","Vbscript",$str); //过滤script标签
$str=preg_replace("/on([a-z]+)\s*=/si","On\\1=",$str); //过滤script标签
$str=preg_replace("/&#/si","&＃",$str); //过滤script标签，如javAsCript:alert(
```

清除空格，换行
```php
function DeleteHtml($str)
{
    $str = trim($str);
    $str = strip_tags($str,"");
    $str = ereg_replace("\t","",$str);
    $str = ereg_replace("\r\n","",$str);
    $str = ereg_replace("\r","",$str);
    $str = ereg_replace("\n","",$str);
    $str = ereg_replace(" "," ",$str);
    return trim($str);
}
```

过滤所有html标签的正则表达式：

    </?[^>]+>
      
    //过滤所有html标签的属性的正则表达式：
      
    $html = preg_replace("/<([a-zA-Z]+)[^>]*>/","<\\1>",$html);
    

过滤除了src之外的所有属性

    $str= preg_replace('/\s(?!src)[a-zA-Z]+=[\'\"]{1}[^\'\"]+[\'\"]{1}/iu',' $str);
    

过滤设置过滤除了alt和src之外的所有属性

    $str = preg_replace('/\s(?!(src|alt))[a-zA-Z]+=[^\s]*/iu',' ', $str);
    

过滤所有html标签的属性的正则表达式:

    $str = preg_replace("/<([a-z]+)[^>]*>/i","",$str );
    

只过滤alt属性的正则表达式:

    (\s)alt=[^\s]*
    

过滤所有html标签的属性的正则表达式:

```php
$search = array ("'<script[^>]*?>.*?</script>'si", // 去掉 javascript 
"'<[\/\!]*?[^<>]*?>'si", // 去掉 HTML 标记 
"'([\r\n])[\s]+'", // 去掉空白字符 
"'&(quot|#34);'i", // 替换 HTML 实体 
"'&(amp|#38);'i", 
"'&(lt|#60);'i", 
"'&(gt|#62);'i", 
"'&(nbsp|#160);'i" 
); // 作为 PHP 代码运行 
$replace = array ("","","\\1","\"","&","<",">"," "); 
$html = preg_replace($search, $replace, $html);
```