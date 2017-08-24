# [PHP实现归并排序（合并排序）--算法原理分析][0]



版权声明：本文为博主原创文章，未经博主允许不得转载。

归并排序：时间复杂度为~O(nlogn)--又称 合并排序

归并（Merge）排序法是将两个（或两个以上）有序表合并成一个新的有序表，

即把待排序序列分为若干个有序的子序列，再把有序的子序列合并为整体有序序列。

归并排序的一个缺点是它需要存储器有另一个大小等于数据项数目的数组。如果初始数组几乎占满整个存储器，那么归并排序将不能工作，但是如果有足够的空间，归并排序会是一个很好的选择。

```php
    <?php
    $arrStoreList = array(3,2,4,1,5);
    $sort = new Merge_sort();
    $sort->stableSort($arrStoreList, function ($a, $b) {    // function ($a, $b)匿名函数
                return $a < $b;
    });
    //静态调用方式也行
    /*Merge_sort:: stableSort($arrStoreList, function ($a, $b) {
                return $a < $b;
    });*/
    print_r($arrStoreList);
    
    class Merge_sort{
    
     public static function stableSort(&$array, $cmp_function = 'strcmp') {
            //使用合并排序
            self::mergeSort($array, $cmp_function);
            return;
        }
     public static function mergeSort(&$array, $cmp_function = 'strcmp') {
            // Arrays of size < 2 require no action.
            if (count($array) < 2) {
                return;
            }
            // Split the array in half
            $halfway = count($array) / 2;
            $array1 = array_slice($array, 0, $halfway);
            $array2 = array_slice($array, $halfway);
            // Recurse to sort the two halves
            self::mergeSort($array1, $cmp_function);
            self::mergeSort($array2, $cmp_function);
            // If all of $array1 is <= all of $array2, just append them.
    //array1 与 array2 各自有序;要整体有序，需要比较array1的最后一个元素和array2的第一个元素大小
            if (call_user_func($cmp_function, end($array1), $array2[0]) < 1) {  
                $array = array_merge($array1, $array2);
    
                return;
            }
            // 将两个有序数组合并为一个有序数组：Merge the two sorted arrays into a single sorted array
            $array = array();
            $ptr1 = $ptr2 = 0;
            while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
                if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) < 1) {
                    $array[] = $array1[$ptr1++];
                } else {
                    $array[] = $array2[$ptr2++];
                }
            }
            // Merge the remainder
            while ($ptr1 < count($array1)) {
                $array[] = $array1[$ptr1++];
            }
            while ($ptr2 < count($array2)) {
                $array[] = $array2[$ptr2++];
            }
            return;
        }
    }
    ?>
```

  
输出结果：`Array ( [0] => 5 [1] => 4 [2] => 3 [3] => 2 [4] => 1)`

[算法][9]原理分析：关键是理解递归调用及其返回函数的原理

![][10]

[0]: http://blog.csdn.net/dalaoadalaoa/article/details/49229295
[1]: http://www.csdn.net/tag/%e7%ae%97%e6%b3%95
[2]: http://www.csdn.net/tag/%e5%bd%92%e5%b9%b6%e6%8e%92%e5%ba%8f
[3]: http://www.csdn.net/tag/php
[8]: #
[9]: http://lib.csdn.net/base/datastructure
[10]: ./img/20151018175227963.png