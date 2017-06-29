<?php 

$pair = new \Ds\Pair("a", 1);
print_r($pair);

$pair->clear();
print_r($pair);




$a = new \Ds\Pair("a", 1);
$b = $a->copy();

$a->key = "x";

print_r($a);
print_r($b);



$a = new \Ds\Pair("a", 1);
$b = new \Ds\Pair();

var_dump($a->isEmpty());
var_dump($b->isEmpty());





$pair = new \Ds\Pair("a", 1);

var_dump($pair->toArray());


