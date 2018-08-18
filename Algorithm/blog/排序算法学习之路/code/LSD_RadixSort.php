<?php
/**
 * ���ߣ�����
 * ���˲��ͣ����䲩��
 * ����url��www.onmpw.com
 * ************
 * LSD��������
 * ************
 */
/**
 * �ҵ����������λ��
 */
function FindMaxBit($arr){
    /*
     * ���Ȳ���������������
     */
    $p = $arr[0];
    for($i=1;$i<count($arr);$i++){
        if($arr[$i]>=$p){
            $p = $arr[$i];
        }
    }
    //�õ��������Ժ󣬼�����������ж���λ
    $d = 1;
    while(floor($p/10)>0){
        $d++;
        $p = floor($p/10);
    }
    return $d;
}
/**
 * ֱ�ӽ����ݴ���Ͱ�У������������Ķ���
 */
function LSD1_RadixSort(&$arr){
    //�õ����������λ��
    $d = FindMaxBit($arr);
    $bucket = array();
    //��ʼ������
    for($i=0;$i<10;$i++){
        $bucket[$i]=array('num'=>0,'val'=>array());
    }
    /*
     * ��ʼ���з���
     */
    $radix = 1;
    for($i=1;$i<=$d;$i++){
        for($j=0;$j<count($arr);$j++){
            $k = floor($arr[$j]/$radix)%10;
            $bucket[$k]['num']++;
            array_push($bucket[$k]['val'],$arr[$j]);
        }
        $arr = array();
        foreach ($bucket as $key => $val) {
            for ($j = 0; $j < $val['num']; $j ++) {
                $arr[] = $val['val'][$j];
            }
        }
        for($l=0;$l<10;$l++){
            $bucket[$l]=array('num'=>0,'val'=>array());
        }
        $radix *= 10;
    }
}
/**
 * ����һ����ʱ���У�Ͱ��ֻ��ԭ�����е�Ԫ������ʱ�����е�λ��
 */
function LSD_RadixSort(&$arr){
    //�õ����������λ��
    $d = FindMaxBit($arr);
    $bucket = array();
    $temp = array();
    //��ʼ������
    for($i=0;$i<10;$i++){
        $bucket[$i] = 0;
    }
    /*
     * ��ʼ���з���
     */
    $radix = 1;
    for($i=1;$i<=$d;$i++){
        for($j=0;$j<count($arr);$j++){
            $k = floor($arr[$j]/$radix)%10;
            $bucket[$k]++;
        }
        //��Ͱ�е���ԭ��������ʱ�����е�λ��
        for($j=1;$j<10;$j++){
            $bucket[$j] += $bucket[$j-1];
        }
        for($j=count($arr)-1;$j>=0;$j--){
            $k = floor($arr[$j]/$radix)%10;
            $temp[--$bucket[$k]] = $arr[$j];
        }
        /*
         * ����ʱ���и�ֵ��ԭ����
         */
        for($j=0;$j<count($temp);$j++){
            $arr[$j] = $temp[$j];
        }
        for($j=0;$j<10;$j++){
            $bucket[$j] = 0;
        }
        $radix *= 10;
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
LSD_RadixSort($arr);
print_r($arr);