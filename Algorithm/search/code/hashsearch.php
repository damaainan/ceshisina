<?php

$arr   = [];
$count = rand(10, 30);
for ($i = 0; $i < $count; $i++) {
    $val       = rand(1, 500);
    $arr[$val] = $val;
}
$number = 100;
if (isset($arr[$number])) {
    echo "$number found ";
} else {
    echo "$number not found";
}
