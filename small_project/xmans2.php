<?php
header("Content-type:text/html; Charset=utf-8");

/**
 * 圣诞树
 */
class xmas {
    public $w;
    public function __construct($w = 31) {
        $this->w = $w;
    }
    public function plant() {
        $tree = self::getTree();
        $root = self::getRoot();
        foreach ($tree as $val) {
            echo $val, "\n";
        }
        for ($i = 0; $i < 5; $i++) {
            echo $root, "\n";
        }
    }
    public function getTree() {
        $w = $this->w;
        $arr[1] = str_pad('', ($w - 3) / 2, ' ') . "*";
        for ($i = ($w - 1) / 2; $i >= 2; $i = $i - 1) {
            $num = $w - 2 * $i;
            $randstr = self::randStr($num);
            $arr[$i] = str_pad('/', $i - 1, ' ', STR_PAD_LEFT) . $randstr . str_pad("\\", $i - 1, ' ', STR_PAD_RIGHT);
        }
        $arr[($w + 1) / 2] = str_pad('', $w - 2, '^');
        sort($arr);
        return $arr;
    }
    public function getRoot() {
        $w = $this->w;
        $width = 3;
        $l = ($w - $width) / 2;
        $r = ($w - $width) / 2 + $width;
        return str_pad('|', $l - 1, ' ', STR_PAD_LEFT) . str_pad('', $width, ' ') . str_pad("|", $r - 1, ' ', STR_PAD_RIGHT);
    }
    public function randStr($n) {
        $strA = ["@", "#", "%", "P", "W", 'a', '2', 'b', '=', '-', '^', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '];
        $out = ["0;30", "1;30", "0;34", "1;34", "0;32", "1;32", "0;36", "1;36", "0;31", "1;31", "0;35", "1;35", "0;33", "1;33", "0;37", "1;37"];

        $str = '';
        for ($i = 0; $i < $n; $i++) {
            $randstr = $strA[array_rand($strA)];
            $randcolor = $out[array_rand($out)];
            $str .= "\033[" . $randcolor . "m" . $randstr . "\033[0m";
        }
        return $str;
    }
}

$xmas = new xmas(31);
$xmas->plant();

// $str = $xmas->randStr(20);
// echo $str;

// echo str_pad('/', 20, ' ', STR_PAD_LEFT) . 'test' . str_pad("\\", 2, ' ', STR_PAD_RIGHT);

// $out = "[44m";
// echo chr(27) . "$out" . "text" . chr(27) . "[0m";
//
// echo "\033[".'1;33'."m"."text"."\033[0m";