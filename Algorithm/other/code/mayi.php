<?php
// PHP实现的蚂蚁爬杆路径算法代码
/**
 * 有一根27厘米的细木杆，在第3厘米、7厘米、11厘米、17厘米、23厘米这五个位置上各有一只蚂蚁。
 * 木杆很细，不能同时通过一只蚂蚁。开始 时，蚂蚁的头朝左还是朝右是任意的，它们只会朝前走或调头，
 * 但不会后退。当任意两只蚂蚁碰头时，两只蚂蚁会同时调头朝反方向走。假设蚂蚁们每秒钟可以走一厘米的距离。
 * 编写程序，求所有蚂蚁都离开木杆 的最小时间和最大时间。
 */
function add2($directionArr, $count, $i)
{
    if (0 > $i) {
        // 超出计算范围
        return $directionArr;
    }
    if (0 == $directionArr[$i]) {
        // 当前位加1
        $directionArr[$i] = 1;
        return $directionArr;
    }
    $directionArr[$i] = 0;
    return add2($directionArr, $count, $i - 1); // 进位
}
$positionArr = array( // 所在位置
    3,
    7,
    11,
    17,
    23,
);
function path($positionArr)
{
    // 生成测试路径
    $pathCalculate = array();
    $count         = count($positionArr);
    $directionArr  = array_fill(0, $count, 0); // 朝向
    $end           = str_repeat('1', $count);
    while (true) {
        $path                       = implode('', $directionArr);
        $pathArray                  = array_combine($positionArr, $directionArr);
        $total                      = calculate($positionArr, $directionArr);
        $pathCalculate['P' . $path] = $total;
        if ($end == $path) {
            // 遍历完成
            break;
        }
        $directionArr = add2($directionArr, $count, $count - 1);
    }
    return $pathCalculate;
}
function calculate($positionArr, $directionArr)
{
    $total  = 0; // 总用时
    $length = 27; // 木杆长度
    while ($positionArr) {
        $total++; // 步增耗时
        $nextArr = array(); // 下一步位置
        foreach ($positionArr as $key => $value) {
            if (0 == $directionArr[$key]) {
                $next = $value - 1; // 向0方向走一步
            } else {
                $next = $value + 1; // 向1方向走一步
            }
            if (0 == $next) {
                // 在0方向走出
                continue;
            }
            if ($length == $next) {
                // 在1方向走出
                continue;
            }
            $nextArr[$key] = $next;
        }
        $positionArr = $nextArr; // 将$positionArr置为临时被查找数组
        foreach ($nextArr as $key => $value) {
            $findArr = array_keys($positionArr, $value);
            if (count($findArr) < 2) {
                // 没有重合的位置
                continue;
            }
            foreach ($findArr as $findIndex) {
                $directionArr[$findIndex] = $directionArr[$findIndex] ? 0 : 1; // 反向处理
                unset($positionArr[$findIndex]); // 防止重复查找计算
            }
        }
        $positionArr = $nextArr; // 将$positionArr置为下一步结果数组
    }
    return $total;
}
$pathCalculate = path($positionArr);
echo '<pre>calculate-';
print_r($pathCalculate);
echo 'sort-';
asort($pathCalculate);
print_r($pathCalculate);
