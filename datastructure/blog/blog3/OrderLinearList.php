<?php
/**
 * 顺序表基本操作
 *
 *包括
 *1.顺序表的初始化 __contruct()
 *2.清空顺序表 __destruct()
 *3.判断顺序表是否为空 isEmpty()
 *4.返回顺序表的长度 getLength()
 *5.根据下标返回顺序表中的某个元素 getElement()
 *6.返回顺序表中某个元素的位置 getElementPosition()
 *7.返回顺序表中某个元素的直接前驱元素 getElementPredecessorr()
 *8.返回某个元素的直接后继元素 getElementSubsequence()
 *9.指定下标位置返回元素 getElemForPos()
 *10.根据下标或者元素值删除顺序表中的某个元素 getDeleteElement()
 *11.根据元素位置删除顺序表中的某个元素 getDeleteEleForPos()
 *12.在指定位置插入一个新的结点 getInsertElement()
 */

header("content-type:text/html;charset=utf-8");
class OrderLinearList{
    public $oll;//顺序表
    /**
     * 顺序表初始化
     *
     * @param mixed $oll
     * @return void
     *  
     */
    public function __construct($oll=array()){
        $this->oll=$oll;
    }    
    /**
     * 清空顺序表
     *@return void
     */
    public function __destruct(){
        foreach($this->oll as $key=>$value){
            unset($this->oll[$key]);
        }
    }     
    /**
     * 判断顺序表是否为空
     * @return boolean 为空返回true,否则返回false
     * */
    public function isEmpty(){
        if(count($this->oll) > 0){
            return false;
        }else{
            return true;
        }
    }      
    /**
     * 返回顺序表的长度
     * @return int
     * */
    public function getLength(){
        return count($this->oll);
    }    
    /**
     * 返回顺序表中下标为$key的元素
     *
     * @param mixed $key 顺序表元素的下标
     * @return mixed
     * */
    public function getElement($key){
        return $this->oll[$key];
    }    
    /**
     * 返回顺序表中某个元素的位置
     *
     * @param mixed $value 顺序表中某个元素的值
     * @return int 从1开始,如果返回-1表示不存在该元素
     * */
    public function getElementPosition($value){
        $i=0;
        foreach($this->oll as $val){
            $i++;
            if(strcmp($value,$val) === 0){
                return $i;
            }
        }
        return -1;
    }    
    /**
     * 返回顺序表中某个元素的直接前驱元素
     *
     *@param mixed  $value顺序表中某个元素的值
     *@param bool $tag 如果$value为下标则为1,如果$value为元素值则为2
     *@return array array('value'=>...)直接前驱元素值，array('key'=>...)直接前驱元素下标
     **/
    public function getElementPredecessorr($value,$tag=1){
        $i=0;
        foreach($this->oll as $key=>$val){
            $i++;
            if($tag ==1 ){
                if(strcmp($key,$value) === 0){
                    if($i == 1){
                        return false;
                    }
                    prev($this->oll);
                    prev($this->oll);
                    return array('value'=>current($this->oll),'key'=>key($this->oll));
                }
            }
            if($tag == 2){
                if(strcmp($val,$value) === 0){
                    if($i == 1){
                        return false;
                    }
                    prev($this->oll);
                    prev($this->oll);
                    return array('value'=>current($this->oll),'key'=>key($this->oll));
                }
            }
        }
    }     
    /**
     * 返回某个元素的直接后继元素
     *
     *@param mixed  $value顺序表中某个元素的值
     *@param bool $tag 如果$value为下标则为1,如果$value为元素值则为2
     *@return array array('value'=>...)直接后继元素值，array('key'=>...)直接后继元素下标
     **/
    public function getElementSubsequence($value,$tag=1){
        $i=0;
        $len=count($this->oll);
        foreach($this->oll as $key=>$val){
            $i++;
            if($tag ==1 ){
                if(strcmp($key,$value) == 0){
                    if($i == $len){
                        return false;
                    }
                    return array('value'=>current($this->oll),'key'=>key($this->oll));
                }
            }
            if($tag == 2){
                if(strcmp($val,$value) == 0){
                    if($i == $len){
                        return false;
                    }
                    return array('value'=>current($this->oll),'key'=>key($this->oll));
                }
            }
        }
        return false;
    }     
    /**
     * 在指定位置插入一个新的结点
     *
     * @param mixed $p 新结点插入位置,从1开始
     * @param mixed $value 顺序表新结点的值
     * @param mixed $key 顺序表新结点的下标
     * @param bool $tag 是否指定新结点的下标,1表示默认下标,2表示指定下标
     * @return bool 插入成功返回true，失败返回false
     * */
    public function getInsertElement($p,$value,$key=null,$tag=1){
        $p=(int)$p;
        $len=count($this->oll);
        $oll=array();
        $i=0;
        if($p > $len || $p < 1){
            return false;
        }
        foreach($this->oll as $k=>$v){
            $i++;
            if($i==(int)$p){
                if($tag == 1){
                    $oll[]=$value;
                }else if($tag == 2){
                    $keys=array_keys($oll);
                    $j=0;
                    if(is_int($key)){
                        while(in_array($key,$keys,true)){
                            $key++;
                        }
                    }else{
                        while(in_array($key,$keys,true)){
                            $j++;
                            $key.=(string)$j;
                        }
                    }
                    $oll[$key]=$value;
                }else{
                    return false;
                }
                $key=$k;
                $j=0;
                $keys=array_keys($oll);
                if(is_int($key)){
                    $oll[]=$v;
                }else{
                    while(in_array($key,$keys,true)){
                        $j++;
                        $key.=(string)$j;
                    }
                    $oll[$key]=$v;
                }
            }else{
                if($i>$p){
                    $key=$k;
                    $j=0;
                    $keys=array_keys($oll);
                    if(is_int($key)){
                        $oll[]=$v;
                    }else{
                        while(in_array($key,$keys,true)){
                            $j++;
                            $key.=(string)$j;
                        }
                        $oll[$key]=$v;
                    }
                }else{
                    if(is_int($k)){
                        $oll[]=$v;
                    }else{
                        $oll[$k]=$v;
                    }
                }
            }
        }
        $this->oll=$oll;
        return true;
    }      
    /**
     * 根据元素位置返回顺序表中的某个元素
     *
     * @param int $position 元素位置从1开始
     * @return array  array('value'=>...)元素值，array('key'=>...)元素下标
     * */
    public function getElemForPos($position){
        $i=0;
        $len=count($this->oll);
        $position=(int)$position;
        if($position > $len || $position < 1){
            return false;
        }
        foreach($this->oll as $val){
            $i++;
            if($i == $position){
                return array('value'=>current($this->oll),'key'=>key($this->oll));
            }
        }
    }
    /**
     * 根据下标或者元素值删除顺序表中的某个元素
     *
     * @param mixed $value 元素下标或者值
     * @param int $tag 1表示$value为下标，2表示$value为元素值
     * @return bool 成功返回true,失败返回false
     * */
    public function getDeleteElement($value,$tag=1){
        $len=count($this->oll);
        foreach($this->oll as $k=>$v){
            if($tag == 1){
                if(strcmp($k,$value) === 0){
                }else{
                    if(is_int($k)){
                        $oll[]=$v;
                    }else{
                        $oll[$k]=$v;
                    }
                }
            }
            if($tag ==2){
                if(strcmp($v,$value) === 0){
                }else{
                    if(is_int($k)){
                        $oll[]=$v;
                    }else{
                        $oll[$k]=$v;
                    }
                }
            }
        }
        $this->oll=$oll;
        if(count($this->oll) == $len){
            return false;
        }
        return true;
    }      
    /**
     * 根据元素位置删除顺序表中的某个元素
     *
     * @param int $position 元素位置从1开始
     * @return bool 成功返回true,失败返回false
     * */
    public function getDeleteEleForPos($position){
        $len=count($this->oll);
        $i=0;
        $position=(int)$position;
        if($position > $len || $position < 1){
            return false;
        }
        foreach($this->oll as $k=>$v){
            $i++;
            if($i == $position){
            }else{
                if(is_int($k)){
                    $oll[]=$v;
                }else{
                    $oll[$k]=$v;
                }
            }
        }
        $this->oll=$oll;
        if(count($this->oll) == $len){
            return false;
        }
        return true;
    }
}


