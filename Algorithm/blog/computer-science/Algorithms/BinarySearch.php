<?php

# Search the ordered array by dividing it in halves
# Time Complexity:
# Binary Search: O(log(n))
# Linear (Brute Force):	O(n)
#
# Animation:
# https://www.cs.usfca.edu/~galles/visualization/Search.html

function binary_search($needle, $haystack){
    $left = 0;
    $right = count($haystack) - 1;
    
    while($left <= $right){
        $mid = round(($left + $right) / 2);
        
        if($haystack[$mid] == $needle){
            return $mid;
        } elseif ($haystack[$mid] > $needle){
            $right = $mid - 1;
        } elseif($haystack[$mid] < $needle) {
            $left = $mid + 1;
        }
    }
    
    return -1;
}

$array = range(1,100,2);
$index = binary_search(43, $array);
echo "index of {$array[$index]} is $index";
