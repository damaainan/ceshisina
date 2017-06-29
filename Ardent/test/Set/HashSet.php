<?php

namespace Ardent\Collection;

require_once __DIR__ . '/../../vendor/autoload.php';



 $set = new HashSet();

        $scalar = 0;
        var_dump($set->has($scalar));
        $set->add($scalar);
        var_dump($set->has($scalar));
        var_dump($set->has('0'));

        $object = new \StdClass();
        var_dump($set->has($object));
        $set->add($object);
        var_dump($set->has($object));

        $resource = fopen(__FILE__, 'r');
        var_dump($set->has($resource));
        $set->add($resource);
        var_dump($set->has($resource));
        fclose($resource);

        $emptyArray = array();
        var_dump($set->has($emptyArray));
        $set->add($emptyArray);
        var_dump($set->has($emptyArray));

        $array = array(0, 1);
        var_dump($set->has($array));
        $set->add($array);
        var_dump($set->has($array));

        $null = null;

        var_dump($set->has($null));
        $set->add($null);
        var_dump($set->has($null));