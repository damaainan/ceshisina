<?php 

header("Content-type:text/html; Charset=utf-8");

function Gen()
{
    yield 'key' => 'value';
}

$gen = Gen();

echo "{$gen->key()} => {$gen->current()}";




function getLines($file) {
     $f = fopen($file, 'r');
     try {
         while ($line = fgets($f)) {
             yield $line;
         }
     } finally {
         fclose($f);
     }
 }

$getLines = getLines('no_such_file.txt');
$getLines->rewind(); // with ->rewind(), a file read error will be thrown here and a log file will not be cleared

openAndClearLogFile();

 foreach ($getLines as $n => $line) { // without ->rewind(), the script will die here and your log file will be cleared
     writeToLogFile('reading: ' . $line . "\n");
 }

closeLogFile();





function printer() {
    while (true) {
        $string = yield;
        echo $string;
    }
}

$printer = printer();
$printer->send('Hello world!');



/*
$coroutine=call_user_func(create_function('', 
<<<fun_code
     echo "inner 1:\n";
     $rtn=(yield 'yield1');
     echo 'inner 2:';var_export($rtn);echo "\n";
     $rtn=(yield 'yield2');
     echo 'inner 3:';var_export($rtn);echo "\n";
     $rtn=(yield 'yield3');
     echo 'inner 4:';var_export($rtn);echo "\n";
 fun_code
 ));
 echo ":outer 1\n";                                       // :outer 1
 var_export($coroutine->current());echo ":outer 2\n";     // inner 1:, 'yield1':outer 2
 var_export($coroutine->current());echo ":outer 3\n";     // 'yield1':outer 3
 var_export($coroutine->next());echo ":outer 4\n";        // inner 2:NULL, NULL:outer 4
 var_export($coroutine->current());echo ":outer 5\n";     // 'yield2':outer 5
 var_export($coroutine->send('jack'));echo ":outer 6\n";  // inner 3:'jack', 'yield3':outer 6
 var_export($coroutine->current());echo ":outer 7\n";     // 'yield3':outer 7
 var_export($coroutine->send('peter'));echo ":outer 8\n"; // inner 4:'peter', NULL:outer 8 

 */


function foo() {
     $string = yield;
     echo $string;
     for ($i = 1; $i <= 3; $i++) {
         yield $i;
     }
 }

$generator = foo();
$generator->send('Hello world!');
 foreach ($generator as $value) echo "$value\n";




function nums() {
     for ($i = 0; $i < 5; ++$i) {
                 //get a value from the caller
         $cmd = (yield $i);
         
         if($cmd == 'stop')
             return;//exit the function
         }     
 }

$gen = nums();
 foreach($gen as $v)
 {
     if($v == 3)//we are satisfied
         $gen->send('stop');
     
     echo "{$v}\n";
 }