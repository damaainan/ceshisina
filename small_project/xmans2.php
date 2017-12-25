<?php
header("Content-type:text/html; Charset=utf-8");


class xmas {
    public $h = 120;
    public $w = 61;

    public function getTree($w) {
        $arr[$w] = str_pad('', $w, '^');
        $arr[1] = str_pad('', 30, ' ') . "*" . str_pad('', 30, ' ');
        for ($i = $w - 1; $i > 1; $i = $i - 2) {
            $num = ($w - $i - 2 + 1) / 2;
            $randstr = self::randStr($num);
            $arr[$i] = str_pad('/', $num, ' ', STR_PAD_LEFT) . $randstr . str_pad("\\", $num, ' ', STR_PAD_RIGHT);
        }
    }
    public function randStr($n) {
        $strA = ["@", "#", "%", "P", "W", ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '];
        $out = ["0;30", "1;30", "0;34", "1;34", "0;32", "1;32", "0;36", "1;36", "0;31", "1;31", "0;35", "1;35", "0;33", "1;33", "0;37", "1;37"];

        $str = '';
        for ($i = 0; $i < $n; $i++) {
            $randstr = $strA[array_rand($strA)];
            $randcolor = $out[array_rand($out)];
            // echo "\n",$randcolor,"\n";
            $str .= "\033[" . $randcolor . "m" . $randstr . "\033[0m";
            // echo $str;
        }
        return $str;
    }
}

$xmas = new xmas();
$str = $xmas->randStr(20);
echo $str;

// $out = "[44m";
// echo chr(27) . "$out" . "text" . chr(27) . "[0m";
//
// echo "\033[".'1;33'."m"."text"."\033[0m";