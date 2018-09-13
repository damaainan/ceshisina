<?php

try {
    // iterate directly over the object
    foreach (new SplFileObject("/usr/local/apache/logs/access_log") as $line)
    // and echo each line of the file
    {
        echo $line . '<br />';
    }

} catch (Exception $e) {
    echo $e->getMessage();
}
