<?php
/**
 *队列的链式存储和队列的基本操作
 *1.初始化队列
 *2.链队列的入队操作
 *3.链队列的出队操作
 *4.仅返回队列中的全部元素
 *5.返回队列元素个数
 *6.判断队列是否为空
 *7.将所有元素出队列*
 *@author xudianyang<-->
 *@version $Id:QueueLinked.class.php,v 1.0 2011/02/12 13:05:00 uw Exp
 *@copyright ©2011,xudianyang
 **/
header("content-type:text/html;charset=utf-8");
class QLNode{
    public $mElem=null;
    public $mNext=null;
}
class QueueLinked{
    //队列“队首指针”
    public $mFront=null;
    //队列“队尾指针”
    public $mRear=null;
    //队列长度
    public static $mLength=0;
    public $mNext=null;
    /**
     *初始化队列
     *
     *@return void
     */
    public function __construct(){
        $this->mFront=$this;
        $this->mRear=$this;
        self::$mLength=0;
        $this->mNext=null;
    }
    /**
     *链队列的入队操作
     *
     *@param mixed $e 入队新元素值
     *@return void
     */
    public function getInsertElem($e){
        $newLn=new QLNode();
        $newLn->mElem=$e;
        $newLn->mNext=null;
        $this->mRear->mNext=$newLn;
        $this->mRear=$newLn;
        self::$mLength++;
    }
    /**
     *链队列的出队操作
     *
     *@param mixed $e 出队的元素的值保存在此变量中
     *@return boolean 成功返回true,否则返回false
     */
    public function getDeleteElem(&$e){
        if($this->mFront == $this->mRear){
            return false;
        }
        $p=$this->mFront->mNext;
        $e=$p->mElem;
        $this->mFront->mNext=$p->mNext;
        if($p==$this->mRear){
            $this->mRear=$this->mFront;
        }
        self::$mLength--;
        return true;
    }
    /**
     *仅返回队列中的全部元素
     *
     *@return array 队列全部元素所组成的一个数组
     */
    public function getAllElem(){
        $qldata=array();
        if($this->mFront==$this->mRear){
            return $qldata;
        }
        $p=$this->mFront->mNext;
        while($p!=null){
            $qldata[]=$p->mElem;
            $p=$p->mNext;
        }
        return $qldata;
    }
    /**
     *返回队列元素个数
     *
     *@return int
     */
    public function getLength(){
        return self::$mLength;
    }
    /**
     *判断队列是否为空
     *
     *@return boolean 为空返回true,否则返回false
     */
    public function getIsEmpty(){
        if($this->mFront == $this->mRear){
            return true;
        }else{
            return false;
        }
    }
    /**
     *将所有元素出队列
     *
     *@return array 所有出队列的元素所组成的一个数组
     */
    public function getDeleteAllElem(){
        $qldata=array();
        if($this->mFront == $this->mRear){
            return $qldata;
        }
        while($this->mFront->mNext!=null){
            $qldata[]=$this->mFront->mNext->mElem;
            $this->mFront->mNext=$this->mFront->mNext->mNext;
            self::$mLength--;
        }
        $this->mFront->mNext=null;
        $this->mRear=$this->mFront;
        return $qldata;
    }
}