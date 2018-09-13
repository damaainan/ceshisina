<?php
require './bean/beans.php';

$classprotype = new ReflectionClass("Grade");
$class = $classprotype->newInstanceArgs(array("name"=>"大三"));
var_dump($class);
echo $class->getName();