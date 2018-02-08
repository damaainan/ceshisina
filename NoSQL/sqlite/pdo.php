<?php
$db = new PDO('sqlite:dinner.db');

$stmt = $db->prepare('SELECT dish,price FROM meals WHERE meal LIKE ?');
$stmt->execute(array($_POST['meal']));
$rows = $stmt->fetchAll();
var_dump($rows);
