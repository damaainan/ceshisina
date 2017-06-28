<?php
$sequence = new \Ds\Vector([1, 2, 3]);

var_dump($sequence->merge([4, 5, 6]));
var_dump($sequence);







$sequence = new \Ds\Vector([1, 2, 3]);

var_dump($sequence->pop());
var_dump($sequence->pop());
var_dump($sequence->pop());




$sequence = new \Ds\Vector();

$sequence->push("a");
$sequence->push("b");
$sequence->push("c", "d");
$sequence->push(...["e", "f"]);

print_r($sequence);






$sequence = new \Ds\Vector([1, 2, 3]);

$callback = function($carry, $value) {
    return $carry * $value;
};

var_dump($sequence->reduce($callback, 5));

// Iterations:
//
// $carry = $initial = 5
//
// $carry = $carry * 1 =  5
// $carry = $carry * 2 = 10
// $carry = $carry * 3 = 30

$sequence = new \Ds\Vector([1, 2, 3]);

var_dump($sequence->reduce(function($carry, $value) {
    return $carry + $value + 5;
}));

// Iterations:
//
// $carry = $initial = null
//
// $carry = $carry + 1 + 5 =  6
// $carry = $carry + 2 + 5 = 13
// $carry = $carry + 3 + 5 = 21






$sequence = new \Ds\Vector(["a", "b", "c"]);

var_dump($sequence->remove(1));
var_dump($sequence->remove(0));
var_dump($sequence->remove(0));



$sequence = new \Ds\Vector(["a", "b", "c"]);
$sequence->reverse();

print_r($sequence);





$sequence = new \Ds\Vector(["a", "b", "c"]);

print_r($sequence->reversed());
print_r($sequence);



$sequence = new \Ds\Vector(["a", "b", "c", "d"]);

$sequence->rotate(1);  // "a" is shifted, then pushed.
print_r($sequence);

$sequence->rotate(2);  // "b" and "c" are both shifted, the pushed.
print_r($sequence);



$sequence = new \Ds\Vector(["a", "b", "c"]);

$sequence->set(1, "_");
print_r($sequence);

$sequence = new \Ds\Vector(["a", "b", "c"]);

$sequence[1] = "_";
print_r($sequence);




$sequence = new \Ds\Vector(["a", "b", "c"]);

var_dump($sequence->shift());
var_dump($sequence->shift());
var_dump($sequence->shift());




$sequence = new \Ds\Vector(["a", "b", "c", "d", "e"]);

// Slice from 2 onwards
print_r($sequence->slice(2));

// Slice from 1, for a length of 3
print_r($sequence->slice(1, 3));

// Slice from 1 onwards
print_r($sequence->slice(1));

// Slice from 2 from the end onwards
print_r($sequence->slice(-2));

// Slice from 1 to 1 from the end



$sequence = new \Ds\Vector([4, 5, 1, 3, 2]);
$sequence->sort();

print_r($sequence);



$sequence = new \Ds\Vector([4, 5, 1, 3, 2]);

$sequence->sort(function($a, $b) {
    return $b <=> $a;
});

print_r($sequence);



$sequence = new \Ds\Vector([4, 5, 1, 3, 2]);

print_r($sequence->sorted());

$sequence = new \Ds\Vector([4, 5, 1, 3, 2]);

$sorted = $sequence->sorted(function($a, $b) {
    return $b <=> $a;
});

print_r($sorted);





$sequence = new \Ds\Vector([1, 2, 3]);
var_dump($sequence->sum());

$sequence = new \Ds\Vector([1, 2.5, 3]);
var_dump($sequence->sum());



$sequence = new \Ds\Vector([1, 2, 3]);

$sequence->unshift("a");
$sequence->unshift("b", "c");

print_r($sequence);