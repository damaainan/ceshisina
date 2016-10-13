<?php
header("Content-type:text/html; Charset=utf-8");
include('./pdf/Converter.php');
$converter = new Converter;
$str=$converter->parseString('<h1>Heading</h1>');
echo $str;
// Returns: # Heading