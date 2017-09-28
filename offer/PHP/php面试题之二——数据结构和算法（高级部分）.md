# [php面试题之二——数据结构和算法（高级部分）][0]

### 二、数据结构和算法

###### 1.使对象可以像数组一样进行foreach循环，要求属性必须是私有。(Iterator模式的PHP5实现，写一类实现Iterator接口)（腾讯）

```php
    <?php
        class Test implements Iterator{
        private $item = array('id'=>1,'name'=>'php');
    
        public function rewind(){
            reset($this->item);
        }
    
        public function current(){
            return current($this->item);
        }
    
        public function key(){
            return key($this->item);
        }
    
        public function next(){
            return next($this->item);
        }
    
        public function valid(){
            return($this->current()!==false);
        }
    }
        //测试
        $t=new Test;
        foreach($t as $k=>$v){
            echo$k,'--->',$v,'<br/>';
        }
    ?>
```

###### 2.用PHP实现一个双向队列（腾讯）

```php
    <?php
        class Deque{
        private $queue=array();
        public function addFirst($item){
            return array_unshift($this->queue,$item);
        }
    
        public function addLast($item){
            return array_push($this->queue,$item);
        }
        public function removeFirst(){
            return array_shift($this->queue);
        }
    
        public function removeLast(){
            return array_pop($this->queue);
        }
    }
    ?>
```

###### 3.请使用冒泡排序法对以下一组数据进行排序10 2 36 14 10 25 23 85 99 45。

```php
    <?php
        // 冒泡排序
        function bubble_sort(&$arr){
            for ($i=0,$len=count($arr); $i < $len; $i++) {
                for ($j=1; $j < $len-$i; $j++) {
                    if ($arr[$j-1] > $arr[$j]) {
                        $temp = $arr[$j-1];
                        $arr[$j-1] = $arr[$j];
                        $arr[$j] = $temp;
                    }
                }
            }
        }
    
        // 测试
        $arr = array(10,2,36,14,10,25,23,85,99,45);
        bubble_sort($arr);
        print_r($arr);
    ?>
```

###### 4.写出一种排序算法（要写出代码），并说出优化它的方法。（新浪）

```php
    <?php
        //快速排序
        function partition(&$arr,$low,$high){
            $pivotkey = $arr[$low];
            while($low<$high){
                while($low < $high && $arr[$high] >= $pivotkey){
                    $high--;
                }
                $temp = $arr[$low];
                $arr[$low] = $arr[$high];
                $arr[$high] = $temp;
                while($low < $high && $arr[$low] <= $pivotkey){
                    $low++;
                }
                $temp=$arr[$low];
                $arr[$low]=$arr[$high];
                $arr[$high]=$temp;
            }
            return$low;
        }
    
    
    function quick_sort(&$arr,$low,$high){
        if($low < $high){
            $pivot = partition($arr,$low,$high);
            quick_sort($arr,$low,$pivot-1);
            quick_sort($arr,$pivot+1,$high);
        }
    }
    ?>
```

该算法是通过分治递归来实现的，其效率很大程度上取决于参考元素的选择，可以选择数组的中间元素，也可以随机得到三个元素，然后选择中间的那个元素（三数中值法）。  
另外还有一点，就是当我们在分割时，如果分割出来的子序列的长度很小的话（小于5到20），通常递归的排序的效率就没有诸如插入排序或希尔排序那么快了。因此可以会去判断数组的长度，如果小于10的话，直接用插入排序，而不再递归调用这个快速排序。

###### 5.一群猴子排成一圈，按1，2，...，n依次编号。然后从第1只开始数，数到第m只,把它踢出圈，从它后面再开始数，再数到第m只，在把它踢出去...，如此不停的进行下去，直到最后只剩下一只猴子为止，那只猴子就叫做大王。要求编程模拟此过程，输入m、n,输出最后那个大王的编号。（新浪）（小米）

这是著名的约瑟夫环问题

```php
    <?php
        // 方案一，使用php来模拟这个过程
        function king($n,$m){
            $mokey = range(1, $n);
            $i = 0;
    
            while (count($mokey) >1) {
                $i += 1;
                $head = array_shift($mokey);//一个个出列最前面的猴子
                if ($i % $m !=0) {
                    #如果不是m的倍数，则把猴子返回尾部，否则就抛掉，也就是出列
                    array_push($mokey,$head);
                }
    
                // 剩下的最后一个就是大王了
                return $mokey[0];
            }
        }
        // 测试
        echo king(10,7);
    
        // 方案二，使用数学方法解决
        function josephus($n,$m){
            $r = 0;
            for ($i=2; $i <= $m ; $i++) {
                $r = ($r + $m) % $i;
            }
    
            return $r+1;
        }
        // 测试
        print_r(josephus(10,7));
    ?>
```

