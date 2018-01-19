<?php 

// 批量修改文件名

$arr = glob('*.md');
// var_dump($arr);
foreach ($arr as $value) {
    // echo $value;
    /*
    $handle = @fopen("./".$value,"r");
    $buffer = fgets($handle, 4096) ; // 按行读取
    // echo $buffer;
    fclose($handle);
    $a1 = explode('[', $buffer);
    $aa = explode('.',$value);
    $b1 = explode(']', $a1[1]);
    $oldname = $value;
    $newname = $aa[0].$b1[0].".md";
*/
    $oldname = $value;
    $newname = str_replace('☆============','',$value);
    // echo $newname;
    rename("./".$oldname, "./".$newname);
    echo "\n";
}