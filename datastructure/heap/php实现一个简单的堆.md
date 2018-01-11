# php实现一个简单的堆

 时间 2018-01-11 11:26:56  

原文[https://juejin.im/post/5a56ce6df265da3e4f0a133e][1]


主要介绍优先队列，通过优先队列引出堆， 然后写了一个类（php代码）实现了大根堆

原文请访问我的博客番茄技术小栈 

## 优先队列

### 定义

优先队列是计算机科学中的一类抽象数据类型。优先队列中的每个元素都有各自的优先级，优先级最高的元素最先得到服务；优先级相同的元素按照其在优先队列中的顺序得到服务。优先队列往往用堆来实现。

### 特点

* 普通队列：先进先出，后进后出
* 优先队列：出队顺序和入队顺序无关，和优先级相关

### 现实用途

* 动态任务处理中心（操作系统）

### 为什么使用优先队列

#### 问题

* 在1000000个元素中选出前100名
* 在N个元素中选出前M个元素

#### 解决方法

* 排序，时间复杂度O(NlogN)
* 使用优先队列：时间复杂度（NlogM)

### 优先队列的实现对比

![][4]

## 堆

### 定义

堆的实现通过构造二叉堆（binary heap），实为二叉树的一种；由于其应用的普遍性，当不加限定时，均指该数据结构的这种实现。这种数据结构具有以下性质。

* 任意节点小于（或大于）它的所有后裔，最小元（或最大元）在堆的根上（堆序性）。
* 堆总是一棵完全树。即除了最底层，其他层的节点都被元素填满，且最底层尽可能地从左到右填入。
* 将根节点最大的堆叫做最大堆或大根堆，根节点最小的堆叫做最小堆或小根堆。常见的堆有二叉堆、斐波那契堆等。

![][5]

### 用数组存储二叉堆

![][6]

### 代码实现

```php
    <?php
    require('./SortTestHelper.php');
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
    
    $head_obj = new MaxHeap();
    $n = 10;
    for ($i=0; $i < $n; $i++) {
        $head_obj -> insert(rand(0, 1000));
    }
    
    print_r($head_obj -> getData());
    
    while (!$head_obj -> isEmpty()) {
        echo $head_obj -> extractMax()."\n";
    }
    ?>
```

#### 结果

    生成的堆为：
    Array
    (
        [1] => 916
        [2] => 776
        [3] => 590
        [4] => 615
        [5] => 764
        [6] => 539
        [7] => 95
        [8] => 167
        [9] => 23
        [10] => 374
    )
    打印大根堆为：
    916
    776
    764
    615
    590
    539
    374
    167
    95
    23

[1]: https://juejin.im/post/5a56ce6df265da3e4f0a133e
[4]: https://img1.tuicool.com/jaqQ7zj.png
[5]: https://img1.tuicool.com/IB3qyuF.png
[6]: https://img0.tuicool.com/RBz2Mze.png