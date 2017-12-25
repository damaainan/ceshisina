<?php
header("Content-type:text/html; Charset=utf-8");

/*
// happy xmas
var log = require('npmlog')

module.exports = function (args, cb) {
var s = process.platform === 'win32' ? ' *' : ' \u2605'
var f = '\uFF0F'
var b = '\uFF3C'
var x = process.platform === 'win32' ? ' ' : ''
var o = [
'\u0069', '\u0020', '\u0020', '\u0020', '\u0020', '\u0020',
'\u0020', '\u0020', '\u0020', '\u0020', '\u0020', '\u0020',
'\u0020', '\u2E1B', '\u2042', '\u2E2E', '&', '@', '\uFF61'
]
var oc = [21, 33, 34, 35, 36, 37]
var l = '\u005e'

function w (s) { process.stderr.write(s) }

w('\n')
;(function T (H) {
for (var i = 0; i < H; i++) w(' ')
w(x + '\u001b[33m' + s + '\n')
var M = H * 2 - 1
for (var L = 1; L <= H; L++) {
var O = L * 2 - 2
var S = (M - O) / 2
for (i = 0; i < S; i++) w(' ')
w(x + '\u001b[32m' + f)
for (i = 0; i < O; i++) {
w(
'\u001b[' + oc[Math.floor(Math.random() * oc.length)] + 'm' +
o[Math.floor(Math.random() * o.length)]
)
}
w(x + '\u001b[32m' + b + '\n')
}
w(' ')
for (i = 1; i < H; i++) w('\u001b[32m' + l)
w('| ' + x + ' |')
for (i = 1; i < H; i++) w('\u001b[32m' + l)
if (H > 10) {
w('\n ')
for (i = 1; i < H; i++) w(' ')
w('| ' + x + ' |')
for (i = 1; i < H; i++) w(' ')
}
})(20)
w('\n\n')
log.heading = ''
log.addLevel('npm', 100000, log.headingStyle)
log.npm('loves you', 'Happy Xmas, Noders!')
cb()
}
var dg = false
Object.defineProperty(module.exports, 'usage', {get: function () {
if (dg) module.exports([], function () {})
dg = true
return ' '
}})

 */

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