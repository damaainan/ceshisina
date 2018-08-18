<?php
class Sort {
    /**
     * 简单的选择排序
     *
     * @param unknown_type $arr
     */
    public function selectSort(&$arr) {
        $len = count($arr);
        for ($i = 0; $i < $len; $i++) {
            $min = $i;
            for ($j = $i + 1; $j <= $len - 1; $j++) {
                if ($arr[$min] > $arr[$j]) {
                    //如果找到比$arr[$min]较小的值，则将该下标赋给$min
                    $min = $j;
                }
            }
            if ($min != $i) {
                //若$min不等于$i，说明找到了最小值，则交换
                $this->swap($arr[$i], $arr[$min]);
            }
        }
    }
    /**
     * 将$a和$b两个值进行位置交换
     */
    public function swap(&$a, &$b) {
        $temp = $a;
        $a = $b;
        $b = $temp;
    }
}
$arr = array(4, 6, 1, 2, 9, 8, 7, 3, 5);
$test = new Sort();
$test->selectSort($arr); //简单的选择排序
var_dump($arr);