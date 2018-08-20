# 排序-堆排序

作者  [林湾村龙猫][0] 已关注 2016.01.21 01:20  字数 799 

## **概述**

堆常用来实现优先队列，在这种队列中，待删除的元素为优先级最高（最低）的那个。在任何时候，任意优先元素都是可以插入到队列中去的，是计算机科学中一类特殊的数据结构的统称  
堆的定义：最大（最小）堆是一棵每一个节点的键值都不小于（大于）其孩子（如果存在）的键值的树。大顶堆是一棵完全二叉树，同时也是一棵最大树。小顶堆是一棵完全完全二叉树，同时也是一棵最小树。注意：

* 堆中任一子树亦是堆。
* 以上讨论的堆实际上是二叉堆(Binary Heap)，类似地可定义k叉堆。
* 左右子节点不大于（不小于）父节点。

**_堆排序是根据堆中的根节点是最大或最小值来排序的。_**

## **理论**

[http://blog.csdn.net/morewindows/article/details/6709644][1]  
[http://blog.csdn.net/wypblog/article/details/8076324][2]  
[http://blog.csdn.net/v_july_v/article/details/6198644][3]

## **动画**

![][4]



堆排序动画1

![][5]



堆排序动画2

## **代码（PHP）**

#### **1.堆定义极其排序方法**

```php
<?php
    class Heap{
        protected $tree = array();//数组存储的完全二叉树
        protected $type= 'max';//max-最大堆，min-最小堆
    
        public function __construct($arr=null,$type='max'){
            $this->initHeap($arr,$type);
        }
    
        //初始化数据堆
        public function initHeap($arr = null,$type='max'){
            if(empty($arr)){
                return;
            }
            $this->tree = $arr;
            $this->type = strtolower($type) == 'max'?'max':'min';
            $length = count($arr);
            for($i= (int)($length/2-1); $i>=0;$i--){
                $this->heapFix($i);
            }
        }
    
        //调整$i节点极其之后节点子树
        public function heapFix($i=0,&$tree=null){
            //完全二叉树，i节点的子节点为 2*i+1, 2*i+2（i从0开始）
            if(empty($tree)){
                $tree = &$this->tree;
            }
            $length = count($tree);
            $i_left = 2*$i+1;//左节点
            $i_right = $i_left+1;//右节点
            while($i_left <= $length-1){
                $temp = $i_left;
                if($this->type == 'max'){//大根堆
                    if(!empty($tree[$i_right]) && $tree[$i_left]<$tree[$i_right]){
                        $temp = $i_right;
                    }
                    if($tree[$i] >= $tree[$temp]){
                        break;
                    }
                }else{//小根堆
                    if(!empty($tree[$i_right]) && $tree[$i_left] > $tree[$i_right]){
                        $temp = $i_right;
                    }
                    if($tree[$i] <= $tree[$temp]){
                        break;
                    }
                }
                list($tree[$i],$tree[$temp])= array($tree[$temp],$tree[$i]);
                $i = $temp;
                $i_left = 2*$i+1;
                $i_right = $i_left+1;
            }
        }
    
        //堆排序
        public function heapSort(){
            $arr = array();
            $tree = $this->tree;
            while(!empty($tree)){
                //将根节点和最后一个节点交换，保存最后节点，删除最后节点，调整堆结构
                $length = count($tree);
                list($tree[0],$tree[$length-1]) = array($tree[$length-1],$tree[0]);
                $arr[] = $tree[$length-1];
                unset($tree[$length-1]);
                $this->heapFix(0,$tree);
            }
            return $arr;
        }
    }
```

> 重建堆的基本思想-实际的操作：将最后一个数据的值赋给根结点，然后再从根结点开始进行一次从上向下的调整。调整时先在左右儿子结点中找最小（大）的，如果父结点比这个最小（大）的子结点还小说明不需要调整了，反之将父结点和它交换后再考虑后面的结点。相当于从根结点将一个数据的“下沉”过程。

#### **2.调用**

    $item =array('2','1','4','3','8','6','5','-1','10','3','7','6','6');
    var_dump(implode(',',$item));
    $heap = new Heap($item,'min');
    var_dump(implode(',',$heap->heapSort()));

## **结果**

![][6]



> 堆

![][7]



> 堆排序

[0]: /u/5a327aab786a
[1]: http://blog.csdn.net/morewindows/article/details/6709644
[2]: http://blog.csdn.net/wypblog/article/details/8076324
[3]: http://blog.csdn.net/v_july_v/article/details/6198644
[4]: ./img/301894-673228cea6147ea5.gif
[5]: ./img/301894-cd579af1ba22f2d2.gif
[6]: ./img/301894-2d49a8ce3a664e3b.png
[7]: ./img/301894-bdf7b25249a77ac8.png