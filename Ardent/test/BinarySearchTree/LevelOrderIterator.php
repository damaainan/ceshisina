<?php

namespace Ardent\Collection;

require_once __DIR__ . '/../../vendor/autoload.php';

$tree = new BinaryTree(0);
$tree->setLeft(new BinaryTree(-4));
$tree->left()->setLeft(new BinaryTree(1));
$tree->left()->setRight(new BinaryTree(2));

$tree->setRight(new BinaryTree(4));

$iterator = new LevelOrderIterator($tree, 5);
$expect   = [0, -4, 4, 1, 2];
$actual   = iterator_to_array($iterator);

var_dump($actual);
