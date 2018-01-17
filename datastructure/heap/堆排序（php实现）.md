# 堆排序（php实现）

 时间 2018-01-17 10:22:37  

原文[https://juejin.im/post/5a5eb2eff265da3e347b45d7][1]


堆排序（Heapsort）是指利用堆这种数据结构所设计的一种排序算法。堆积是一个近似完全二叉树的结构，并同时满足堆积的性质：即子结点的键值或索引总是小于（或者大于）它的父节点。

### 简单思路

有了前一篇博客实现的堆，自然而然的想到，将给出的数组先形成一个大根堆，然后逐一的取出，后逆序的返回即可。

### 代码实现

```php
    <?php
    // require('../Library/SortTestHelper.php');
    require('../SortingAdvance/QuickSort.php');
    /**
     * 最大堆
     */
    class MaxHeap{
    
    
        private $data;
        private $count;
    
        public function __construct(){
            $this->data = array();
            $this->count = 0;
        }
    
    
        // public function __construct($arr){
        // }
    
        public function insert($item){
    
            //从1开始
            $this->data[$this->count + 1] = $item;
            $this->_shiftUp($this->count+1);
            $this->count++;
        }
    
        public function  extractMax(){
            $ret = $this->data[1];
            swap( $this->data, 1 , $this->count);
            $this->count--;
            $this->_shiftDown(1);
            return $ret;
        }
    
        public function getMax(){
            return $this->data[1];
        }
    
        public function isEmpty(){
            return $this->count == 0;
        }
    
        public function getData(){
            return $this->data;
        }
    
        /**
         * [_shiftUp 新加入到堆中的元素直接放在数组后面，再与父元素比较后交换位置，直到根节点]
         * @param  [type] $k [description]
         * @return [type]    [description]
         */
        private function _shiftUp($k){
            //如果叶子节点的值比父元素大交换位置，并更新k的值
            while( $k > 1 && $this->data[(int)($k/2)] < $this->data[$k] ){
                // swap( $this->data[(int)($k/2)], $this->data[$k] );
                swap( $this->data, (int)($k/2) , $k);
                $k = (int)($k/2);
            }
        }
    
        /**
         * [_shiftDown 元素出堆的时候，需要维护此时的堆依然是一个大根堆， 此时将数组元素的最后一个值与第一个值交换，后从上往下维护堆的性质]
         * @param  [type] $k [description]
         * @return [type]    [description]
         */
        private function _shiftDown($k){
            //2k代表该节点的左子节点
            while( 2*$k <= $this->count ){
                $j = 2*$k;
                //判断右节点是否存在，并且右节点大于左节点
                if( $j+1 <= $this->count && $this->data[$j+1] > $this->data[$j] ) $j ++;
                if( $this->data[$k] >= $this->data[$j] ) break;
                // swap( $this->data[$k] , $this->data[$j] );
                swap( $this->data, $k , $j );
                $k = $j;
            }
        }
    }
    
    //简单的堆排序
    function headSort1(&$arr, $n){
        $head_obj = new MaxHeap();
        for ($i=0; $i < $n; $i++) {
            $head_obj->insert($arr[$i]);
        }
        //逆序输出
        for ($i=$n-1; $i >= 0; $i--) {
            $arr[$i] = $head_obj -> extractMax();
        }
    }
    
    $n = 10000;
    $arr = generateRandomArray($n, 0, $n);
    $copy_arr1 = $arr;
    $copy_arr2 = $arr;
    $copy_arr3 = $arr;
    $copy_arr4 = $arr;
    // $arr = generateNearlyOrderedArray($n, 100);
    testSort("headSort1", "headSort1", $arr, $n);
    testSort("mergeSort", "mergeSort", $copy_arr1, $n);
    testSort("quickSort", "quickSort", $copy_arr2, $n);
    testSort("quickSort2", "quickSort2", $copy_arr3, $n);
    testSort("quickSort3", "quickSort3", $copy_arr4, $n);
    ?>
```
#### 结果

    headSort1运行的时间为：0.70801091194153s
    mergeSort运行的时间为：0.94017505645752s
    quickSort运行的时间为：0.30204510688782s
    quickSort2运行的时间为：0.19032001495361s
    quickSort3运行的时间为：0.36022400856018s

#### 结果分析

    for ($i=0; $i < $n; $i++) {
            $head_obj->insert($arr[$i]);
        }
    //逆序输出
    for ($i=$n-1; $i >= 0; $i--) {
        $arr[$i] = $head_obj -> extractMax();
    }

这段代码进行了两次O(N _logN)时间复杂度的运算，并且是将已有的数组去生成一个堆，然后借助这个堆重新赋值给数组，其实可以直接用数组去形成一个堆，然后我们就可以进行一次O(N_ logN)复杂度的运算。我们只需要重写一个构造函数，通过一个数组去构造它！ 

## 堆排序的优化

### 代码实现

```php
    <?php
    require('../SortingAdvance/QuickSort.php');
    /**
     * 根据已知数组最大堆
     */
    class HeapSort{
    
    
        private $data;
        private $count;
    
        public function __construct($arr, $n){
            $this->data = array();
            $this->count = $n;
            for ($i=0; $i < $n; $i++) {
                //从1开始
                $this->data[$i+1] = $arr[$i];
            }
            //叶子节点已经是一颗大根堆了，从最后一个非叶子节点进行_shiftDown,知道根节点
            for ($i= (int)($n/2); $i >= 1 ; $i--) {
                $this->_shiftDown($i);
            }
        }
    
    
        // public function __construct($arr){
        // }
    
        public function insert($item){
    
            //从1开始
            $this->data[$this->count + 1] = $item;
            $this->_shiftUp($this->count+1);
            $this->count++;
        }
    
        public function  extractMax(){
            $ret = $this->data[1];
            swap( $this->data, 1 , $this->count);
            $this->count--;
            $this->_shiftDown(1);
            return $ret;
        }
    
        public function getMax(){
            return $this->data[1];
        }
    
        public function isEmpty(){
            return $this->count == 0;
        }
    
        public function getData(){
            return $this->data;
        }
    
        /**
         * [_shiftUp 新加入到堆中的元素直接放在数组后面，再与父元素比较后交换位置，直到根节点]
         * @param  [type] $k [description]
         * @return [type]    [description]
         */
        private function _shiftUp($k){
            //如果叶子节点的值比父元素大交换位置，并更新k的值
            while( $k > 1 && $this->data[(int)($k/2)] < $this->data[$k] ){
                // swap( $this->data[(int)($k/2)], $this->data[$k] );
                swap( $this->data, (int)($k/2) , $k);
                $k = (int)($k/2);
            }
        }
    
        /**
         * [_shiftDown 元素出堆的时候，需要维护此时的堆依然是一个大根堆， 此时将数组元素的最后一个值与第一个值交换，后从上往下维护堆的性质]
         * @param  [type] $k [description]
         * @return [type]    [description]
         */
        private function _shiftDown($k){
            //2k代表该节点的左子节点
            while( 2*$k <= $this->count ){
                $j = 2*$k;
                //判断右节点是否存在，并且右节点大于左节点
                if( $j+1 <= $this->count && $this->data[$j+1] > $this->data[$j] ) $j ++;
                if( $this->data[$k] >= $this->data[$j] ) break;
                // swap( $this->data[$k] , $this->data[$j] );
                swap( $this->data, $k , $j );
                $k = $j;
            }
        }
    }
    
    
    function headSort2(&$arr, $n){
        $head_obj = new HeapSort($arr, $n);
        for ($i=$n-1; $i >= 0; $i--) {
            $arr[$i] = $head_obj -> extractMax();
        }
    }
    
    $n = 10000;
    $arr = generateRandomArray($n, 0, $n);
    $copy_arr1 = $arr;
    $copy_arr2 = $arr;
    $copy_arr3 = $arr;
    $copy_arr4 = $arr;
    
    testSort("headSort2", "headSort2", $arr, $n);
    testSort("mergeSort", "mergeSort", $copy_arr1, $n);
    testSort("quickSort", "quickSort", $copy_arr2, $n);
    testSort("quickSort2", "quickSort2", $copy_arr3, $n);
    testSort("quickSort3", "quickSort3", $copy_arr4, $n);
    
    ?>
```
#### 结果

    quickSort2运行的时间为：0.12423086166382s
    quickSort3运行的时间为：0.051468849182129s
    headSort2运行的时间为：0.033907890319824s
    mergeSort运行的时间为：0.020761013031006s
    quickSort运行的时间为：0.016165018081665s
    quickSort2运行的时间为：0.01316499710083s
    quickSort3运行的时间为：0.026669025421143s

[1]: https://juejin.im/post/5a5eb2eff265da3e347b45d7
