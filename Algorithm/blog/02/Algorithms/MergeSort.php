<?php

# Time complexity: O(N log N)
# Space complexity: O(3n) = O(n)

# Animation:
# https://www.cs.usfca.edu/~galles/visualization/ComparisonSort.html

function mergeSortedArrays($a, $b){
    $n = count($a) + count($b);
    $output = [];
    $i = 0;
    $j = 0;
    //O(n)
    for($k = 0; $k < $n; $k ++){
        if(!isset($a[$i])){
            $output[$k] = $b[$j];
            $j ++;
        }
        elseif(!isset($b[$j])){
            $output[$k] = $a[$i];
            $i ++;
        }
        else{
            if($a[$i] < $b[$j]){
                $output[$k] = $a[$i];
                $i ++;
            }
            elseif($b[$j] < $a[$i]){
                $output[$k] = $b[$j];
                $j ++;
            }
        }
    }
    
    return $output;
}

function splitArray($array){
    $n = count($array);
    $middle = floor($n/2);
    $a = array_slice($array, 0, $middle);
    $b = array_slice($array, $middle);
    
    return [$a, $b];
}

function mergeSort($array){
    list($a, $b) = splitArray($array);
    //number of levels, how much times can we divide n by 2, is log2n
    if(count($a) > 1){
        $a = mergeSort($a);
    }
    if(count($b) > 1){
        $b = mergeSort($b);
    }
    
    return mergeSortedArrays($a, $b);
}

$input = range(1,9999);
shuffle($input);
//print_r($input);

//O(f(x)) combined is O(n log2n)
$output = mergeSort($input);

print_r($output);
