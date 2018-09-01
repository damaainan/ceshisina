<?php
header("Content-type:text/html; Charset=utf-8");

$arr = [];

for ($i = 0; $i < 5000; $i++) {
    $arr[] = rand(1, 50000);
}


    function GetNumInPos($number, $pos)
    {
        $number = strrev( $number );
        return $number[ -- $pos ];
    }


    function LsdRadixSort(array $numbers = array(), $tpos=1) 
    {
        $count = count( $numbers );
        if( $count <= 1 ) return $numbers;

        $bucket = array();
        for($i = 0; $i < 10; $i ++)
        {
            $bucket[$i] = array(0);
        }

        // 由低 $p=1 至高位 $p<=$d 循环排序
        for($p = 1; $p <= $tpos; $p ++) 
        {
            // 将对应数据按当前位的数值放入桶里
            for($i = 0; $i < $count; $i ++)
            {
                $n = GetNumInPos($numbers[$i], $p);
                $index = ++ $bucket[$n][0];
                $bucket[$n][$index] = $numbers[$i];
            }

            // 收集桶里的数据
            for($i = 0, $j = 0; $i < 10; $i ++) 
            {
                for($num = 1; $num <= $bucket[$i][0]; $num ++)
                {
                    $numbers[$j++] = $bucket[$i][$num];
                }
                $bucket[$i][0] = 0;
            }
        }
        return $numbers;
    }



$countsort_start_time = microtime(true);

$countsort_sort =LsdRadixSort($arr,1); // $max 的取值？

$countsort_end_time = microtime(true);

$countsort_need_time = $countsort_end_time - $countsort_start_time;

print_r(" LSD基数排序耗时:" . $countsort_need_time . "<br />");

//  LSD基数排序耗时:0.0056970119476318