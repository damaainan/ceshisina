<?php
/**
 * 作者：迹忆
 * 个人博客：迹忆博客
 * 博客url：www.onmpw.com
 * **********
 * 表插入排序
 * **********
 * @param array $arr 实际数据
 * @param arrat $link 索引表
 */
function TableInsertSort(&$arr,&$link){
    $link[0]=array('next'=>1);//初始化链表  $link第一个元素仅仅作为头部
    $link[1]=array('next'=>0); //将第一个元素放入$link
    /*
     * 开始遍历数组 从第二个元素开始
    */
    for($i=2;$i<=count($arr);$i++){
        $p = $arr[$i]; //存储当前待排序的元素
        $index =0;
        $next = 1;  //从开始位置查找链表
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
$link = array();  //链表
$arr = array(
    1=>array("uname"=>'张三','age'=>20,'occu'=>'PHP程序员'),
    2=>array("uname"=>'李四','age'=>27,'occu'=>'PHP程序员'),
    3=>array("uname"=>'赵五','age'=>19,'occu'=>'PHP程序员'),
    4=>array("uname"=>'王六','age'=>33,'occu'=>'PHP程序员'),
    5=>array("uname"=>'刘大','age'=>35,'occu'=>'PHP程序员'),
    6=>array("uname"=>'公子纠','age'=>29,'occu'=>'PHP程序员'),
    7=>array("uname"=>'公子小白','age'=>26,'occu'=>'PHP程序员'),
    8=>array("uname"=>'管仲','age'=>80,'occu'=>'PHP程序员'),
    9=>array("uname"=>'孔丘','age'=>76,'occu'=>'PHP程序员'),
    10=>array("uname"=>'曾子','age'=>66,'occu'=>'PHP程序员'),
    11=>array("uname"=>'子思','age'=>55,'occu'=>'PHP程序员'),
    12=>array("uname"=>'左丘明','age'=>32,'occu'=>'PHP程序员'),
    13=>array("uname"=>'孟子','age'=>75,'occu'=>'PHP程序员'),
    14=>array("uname"=>'宋襄公','age'=>81,'occu'=>'PHP程序员'),
    15=>array("uname"=>'秦穆公','age'=>22,'occu'=>'PHP程序员'),
    16=>array("uname"=>'楚庄王','age'=>45,'occu'=>'PHP程序员'),
    17=>array("uname"=>'赵盾','age'=>58,'occu'=>'PHP程序员'),
    18=>array("uname"=>'廉颇','age'=>18,'occu'=>'PHP程序员'),
    19=>array("uname"=>'蔺相如','age'=>39,'occu'=>'PHP程序员'),
    20=>array("uname"=>'老子','age'=>100,'occu'=>'PHP程序员'),
);
TableInsertSort($arr, $link);
/*
 * 输出结果
*/
$next = $link[0]['next'];
while($next!=0){
    print_r($arr[$next]);
    $next = $link[$next]['next'];
}