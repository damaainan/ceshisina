<?php 

/**
 * 古代某法官要判决IV个犯人的死刑,他有一条荒唐的法律将犯人站成一个圆圈,从第s个人开始数起,每到第D个人就拉出来处死,然后再数D个,再拉出来处决…… 直到剩下最后一个可以赦免.
 */


function getNum($n,$m){
  //用于把所有的数存到数组初始化
  $a = array();
  //遍历,存入数组
  for($i=1;$i<=$n;$i++){
    $a[$i] = $i;
  }
  //指针归0
  reset($a);
  while(count($a)>1){
    //如果数组中项大于1,继续循环剔除元素
    //剔除规则
    for($j=1;$j<=$m;$j++){
        //如果没有达到数组的最后项
      if(next($a)){
        if($j==$m){
          //删除m项
          unset($a[array_search(prev($a),$a)]);
        }
      }else{
        //如果next不存在,那么指针归0
      reset($a);
      if($j==$m){
        unset($a[array_search(end($a),$a)]);
        reset($a);
      }
    }
   }
  }
  return current($a);
}
echo getNum(5,3);