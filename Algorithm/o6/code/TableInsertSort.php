<?php
/**
 * ���ߣ�����
 * ���˲��ͣ����䲩��
 * ����url��www.onmpw.com
 * **********
 * ���������
 * **********
 * @param array $arr ʵ������
 * @param arrat $link ������
 */
function TableInsertSort(&$arr,&$link){
    $link[0]=array('next'=>1);//��ʼ������  $link��һ��Ԫ�ؽ�����Ϊͷ��
    $link[1]=array('next'=>0); //����һ��Ԫ�ط���$link
    /*
     * ��ʼ�������� �ӵڶ���Ԫ�ؿ�ʼ
    */
    for($i=2;$i<=count($arr);$i++){
        $p = $arr[$i]; //�洢��ǰ�������Ԫ��
        $index =0;
        $next = 1;  //�ӿ�ʼλ�ò�������
        while($next!=0){
            if($arr[$next]['age']<$p['age']){
                $index = $next;
                $next = $link[$next]['next'];
            }
            else break;
        }
        if($next == 0){
            $link[$i]['next'] = 0;
            $link[$index]['next'] = $i;
        }else{
            $link[$i]['next']=$next;
            $link[$index]['next']=$i;
        }
    }
}
$link = array();  //����
$arr = array(
    1=>array("uname"=>'����','age'=>20,'occu'=>'PHP����Ա'),
    2=>array("uname"=>'����','age'=>27,'occu'=>'PHP����Ա'),
    3=>array("uname"=>'����','age'=>19,'occu'=>'PHP����Ա'),
    4=>array("uname"=>'����','age'=>33,'occu'=>'PHP����Ա'),
    5=>array("uname"=>'����','age'=>35,'occu'=>'PHP����Ա'),
    6=>array("uname"=>'���Ӿ�','age'=>29,'occu'=>'PHP����Ա'),
    7=>array("uname"=>'����С��','age'=>26,'occu'=>'PHP����Ա'),
    8=>array("uname"=>'����','age'=>80,'occu'=>'PHP����Ա'),
    9=>array("uname"=>'����','age'=>76,'occu'=>'PHP����Ա'),
    10=>array("uname"=>'����','age'=>66,'occu'=>'PHP����Ա'),
    11=>array("uname"=>'��˼','age'=>55,'occu'=>'PHP����Ա'),
    12=>array("uname"=>'������','age'=>32,'occu'=>'PHP����Ա'),
    13=>array("uname"=>'����','age'=>75,'occu'=>'PHP����Ա'),
    14=>array("uname"=>'���幫','age'=>81,'occu'=>'PHP����Ա'),
    15=>array("uname"=>'���¹�','age'=>22,'occu'=>'PHP����Ա'),
    16=>array("uname"=>'��ׯ��','age'=>45,'occu'=>'PHP����Ա'),
    17=>array("uname"=>'�Զ�','age'=>58,'occu'=>'PHP����Ա'),
    18=>array("uname"=>'����','age'=>18,'occu'=>'PHP����Ա'),
    19=>array("uname"=>'������','age'=>39,'occu'=>'PHP����Ա'),
    20=>array("uname"=>'����','age'=>100,'occu'=>'PHP����Ա'),
);
TableInsertSort($arr, $link);
/*
 * ������
*/
$next = $link[0]['next'];
while($next!=0){
    print_r($arr[$next]);
    $next = $link[$next]['next'];
}