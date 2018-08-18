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