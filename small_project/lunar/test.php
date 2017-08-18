<?php 

require_once "lunar.class.php";


$lunar = new Lunar();
$month = $lunar->convertSolarToLunar(date('Y'),date('m'),date('d'));
print_r($month);