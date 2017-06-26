<?php
// namespace Ardent\Collection;
// include 'Collection/AvlTree.php';


require_once __DIR__.'/vendor/autoload.php';

// use AvlTree;

$tree = new \Ardent\Collection\AvlTree();
        $tree->add(1);
        $tree->add(0);
        $tree->add(2);
        $tree->add(-1);

        $actual = $tree->first();

        echo $actual;