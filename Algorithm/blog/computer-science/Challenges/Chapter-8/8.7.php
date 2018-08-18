<?php

function getPermutations($prefix, $remainder, &$results){
  
  if(strlen($remainder) == 0){
    $results[] = $prefix;
  }
  
  $len = strlen($remainder);
  
  for($i = 0; $i < $len; $i++){
    $before = substr($remainder, 0, $i);
    $after = substr($remainder, $i + 1, $len);
    $c = $remainder[$i];
    getPermutations($prefix . $c, $before . $after, $results);
  }
  
}

$result = [];
getPermutations('', 'ab', $result);
var_dump($result);