$oll=new OrderLinearList(array('xu'=>'徐典阳',32,"是吧",'dianyang'=>10,455));
//判断顺序表是否为空,返回false说明不为空
var_dump($oll->isEmpty());
echo "<br>";
//返回顺序表的长度 返回6
echo $oll->getLength();
echo "<br>";
//根据下标返回顺序表中的某个元素
var_dump($oll->getElement(1));
echo "<br>";
//返回顺序表中某个元素的位置
echo $oll->getElementPosition("是吧");
echo "<br>";
//返回顺序表中某个元素的直接前驱元素
var_dump($oll->getElementPredecessorr("是吧",2));
echo "<br>";
//返回顺序表中某个元素的直接后继元素
var_dump($oll->getElementSubsequence("是吧",2));
echo "<br>";
//根据元素位置返回顺序表中的某个元素
var_dump($oll->getElemForPos(2));
echo "<br>";
//根据下标或者元素值删除顺序表中的某个元素
var_dump($oll->getDeleteElement('徐典阳',$tag=2));
echo "<br>";
//根据元素位置删除顺序表中的某个元素
var_dump($oll->getDeleteEleForPos(1));
echo "<br>";
$oll->getInsertElement(3,"徐珍",$key="xuzheng",$tag=2);
var_dump($oll->oll);
echo "<br>";