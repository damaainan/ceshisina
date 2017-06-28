<?php

namespace Ardent\Collection;

require_once __DIR__ . '/../vendor/autoload.php';

$array    = [];
$iterator = new ArrayIterator($array);
var_dump($iterator);

$array    = [0];
$iterator = new ArrayIterator($array);
var_dump($iterator);
$array    = [0, 2, 4, 6];
$iterator = new ArrayIterator($array);
var_dump($iterator);

$iterator = new ArrayIterator([]);
echo $iterator->isEmpty();
$iterator = new ArrayIterator([1]);
echo $iterator->isEmpty();

$array    = [0, 2, 4, 8];
$iterator = new ArrayIterator($array);

$i = 0;
$iterator->rewind();
while ($i < count($array)) {
    echo $iterator->valid();
    echo $i, $iterator->key();
    echo $array[$i], $iterator->current();
    $iterator->next();
    $i++;
}

$map = [
    'a' => '1',
    'b' => '2',
    'c' => '3',
    'd' => '4',
];
$iterator = new ArrayIterator($map);
var_dump(iterator_to_array($iterator));





$iterator = new ArrayIterator([0, 5, 2, 6]);
$iterator->rewind();

$iterator->seek(3);
var_dump($iterator->current());

$iterator->seek(1);
var_dump($iterator->current());

$iterator->seek(2);
var_dump($iterator->current());

$iterator->seek(0);
var_dump($iterator->current());

$iterator->seek(3);
var_dump($iterator->current());
