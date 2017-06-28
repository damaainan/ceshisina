<?php
$vector = new \Ds\Vector();
var_dump($vector->capacity());

$vector->allocate(100);
var_dump($vector->capacity());



$vector = new \Ds\Vector([1, 2, 3]);
$vector->apply(function($value) { return $value * 2; });

print_r($vector);




$vector = new \Ds\Vector();
var_dump($vector->capacity());

$vector->push(...range(1, 50));
var_dump($vector->capacity());

$vector[] = "a";
var_dump($vector->capacity());




$vector = new \Ds\Vector([1, 2, 3]);
print_r($vector);

$vector->clear();
print_r($vector);



$vector = new \Ds\Vector(['a', 'b', 'c', 1, 2, 3]);

var_dump($vector->contains('a'));                // true
var_dump($vector->contains('a', 'b'));           // true
var_dump($vector->contains('c', 'd'));           // false

var_dump($vector->contains(...['c', 'b', 'a'])); // true

// Always strict
var_dump($vector->contains(1));                  // true
var_dump($vector->contains('1'));                // false

var_dump($sequece->contains(...[]));               // true




$a = new \Ds\Vector([1, 2, 3]);
$b = $a->copy();

// Updating the copy doesn't affect the original
$b->push(4);

print_r($a);
print_r($b);






$vector = new \Ds\Vector([1, 2, 3, 4, 5]);

var_dump($vector->filter(function($value) {
    return $value % 2 == 0;
}));


$vector = new \Ds\Vector([0, 1, 'a', true, false]);

var_dump($vector->filter());






$vector = new \Ds\Vector(["a", 1, true]);

var_dump($vector->find("a")); // 0
var_dump($vector->find("b")); // false
var_dump($vector->find("1")); // false
var_dump($vector->find(1));   // 1





$vector = new \Ds\Vector([1, 2, 3]);
var_dump($vector->first());




$vector = new \Ds\Vector(["a", "b", "c"]);

var_dump($vector->get(0));
var_dump($vector->get(1));
var_dump($vector->get(2));

$vector = new \Ds\Vector(["a", "b", "c"]);

var_dump($vector[0]);
var_dump($vector[1]);
var_dump($vector[2]);


$vector = new \Ds\Vector();

$vector->insert(0, "e");             // [e]
$vector->insert(1, "f");             // [e, f]
$vector->insert(2, "g");             // [e, f, g]
$vector->insert(0, "a", "b");        // [a, b, e, f, g]
$vector->insert(2, ...["c", "d"]);   // [a, b, c, d, e, f, g]

var_dump($vector);




$a = new \Ds\Vector([1, 2, 3]);
$b = new \Ds\Vector();

var_dump($a->isEmpty());
var_dump($b->isEmpty());


$vector = new \Ds\Vector(["a", "b", "c", 1, 2, 3]);

var_dump($vector->join("|"));




$vector = new \Ds\Vector([1, 2, 3]);

var_dump($vector->toArray());