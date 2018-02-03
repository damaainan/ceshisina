<?php
// php sh.php 来源文件名  目标文件名
$name1 = $argv[1];
$name2 = $argv[2];

$handle = @fopen($name1, "r");
$handle1 = @fopen($name2.".js", "a"); // 可以区分的更细 01-1.js 01-2.js ......
if ($handle) {
    $flag = false;
    // $num = 0;
    while (($buffer = fgets($handle, 4096)) !== false) {
        $buffer = trim($buffer);
        if ($buffer === "```js") {
            $flag = true;
            // $num++;
            // $handle1 = @fopen("./code/" . $name2 . "-" . $num . ".js", "a");
            // $handle2 = @fopen("./code/" . $name2 . "-" . $num . ".php", "a");
            fwrite($handle1, "\n");
            // fwrite($handle2, "<?php\n");
            continue;
        } else if ($buffer === "```") {
            fwrite($handle1, "\n");
            // fwrite($handle2, "\n");
            // fclose($handle1);
            // fclose($handle2);
            $flag = false;
        }
        if ($flag) {
            fwrite($handle1, $buffer . "\n");
            // fwrite($handle2, $buffer . "\n");
        }
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}