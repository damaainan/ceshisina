<?php
require_once "random_compat/lib/random.php";
function randPic() {
//关于更好的随机数的图片形式的表现
    header("Content-type: image/png");
    $im = imagecreatetruecolor(512, 512) or die("Cannot Initialize new GD image stream");
    $white = imagecolorallocate($im, 255, 255, 255);
    for ($y = 0; $y < 512; $y++) {
        for ($x = 0; $x < 512; $x++) {
            if (random_int(0, 1) === 1) { // 使用 random_compat 库生成的随机数
                imagesetpixel($im, $x, $y, $white);
            }
        }
    }
    imagepng($im);
    imagedestroy($im);
}
function randPic2() {
    header("Content-type: image/png");
    $im = imagecreatetruecolor(512, 512) or die("Cannot Initialize new GD image stream");
    $white = imagecolorallocate($im, 255, 255, 255);
    for ($y = 0; $y < 512; $y++) {
        for ($x = 0; $x < 512; $x++) {
            $color = mt_rand(0, 1);
            if (mt_rand(0, 1) === 1) {
                imagesetpixel($im, $x, $y, $white);
            }
        }
    }
    imagepng($im);
    imagedestroy($im);
}
function randPic3() {
    header("Content-type: image/png");
    $im = imagecreatetruecolor(512, 512) or die("Cannot Initialize new GD image stream");
    $white = imagecolorallocate($im, 255, 255, 255);
    for ($y = 0; $y < 512; $y++) {
        for ($x = 0; $x < 512; $x++) {
            $color = mt_rand(0, 1);
            if (rand(0, 1) === 1) { // 最差的随机
                imagesetpixel($im, $x, $y, $white);
            }
        }
    }
    imagepng($im);
    imagedestroy($im);
}
// randPic();
randPic3();