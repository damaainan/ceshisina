<?php

namespace Ardent\Collection;

require_once __DIR__ . '/../../vendor/autoload.php';



$map = new SortedMap();
        $map[0] = 1;
        $map[2] = 3;
        $map[3] = 4;
        $map[1] = 2;

        $iterator = $map->getIterator();

      
        var_dump($iterator);

        $iterator->rewind();

        for ($i = 0; $i < count($map); $i++) {

            echo $iterator->key();
            $expectedValue = $map[$i];
            echo $iterator->current();

            $iterator->next();
        }
        var_dump($iterator);
        