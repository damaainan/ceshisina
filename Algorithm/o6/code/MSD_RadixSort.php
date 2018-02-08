<?php
/**
 * ���ߣ�����
 * ���˲��ͣ����䲩��
 * ����url��www.onmpw.com
 * ************
 * MSD��������
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
 * MSD����������
 * @param array $arr  �����������
 * @param array $r    ��ʾ��ǰ�ǰ��յڼ�λ���з���
 */
function MSD_RadixSort(&$arr,$r){
    $radix = pow(10,$r-1);
    $bucket = array();
    //��ʼ������
    for($i=0;$i<10;$i++){
        $bucket[$i]=array('num'=>0,'val'=>array());
    }
    for($j=0;$j<count($arr);$j++){
        $k = floor($arr[$j]%pow(10,$r)/$radix);
        $bucket[$k]['num']++;
        array_push($bucket[$k]['val'],$arr[$j]);
    }
    for($j=0;$j<10;$j++){
        if($bucket[$j]['num']>1){
            MSD_RadixSort($bucket[$j]['val'],$r-1);
        }
    }
    /*
     * ��Ͱ�е������ռ���������ʱ�����������
     */
    $arr = array();
    for($j=0;$j<10;$j++){
        for($i=0;$i<count($bucket[$j]['val']);$i++){
            $arr[] = $bucket[$j]['val'][$i];
        }
    }
}
$arr = array(
    15,77,23,43,90,87,68,32,11,22,33,99,88,66,44,113,
    224,765,980,159,456,7,998,451,96,0,673,82,91,100
);
MSD_RadixSort($arr,FindMaxBit($arr));
print_r($arr);