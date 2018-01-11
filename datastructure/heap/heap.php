<?php
require './SortTestHelper.php';
/**
 * 最大堆
 */
class MaxHeap {

    private $data;
    private $count;

    public function __construct() {
        $this->data = array();
        $this->count = 0;
    }

    // public function __construct($arr){
    // }

    public function insert($item) {

        //从1开始
        $this->data[$this->count + 1] = $item;
        $this->_shiftUp($this->count + 1);
        $this->count++;
    }

    public function extractMax() {
        $ret = $this->data[1];
        swap($this->data, 1, $this->count);
        $this->count--;
        $this->_shiftDown(1);
        return $ret;
    }

    public function getMax() {
        return $this->data[1];
    }

    public function isEmpty() {
        return $this->count == 0;
    }

    public function getData() {
        return $this->data;
    }

    /**
     * [_shiftUp 新加入到堆中的元素直接放在数组后面，再与父元素比较后交换位置，直到根节点]
     * @param  [type] $k [description]
     * @return [type]    [description]
     */
    private function _shiftUp($k) {
        //如果叶子节点的值比父元素大交换位置，并更新k的值
        while ($k > 1 && $this->data[(int) ($k / 2)] < $this->data[$k]) {
            // swap( $this->data[(int)($k/2)], $this->data[$k] );
            swap($this->data, (int) ($k / 2), $k);
            $k = (int) ($k / 2);
        }
    }

    /**
     * [_shiftDown 元素出堆的时候，需要维护此时的堆依然是一个大根堆， 此时将数组元素的最后一个值与第一个值交换，后从上往下维护堆的性质]
     * @param  [type] $k [description]
     * @return [type]    [description]
     */
    private function _shiftDown($k) {
        //2k代表该节点的左子节点
        while (2 * $k <= $this->count) {
            $j = 2 * $k;
            //判断右节点是否存在，并且右节点大于左节点
            if ($j + 1 <= $this->count && $this->data[$j + 1] > $this->data[$j]) {
                $j++;
            }

            if ($this->data[$k] >= $this->data[$j]) {
                break;
            }

            // swap( $this->data[$k] , $this->data[$j] );
            swap($this->data, $k, $j);
            $k = $j;
        }
    }
}

$head_obj = new MaxHeap();
$n = 10;
for ($i = 0; $i < $n; $i++) {
    $head_obj->insert(rand(0, 1000));
}

print_r($head_obj->getData());

while (!$head_obj->isEmpty()) {
    echo $head_obj->extractMax() . "\n";
}