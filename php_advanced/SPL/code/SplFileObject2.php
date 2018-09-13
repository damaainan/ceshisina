<?php

try {
    $file = new SplFileObject("/usr/local/apache/logs/access_log");

    $file->seek(3);

    echo $file->current();
} catch (Exception $e) {
    echo $e->getMessage();
}
