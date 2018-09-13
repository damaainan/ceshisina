<?php
require './bean/beans.php';

$protype = new ReflectionClass ( "Person" );
$properties = $protype->getProperties ();

// 反射获取到类的属性信息
foreach ( $properties as $property ) {
    echo $property . ":";
    $doc = $property->getDocComment ();
    echo "   " . $doc . "\r\n";
    echo "--------------------------------------------------------" . "\r\n";
}


$methods = $protype->getMethods();
foreach ($methods as $method) {
    echo $method->getName()."\r\n";
    $doc = $method->getDocComment ();
    echo "   " . $doc . "\r\n";
    echo "--------------------------------------------------------" . "\r\n";
}