###### 6.写一个二维数组排序算法函数，能够具有通用性，可以调用php内置函数。

```php
    <?php
    //二维数组排序，$arr是数据，$keys是排序的健值，$order是排序规则，1是降序，0是升序
    function array_sort($arr,$keys,$order=0){
        if(!is_array($arr)){
            return false;
        }
        $keysvalue=array();
        foreach($arr as $key => $val){
            $keysvalue[$key] = $val[$keys];
        }
        if($order == 0){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach($keysvalue as $key => $vals){
            $keysort[$key] = $key;
        }
        $new_array=array();
        foreach($keysort as $key=> $val){
            $new_array[$key]=$arr[$val];
        }
        return$new_array;
    }
        //测试
        $person=array(
            array('id'=>2,'name'=>'zhangsan','age'=>23),
            array('id'=>5,'name'=>'lisi','age'=>28),
            array('id'=>3,'name'=>'apple','age'=>17)
        );
        $result = array_sort($person,'name',1);
        print_r($result);
    ?>
```

###### 7.用二分法查找一个长度为10的排好序的线性表，查找不成功时最多需要比较次数是（小米）

4

###### 8.从0,1,2,3,4,5,6,7,8,9，这十个数字中任意选出三个不同的数字，“三个数字中不含0和5”的概率是（小米）

7/15

###### 9.一个三角形三个顶点有3只老鼠，一声枪响，3只老鼠开始沿三角形的边匀速运动，请问他们相遇的概率是（小米）

75%，每只老鼠都有顺时针、逆时钟两种运动方向，3只老鼠共有8种运动情况，只有当3只老鼠都为顺时针或者逆时钟，它们才不会相遇，剩余的6中情况都会相遇，故相遇的概率为6/8=75%。

###### 10.描述顺序查找和二分查找（也叫做折半查找）算法，顺序查找必须考虑效率，对象可以是一个有序数组（小米）

```php
    <?php
        /**
         * 顺序查找
         * @param  array $arr 数组
         * @param   $k   要查找的元素
         * @return   mixed  成功返回数组下标，失败返回-1
         */
        function seq_sch($arr,$k){
            for ($i=0,$n = count($arr); $i < $n; $i++) {
                if ($arr[$i] == $k) {
                    break;
                }
            }
            if($i < $n){
                return $i;
            }else{
                return -1;
            }
        }
    
        /**
         * 二分查找，要求数组已经排好顺序
         * @param  array $array 数组
         * @param  int $low   数组起始元素下标
         * @param  int $high  数组末尾元素下标
         * @param   $k     要查找的元素
         * @return mixed        成功时返回数组下标，失败返回-1
         */
        function bin_sch($array,$low,$high,$k){
            if ($low <= $high) {
                $mid = intval(($low + $high) / 2);
                if ($array[$mid] == $k) {
                    return $mid;
                } elseif ($k < $array[$mid]) {
                    return bin_sch($array,$low,$mid - 1,$k);
                } else{
                    return bin_sch($array,$mid + 1,$high,$k);
                }
            }
            return -1;
        }
    
        // 测试：顺序查找
        $arr1 = array(9,15,34,76,25,5,47,55);
        echo seq_sch($arr1,47);//结果为6
    
        echo "<br />";
    
        // 测试：二分查找
        $arr2 = array(5,9,15,25,34,47,55,76);
        echo bin_sch($arr2,0,7,47);//结果为5
    ?>
```

###### 11.我们希望开发一款扑克游戏，请给出一套洗牌算法，公平的洗牌并将洗好的牌存储在一个整形数组里。（鑫众人云）

```php
    <?php
        $card_num = 54;//牌数
        function wash_card($card_num){
            $cards = $tmp = array();
            for($i = 0;$i < $card_num;$i++){
                $tmp[$i] = $i;
            }
    
            for($i = 0;$i < $card_num;$i++){
                $index = rand(0,$card_num-$i-1);
                $cards[$i] = $tmp[$index];
                unset($tmp[$index]);
                $tmp = array_values($tmp);
            }
            return $cards;
        }
        // 测试：
        print_r(wash_card($card_num));
    ?>
```

###### 12.写出你所知道的排序方法（亿邮）

冒泡排序，快速排序，插入排序，选择排序

学习的热情不因季节的变化而改变

[0]: http://www.cnblogs.com/-shu/p/4600992.html