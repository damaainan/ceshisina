<?php

# O(n^2 * n!)
# forms a tree with n! node on the base with depth = n and each node (at least on the base level) makes n operations
# n * n * n! = n^2 * n!

$permutations_count = 0;

function find_permutations($string, $start_index, $length){
    global $permutations_count;
    if($start_index == $length){
        echo "$string\r\n";
        $permutations_count++;
    }
    for($i = $start_index; $i < $length; $i ++){
        //swap chars
        $permutation = $string;
        $permutation[$start_index] = $string[$i];
        $permutation[$i] = $string[$start_index];
        find_permutations($permutation, $start_index+1, $length);
    }
}

$string = 'tree';
find_permutations($string, 0, strlen($string));
echo $permutations_count."\r\n";
