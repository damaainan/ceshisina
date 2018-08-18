<?php

class MyOrderArray {
    private $arr = [];
    private $elements = 0; // 有效数据的长度

    public function __construct() {

    }

    // 添加数据
    public function insert($value) {
        $i = 0;
        for (; $i < $this->elements; $i++) { 
            if ($this->arr[$i] > $value) {
                break;
            }
        }

        for ($j = $this->elements; $j > $i ; $j--) { 
            $this->arr[$j] = $this->arr[$j - 1];
        }

        $this->arr[$i] = $value;
        $this->elements++;
    }

    // 显示数据
    public function display() {
        echo '[';
        for ($i = 0; $i < $this->elements; $i++) { 
            echo $this->arr[$i];
            if ($i != $this->elements - 1) {
                echo ' ';
            }
        }
        echo ']';
    }

    // 查找数据 线性查找
    public function search($value) {
        $i = 0;
        for (; $i < $this->elements; $i++) { 
            if ($value == $this->arr[$i]) {
                break;
            }
        }

        if ($i == $this->elements) {
            return -1;
        }

        return $i;
    }

    // 查找数据 二分查找，又名折半查找 算法复杂度为o(log(n))
    public function binarySearch($value) {
        $middle = 0;
        $low = 0;
        $pow = $this->elements;

        while (true) {
            $middle = (int) (($low + $pow) / 2);
            if ($this->arr[$middle] == $value) {
                return $middle;
            } else if ($low > $pow) {
                return -1;
            } else {
                if ($this->arr[$middle] > $value) {
                    $pow = $middle - 1;
                } else {
                    $low = $middle + 1;
                }
            }
        }

    }

    // 删除数据
    public function delete($index) {
        if ($index >= $this->elements || $index < 0) {
            throw new Exception("ArrayIndexOutOfBoundsException");
        }

        for ($i = $index; $i < $this->elements; $i++) { 
            if ($i == $this->elements - 1) {
                unset($this->arr[$i]);
                break;
            }

            $this->arr[$i] = $this->arr[$i + 1];
        }
        $this->elements--;
    }

    // 更新数据
    public function update($index, $newValue) {
        if ($index >= $this->elements || $index < 0) {
            throw new Exception("ArrayIndexOutOfBoundsException");
        }

        $this->arr[$index] = $newValue;
    }

    // 查找数据，根据索引来查
    public function get($index) {
        if ($index >= $this->elements || $index < 0) {
            throw new Exception("ArrayIndexOutOfBoundsException");
        }

        return $this->arr[$index];
    }

}


$arr = new MyOrderArray();
$arr->insert(20);
$arr->insert(10);
$arr->insert(5);
$arr->insert(100);
// $arr->insert(90);
echo $arr->display();
echo '<br />';
echo $arr->binarySearch(10);





