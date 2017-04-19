<?php
/**
 * 本文实例讲述了php堆排序实现原理与应用方法。分享给大家供大家参考。具体分析如下：

这里以php作为描述语言较详细讲解堆排序原理,因保证程序可读性,故不做优化,php程序中关于堆的一些概念如下:

假设n为当前数组的key则,n的父节点为 n>>1 或者 n/2(整除);n的左子节点l= n<<1 或 l=n*2,n的右子节点r=(n<<1)+1 或 r=l+1

$arr=array(1,8,7,2,3,4,6,5,9);

数组$arr的原形态结构如下:

1

/ 

8 7

/ / 

2 3 4 6

/ 

5 9

heapsort($arr);print_r($arr);
排序后生成标准的小顶堆结构如下:

1

/ 

2 3

/ / 

4 5 6 7

/ 

8 9

既数组:array(1,2,3,4,5,6,7,8,9):tion]
 * @return   [type] [description]
 */
function heapsort(&$arr)   
  
{   
  
        //求最后一个元素位   
  
        $last=count($arr);   
  
        //堆排序中通常忽略$arr[0]   
  
        array_unshift($arr,0);   
  
        //最后一个非叶子节点   
  
        $i=$last>>1;   
  
   
  
        //整理成大顶堆,最大的数整到堆顶,并将最大数和堆尾交换,并在之后的计算中忽略数组后端的最大数(last),直到堆顶(last=堆顶)   
  
        while(true)   
  
        {   
  
                adjustnode($i,$last,$arr);   
  
                if($i>1)   
  
                {   
  
                        //移动节点指针,遍历所有非叶子节点   
  
                        $i--;   
  
                }   
  
                else   
  
                {   
  
                        //临界点last=1,既所有排序完成   
  
                        if($last==1)break;   
  
                        //当i为1时表示每一次的堆整理都将得到最大数(堆顶,$arr[1]),重复在根节点调整堆   
  
                        swap($arr[$last],$arr[1]);   
  
                        //在数组尾部按大小顺序保留最大数,定义临界点last,以免整理堆时重新打乱数组后面已排序好的元素   
  
                        $last--;   
  
                }   
  
        }   
  
        //弹出第一个数组元素   
  
        array_shift($arr);   
  
}   
  
   
  
//整理当前树节点($n),临界点$last之后为已排序好的元素   
  
function adjustnode($n,$last,&$arr)   
  
{   
  
        $l=$n<<1;        //$n的左孩子位   
  
        if(!isset($arr[$l])||$l>$last) return ;   
  
        $r=$l+1;        //$n的右孩子位   
  
   
  
        //如果右孩子比左孩子大,则让父节点的右孩子比   
  
        if($r<=$last&&$arr[$r]>$arr[$l]) $l=$r;   
  
        //如果其中子节点$l比父节点$n大,则与父节点$n交换   
  
        if($arr[$l]>$arr[$n])                   
  
        {   
  
                //子节点($l)的值与父节点($n)的值交换   
  
                swap($arr[$l],$arr[$n]);   
  
                //交换后父节点($n)的值($arr[$n])可能还小于原子节点($l)的子节点的值,所以还需对原子节点($l)的子节点进行调整,用递归实现   
  
                adjustnode($l,$last,$arr);   
  
        }  
  
}   
  
   
  
//交换两个值   
  
function swap(&$a,&$b)   
  
{   
  
        $a=$a ^ $b;  
  
         $b=$a ^ $b;  
  
         $a=$a ^ $b;   
  
}   