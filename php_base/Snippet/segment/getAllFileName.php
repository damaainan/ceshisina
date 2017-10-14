<?php
/**
 * Created by PhpStorm.
 * User: zoco
 * Date: 16/10/29
 * Time: 17:12
 */
function searchDir($path, &$data)
{
    if (is_dir($path)) {
        $dp = dir($path);
        while ($file = $dp->read()) {
            if ($file != '.' && $file != '..') {
                searchDir($path . '/' . $file, $data);
            }
        }
        $dp->close();
    }
    if (is_file($path)) {
        $data[] = $path . '<br>';
    }
}
function getDir($dir)
{
    $data = array();
    searchDir($dir, $data);
    return $data;
}
print_r(getDir('./'));
