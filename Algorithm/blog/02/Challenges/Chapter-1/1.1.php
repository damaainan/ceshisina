<?php

# implement an algorithm to determine if a string has all unique characters. Use only a string or an array (not a hash table, not a vector)

function hasAllUniqueCharacters($string){

	for($i = 0; $i < strlen($string); $i++){
		$letter1 = $string[$i];
		for($j = $i+1; $j < strlen($string); $j++){
			$letter2 = $string[$j];
			if($letter1 === $letter2){
				return false;
			}
		}
	}
	return true;
}

// Brute Force: pow(n, 2);
// Improved: n log n

var_dump(hasAllUniqueCharacters("the name"));
