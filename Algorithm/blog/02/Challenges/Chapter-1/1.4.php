<?php

# Palindrome Permutations

# answer is that we have to check if <=1 of chars have odd number of occurences
# can use a hash table or a bit map (vector)

function getCharNumber($char){
  return ord(strtolower($char)) - 96;
}

function toggleBitVector(&$vector, $bit_number){
  $bit_number -= 1;
  $substr = ($bit_number+1);
  $vector = str_pad($vector, $substr, "0", STR_PAD_LEFT);
    
  $current_value = (int)(($vector & decbin(1 << $bit_number)) != 0);
  $new_value = decbin((int)!$current_value);
  $mask = (substr(decbin(~(1 << $bit_number)), -$substr));
  $vector = (($vector & $mask) | decbin($new_value << $bit_number));
}

function isPermutationOfPalindrome($string){
    
    $bit_vector = decbin(0);
    
    for($i = 0; $i < strlen($string); $i++){
      if($string[$i] != ' '){
        $number = getCharNumber($string[$i]);
        toggleBitVector($bit_vector, $number);
      }
    }
    
    $n1 = str_pad(decbin(bindec($bit_vector) - 1), strlen(($bit_vector)), 0, STR_PAD_LEFT);
    $n2 = str_pad(($bit_vector), strlen(($bit_vector)), 0, STR_PAD_LEFT);
    
    // result vector - 1 & vector
    // if it's == 0, then we have only 1 bit == 1
    
    return  bindec($n1 & $n2) == 0;
}

// brute force: O(pow(n,3) * n!)
// final: 
// BCR: O(n)

$string = 'aabbccdeeffgghh';
var_dump(isPermutationOfPalindrome($string));
