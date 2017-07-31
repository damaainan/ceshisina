<?php 

//https://segmentfault.com/a/1190000009764351

function IntToString($num)
{
    $cns = ['零','一','二','三','四','五','六','七','八','九'];

    $ws = ['','十','百','千','万','十','百','千','亿','十','百','千'];

    $str = '';
    foreach (array_reverse(str_split((string)$num,1)) as $key => $value) 
        $str .= $ws[$key].$cns[$value];
    $temp = '';//反转字符串
    for($i = strlen($str)-1; $i>=0; $i--)
        $temp .= mb_substr($str,$i,1,'utf-8');

    return $temp;
}

echo IntToString(231231251237);