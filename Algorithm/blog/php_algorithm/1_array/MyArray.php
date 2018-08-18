<?php

class MyArray {
    private $arr = [];
    private $elements = 0; // 有效数据的长度

    public function __construct() {

    }

    // 添加数据
    public function insert($value) {
        $this->arr[$this->elements] = $value;
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

    // 查找数据
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


$arr = new MyArray();
$arr->insert(20);
$arr->insert(10);
$arr->insert(5);
$arr->insert(100);
$arr->insert(90);
$arr->display();
echo '<br />';
echo $arr->search(90);
//echo '<br />';
//echo $arr->get(3);
//echo '<br />';
//$arr->delete(4);
//$arr->display();
echo '<br />';
$arr->update(4, 199);
$arr->display();





