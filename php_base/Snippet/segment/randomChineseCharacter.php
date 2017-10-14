<?php
/**
* 生成随机汉字
*
* Created by PhpStorm.
* User: zoco
* Date: 16/10/29
* Time: 16:54
*/
$unidec = rand(19968,24869);
echo $unidec."\n";
$unichr = '&#'.$unidec.';';
echo $unichr."\n";
$zhchchr = mb_convert_encoding($unichr,'UTF-8','HTML-ENTITIES');
echo $zhchchr;