## PHP如何防止XSS攻击

来源：[https://segmentfault.com/a/1190000012982613](https://segmentfault.com/a/1190000012982613)

PHP防止XSS跨站脚本攻击的方法:是针对非法的HTML代码包括单双引号等，使用htmlspecialchars()函数 。

在使用`htmlspecialchars()`函数的时候注意`第二个参数`, 直接用htmlspecialchars($string) 的话,第二个参数默认是ENT_COMPAT,函数默认`只是转化双引号("), 不对单引号(')做转义`.

所以,htmlspecialchars函数更多的时候要加上第二个参数, 应该这样用: htmlspecialchars($string,ENT_QUOTES).当然,如果需要不转化如何的引号,用htmlspecialchars($string,ENT_NOQUOTES).

另外, 尽量少用`htmlentities`, 在全部英文的时候htmlentities和htmlspecialchars没有区别,都可以达到目的.但是,中文情况下, htmlentities却会转化所有的html代码，连同里面的它无法识别的中文字符也给转化了。

htmlentities和htmlspecialchars这两个函数对 `'`之类的字符串支持不好,都不能转化, 所以用htmlentities和htmlspecialchars转化的字符串只能防止XSS攻击,不能防止SQL注入攻击.

所有有打印的语句如echo，print等 在打印前都要使用htmlentities() 进行过滤，这样可以防止Xss，注意中文要写出htmlentities($name,ENT_NOQUOTES,GB2312) 。

(1).网页不停地刷新 `<meta http-equiv="refresh" content="0;">`

(2).嵌入其它网站的链接 `<iframe src=http://xxxx width=250 height=250></iframe>`  除了通过正常途径输入XSS攻击字符外，还可以绕过JavaScript校验，通过修改请求达到XSS攻击的目的.

```php
<?php
//php防注入和XSS攻击通用过滤
$_GET     && SafeFilter($_GET);
$_POST    && SafeFilter($_POST);
$_COOKIE  && SafeFilter($_COOKIE);
  
function SafeFilter (&$arr) 
{
   $ra=Array('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/','/script/','/javascript/','/vbscript/','/expression/','/applet/'
   ,'/meta/','/xml/','/blink/','/link/','/style/','/embed/','/object/','/frame/','/layer/','/title/','/bgsound/'
   ,'/base/','/onload/','/onunload/','/onchange/','/onsubmit/','/onreset/','/onselect/','/onblur/','/onfocus/',
   '/onabort/','/onkeydown/','/onkeypress/','/onkeyup/','/onclick/','/ondblclick/','/onmousedown/','/onmousemove/'
   ,'/onmouseout/','/onmouseover/','/onmouseup/','/onunload/');
     
   if (is_array($arr))
   {
     foreach ($arr as $key => $value) 
     {
        if (!is_array($value))
        {
          if (!get_magic_quotes_gpc())  //不对magic_quotes_gpc转义过的字符使用addslashes(),避免双重转义。
          {
             $value  = addslashes($value); //给单引号（'）、双引号（"）、反斜线（\）与 NUL（NULL 字符）
             加上反斜线转义
          }
          $value       = preg_replace($ra,'',$value);     //删除非打印字符，粗暴式过滤xss可疑字符串
          $arr[$key]     = htmlentities(strip_tags($value)); //去除 HTML 和 PHP 标记并转换为 HTML 实体
        }
        else
        {
          SafeFilter($arr[$key]);
        }
     }
   }
}
?>
$str = 'www.90boke.com<meta http-equiv="refresh" content="0;">';
SafeFilter ($str); //如果你把这个注释掉，提交之后就会无休止刷新
echo $str;
```

```php
//------------------------------php防注入和XSS攻击通用过滤-----Start--------------------------------------------//
function string_remove_xss($html) {
    preg_match_all("/\<([^\<]+)\>/is", $html, $ms);
 
    $searchs[] = '<';
    $replaces[] = '<';
    $searchs[] = '>';
    $replaces[] = '>';
 
    if ($ms[1]) {
        $allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote';
        $ms[1] = array_unique($ms[1]);
        foreach ($ms[1] as $value) {
            $searchs[] = "<".$value.">";
 
            $value = str_replace('&', '_uch_tmp_str_', $value);
            $value = string_htmlspecialchars($value);
            $value = str_replace('_uch_tmp_str_', '&', $value);
 
            $value = str_replace(array('\\', '/*'), array('.', '/.'), $value);
            $skipkeys = array('onabort','onactivate','onafterprint','onafterupdate','onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
                    'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload','onbeforeupdate','onblur','onbounce','oncellchange','onchange',
                    'onclick','oncontextmenu','oncontrolselect','oncopy','oncut','ondataavailable','ondatasetchanged','ondatasetcomplete','ondblclick',
                    'ondeactivate','ondrag','ondragend','ondragenter','ondragleave','ondragover','ondragstart','ondrop','onerror','onerrorupdate',
                    'onfilterchange','onfinish','onfocus','onfocusin','onfocusout','onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete',
                    'onload','onlosecapture','onmousedown','onmouseenter','onmouseleave','onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel',
                    'onmove','onmoveend','onmovestart','onpaste','onpropertychange','onreadystatechange','onreset','onresize','onresizeend','onresizestart',
                    'onrowenter','onrowexit','onrowsdelete','onrowsinserted','onscroll','onselect','onselectionchange','onselectstart','onstart','onstop',
                    'onsubmit','onunload','javascript','script','eval','behaviour','expression','style','class');
            $skipstr = implode('|', $skipkeys);
            $value = preg_replace(array("/($skipstr)/i"), '.', $value);
            if (!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
                $value = '';
            }
            $replaces[] = empty($value) ? '' : "<" . str_replace('&quot;', '"', $value) . ">";
        }
    }
    $html = str_replace($searchs, $replaces, $html);
 
    return $html;
}
//php防注入和XSS攻击通用过滤 
function string_htmlspecialchars($string, $flags = null) {
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = string_htmlspecialchars($val, $flags);
        }
    } else {
        if ($flags === null) {
            $string = str_replace(array('&', '"', '<', '>'), array('&', '&quot;', '<', '>'), $string);
            if (strpos($string, '&#') !== false) {
                $string = preg_replace('/&((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
            }
        } else {
            if (PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if (!defined('CHARSET') || (strtolower(CHARSET) == 'utf-8')) {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }
 
    return $string;
}
//------------------php防注入和XSS攻击通用过滤-----End--------------------------------------------//
```

PHP中的设置 
PHP5.2以上版本已支持HttpOnly参数的设置，同样也支持全局的HttpOnly的设置，在php.ini中

```
----------------------------------------------------- 
 session.cookie_httponly = 
-----------------------------------------------------
```

设置其值为1或者TRUE，来开启全局的Cookie的HttpOnly属性，当然也支持在代码中来开启：

```php
<?php 
ini_set("session.cookie_httponly", 1);   
// or session_set_cookie_params(0, NULL, NULL, NULL, TRUE);   
?>
```

Cookie操作函数setcookie函数和setrawcookie函数也专门添加了第7个参数来做为HttpOnly的选项，开启方法为：

```php

<?php  
setcookie("abc", "test", NULL, NULL, NULL, NULL, TRUE);   
setrawcookie("abc", "test", NULL, NULL, NULL, NULL, TRUE);  
?>
```

老版本的PHP就不说了。没企业用了吧。

