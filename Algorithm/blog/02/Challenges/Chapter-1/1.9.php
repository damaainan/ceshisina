<?php

# string rotation

# :3

function isSubstring($needle, $haystack){
  //todo
  // return true
}

function isRotation($needle, $haystack){
  //only one call to isSubstring
  
  //concatenate $haystack to inself
  //call the method
  
  $doubled_haystack = $haystack . $haystack; // O(n)
  return isSubstring($needle, $haystack);
 
}

// final: O(n)
// BCR: O(n)

$s1 = "waterbottle";
$s2 = "erbottlewat";

var_dump(isRotation($s1, $s2));
