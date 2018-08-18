<?php

$a = [
    [0,1,2,3],
    [4,5,6,7],
    [7,9,10,11],
    [12,13,14,15],
];
// $a = [
//     [0,1,3],
//     [4,5,6],
//     [7,8,9]
// ];


function rotate(&$matrix){
    //if check
    $cnt = count($matrix);
    for($layer = 0; $layer < $cnt / 2; $layer++){ // n/2
        $first = $layer;
        $last = $cnt - 1 - $layer;
        for($i = $first; $i < $last; $i++){ // n
            $offset = $i - $first;
            $top = $matrix[$first][$i]; //save top
            
            //left -> top
            $matrix[$first][$i] = $matrix[$last-$offset][$first];
            
            //bottom -> left
            $matrix[$last-$offset][$first] = $matrix[$last][$last-$offset];
            
            //right -> bottom
            $matrix[$last][$last-$offset] = $matrix[$i][$last];
            
            //top -> right
            $matrix[$i][$last] = $top; // right <- saved top
        }
    }
    return true;
}

rotate($a);
print_r($a);

//final: O(pow(n,2))
//BCR: O(pow(n,2))
