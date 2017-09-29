<?php
header("Content-type:text/html; Charset=utf-8");
/**
 * 多维数组去重
 * @param array
 * @return array
 */
function superUnique($array)
{
    $result = array_map("unserialize", array_unique(array_map("serialize", $array)));

    foreach ($result as $key => $value) {
        if (is_array($value)) {
            $result[$key] = superUnique($value);
        }
    }

    return $result;
}
