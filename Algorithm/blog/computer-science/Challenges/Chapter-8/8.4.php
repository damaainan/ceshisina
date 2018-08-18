<?php

function getAllSubsets($set){
  
  $subsets = array();
  
  // generate all integers from 1 to pow(2, n-1)
  $dec_mask = 0;
  for($i = 1; $i < pow(2, count($set)); $i ++){
    $dec_mask ++;
    $subsets[] = convertMaskToSubSet($set, $dec_mask);
  }
  
  return $subsets;
}

function convertMaskToSubSet($set, $dec_mask){
  $bin_mask = str_pad(decbin($dec_mask), count($set), 0, STR_PAD_LEFT);
  $subset = [];
  for($i = 0; $i < strlen($bin_mask); $i++){
    if($bin_mask[$i] == 1){
      $subset[] = $set[$i];
    }
  }
  return $subset;
}

print_r(getAllSubsets(['a', 'b', 'c']));

// O(n * pow(2, n))
// space O(n * pow(2, n))
