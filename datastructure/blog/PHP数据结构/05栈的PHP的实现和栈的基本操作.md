# PHP数据结构之五：栈的PHP的实现和栈的基本操作

作者：小涵 | 来源：互联网 | 2013-08-12 15:47 

阅读: 1369 

栈和队列是两种应用非常广泛的数据结构，它们都来自线性表数据结构，都是“操作受限”的线性表。

栈和队列是两种应用非常广泛的数据结构，它们都来自线性表数据结构，都是“操作受限”的线性表。 

栈 

栈在计算机的实现有多种方式： 

硬堆栈：利用CPU中的某些寄存器组或类似的硬件或使用内存的特殊区域来实现。这类堆栈容量有限，但速度很快； 

软堆栈：这类堆栈主要在内存中实现。堆栈容量可以达到很大。在实现方式上，又有动态方式和静态方式两种 

1.定义：  
栈(Stack)：是限制在表的一端进行插入和删除操作的线性表。又称为后进先出LIFO (Last In First Out)或先进后出FILO (First In Last Out)线性表。 

栈顶(Top)：允许进行插入、删除操作的一端，又称为表尾。用栈顶指针(top)来指示栈顶元素。 

栈底(Bottom)：是固定端，又称为表头。 

空栈：当表中没有元素时称为空栈。 

2.栈的顺序存储表示 

3.栈的链式存储表示  
栈的链式存储结构称为链栈，是运算受限的单链表。其插入和删除操作只能在表头位置上进行。因此，链栈没有必要像单链表那样附加头结点，栈顶指针top就是链表的头指针。下面是PHP实现的栈的基本操作，仅为本人个人创作，仅供参考！

```php

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

```


