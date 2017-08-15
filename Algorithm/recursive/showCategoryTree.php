<?php

/**

Id categoryName parentCategory sortInd
1  First          0              0
2  Second         1              0
3  Third          1              1
4  Fourth         3              0
5  Fifth          4              0
6  Sixth          5              0
7  Seventh        6              0
8  Eight          7              0
9  Ninth          1              0
10 Tenth          2              1



 */

function showCategoryTree(array $categories, int $n)
{
    if (isset($categories[$n])) {
        foreach ($categories[$n] as $category) {
            echo str_repeat("-", $n) . "" . $category['categoryName'] . "\n";
            showCategoryTree($categories, $category['id']);
        }
    }
    return;
}

$categories = [
    0 => [['categoryName' => 'First', 'id' => 1]],
    1 => [['categoryName' => 'Second', 'id' => 2], ['categoryName' => 'Third', 'id' => 3], ['categoryName' => 'Ninth', 'id' => 9]],
    2 => [['categoryName' => 'Tenth', 'id' => 10]],
    3 => [['categoryName' => 'Fourth', 'id' => 4]],
    4 => [['categoryName' => 'Fifth', 'id' => 5]],
    5 => [['categoryName' => 'Sixth', 'id' => 6]],
    6 => [['categoryName' => 'Seventh', 'id' => 7]],
    7 => [['categoryName' => 'Eight', 'id' => 8]],
];

showCategoryTree($categories, 0);
