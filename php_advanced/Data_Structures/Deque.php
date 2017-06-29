<?php
$deque = new \Ds\Deque();
var_dump($deque->capacity());

$deque->allocate(100);
var_dump($deque->capacity());



$deque = new \Ds\Deque([1, 2, 3]);
$deque->apply(function($value) { return $value * 2; });

print_r($deque);



$deque = new \Ds\Deque();
var_dump($deque->capacity());

$deque->push(...range(1, 50));
var_dump($deque->capacity());



$deque = new \Ds\Deque([1, 2, 3]);
print_r($deque);

$deque->clear();
print_r($deque);




$deque = new \Ds\Deque(['a', 'b', 'c', 1, 2, 3]);

var_dump($deque->contains('a'));                // true
var_dump($deque->contains('a', 'b'));           // true
var_dump($deque->contains('c', 'd'));           // false

var_dump($deque->contains(...['c', 'b', 'a'])); // true

// Always strict
var_dump($deque->contains(1));                  // true
var_dump($deque->contains('1'));                // false

var_dump($sequece->contains(...[]));               // true




$a = new \Ds\Deque([1, 2, 3]);
$b = $a->copy();

$b->push(4);

print_r($a);
print_r($b);




$deque = new \Ds\Deque([1, 2, 3, 4, 5]);

var_dump($deque->filter(function($value) {
    return $value % 2 == 0;
}));



$deque = new \Ds\Deque([0, 1, 'a', true, false]);

var_dump($deque->filter());




$deque = new \Ds\Deque(["a", 1, true]);

var_dump($deque->find("a")); // 0
var_dump($deque->find("b")); // false
var_dump($deque->find("1")); // false
var_dump($deque->find(1));   // 1



$deque = new \Ds\Deque();

$deque->insert(0, "e");             // [e]
$deque->insert(1, "f");             // [e, f]
$deque->insert(2, "g");             // [e, f, g]
$deque->insert(0, "a", "b");        // [a, b, e, f, g]
$deque->insert(2, ...["c", "d"]);   // [a, b, c, d, e, f, g]

var_dump($deque);




$a = new \Ds\Deque([1, 2, 3]);
$b = new \Ds\Deque();

var_dump($a->isEmpty());
var_dump($b->isEmpty());



$deque = new \Ds\Deque(["a", "b", "c", 1, 2, 3]);

var_dump($deque->join("|"));



$deque = new \Ds\Deque([1, 2, 3]);

print_r($deque->map(function($value) { return $value * 2; }));
print_r($deque);




$deque = new \Ds\Deque([1, 2, 3]);

var_dump($deque->merge([4, 5, 6]));
var_dump($deque);




$deque = new \Ds\Deque([1, 2, 3]);

var_dump($deque->pop());
var_dump($deque->pop());
var_dump($deque->pop());




$deque = new \Ds\Deque();

$deque->push("a");
$deque->push("b");
$deque->push("c", "d");
$deque->push(...["e", "f"]);

print_r($deque);



$deque = new \Ds\Deque([1, 2, 3]);

$callback = function($carry, $value) {
    return $carry * $value;
};

var_dump($deque->reduce($callback, 5));

// Iterations:
//
// $carry = $initial = 5
//
// $carry = $carry * 1 =  5
// $carry = $carry * 2 = 10
// $carry = $carry * 3 = 30




$deque = new \Ds\Deque([1, 2, 3]);

var_dump($deque->reduce(function($carry, $value) {
    return $carry + $value + 5;
}));

// Iterations:
//
// $carry = $initial = null
//
// $carry = $carry + 1 + 5 =  6
// $carry = $carry + 2 + 5 = 13
// $carry = $carry + 3 + 5 = 21




$deque = new \Ds\Deque(["a", "b", "c"]);

var_dump($deque->shift());
var_dump($deque->shift());
var_dump($deque->shift());



$deque = new \Ds\Deque(["a", "b", "c", "d", "e"]);

// Slice from 2 onwards
print_r($deque->slice(2));

// Slice from 1, for a length of 3
print_r($deque->slice(1, 3));

// Slice from 1 onwards
print_r($deque->slice(1));

// Slice from 2 from the end onwards
print_r($deque->slice(-2));

// Slice from 1 to 1 from the end
print_r($deque->slice(1, -1));



$deque = new \Ds\Deque([4, 5, 1, 3, 2]);
$deque->sort();

print_r($deque);

$deque = new \Ds\Deque([4, 5, 1, 3, 2]);

$deque->sort(function($a, $b) {
    return $b <=> $a;
});

print_r($deque);



$deque = new \Ds\Deque([4, 5, 1, 3, 2]);

$sorted = $deque->sorted(function($a, $b) {
    return $b <=> $a;
});

print_r($sorted);




$deque = new \Ds\Deque([1, 2, 3]);
var_dump($deque->sum());



$deque = new \Ds\Deque([1, 2, 3]);

var_dump($deque->toArray());




$deque = new \Ds\Deque([1, 2, 3]);

$deque->unshift("a");
$deque->unshift("b", "c");

print_r($deque);
