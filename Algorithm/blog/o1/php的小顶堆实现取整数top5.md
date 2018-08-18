# php的小顶堆实现取整数top5

[michaelgbw][0] 关注 2016.06.02 15:55  字数 1129  阅读 200 评论 0 喜欢 3 赞赏 1

#### 堆的简单介绍

> 堆实际上是一棵完全二叉树，其任何一非叶节点满足性质  `Key[i]<=key[2i+1]&&Key[i]<=key[2i+2]`  或者  `Key[i]>=Key[2i+1]&&key>=key[2i+2]`  即任何一非叶节点的关键字不大于或者不小于其左右孩子节点的关键字。堆分为大顶堆和小顶堆，满足  `Key[i]>=Key[2i+1]&&key>=key[2i+2]`  称为大顶堆，满足` Key[i]<=key[2i+1]&&Key[i]<=key[2i+2]` 称为小顶堆。由上述性质可知大顶堆的堆顶的关键字肯定是所有关键字中最大的，小顶堆的堆顶的关键字是所有关键字中最小的。

#### 排序的算法简单介绍

> 所谓堆排序的过程，就是把一些无序的对象，逐步建立起一个堆的过程。

#### 举个栗子

> 下面举例说明：  
> 给定一个整形数组a[]={16,7,3,20,17,8}，对其进行堆排序。  
> 首先根据该数组元素构建一个完全二叉树，得到

![][1]

  
> 然后需要构造初始堆，则从最后一个非叶节点开始调整，调整过程如下：

![][2]

![][3]

![][4]

  
> 20和16交换后导致16不满足堆的性质，因此需重新调整

![][5]

  
> 这样就得到了初始堆。  
> 即每次调整都是从父节点、左孩子节点、右孩子节点三者中选择最大者跟父节点进行交换(交换之后可能造成被交换的孩子节点不满足堆的性质，因此每次交换之后要重新对被交换的孩子节点进行调整)。有了初始堆之后就可以进行排序了。

![][6]

  
> 此时3位于堆顶不满堆的性质，则需调整继续调整

![][7]

![][8]

![][9]

![][10]

![][11]

![][12]

![][13]

![][14]

![][15]

![][16]

  
> 这样整个区间便已经有序了。  
> 从上述过程可知，堆排序其实也是一种选择排序，是一种树形选择排序。只不过直接选择排序中，为了从R[1...n]中选择最大记录，需比较n-1次，然后从R[1...n-2]中选择最大记录需比较n-2次。事实上这n-2次比较中有很多已经在前面的n-1次比较中已经做过，而树形选择排序恰好利用树形的特点保存了部分前面的比较结果，因此可以减少比较次数。对于n个关键字序列，最坏情况下每个节点需比较log2(n)次，因此其最坏情况下时间复杂度为nlogn。堆排序为不稳定排序，不适合记录较少的排序。

#### > 我们的需求

> 有人就要问了，我们为什么要用对排序呢，直接sort后取前top5就好了呀， - -是的，那是对于一般的小文件，可要是文件或数组的量级特别大呢？指定就不好使了，毕竟不能直接对其数组进行操作了。这时我们的对排序就派上用场了；

#### 话不多说上代码吧

```php
    <?php
    
    /**
     *@author:gongbangwei(18829212319@163.com)
     *@version:1.0
     *@date:2016-06-01
     *基于小顶堆的的取top的排序操作
     */
    class heaptree{
        public $arr=array();
        public $top;
        public function __construct($top){
            $this->top=$top;
        }
        public function add($value){
            $len=count($this->arr);
            foreach ($this->arr as $key => $val) {
                if($val==$value){
                    return ;
                }
            }
            if($len < $this->top){
                array_push($this->arr, $value);
                $this->adjust(0);
            }
            else{
                if($this->arr[0] < $value){
    
                    if(count($this->arr) >= $this->top){
                        $this->arr[0]=$value;
                    }
                    else{
                        array_unshift($this->arr, $value);
                    }
                    $this->adjust(0);
                }
            }
        }
        public function adjust($num){
            $lchild=$num * 2 +1;
            $rchild=$num * 2 +2;
            if(isset($this->arr[$lchild])){
                $tempmin=(($this->arr[$num] < $this->arr[$lchild]) ? $num : $lchild);
                if(isset($this->arr[$rchild])){
                    $tempmin= ($this->arr[$tempmin]<$this->arr[$rchild]) ? $tempmin : $rchild;
                }
            self::swap($num,$tempmin);
            //递归对左右子树进行操作
            $this->adjust($lchild);
            $this->adjust($rchild);
            }
            else{
                return $this->arr;
            }
        }
        public function getarr(){
            return $this->arr;
        }
        private function swap($one,$another){
            $tmp=$this->arr[$one];
            $this->arr[$one]=$this->arr[$another];
            $this->arr[$another]=$tmp;
            return $this->arr;
        }
    }
    ////////test////////
    //取top5
    $heap= new heaptree(5);
    //给100个随机数，当然我们的需求可能比这个大的多
    for($i=0;$i<100;$i++){
        $heap->add(rand(0,200));
    }
    print_r($heap->getarr());
```

> 其实在理解一个算法后再对其进行code就容易的多希望大家多多支持，今后会坚持发表一些心得体会的，那就这样

[0]: /u/d75bddfb0fac
[1]: ./2199772-d8a70baa60259d1c.jpg
[2]: ./2199772-9ee7d38751d156d6.jpg
[3]: ./2199772-a892fe9c381b18c2.jpg
[4]: ./2199772-f55fdcda68b9d267.jpg
[5]: ./2199772-569e2975bb41016a.jpg
[6]: ./2199772-34a0cf7734b7ccbc.jpg
[7]: ./2199772-0e3f4b769aabacc3.jpg
[8]: ./2199772-8b283c05feffeeee.jpg
[9]: ./2199772-dd6fe547e0cdcf54.jpg
[10]: ./2199772-7ae6732eb448343d.jpg
[11]: ./2199772-5a1e079565593b58.jpg
[12]: ./2199772-79b92514f0006cea.jpg
[13]: ./2199772-98daef87247d77f5.jpg
[14]: ./2199772-ac094c6fc9e24b53.jpg
[15]: ./2199772-c62b425135fdc8f2.jpg
[16]: ./2199772-9dd9e62566efe543.jpg