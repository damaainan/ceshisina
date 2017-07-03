<?php 


    //定义链表节点
    class Node{
        public $data = null;//当前节点数据
        public $next=null;//下一个节点
    
        public function __construct($data){
            $this->data = $data;
        }
    }

    //定义哈希表
    class HashTable{
        protected $arr_table = array();//哈希表
        public  $length =0;//哈希表长度
    
        //根据输入，确定其在哈希表中的位置，即哈希函数
        protected function hashPosition($input){
            if(!is_numeric($input)){//不是数值，映射为数值
                $input = crc32($input);
            }
            $position = $input%$this->length;//取模运算
            return abs($position);
        }
    
        //初始化
        public function __construct($length=10){
            $this->length = $length;
        }
    
        //初始化一个哈希表
        public function initHashTable($arr){
            for($i=0;$i<$this->length;$i++){
                $this->arr_table[$i] = null;
            }
            foreach($arr as $value){
                $this->insertNode($value);
            }
        }
    
        //插入一个值
        public function insertNode($input){
            if(empty($input)){
                return array();
            }
            $position = $this->hashPosition($input);//获取输入在哈希表中的位置
            $num=1;
            if(empty($this->arr_table[$position])){
                $this->arr_table[$position] = new Node($input);
            }else{//拉链法解决哈希冲突
                $temp_node = $this->arr_table[$position];
                while(true){
                    if($temp_node->next == null){
                        $temp_node->next = new Node($input);
                        break;
                    }
                    $num++;
                    $temp_node = $temp_node->next;
                }
            }
            return array($position,$num);
        }
    
        //删除一个值
        public function deleteNode($input){
            if(empty($input)){
                return false;
            }
            $position = $this->hashPosition($input);
            if(empty($this->arr_table[$position])){
                return false;
            }else{
                $per_node = null;
                $temp_node = $this->arr_table[$position];
                while($temp_node != null){
                    if($temp_node->data == $input){
                        break;
                    }
                    $per_node = $temp_node;
                    $temp_node = $temp_node->next;
                }
                if(empty($temp_node)){
                    return false;
                }
                if($per_node == null){//第一个节点，为要删除的节点
                    $this->arr_table[$position] = $temp_node->next;
                }else{//非第一个节点
                    $per_node->next = $temp_node->next;
                }
            }
            return true;
        }
    
        //查找一个节点
        public function searchNode($input){
    
            if(empty($input)){
                return array();
            }
            $position = $this->hashPosition($input);
            $num=1;
            if(empty($this->arr_table[$position])){
                return array();
            }else{
                $temp_node = $this->arr_table[$position];
                while($temp_node != null){
                    if($temp_node->data == $input){
                        break;
                    }
                    $num++;
                    $temp_node = $temp_node->next;
                }
                if($temp_node == null){
                    return array();
                }
            }
            return array($position,$num);
        }
    
        //显示当前哈希表
        public function printHashTable(){
            foreach($this->arr_table as $hash=>$item){
                echo $hash.' |';
                $temp_node = $item;
                while($temp_node != null){
                    echo $temp_node->data.' , ';
                    $temp_node = $temp_node->next;
                }
                echo '<hr>';
            }
        }
    }

    $item= array('1','10','100','test','test2','test3','33','22','26','90','101','100','47','63','txm','tom','cat','apache','nginx','777','333');


    $hashTable = new HashTable(10);
    $hashTable->initHashTable($item);
    $hashTable->printHashTable();
    var_dump($hashTable->deleteNode('100'));
    var_dump($hashTable->searchNode('100'));
    //$hashTable->printHashTable();