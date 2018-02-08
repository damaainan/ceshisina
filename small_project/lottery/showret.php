<?php 
/*
$db = new SQLite3('my.sqlite');
$rst = $db->query("select * from result limit 1000;");
$row = $rst->fetchArray();
// foreach ($rst as $va) {
print_r($row);
// }

*/

$db = new PDO('sqlite:my.sqlite');

$stmt = $db->prepare('SELECT * FROM  result');
$stmt->execute();
$rows = $stmt->fetchAll();
foreach ($rows as $val) {
    echo $val[0],"=====",$val[1],"\n";
}