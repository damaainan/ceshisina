<?php

namespace Ardent\Collection;

require_once __DIR__ . '/../../vendor/autoload.php';

return new PostOrderIterator($root, $count);

$tree = new BinaryTree(0);
$tree->setLeft(new BinaryTree(-4));
$tree->left()->setLeft(new BinaryTree(1));
$tree->left()->setRight(new BinaryTree(2));

$iterator = new PostOrderIterator($tree, 4);
$expect   = [1, 2, -4, 0];
$actual   = iterator_to_array($iterator);
var_dump($actual);
