<?php
/**
 * User: rudy
 * Date: 2016/01/20 12:31
 *
 *  功能描述
 *
 */

include 'TLogger.php';

$log = TLogger::getLogger();
$log->log(array('abaa',"'aaa'",'中午'));
$log->log($log);