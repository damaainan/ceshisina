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

$item =array('2','1','4','3','8','6','5','-1','10','3','7','6','6');
var_dump(implode(',',$item));
$heap = new Heap($item,'min');
var_dump(implode(',',$heap->heapSort()));    