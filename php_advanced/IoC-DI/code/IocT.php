<?php 
require_once("Ioc.php");

class book
{
    private static $db;
    private static $file;
    public static function setdb($db)
    {
        static::$db = $db;
    }
    public static function setfile($file)
    {
        static::$file = $file;
    }
    public static function get()
    {
        echo static::$file,'******',static::$db;
    }
}

