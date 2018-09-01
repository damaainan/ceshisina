<?php
// 重复二分搜索

function repetitiveBinarySearch(array $numbers, int $needle): int
{
    $low             = 0;
    $high            = count($numbers) - 1;
    $firstOccurrence = -1;
    while ($low <= $high) {
        $mid = (int) (($low + $high) / 2);
        if ($numbers[$mid] === $needle) {
            $firstOccurrence = $mid;
            $high            = $mid - 1;
        } else if ($numbers[$mid] > $needle) {
            $high = $mid - 1;
        } else {
            $low = $mid + 1;
        }
    }
    return $firstOccurrence;
}

$numbers = [1, 2, 2, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 4, 4, 5, 5];
$number  = 2;
$pos     = repetitiveBinarySearch($numbers, $number);
if ($pos >= 0) {
    echo "$number Found at position $pos \n";
} else {
    echo "$number Not found \n";
}
$number = 5;
$pos    = repetitiveBinarySearch($numbers, $number);
if ($pos >= 0) {
    echo "$number Found at position $pos \n";
} else {
    echo "$number Not found \n";
}
