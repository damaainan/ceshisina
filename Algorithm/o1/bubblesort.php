<?php 

    class Sort{
        /**
         * 简单的交换排序
         * 冒泡排序初级版
         * 这个不算是标准的冒泡排序算法，因为不满足“两两比较相邻记录”的冒泡排序思想，她更应该是最最简单的交换排序而已
         * 思路：让每一个关键字和她后面的“每一个”关键字比较，如果大则交换
         * 缺点：效率很低
         */
        public function bubbleSort1(&$arr){
            $len=count($arr);
            for ($i=0;$i<$len;$i++) {
                for ($j=$i+1;$j<$len;$j++){
                    if ($arr[$i]>$arr[$j]) {///这里让每一个关键字和她后面的“每一个”关键字都进行比较
                        $this->swap($arr[$i],$arr[$j]);    
                    }
                }
            }
        }
        /**
         * 正宗的冒泡排序算法
         * 思路：通过“两两比较相邻记录”，从而将最小的值排到最顶端
         */
        public function bubbleSort2(&$arr){
            $len=count($arr);
            for ($i=0;$i<$len;$i++){
                for($j=$len-1;$j>$i;$j--) {//$j是从后往前循环
                    if($arr[$j]<$arr[$j-1]) {//注意：这里是“两两比较相邻记录”，以bubbleSort1不同
                        $this->swap($arr[$j],$arr[$j-1]);//这里使用“引用”操作符
                    }
                }
            }
        }
        /**
         * 冒泡排序算法的改进
         * 如果要排序的数组是：[2,1,3,4,5,6,7,8,9]的话，其实只需要将1和2进行比较交换即可，后面的循环中的第二个for循环无需执行，但是如果使用bubbleSort2的话
         * 照样会将$i=2到9及后面的for循环都执行一遍，这些比较明显是多余的
         * 改进思路：在i变量的for循环中，增加了对flag是否为true的判断
         */
        public function bubbleSort3(&$arr){
            $len=count($arr);
            $flag=true;
            for ($i=0;$i<$len && $flag;$i++){//如果之前的一次循环判断中，都没有进行数据交换，则表明目前的数据已经是有序的了，从而跳出循环
                $flag=false;
                for($j=$len-1;$j>$i;$j--) {//$j是从后往前循环
                    if($arr[$j]<$arr[$j-1]) {//注意：这里是“两两比较相邻记录”，以bubbleSort1不同
                        $this->swap($arr[$j],$arr[$j-1]);//这里使用“引用”操作符
                        $flag=true; //如果有数据交换，则将$flag设为true
                    }
                }
            }
        }
        /**
         * 将$a和$b两个值进行位置交换
         */
        public function swap(&$a,&$b) { // 传引用 在 函数定义时写 函数引用时不写  否则报错
            $temp=$a;
            $a=$b;
            $b=$temp;
        }
    }
    $test=new Sort();
    $arr=array(4,6,1,2,9,8,7,3,5);
   $test->bubbleSort1($arr);//简单的交换排序
   var_dump($arr);
   $arr=array(4,6,1,2,9,8,7,3,5);
   $test->bubbleSort2($arr);//冒泡排序
   var_dump($arr);

   $arr=array(4,6,1,2,9,8,7,3,5);
    $test->bubbleSort3($arr);//改进后的冒泡排序
   var_dump($arr);
