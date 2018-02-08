<?php
/**
 * ���ߣ�����
 * ���˲��ͣ����䲩��
 * ����url��www.onmpw.com
 * ************
 * �鲢����
 * ���а�������ʵ�ַ�ʽ һ���ǵݹ鷽ʽ  һ����ջ�ķǵݹ鷽ʽ
 * ************
 */
function Merge($arr,$l,$m,$r){
    $t = $arr;
    $start = $l;
    $end = $m+1;
    while($l<=$r){
        if($l>$m||$end>$r) break;
        if($arr[$l]<$arr[$end]){
            $t[$start++] = $arr[$l++];
        }else{
            $t[$start++] = $arr[$end++];
        }
    }
    if($l<=$m){
        $s = $l;
        $e = $m;
    }elseif($r>=$end){
        $s = $end;
        $e = $r;
    }
    while($s<=$e){
        $t[$start++] = $arr[$s++];
    }
    $arr = $t;
    return $arr;
}
/**
 *�鲢���򡪡��ݹ鷽ʽ
 */
function MergeSortRecurse(&$arr,$l,$r){
    if($r <= $l) return ;
    $m = floor(($l+$r)/2);
    MergeSort($arr,$l,$m);
    MergeSort($arr,$m+1,$r);
    $arr = Merge($arr,$l,$m,$r);
}
/**
 * �鲢���򡪡��ǵݹ鷽ʽ
 */
function MergeSort(&$arr){
    $stack = array();
    $stack1 = array();
    $temp = array(0,count($arr)-1,floor((0+count($arr)-1)/2));
    array_push($stack,$temp);
    while(count($stack)>0){
        $temp = array_pop($stack);
        array_push($stack1,$temp);
        if($temp[0]<$temp[2]){
            array_push($stack,array($temp[0],$temp[2],floor(($temp[0]+$temp[2])/2)));
        }
        if($temp[2]+1<$temp[1]){
            array_push($stack,array($temp[2]+1,$temp[1],floor(($temp[2]+1+$temp[1])/2)));
        }
    }
    while(count($stack1)>0){
        $temp = array_pop($stack1);
        $arr = Merge($arr,$temp[0], $temp[2], $temp[1]);
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
MergeSort($arr);
print_r($arr);