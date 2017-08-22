<?php 
header("Content-type:text/html; Charset=utf-8");
$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 10000);
}


//1 插入排序


function insertionSort($arr)
{

    for ($i = 1; $i < count($arr); $i++) { // 从后向前 
        $tmp = $arr[$i]; //设置监视哨   从 第二个元素开始 依次后移  相当于每次重新 立 flag
        $key = $i - 1; //设置开始查找的位置    flag 的前一个元素开始
        while ($key >= 0 && $tmp < $arr[$key]) { // 监视哨的值比查找的值小 并且 值有效
            $arr[$key + 1] = $arr[$key];  //数组的值进行后移
            $key--;  //要查找的位置后移
        }
        if (($key + 1) != $i) //放置监视哨
            $arr[$key + 1] = $tmp; // 此处 $key 已经 相比    “数组的值进行后移”  时的 $key 小 1，故需加 1 
    }
    return $arr;

}

    function insertion(array $numbers = array())
    {
        $count = count( $numbers );
        if( $count <= 1 ) return $numbers;

        for($i = 1; $i < $count; $i ++)
        {
            $temp = $numbers[$i];//监视哨
            for($j = $i-1; $j >= 0 && $numbers[$j] > $temp; $j --) // for 循环 写成 while 循环更好理解
            {
                $numbers[$j+1] = $numbers[$j];
            }
            $numbers[$j+1] = $temp;
        }

        return $numbers;
    }


$insertion_start_time = microtime(true);

$insertion_sort = insertionSort($arr);

$insertion_end_time = microtime(true);

$insertion_need_time = $insertion_end_time - $insertion_start_time;

print_r("插入排序耗时:" . $insertion_need_time . "<br />");

// 插入排序耗时:6.5865030288696