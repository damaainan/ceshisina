## 剑指offer中的算法题(PHP版)

来源：[https://segmentfault.com/a/1190000008675563](https://segmentfault.com/a/1190000008675563)


## 二维数组中的查找

在一个二维数组中，每一行都按照从左到右递增的顺序排序，每一列都按照从上到下递增的顺序排序。请完成一个函数，输入这样的一个二维数组和一个整数，判断数组中是否含有该整数。

```php
function search($target, $array)
{
    $i = count($array[0]) - 1;
    $j = 0;

    if ($array[count($array) - 1][$i] < $target || $array[0][0] > $target) {
        return false;
    }

    for ($i; $i >= 0; $i--) {
        if ($array[$j][$i] <= $target) {
            for ($j; $j < count($array); $j++) {
                if ($array[$j][$i] == $target) {
                    return true;
                } else if ($array[$j][$i] > $target) {
                    break;
                }
            }
        }
    }

    return false;
}


$s = [[1, 2, 8, 9], [2, 4, 9, 12], [4, 7, 10, 13], [6, 8, 11, 15]];
var_dump(search(1, $s));
```
## 替换空格

请实现一个函数，将一个字符串中的空格替换成“%20”。例如，当字符串为We Are Happy.则经过替换之后的字符串为We%20Are%20Happy。

```php
function replace($str) {
    $len = strlen($str);
    if ($len <= 0 ) {
        return false;
    }
    $blank = 0;
    for ($i = 0; $i < $len; $i++) {
        if ($str[$i] == ' ') {
            $blank++;
        }
    }
    if ($blank == 0) {
        return false;
    }

    $new_length = ($len + $blank * 2) - 1;

    for ($i = $len-1; $i >=0; $i--) {
        if ($str[$i] == ' ') {
            $str[$new_length--] = '0';
            $str[$new_length--] = '2';
            $str[$new_length--] = '%';
        } else {
            $str[$new_length--] = $str[$i];
        }
    }

    return $str;
}


var_dump(replace('We Are Happy'));
```
## 斐波那契数列

大家都知道斐波那契数列，现在要求输入一个整数n，请你输出斐波那契数列的第n项。解法有两种，一种是递归法，一种是迭代法,但是递归法计算的时间复杂度是以n的指数的方式递增的， **`如果面试中千万不要用递归法`** ，一定要用迭代法。

递归法

```php
function a($n) {
    if ($n == 0) {
        return 0;
    } else if ($n == 1) {
        return 1;
    }

    return a($n-1) + a($n - 2);
}


echo a(10);
```

迭代法

```php
function a($n) {

    $ret = array();

    for ($i=0; $i <= $n; $i++) {
        if ($i == 0) {
            $ret[$i] = 0;
            continue;
        }else if ($i == 1) {
            $ret[$i] = 1;
            continue;
        }

        $ret[$i] = $ret[$i-1] + $ret[$i-2];
    }

    return $ret[$n];
}

echo a(10);
```
## 调整数组顺序使奇数位于偶数前面

输入一个整数数组，实现一个函数来调整该数组中数字的顺序，使得所有的奇数位于数组的前半部分，所有的偶数位于位于数组的后半部分

```php
function test($array) {

    if (count($array) < 2) {
        return false;
    }

    $start = 0;
    $end = count($array) - 1;

    while ($start < $end) {
        while ($array[$end] % 2 == 0 && $start < $end) {
            $end--;
        }

        while ($array[$start] % 2 == 1 && $start < $end) {
            $start++;
        }

        if ($array[$start] % 2 == 0 && $array[$end] % 2 == 1) {
            $temp = $array[$end];
            $array[$end] = $array[$start];
            $array[$start] = $temp;
        }
    }

    return $array;
}
```
## 顺时针打印矩阵

输入一个矩阵，按照从外向里以顺时针的顺序依次打印出每一个数字

这个是书上的方法

```php
function printMatrixMain($data) {
    $start = 0;
    $columns = count($data);
    $rows = count($data[0]);

    while ($columns > $start * 2 && $rows > $start * 2) {
        printMatrix($data, $columns, $rows, $start);
        ++$start;
    }
}


function printMatrix($data, $columns, $rows, $start)
{
    $endx     = $columns - 1 - $start;
    $endy     = $rows - 1 - $start;

    for ($i = $start; $i <= $endx; $i++) {
        echo $data[$start][$i], '/';
    }

    for ($i = $start + 1; $i <= $endy; $i++) {
        echo $data[$i][$endx], '/';
    }

    for ($i = $endx - 1; $i >= $start; $i--) {
        echo $data[$endy][$i], '/';
    }

    for ($i = $endy - 1; $i > $start; $i--) {
        echo $data[$i][$start], '/';
    }
}


$s = array(
    array(1, 2, 3, 4, 5),
    array(5, 6, 7, 8, 9),
    array(9, 10, 11, 12, 13),
    array(13, 14, 15, 16, 17),
    array(1, 2, 3, 4, 5),
);

printMatrixMain($s);
```

这个是我自己写的方法

```php
function printMatrix($data)
{
    $start    = 0;
    $num      = 0;
    $can_loop = true;
    $endx     = count($data[0]) - 1;
    $endy     = count($data) - 1;

    while ($can_loop) {
        if (($endx - $num - $start < 2) || ($endy - $num - $start) < 2) {
            $can_loop = false;
        }

        for ($i = $start; $i <= $endx - $num; $i++) {
            echo $data[$start][$i], '/';
        }

        for ($i = $start + 1; $i <= $endy - $num; $i++) {
            echo $data[$i][$endx - $num], '/';
        }

        for ($i = $endx - $num - 1; $i >= $start; $i--) {
            echo $data[$endy - $num][$i], '/';
        }

        for ($i = $endy - $num - 1; $i > $start; $i--) {
            echo $data[$i][$start], '/';
        }

        $start++;
        $num++;
    }
}


$s = array(
    array(1, 2, 3, 4, 5),
    array(5, 6, 7, 8, 9),
    array(9, 10, 11, 12, 13),
    array(13, 14, 15, 16, 17),
    array(1, 2, 3, 4, 5),
);

printMatrix($s);
```
## 二叉搜索树的后序遍历序列

输入一个整数数组，判断该数组是不是某二叉搜索树的后序遍历的结果。如果是则输出true,否则输出false.

```php
function VerifySquenceOfBST($data) {
    if(empty($data)){
        return false;
    }
    
    $length = count($data);
    $root   = $data[$length - 1];

    for($i = 0; $i<$length-1; $i++) {
        if($data[$i] > $root) {
            break;
        }
    }

    $j = $i;

    for($j; $j < $length -1; $j++) {
        if ($data[$j] < $root) {
            return false;
        }
    }

    $left = true;
    $right = true;

    if($i > 0) {
        $left = VerifySquenceOfBST(array_slice($data, 0, $i));
    }

    if($j < $length - 1) {
        $right = VerifySquenceOfBST(array_slice($data, $i, $length-1-$i));
    }

    return ($left&&$right);
}

var_dump(VerifySquenceOfBST([5,7,6,9,11,10,8]));
```
## 字符串的排列

输入一个字符串，打印出所有字符串的组合，例如：abc 会打印出abc,acb bac bca cab cba

```php
function Permutation($str) {
    if ($str == NULL)
        return false;
    if (!is_string($str))
        return false;
    echo_child($str, 0);             /*书上用指针，这里我们用下标*/
}

function echo_child($str, $index) {
    $len = strlen($str);
    if ($len == ($index + 1)) {
        echo $str . "    ";
        return false;
    } else {
        for ($i = $index; $i < $len; $i++) {
            $new_str         = $str;
            $tmp             = $new_str[$index];
            $new_str[$index] = $new_str[$i];
            $new_str[$i]     = $tmp;              /*以上是和第一个字符交换*/
            echo_child($new_str, $index + 1);  /*运用递归不断对余下的部分进行交换*/
        }
    }
}

Permutation('abc');
```
## 数组中出现次数超过一半的数字

数组中有一个数字出现的次数超过数组长度的一半，请找出这个数字。例如输入一个长度为9的数组{1,2,3,2,2,2,5,4,2}。由于数字2在数组中出现了5次，超过数组长度的一半，因此输出2。如果不存在则输出0。

```php
function MoreThanHalfNum_Solution($data)
{
   if (empty($data)) {
        return false;
    }

    $ret = array();

    foreach ($data as $key => $val) {
        if (empty($ret)) {
            $ret['value'] = $val;
            $ret['count'] = 1;
        } else {
            if ($val == $ret['value']) {
                $ret['count']++;
            } else {
                if (--$ret['count'] == 0) {
                    $ret['value'] = $val;
                    $ret['count'] = 1;
                }
            }
        }
    }

    if (checkMoreThanHalf($data, $ret['value'])) {
        return $ret['value'];
    } else {
        return 0;
    }
}

function checkMoreThanHalf($data, $number) {
    $time = 0;

    foreach ($data as $key => $val) {
        if ($val == $number) {
            $time++;
        }
    }

    if ($time * 2 > count($data)) {
        return true;
    } else {
        return false;
    }
}
```
## 连续子数组的最大和

输入一个整形数组，数组里有正数也有负数。数组中一个或连续的多个整数组成一个子数组。求所有子数组的和的最大值。要求时间复杂度为O(n)。

```php
function FindGreatestSumOfSubArray($data)
{
    if (empty($data)) {
        return false;
    }

    $ret = 0;
    $max = $data[0];

    for ($i = 0; $i < count($data); $i++) {
        $ret = $ret + $data[$i];

        if ($ret > $max) {
            $max = $ret;
        }

        if ($ret < 0) {
            $ret = 0;
        }
    }

    return $max;
}
```
## 整数中1出现的次数（从1到n整数中1出现的次数）

例如输入12，从1到12这些整数中包含1的数字有1，10，11和12，1一共出现了5次

```php
function NumberOf1Between1AndN_Solution($str)
{
    settype($str, 'string');

    if ($str == 0 || strlen($str) == 0) {
        return 0;
    }

    $first = $str[0];

    if (strlen($str) == 1 && $first > 0) {
        return 1;
    }

    if ($first > 1) {
        $numFirstDigit = powerBase10(strlen($str) - 1);
    } else {
        $numFirstDigit = substr($str, 1) + 1;
    }

    $numOtherDigits = $first * (strlen($str)-1) * powerBase10(strlen($str)-2);
    $numRecursive = NumberOf1Between1AndN_Solution(substr($str, 1));

    return $numFirstDigit + $numOtherDigits + $numRecursive;
}

function powerBase10($number) {
    return pow(10, $number);
}
```
## 数组中只出现一次的数字

一个整型数组里除了两个数字之外，其他的数字都出现了两次。请写程序找出这两个只出现一次的数字。

```php
function FindNumsAppearOnce($array) {
    if (empty($array)) {
        return false;
    }

    $xor = 0;
    foreach ($array as $key => $val) {
        $xor ^= $val;
    }

    $indexOf1 = findFirstBit1($xor);

    $result = array(0,0);
    foreach ($array as $key => $value) {
        if (isBit1($value, $indexOf1)) {
            $result[0] ^= $value;
        } else {
            $result[1] ^= $value;
        }
    }

    return $result;
}


function findFirstBit1($num) {
    $index = 0;

    while (($num & 1) == 0) {
        $num >>= 1;
        $index++;
    }

    return $index;
}

function isBit1($num, $indexBit) {
    $num >>= $indexBit;

    return (($num & 1) === 1);
}

$ret = FindNumsAppearOnce(array(2, 4, 3, 6, 3, 2,5,5));
echo $ret[0],$ret[1];
```
## 和为S的两个数字

输入一个递增排序的数组和一个数字S，在数组中查找两个数，使得他们的和正好是S，如果有多对数字的和等于S，输出任意一对即可。

```php
function FindNumbersWithSum($array, $sum) {
    if (empty($array)) {
        return false;
    }

    $start = 0;
    $end   = count($array) - 1;

    while ($start < $end) {
        if ($array[$start] + $array[$end] < $sum) {
            ++$start;
        } else if ($array[$start] + $array[$end] > $sum) {
            --$end;
        } else {
            return $array[$start].'/'.$array[$end];
        }
    }

    return false;
}
```
## 圆圈中最后剩下的数字

0,1,...,n-1这n个数字排成一个圆圈，从数字0开始每次从这个圆圈里删除第m个数字，求出这个圆圈里剩下的最后一个数字。

```php
//通过总结出的公式提供的算法，公式证明过程不展开。
function LastRemaining_Solution($n, $m) {
    if ($n < 1 || $m < 1) {
        return -1;
    }

    $last = 0;

    for($i = 2; $i <= $n; $i++) {
        $last = ($last + $m) % $i;
    }

    return $last;
}



//模拟环形链表，这个效率和性能都不如前一个，但是思路简单
function LastRemaining_Solution2($n, $m) {
    if ($n < 1 || $m < 1) {
        return -1;
    }

    $data = array();
    for ($i=0; $i< $n; $i++) {
        $data[] = $i;
    }

    $p = 0;
    while (count($data) >1 ) {
        for ($i=1; $i<$m; $i++) {
            ++$p;
            $p = isset($data[$p]) ? $p : 0;
        }
        unset($data[$p]);
        $data = array_values($data);
        $p = isset($data[$p]) ? $p : 0;
    }

    return reset($data);
}
```
