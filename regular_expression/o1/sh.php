<?php 
// php sh.php 来源文件名  目标文件名
$name1 = $argv[1]; 
$name2 = $argv[2]; 

$handle = @fopen($name1, "r");
$handle1 = @fopen($name2, "a"); // 可以区分的更细 01-1.js 01-2.js ......
if ($handle) {
    $flag = false;
    while (($buffer = fgets($handle, 4096)) !== false) {
        $buffer = trim($buffer);
        if($buffer === "```js"){
            $flag = true;
            fwrite($handle1, "\n");
            continue;
        }else if($buffer === "```"){
            fwrite($handle1, "\n");
            $flag = false;
        }
        if($flag){
            fwrite($handle1, $buffer."\n");
        }
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}