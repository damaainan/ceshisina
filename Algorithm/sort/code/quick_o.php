<?php
class QuickSort
{
    /**
     * 外部调用快速排序的方法
     *
     * @param $arr array 整个序列
     */
    public static function sort(&$arr)
    {
        $length = count($arr);
        self::sortRecursion($arr, 0, $length - 1);
    }

    /**
     * 递归地对序列分区排序
     *
     * @param $arr array 整个序列
     * @param $l int 待排序的序列左端
     * @param $r int 待排序的序列右端
     */
    private static function sortRecursion(&$arr, $l, $r)
    {
        if ($l >= $r) {
            return;
        }
        $p = self::partition($arr, $l, $r);
        // $p = self::propartition($arr, $l, $r); // 优化
        //对基准点左右区域递归调用排序算法
        self::sortRecursion($arr, $l, $p - 1);
        self::sortRecursion($arr, $p + 1, $r);
    }

    /**
     * 分区操作
     *
     * @param $arr array 整个序列
     * @param $l int 待排序的序列左端
     * @param $r int 待排序的序列右端
     * @return mixed 基准点
     */
    private static function partition(&$arr, $l, $r)
    {
        $v = $arr[$l];
        $j = $l;
        for ($i = $l + 1; $i <= $r; $i++) {
            if ($arr[$i] < $v) {
                $j++;
                self::swap($arr, $i, $j);
            }
        }
        self::swap($arr, $l, $j);
        return $j;
    }

    private static function propartition(&$arr, $l, $r)
    {
        //优化1：从数组中随机选择一个数与最左端的数交换，达到随机挑选的效果
        //这个优化使得快速排序在应对近似有序数组排序时，迭代次数更少，排序算法效率更高
        self::swap($arr, $l, rand($l + 1, $r));

        $v = $arr[$l];
        $j = $l;
        for ($i = $l + 1; $i <= $r; $i++) {
            if ($arr[$i] < $v) {
                $j++;
                self::swap($arr, $i, $j);
            }
        }
        self::swap($arr, $l, $j);
        return $j;
    }

    /**
     * 交换数组的两个元素
     *
     * @param $arr array
     * @param $i int
     * @param $j int
     */
    private static function swap(&$arr, $i, $j)
    {
        $tmp     = $arr[$i];
        $arr[$i] = $arr[$j];
        $arr[$j] = $tmp;
    }
    // 生成指定元素个数的随机数组
    public static function generateRandomArray($n)
    {
        $list = [];
        for ($i = 0; $i < $n; $i++) {
            $list[$i] = rand();
        }
        return $list;
    }
    /**
     * 生成近似顺序排序的数组
     *
     * @param $n int 元素个数
     * @param $swapTimes int 交换次数
     * @return array 生成的数组
     */
    public static function generateNearlyOrderedIntArray($n, $swapTimes)
    {
        $arr = [];
        for ($i = 0; $i < $n; $i++) {
            $arr[] = $i;
        }
        //交换数组中的任意两个元素
        for ($i = 0; $i < $swapTimes; $i++) {
            $indexA       = rand() % $n;
            $indexB       = rand() % $n;
            $tmp          = $arr[$indexA];
            $arr[$indexA] = $arr[$indexB];
            $arr[$indexB] = $tmp;
        }
        return $arr;
    }
}
$start_time = microtime(true);
$QuickSort = new QuickSort();

$arr = $QuickSort::generateRandomArray(10000);

$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n"); // 5.6272971630096


$start_time = microtime(true);
$QuickSort::sort($arr);
$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n"); 

$arr = $QuickSort::generateRandomArray(100000);

$start_time = microtime(true);
$QuickSort::sort($arr);
$end_time = microtime(true);
$need_time = $end_time - $start_time;

print_r("排序耗时:" . $need_time . "\r\n"); 