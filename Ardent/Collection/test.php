<?php
namespace Ardent\Collection\AvlTree;


$tree = new AvlTree();
        $tree->add(1);
        $tree->add(0);
        $tree->add(2);
        $tree->add(-1);

        $actual = $tree->first();

        echo $actual;