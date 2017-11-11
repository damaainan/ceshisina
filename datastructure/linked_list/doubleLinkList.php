<?php  
//php实现双链表的删除和插入节点
class node{  
    public $prev;  
    public $next;  
    public $data;  
    public function __construct($data,$prev=null,$next=null){  
        $this->data=$data;  
        $this->prev=$prev;  
        $this->next=$next;  
    }  
}  
  
class doubleLinkList{  
    private $head;  
    public function __construct()  
    {  
        $this->head=new node("head",null,null);  
    }  
    //插入节点  
    public function insertLink($data){  
        $p=new node($data,null,null);  
        $q=$this->head->next;  
        $r=$this->head;  
        while($q){  
            if($q->data>$data){  
                $q->prev->next=$p;  
                $p->prev=$q->prev;  
                $p->next=$q;  
                $q->prev=$p;  
            }else{  
            $r=$q;$q=$q->next;  
            }  
        }  
        if($q==null){  
            $r->next=$p;  
            $p->prev=$r;  
        }  
    }  
    //从头输出节点  
    public function printFromFront(){  
        $p=$this->head->next;  
        $string="";  
        while($p){  
        $string.=$string?",":"";  
        $string.=$p->data;  
        $p=$p->next;  
        }  
        echo  $string."<br>";  
    }  
    //从尾输出节点  
    public function printFromEnd(){  
        $p=$this->head->next;  
        $r=$this->head;  
        while($p){  
        $r=$p;$p=$p->next;  
        }  
        $string="";  
        while($r){  
            $string.=$string?",":"";  
            $string.=$r->data;  
            $r=$r->prev;  
        }  
        echo $string."<br>";  
    }  
    public function delLink($data){  
        $p=$this->head->next;  
        if(!$p)  
        return;  
        while($p){  
            if($p->data==$data)  
            {  
                  
                $p->next->prev=$p->prev;  
                $p->prev->next=$p->next;  
                unset($p);  
                return;  
            }  
            else{  
                $p=$p->next;  
            }  
        }  
        if($p==null)  
        echo "没有值为{$data}的节点";  
    }  
      
}  
  
$link=new doubleLinkList();  
$link->insertLink(1);  
$link->insertLink(2);  
$link->insertLink(3);  
$link->insertLink(4);  
$link->insertLink(5);  
$link->delLink(3);  
$link->printFromFront();  
$link->printFromEnd();  
$link->delLink(6);  