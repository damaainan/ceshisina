<?php

require_once __DIR__.'/vendor/autoload.php';

$map = new \Ardent\Collection\SortedMap();
        $map[0] = 1;
        $map[2] = 3;
        $map[3] = 4;
        $map[1] = 2;

        $iterator = $map->getIterator();
         $iterator->rewind();
          $iterator->next();