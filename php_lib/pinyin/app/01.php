<?php 
require "../vendor/autoload.php";


use Overtrue\Pinyin\Pinyin;

// header("Content-type:text/html; Charset=utf-8");
// 小内存型
$pinyin = new Pinyin(); // 默认
// 内存型
// $pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
// I/O型
// $pinyin = new Pinyin('Overtrue\Pinyin\GeneratorFileDictLoader');

$str = implode($pinyin->convert('带着希望去旅行，比到达终点更美好')," ");
echo $str;
echo "\n";
// ["dai", "zhe", "xi", "wang", "qu", "lv", "xing", "bi", "dao", "da", "zhong", "dian", "geng", "mei", "hao"]

$str = implode($pinyin->convert('带着希望去旅行，比到达终点更美好', PINYIN_UNICODE)," ");
echo $str;
echo "\n";
// ["dài","zhe","xī","wàng","qù","lǚ","xíng","bǐ","dào","dá","zhōng","diǎn","gèng","měi","hǎo"]

$str = implode($pinyin->convert('带着希望去旅行，比到达终点更美好', PINYIN_ASCII)," ");
echo $str;
echo "\n";
//["dai4","zhe","xi1","wang4","qu4","lv3","xing2","bi3","dao4","da2","zhong1","dian3","geng4","mei3","hao3"]