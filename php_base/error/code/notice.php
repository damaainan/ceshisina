<?php 
error_reporting(E_ALL);

$a = ["a" => 1];

class UnSerializeException extends ErrorException
{

}

set_error_handler(function ($severity, $message, $file, $line) {
    $info = explode(":", $message);

    if ($severity == E_NOTICE) {
        if ($info[0] == "unserialize()") {
            throw new UnSerializeException($message);
        }
        return true;
    } else {

        throw new ErrorException($message, 0, $severity, $file, $line);;
    }
});


try {
    $b = unserialize(json_encode($a));
} catch (ErrorException $exception) {
    var_dump(get_class($exception), $exception->getMessage(), $exception->getTraceAsString()); // 捕获到了
} finally {
    restore_error_handler();
}

try {
    $b = unserialize(json_encode($a));
} catch (ErrorException $exception) {
    var_dump(get_class($exception), $exception->getMessage(), $exception->getTraceAsString()); // 无法捕获
}