<?php
declare(strict_types=1);

class Coroutine
{
    public static function create(callable $callback) : Generator
    {
        return (function () use ($callback) {
            try {
                yield $callback;
            } catch (Exception $e) {
                echo "OH.. an error, but don't care and continue...", PHP_EOL;
            }
       })();
    }

    public static function run(array $cos)
    {
        $cnt = count($cos);
        while ($cnt > 0) {
            $loc = random_int(0, $cnt-1);  // 用 random 模拟调度策略。
            $cos[$loc]->current()();
            array_splice($cos, $loc, 1);
            $cnt--;
        }
    }
}

$co = new Coroutine();

$cos = [];
for ($i = 1; $i <= 10; $i++) {
    $cos[] = $co::create(function () use ($i) { echo "Co.{$i}.", PHP_EOL; });
}
$co::run($cos);

$cos = [];
for ($i = 1; $i <= 20; $i++) {
    $cos[] = $co::create(function () use ($i) { echo "Co.{$i}.", PHP_EOL; });
}
$co::run($cos);