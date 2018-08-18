<?php
/**
 * ���ߣ�����
 * ���˲��ͣ����䲩��
 * ����url��www.onmpw.com
 * ************
 * ��������
 * ���а�������ʵ�ַ�ʽ һ���ǵݹ鷽ʽ  һ����ջ�ķǵݹ鷽ʽ
 * ************
 */
function FindPiv(&$arr,$s,$e){
    $p = $s; //��׼��ʼλ��
    $v = $arr[$p];  //������ĵ�һ��ֵ��Ϊ��׼ֵ
    while($s<$e){
        while($arr[$e]>$v&&$e>$p){
            $e--;
        }
        $arr[$p] = $arr[$e];
        $p = $e;
        while($arr[$s]<$v&&$s<$p){
            $s++;
        }
        $arr[$p] = $arr[$s];
        $p = $s;
    }
    $arr[$p] = $v;
    return $p;
}
/**
 * �������򡪡��ݹ鷽ʽ
 */
function FastSortRecurse(&$arr,$s,$e){
    if($s>=$e) return ;
    $nextP = FindPiv($arr,$s,$e);  //�ҵ���һ����׼����λ��
    FastSortRecurse($arr,$s,$nextP-1);
    FastSortRecurse($arr,$nextP+1,$e);
}
/**
 * �������򡪡��ǵݹ鷽ʽ
 */
function FastSort(&$arr){
    $stack = array();
    array_push($stack,array(0,count($arr)-1));
    while(count($stack)>0){
        $temp = array_pop($stack);
        $p = FindPiv($arr, $temp[0], $temp[1]);
        if($p+1<$temp[1]) array_push($stack,array($p+1,$temp[1]));
        if($temp[0]<$p-1) array_push($stack,array($temp[0],$p-1));
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
FastSort($arr,0,count($arr)-1);
print_r($arr);