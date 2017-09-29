# [mb_convert_encoding使用举例][0]

 2015-09-15 17:11  127人阅读  



### mb_convert_encoding函数使用举例.  

`mb_convert_encoding`函数功能非常强大，如果你能够知道一种字符的编码格式，基本上都可以转换成utf-8格式。

说明：


        mb_convert_encoding — 转换字符的编码  
        string mb_convert_encoding ( string $str , string $to_encoding [, mixed $from_encoding = mb_internal_encoding() ] )  
          
        str             要编码的 string。  
        to_encoding     str要转换成的编码类型。  
        from_encoding   在转换前通过字符代码名称来指定。它可以是一个 array 也可以是逗号分隔的枚举列表。 如果没有提供 from_encoding，则会使用内部（internal）编码。  
          
        支持的编码：  
            UCS-4*  
            UCS-4BE  
            UCS-4LE*  
            UCS-2  
            UCS-2BE  
            UCS-2LE  
            UTF-32*  
            UTF-32BE*  
            UTF-32LE*  
            UTF-16*  
            UTF-16BE*  
            UTF-16LE*  
            UTF-7  
            UTF7-IMAP  
            UTF-8*  
            ASCII*  
            EUC-JP*  
            SJIS*  
            eucJP-win*  
            SJIS-win*  
            ISO-2022-JP  
            ISO-2022-JP-MS  
            CP932  
            CP51932  
            SJIS-mac** (别名： MacJapanese)  
            SJIS-Mobile#DOCOMO** (别名： SJIS-DOCOMO)  
            SJIS-Mobile#KDDI** (别名： SJIS-KDDI)  
            SJIS-Mobile#SOFTBANK** (别名： SJIS-SOFTBANK)  
            UTF-8-Mobile#DOCOMO** (别名： UTF-8-DOCOMO)  
            UTF-8-Mobile#KDDI-A**  
            UTF-8-Mobile#KDDI-B** (别名： UTF-8-KDDI)  
            UTF-8-Mobile#SOFTBANK** (别名： UTF-8-SOFTBANK)  
            ISO-2022-JP-MOBILE#KDDI** (别名： ISO-2022-JP-KDDI)  
            JIS  
            JIS-ms  
            CP50220  
            CP50220raw  
            CP50221  
            CP50222  
            ISO-8859-1*  
            ISO-8859-2*  
            ISO-8859-3*  
            ISO-8859-4*  
            ISO-8859-5*  
            ISO-8859-6*  
            ISO-8859-7*  
            ISO-8859-8*  
            ISO-8859-9*  
            ISO-8859-10*  
            ISO-8859-13*  
            ISO-8859-14*  
            ISO-8859-15*  
            byte2be  
            byte2le  
            byte4be  
            byte4le  
            BASE64  
            HTML-ENTITIES  
            7bit  
            8bit  
            EUC-CN*  
            CP936  
            GB18030**  
            HZ  
            EUC-TW*  
            CP950  
            BIG-5*  
            EUC-KR*  
            UHC (CP949)  
            ISO-2022-KR  
            Windows-1251 (CP1251)  
            Windows-1252 (CP1252)  
            CP866 (IBM866)  
            KOI8-R*  

  
示例代码： 

```php
<?php  
header("Content-type: text/html; charset=utf-8");  
  
//测试mb_convert_encoding函数将HTML-ENTITIES转换为utf8格式  
$str = "web 前端高级开发工程师";  
echo mb_convert_encoding($str, 'UTF-8', 'HTML-ENTITIES');  
echo "<br/>";  
  
//测试mb_convert_encoding与iconv的utf8转换为GBK  
$data = "你好世界";  
  
$str1 = mb_convert_encoding($data, "GBK", "UTF-8");  
$str2 = iconv("UTF-8","GB2312//IGNORE",$data);  
  
if($str1==$str2){  
    echo "转换之后的字符相同<br/>";      
}  
  
//测试mb_convert_encoding与iconv的GBK转换为utf8  
  
$str3 = mb_convert_encoding($str1, "UTF-8", "GBK");  
$str4 = iconv("GB2312","UTF-8//IGNORE",$str2);  
  
if($str3==$str4){  
    echo "转换之后的字符相同<br/>";  
    echo "逆向转换之后的字符是：".$str3."<br/>";  
}  
  
  
//测试base64与mb_convert_encoding转换  
$str5 = base64_encode("你好,世界");  
echo mb_convert_encoding($str5, "UTF-8", "BASE64");  
  
?>  
```
  
### iconv()函数与mb_convert_encoding()函数的比较.

  
    iconv — Convert string to requested character encoding([PHP][9] 4 >= 4.0.5, PHP 5)  
    mb_convert_encoding — Convert character encoding(PHP 4 >= 4.0.6, PHP 5)  
  
#### <1>.用法：  

    
    string mb_convert_encoding ( string str, string to_encoding [, mixed from_encoding] )  

需要先启用 mbstring 扩展库，在 php.ini里将; extension=php_mbstring.dll 前面的 ; 去掉

    string iconv ( string in_charset, string out_charset, string str )  

#### <2>.注意：  

第二个参数，除了可以指定要转化到的编码以外，还可以增加两个后缀：`//TRANSLIT` 和 `//IGNORE`，其中：  
`//TRANSLIT` 会自动将不能直接转化的字符变成一个或多个近似的字符，  
`//IGNORE` 会忽略掉不能转化的字符，而默认效果是从第一个非法字符截断。  
Returns the converted string or FALSE on failure.  
  
#### < 3>.使用：  

1. 发现iconv在转换字符"-"到gb2312时会出错，如果没有ignore参数，所有该字符后面的字符串都无法被保存。不管怎么样，这个"-"都无法转换成功，无法输出。另外`mb_convert_encoding`没有这个bug.  
2. `mb_convert_encoding` 可以指定多种输入编码，它会根据内容自动识别,但是执行效率比iconv差太多；如：

```
    $str = mb_convert_encoding($str,"euc-jp","ASCII,JIS,EUC-JP,SJIS,UTF- 8");
```

“ASCII,JIS,EUC-JP,SJIS,UTF-8”的顺序不同效果也有差异.  

3. 一般情况下用 iconv，只有当遇到无法确定原编码是何种编码，或者iconv转化后无法正常显示时才用`mb_convert_encoding` 函数.  

from_encoding is specified by character code name before conversion. it can be array or string - comma separated enumerated list. If it is not specified, the internal encoding will be used.  

```
    $str = mb_convert_encoding($str, "UCS-2LE", "JIS, eucjp-win, sjis-win");  
    $str = mb_convert_encoding($str, "EUC-JP', "auto");  
```

#### <4>.例子：  

    $content = iconv("GBK", "UTF-8", $content);  
    $content = mb_convert_encoding($content, "UTF-8", "GBK"); 

[0]: /u011132987/article/details/48470207
[9]: http://lib.csdn.net/base/php