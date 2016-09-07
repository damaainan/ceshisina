<?php
       function mergeSort(&$arr) {
            $len = count($arr);//求得数组长度
            mSort($arr, 0, $len-1);
        }
        //实际实现归并排序的程序
        function mSort(&$arr, $left, $right) {
            if($left < $right) {
                //说明子序列内存在多余1个的元素，那么需要拆分，分别排序，合并
                //计算拆分的位置，长度/2 去整
                $center = floor(($left+$right) / 2);
                //递归调用对左边进行再次排序：
                mSort($arr, $left, $center);
                //递归调用对右边进行再次排序
                mSort($arr, $center+1, $right);
                //合并排序结果
                mergeArray($arr, $left, $center, $right);
            }
        }
        //将两个有序数组合并成一个有序数组
        function mergeArray(&$arr, $left, $center, $right) {
            //设置两个起始位置标记
            $a_i = $left;
            $b_i = $center+1;
            while($a_i<=$center && $b_i<=$right) {
                //当数组A和数组B都没有越界时
                if($arr[$a_i] < $arr[$b_i]) {
                    $temp[] = $arr[$a_i++];
                } else {
                    $temp[] = $arr[$b_i++];
                }
            }
            //判断 数组A内的元素是否都用完了，没有的话将其全部插入到C数组内：
            while($a_i <= $center) {
                $temp[] = $arr[$a_i++];
            }
            //判断 数组B内的元素是否都用完了，没有的话将其全部插入到C数组内：
            while($b_i <= $right) {
                $temp[] = $arr[$b_i++];
            }
            //将$arrC内排序好的部分，写入到$arr内：
            for($i=0, $len=count($temp); $i<$len; $i++) {
                $arr[$left+$i] = $temp[$i];
            }

        }
        $arr = array(23,0,32,45,56,75,43,0,34);
        mergeSort($arr);
        var_dump($arr);
