<?php 
header("Content-type:text/html; Charset=utf-8");
function unicode_encode($name)
{
    $name = iconv('UTF-8', 'UCS-2', $name);
    $len = strlen($name);
    $str = '';
    for ($i = 0; $i < $len - 1; $i = $i + 2)
    {
        $c = $name[$i];
        $c2 = $name[$i + 1];
        if (ord($c) > 0)
        {    // 两个字节的文字
            $str .= '\u'.base_convert(ord($c), 10, 16).base_convert(ord($c2), 10, 16);
        }
        else
        {
            $str .= $c2;
        }
    }
    return $str;
}
function unicode_decode($name)
{
    // 转换编码，将Unicode编码转换成可以浏览的utf-8编码
    $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
    preg_match_all($pattern, $name, $matches);
    if (!empty($matches))
    {
        // $name = '';
        for ($j = 0; $j < count($matches[0]); $j++)
        {
            $str = $matches[0][$j];
            if (strpos($str, '\\u') === 0)
            {
                $code = base_convert(substr($str, 2, 2), 16, 10);
                $code2 = base_convert(substr($str, 4), 16, 10);
                $cc = chr($code).chr($code2);
                $c = iconv('UCS-2', 'UTF-8', $cc);
                // echo $cc;
                echo "**";
                echo $c;
                // $name .= $c;
                $name=str_replace($str,$c,$name);
            }
            // else
            // {
            //     $name .= $str;
            // }
        }
    }
    return $name;
}

function unicode_to_utf8($unicode_str) {
    $utf8_str = '';
    $code = intval(hexdec($unicode_str));
    //这里注意转换出来的code一定得是整形，这样才会正确的按位操作
    $ord_1 = decbin(0xe0 | ($code >> 12));
    $ord_2 = decbin(0x80 | (($code >> 6) & 0x3f));
    $ord_3 = decbin(0x80 | ($code & 0x3f));
    $utf8_str = chr(bindec($ord_1)) . chr(bindec($ord_2)) . chr(bindec($ord_3));
    return $utf8_str;
}

// echo '<h3>YOKA\u738b -> '.unicode_decode('YOKA\u738b').'</h3>';
// $name = 'YOKA王';
// echo '<h3>'.unicode_encode($name).'</h3>';
$str=<<<EOF
<div class=\"WB_detail\">\r\n            <div class=\"WB_info\">\r\n                <a suda-uatrack=\"key=feed_headnick&value=pubuser_nick:4011645464942003\" target=\"_blank\"  class=\"W_f14 W_fb S_txt1\" nick-name=\"\u7a0b\u5e8f\u5458\u90b9\u6b23\" title=\"\u7a0b\u5e8f\u5458\u90b9\u6b23\" href=\"\/sdxinz?refer_flag=0000015010_&from=feed&loc=nickname\" title=\"\u7a0b\u5e8f\u5458\u90b9\u6b23\" usercard=\"id=1912273717&refer_flag=0000015010_\">\u7a0b\u5e8f\u5458\u90b9\u6b23<\/a><a target=\"_blank\"  suda-data=\"key=pc_apply_entry&value=feed_icon\" href=\"http:\/\/verified.weibo.com\/verify\"><i title= \"\u5fae\u535a\u4e2a\u4eba\u8ba4\u8bc1 \" class=\"W_icon icon_approve\"><\/i><\/a><a target=\"_blank\" href=\"http:\/\/sports.weibo.com\/olympics2016\" title=\"\u56f4\u89c2\u5965\u8fd0\u4f1a\"><i class=\"W_icon icon_olympic\"><\/i><\/a>
EOF;
$str=str_replace('\/','/',$str);
$str=str_replace('\"','"',$str);
$str=str_replace('\n','',$str);
$str=str_replace('\r','',$str);
$str=str_replace('\t','',$str);
echo unicode_decode($str);
// echo unicode_to_utf8("8bc1") ;


 // $pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
 //    preg_match_all($pattern, $name, $matches);
 //    if (!empty($matches))
 //    {
 //        // $name = '';
 //        for ($j = 0; $j < count($matches[0]); $j++)
 //        {
 //            $str = $matches[0][$j];
 //            if (strpos($str, '\\u') === 0)
 //            {
 //                $code = base_convert(substr($str, 2, 2), 16, 10);
 //                $code2 = base_convert(substr($str, 4), 16, 10);
 //                $cc = chr($code).chr($code2);
 //                $c = iconv('UCS-2', 'UTF-8', $cc);
 //                echo $cc;
 //                echo $c;
 //                // $name .= $c;
 //                $name=str_replace($cc,$c,$name);
 //            }
 //            // else
 //            // {
 //            //     $name .= $str;
 //            // }
 //        }
 //    }