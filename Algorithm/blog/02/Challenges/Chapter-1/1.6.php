<?php

# :2

function compress($string){
	
	# can optimize by precounting the size of the compressed string, but the time complexity 
	# will be almost the same, we're just eliminating the unnecessary work

	$builder = new StringBuilder();

	$previous_count = 0;
	for($i = 0; $i < strlen($string); $i ++){ //O(n)
		$previous_count++;
		$char = $string[$i];
		if($i == strlen($string)-1){
			$next_char = null;
		}
		else{
			$next_char = $string[$i+1];
		}
		if($char !== $next_char){

			$builder->append($char);
			$builder->append($previous_count);

			$previous_count = 0;
		}

	}

	$new_string = $builder->combine(); //O(n)
	if(strlen($new_string) < strlen($string)){
		return $new_string;
	}
	else{
		return $string;
	}


}

class StringBuilder {
	
	protected $array;

	public function __construct(){
		$this->array = [];
	}

	public function append($string){
		$this->array[] = $string;
	}

	public function combine(){ //O(n)

		if(count($this->array) > 0){
			// $string = implode('', $this->array);
			$string = $this->array[0];
			$index = strlen($string);
			for($i = 1; $i < count($this->array); $i++){ //O(n)
				$string[$index] = $this->array[$i]; // array elements are 1 char length
				$index++;
			}
			return $string;
		}
		else{
			return '';
		}
	}
}


var_dump(compress("aaaaaaabbfewww"));

//aabcccbbaaa

//AbbddAbe


// Brute force: O(pow(n, 3))
// Improved: O(n)
// BCR: O(n)
