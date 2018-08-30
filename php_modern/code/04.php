<?php
(function (callable $fn): callable {
    return (function (callable $f): callable {
        return $f($f);
    })(function (callable $f) use ($fn): callable {
        return $fn(function (int $n) use ($f): int {
            return $f($f)($n);
        });
    });}
)(function (callable $g): callable {
    return function (int $n) use ($g): int {
        return in_array($n, [1, 2]) ? 1 : $g($n - 1) + $g($n - 2);
    };
})(10);
