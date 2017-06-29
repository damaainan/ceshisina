<?php

namespace Ardent\Collection;

require_once __DIR__ . '/../../vendor/autoload.php';

$tree = new BinaryTree(0);
$tree->setLeft(new BinaryTree(-4));
$tree->left()->setLeft(new BinaryTree(1));
$tree->left()->setRight(new BinaryTree(2));

$iterator = new InOrderIterator($tree, 4);

$actual = iterator_to_array($iterator);
var_dump($actual);
