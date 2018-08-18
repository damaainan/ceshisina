<?php


function countWays($number, $memo = []){
  if($number == 0){
    return 1;
  }
  if($number == 1){
    return 1;
  }
  if($number == 2){
    return 2;
  }
  if(isset($memo[$number])){
    return $memo[$number];
  }
  
  $memo[$number] = countWays($number-1, $memo) + countWays($number-2, $memo) + countWays($number-3, $memo);
  return $memo[$number];
}

print_r(countWays(20));

// Brute force: O(pow(3, n))
// Final: O(n)
// Space: O(n)
