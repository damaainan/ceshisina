<?php
//多维数组根据某个key排序
usort($arrData,array($this, 'sortByKey'));
/** 
 * @brief 根据pubtime倒序排序,结合usort()函数使用
 * @param array $arrA
 * @param array $arrB
 * @return int 
 */
function sortByKey($arrA, $arrB) {
    return ($arrA['key'] > $arrB['key']) ? -1 : 1;
}   
/** 
 * @brief 多维数组排序
 * @param array &$arr 待排序数组名
 * @param string $col 排序依据的字段名
 * @param int $dir(SORT_ASC|SORT_DESC)  排序方向
 * @return null
 */
function arraySortByColumn(&$arr, $col, $dir=SORT_ASC) {
    $sort_col = array();
    foreach ($arr as $key=>$row) {
        $sort_col[$key] = $row[$col];
    }   
    array_multisort($sort_col, $dir, $arr);
}  