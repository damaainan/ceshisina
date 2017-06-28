<?php
$sequence = new \Ds\Vector();
var_dump($sequence->capacity());

$sequence->allocate(100);
var_dump($sequence->capacity());



$sequence = new \Ds\Vector([1, 2, 3]);
$sequence->apply(function($value) { return $value * 2; });

print_r($sequence);





$sequence = new \Ds\Vector();
var_dump($sequence->capacity());

$sequence->push(...range(1, 50));
var_dump($sequence->capacity());

$sequence[] = "a";
var_dump($sequence->capacity());



$sequence = new \Ds\Vector(['a', 'b', 'c', 1, 2, 3]);

var_dump($sequence->contains('a'));                // true
var_dump($sequence->contains('a', 'b'));           // true
var_dump($sequence->contains('c', 'd'));           // false

var_dump($sequence->contains(...['c', 'b', 'a'])); // true

// Always strict
var_dump($sequence->contains(1));                  // true
var_dump($sequence->contains('1'));                // false

var_dump($sequence->contains(...[]));               // true




$sequence = new \Ds\Vector([1, 2, 3, 4, 5]);

var_dump($sequence->filter(function($value) {
    return $value % 2 == 0;
}));


$sequence = new \Ds\Vector([0, 1, 'a', true, false]);

var_dump($sequence->filter());



$sequence = new \Ds\Vector(["a", 1, true]);

var_dump($sequence->find("a")); // 0
var_dump($sequence->find("b")); // false
var_dump($sequence->find("1")); // false
var_dump($sequence->find(1));   // 1






$sequence = new \Ds\Vector([1, 2, 3]);
var_dump($sequence->first());





$sequence = new \Ds\Vector(["a", "b", "c"]);

var_dump($sequence->get(0));
var_dump($sequence->get(1));
var_dump($sequence->get(2));


$sequence = new \Ds\Vector(["a", "b", "c"]);

var_dump($sequence[0]);
var_dump($sequence[1]);
var_dump($sequence[2]);





$sequence = new \Ds\Vector();

$sequence->insert(0, "e");             // [e]
$sequence->insert(1, "f");             // [e, f]
$sequence->insert(2, "g");             // [e, f, g]
$sequence->insert(0, "a", "b");        // [a, b, e, f, g]
$sequence->insert(2, ...["c", "d"]);   // [a, b, c, d, e, f, g]

var_dump($sequence);





$sequence = new \Ds\Vector(["a", "b", "c", 1, 2, 3]);

var_dump($sequence->join("|"));







$sequence = new \Ds\Vector([1, 2, 3]);
var_dump($sequence->last());




$sequence = new \Ds\Vector([1, 2, 3]);

print_r($sequence->map(function($value) { return $value * 2; }));
print_r($sequence);