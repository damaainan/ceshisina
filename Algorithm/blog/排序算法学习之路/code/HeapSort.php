<?php
/**
 * ���ߣ�����
 * ���˲��ͣ����䲩��
 * ����url��www.onmpw.com
 * ************
 * ������
 * ************
 */
/**
 * ��������
 */
function swap(&$arr,$a,$b){
    $t = $arr[$a];
    $arr[$a] = $arr[$b];
    $arr[$b] = $t;
}
/**
 * ����һ���ڵ���������ӽڵ�����󶥶ѵ�����
 */
function MakeHeapChild(&$arr,$pos,$end){
    $p = false;
    if($pos*2+1 <= $end){
        //�����ӽڵ���Ƚϣ��ҳ���Сֵ
        if($arr[$pos*2-1]>=$arr[$pos*2]){
            $p = $pos*2;
        }else{
            $p = $pos*2+1;
        }
    }elseif($pos*2<=$end){
        $p = $pos*2;
    }
    if(!$p) return $p;
    //�Ƚϵ�ǰ�ڵ������С���ӽڵ�
    if($arr[$pos-1]<=$arr[$p-1]){
        swap($arr,$pos-1,$p-1);
        return $p;
    }
    return false;

}
function HeapSort(&$arr){
    $start = floor(count($arr)/2); //�ҵ����һ����Ҷ�ӽڵ�
    $end = count($arr);
    /*
     * ����󶥶�
    */
    while($start>0){
        $p = $start;
        while($p){
            $p = MakeHeapChild($arr,$p,$end);
        }
        $start-- ;
    }
    //����󶥶ѽ���
    /*
     * ���������ڵ�����һ��Ҷ�ӽڵ� ���ε����󶥶�
     */
    while($end>1){
        swap($arr,0,$end-1);
        $end--;
        $p = 1;
        while($p){
            $p = MakeHeapChild($arr,$p,$end);
        }
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
HeapSort($arr);
print_r($arr);