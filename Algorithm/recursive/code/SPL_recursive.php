<?php

$path  = realpath('.');
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
foreach ($files as $name => $file) {
    echo "$name\n";
}
