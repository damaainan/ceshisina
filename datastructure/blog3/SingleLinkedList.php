<?php
/**
 *单链表的基本操作
 *1.初始化单链表 __construct()
 *2.清空单链表 clearSLL()
 *3.返回单链表长度 getLength()
 *4.判断单链表是否为空 getIsEmpty()
 *5.头插入法建表 getHeadCreateSLL()
 *6.尾插入法建表 getTailCreateSLL()
 *7.返回第$i个元素 getElemForPos()
 *8.查找单链表中是否存在某个值的元素 getElemIsExist()
 *9.单链表的插入操作 getInsertElem()
 *10.遍历单链表中的所有元素 getAllElem()
 *11.删除单链中第$i个元素 getDeleteElem()
 *12.删除单链表中值为$value的前$i($i-->=1)个结 点 getDeleteElemForValue()
 *13.删除单链表所有重复的值 getElemUnique()
 **/
header("content-type:text/html;charset=UTF-8");
class LNode{
    public $mElem;
    public $mNext;
    public function __construct(){
        $this->mElem=null;
        $this->mNext=null;
    }
}
class SingleLinkedList{
    //头结点数据
    public $mElem;
    //下一结点指针
    public $mNext;
    //单链表长度
    public static $mLength=0;
    /**
     *初始化单链表
     *
     *@return void
     */
    public function __construct(){
        $this->mElem=null;
        $this->mNext=null;
    }
    /**
     *清空单链表
     *
     *@return void
     */
    public function clearSLL(){
        if(self::$mLength>0){
            while($this->mNext!=null){
                $q=$this->mNext->mNext;
                $this->mNext=null;
                unset($this->mNext);
                $this->mNext=$q;
            }
            self::$mLength=0;
        }
    }
    /**
     *返回单链表长度
     *
     *@return int
     */
    public static function getLength(){
        return self::$mLength;
    }
    /**
     *判断单链表是否为空
     *
     *@return bool 为空返回true,不为空返回false
     */
    public function getIsEmpty(){
        if(self::$mLength==0 && $this->mNext==null){
            return true;
        }else{
            return false;
        }
    }
    /**
     *头插入法建表
     *
     *@param array $sarr 建立单链表的数据
     *@return void
     */
    public function getHeadCreateSLL($sarr){
        $this->clearSLL();
        if(is_array($sarr)){
            foreach($sarr as $value){
                $p=new LNode();
                $p->mElem=$value;
                $p->mNext=$this->mNext;
                $this->mNext=$p;
                self::$mLength++;
            }
        }else{
            return false;
        }
    }
    /**
     *尾插入法建表
     *
     *@param array $sarr 建立单链表的数据
     *@return void
     */
    public function getTailCreateSLL($sarr){
        $this->clearSLL();
        if(is_array($sarr)){
            $q=$this;
            foreach($sarr as $value){
                $p=new LNode();
                $p->mElem=$value;
                $p->mNext=$q->mNext;
                $q->mNext=$p;
                $q=$p;
                self::$mLength++;
            }
        }else{
            return false;
        }
    }
    /**
     *返回第$i个元素
     *
     *@param int $i 元素位序，从1开始
     *@return mixed
     */
    public function getElemForPos($i){
        $i=(int)$i;
        if($i>self::$mLength || $i < 1){
            return null;
        }
        $j=1;
        $p=$this->mNext;
        while($j<$i){
            $q=$p->mNext;
            $p=$q;
            $j++;
        }
        return $p;
    }
    /**
     *查找单链表中是否存在某个值的元素
     *如果有返回该元素结点，否则返回null
     *
     *@param mixed $value 查找的值
     *@return mixed
     */
    public function getElemIsExist($value){
        $p=$this;
        while($p->mNext != null && strcmp($p->mElem,$value)!==0){
            $p=$p->mNext;
        }
        if(strcmp($p->mElem,$value)===0){
            return $p;
        }else{
            return null;
        }
    }
    /**
     *查找单链表中是否存在某个值的元素
     *如果有返回该元素位序，否则返回-1
     *
     *@param mixed $value 查找的值
     *@return mixed
     */
    public function getElemPosition($value){
        $p=$this;
        $j=0;
        while($p->mNext != null && strcmp($p->mElem,$value)!==0){
            $j++;
            $p=$p->mNext;
        }
        if(strcmp($p->mElem,$value)===0){
            return $j;
        }else{
            return -1;
        }
    }/**
 *单链表的插入操作
 *
 *@param int $i 插入元素的位序，即在什么位置插入新的元素,从1开始
 *@param mixed $e 插入的新的元素值
 *@return boolean 插入成功返回true，失败返回false
 */
    public function getInsertElem($i,$e){
        if($i>self::$mLength || $i<1){
            return false;
        }
        $j=1;
        $p=$this;
        while($p->mNext!=null && $j<$i){
            $p=$p->mNext;
            $j++;
        }
        $q=new LNode();
        $q->mElem=$e;
        $q->mNext=$p->mNext;
        $p->mNext=$q;
        self::$mLength++;
        return true;
    }
    /**
     *遍历单链表中的所有元素
     *
     *@return array 包括单链中的所有元素
     */
    public function getAllElem(){
        $slldata=array();
        if($this->getIsEmpty()){
        }else{
            $p=$this->mNext;
            while($p->mNext!=null){
                $slldata[]=$p->mElem;
                $p=$p->mNext;
            }
            $slldata[]=$p->mElem;
        }
        return $slldata;
    }
    /**
     *删除单链中第$i个元素
     *@param int $i 元素位序
     *@return boolean 删除成功返回true,失败返回false
     */
    public function getDeleteElem($i){
        $i=(int)$i;
        if($i>self::$mLength || $i<1){
            return false;
        }
        $p=$this;
        $j=1;
        while($j<$i){
            $p=$p->mNext;
            $j++;
        }
        $q=$p->mNext;
        $p->mNext=$q->mNext;
        $q=null;
        unset($q);
        self::$mLength--;
    }
    /**
     *删除单链表中值为$value的前$i($i>=1)个结 点
     *
     *@param mixed 待查找的值
     *@param $i 删除的次数，即删除查找到的前$i个
    @return void
     */
    public function getDeleteElemForValue($value,$i=1){
        if($i>1){
            $this->getDeleteElemForValue($value,$i-1);
        }
        $vp=$this->getElemPosition($value);
        $this->getDeleteElem($vp);
    }
    /**
     *删除单链表所有重复的值
     *
     *@return void
     */
    public function getElemUnique(){
        if(!$this->getIsEmpty()){
            $p=$this;
            while($p->mNext!=null){
                $q=$p->mNext;
                $ptr=$p;
                while($q->mNext!=null){
                    if(strcmp($p->mElem,$q->mElem)===0){
                        $ptr->mNext=$q->mNext;
                        $q->mNext=null;
                        unset($q->mNext);
                        $q=$ptr->mNext;
                        self::$mLength--;
                    }else{
                        $ptr=$q;
                        $q=$q->mNext;
                    }
                }
                if(strcmp($p->mElem,$q->mElem)===0){
                    $ptr->mNext=null;
                    self::$mLength--;
                }
                $p=$p->mNext;
            }
        }
    }

    
}//end class 