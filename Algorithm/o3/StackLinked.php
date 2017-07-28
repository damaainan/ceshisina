<?php
/**
 *栈的链式储存表示和栈的基本操作
 *
 *1.初始化栈 __construct()
 *2.判断栈是否空栈 getIsEmpty()
 *3.将所有元素出栈 getAllPopStack()
 *4.返回栈内元素个数 getLength()
 *5.元素进栈 getPushStack()
 *6.元素出栈 getPopStack()
 *7.仅返回栈内所有元素 getAllElem()*8.返回栈内某个元素的个数 getCountForElem()
 */
header("content-type:text/html;charset=UTF-8");
class LNode{
    public $mElem;
    public $mNext;
    public function __construct(){
        $this->mElem=null;
        $this->mNext=null;
    }
}
class StackLinked{
 
    //头“指针”，指向栈顶元素
    public $mNext;
    public static $mLength;
 
    /**
    *初始化栈
    *
    *@return void
    */
    public function __construct(){
        $this->mNext=null;
        self::$mLength=0;
    }
 
    /**
     *判断栈是否空栈
     *
     *@return boolean  如果为空栈返回true,否则返回false
     */
    public function getIsEmpty(){
        if($this->mNext==null){
            return true;
        }else{
            return false;
        }
    }
 
    /**
     *将所有元素出栈
     *
     *@return array 返回所有栈内元素
     */
    public function getAllPopStack(){
        $e=array();
        if($this->getIsEmpty()){
        }else{
            while($this->mNext!=null){
                $e[]=$this->mNext->mElem;
                $this->mNext=$this->mNext->mNext;
            }
        }
        self::$mLength=0;
        return $e;
    }
 
    /**
     *返回栈内元素个数
     *
     *@return int
     */
    public static function getLength(){
        return self::$mLength;
    }
 
    /**
     *元素进栈
     *
     *@param mixed $e 进栈元素值
     *@return void
     **/
    public function getPushStack($e){
        $newLn=new LNode();
        $newLn->mElem=$e;
        $newLn->mNext=$this->mNext;
        $this->mNext=&$newLn;
        self::$mLength++;
    }
 
    /**
     *元素出栈
     *
     *@param LNode $e 保存出栈的元素的变量
     *@return boolean 出栈成功返回true,否则返回false
     **/
    public function getPopStack(&$e){
        if($this->getIsEmpty()){
            return false;
        }
        $p=$this->mNext;
        $e=$p->mElem;
        $this->mNext=$p->mNext;
        self::$mLength--;
    }
 
    /**
     *仅返回栈内所有元素
     *
     *@return array 栈内所有元素组成的一个数组
     */
    public function getAllElem(){
        $sldata=array();
        if($this->getIsEmpty()){
        }else{
            $p=$this->mNext;
            while($p!=null){
                $sldata[]=$p->mElem;
                $p=$p->mNext;
            }
            return $sldata;
        }
 
    }
    /**
     * 返回栈内某个元素的个数
     *
     * @param mixed $e 待查找的元素的值
     * @return int
     * */
    public function getCountForElem($e){
        $allelem=$this->getAllElem();
        $count=0;
        foreach($allelem as $value){
            if($e === $value){
                $count++;
            }
        }
        return $count;
    }
}