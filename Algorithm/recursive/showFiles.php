<?php

function showFiles(string $dirName, array &$allFiles = [])
{
    $files = scandir($dirName);
    foreach ($files as $key => $value) {
        $path = realpath($dirName . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $allFiles[] = $path;
        } else if ($value != "." && $value != "..") {
            showFiles($path, $allFiles);
            $allFiles[] = $path;
        }
    }
    return;
}
$files = [];
showFiles("..", $files);

foreach($files as $file) {
echo $file."\n";
}