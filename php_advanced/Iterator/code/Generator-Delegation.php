<?php
declare(strict_types=1);

$seh_seh_liām = function () {
    $generator = function () {
        yield from range(1, 3);

        foreach (range(4, 6) as $i) {
            yield $i;
        }
    };

    foreach ($generator() as $value) {
        echo "每天念 PHP 是最好的编程语言 6 遍...第 $value 遍...", PHP_EOL;
    }
};

$seh_seh_liām();