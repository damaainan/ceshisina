<?php

# Given two strings, write a method to deside if one is a permutation of the other

function isPermutation($first_string, $second_string){
	merge_sort($first_string); // O(n log n)
	merge_sort($second_string); // O(n log n)
	if($first_string == $second_string){ //O(n)
		return true;
	}
	return false;
}

function isPermutationB($first_string, $second_string){
	if(strlen($first_string) != strlen($second_string)){
		return false;
	}

	//calculate char statistics for each string
	//foreach char, compare count
	$stat1 = []; // hash table
	for($i = 0; $i < strlen($first_string); $i++){ //O(n)
		$char = $first_string[$i];
		if(!isset($stat1[$char])){
			$stat1[$char] = 0;
		}
		$stat1[$char]++;
	}

	$stat2 = []; // hash table
	for($j = 0; $j < strlen($second_string); $j ++){ //O(n)
		$char = $second_string[$j];
		if(!isset($stat2[$char])){
			$stat2[$char] = 0;
		}
		$stat2[$char]++;
	}

	foreach($stat1 as $key => $value){ //O(n)
		if(!isset($stat2[$key]) || $stat2[$key] != $value){
			return false;
		}
	}
	return true;
}

//permutation is the same set of characters
// BCR: n log n or n

var_dump(isPermutation("abc", "cab"));
var_dump(isPermutationB("abc", "cab"));
