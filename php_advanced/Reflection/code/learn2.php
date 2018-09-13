<?php

require './bean/beans.php';

$protype = new ReflectionClass("Person");

$methods = $protype->getMethods();
foreach ($methods as $method) {
    echo $method->getName()."\r\n";
}