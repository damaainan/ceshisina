<?php 
header("Content-type:text/html; Charset=utf-8");


// PHP输出json数据时中文不进行unicode编码的几种方法总结

/** 
 *  使用url_encode()对字符串进行编码
 *  @param string/array $str 需要编码的数据
 *  @return string/array $str 返回编码后的字符串
 */  
function url_encode($str) {  
    if(is_array($str)) {  
        foreach($str as $key=>$value) {  
            $str[urlencode($key)] = url_encode($value);  
        }  
    } else {  
        $str = urlencode($str);  
    }  

    return $str;  
}  

/**
* 输出json数据，不解析中文
* @param string/array $str 需要进行json编码的数据
* @return string  输出json数据
*/
function encode_json($str) {  
    $result = urldecode(json_encode(url_encode($str)));  
    return $result; 
}

// 用json_encode()直接输出的数据能直接在APP中直接使用，需要输出的json数据在客户端开发的 控制台(命令行) 不解析，在客户端输出后可自动解析 


$arr = [
    "code"=> "200", 
    "message"=> "success", 
    "data"=> [
        [
            "id"=> "70", 
            "content"=> "中文测试", 
        ]
    ]
];

$json = encode_json($arr);

echo $json;