# 查找-哈希表算法

作者  林湾村龙猫 关注 2016.01.21 01:05 

## **概述**

Hash，一般翻译做“散列”，也有直接音译为“哈希”的，就是把任意长度的输入（又叫做预映射， pre-image），通过散列算法，变换成固定长度的输出，该输出就是散列值。这种转换是一种压缩映射，也就是，散列值的空间通常远小于输入的空间，不同的输入可能会散列成相同的输出，而不可能从散列值来唯一的确定输入值。简单的说就是一种将任意长度的消息压缩到某一固定长度的消息摘要的函数。

## **理论**

[http://blog.csdn.net/v_july_v/article/details/6256463][1]  
[http://blog.sina.com.cn/s/blog_64e21a0a0100hy8a.html][2]  
[http://blog.csdn.net/jdh99/article/details/8490704][3]

## **代码（PHP）**

php中，对象赋值默认是对象引用赋值，类似与c/c++中的指针。

#### **1.链表节点类**

    //定义链表节点
    class Node{
        public $data = null;//当前节点数据
        public $next=null;//下一个节点
    
        public function __construct($data){
            $this->data = $data;
        }
    }

#### **2.哈希表类**

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

#### **3.数据准备**

    $item= array('1','10','100','test','test2','test3','33','22','26','90','101','100','47','63','txm','tom','cat','apache','nginx','777','333');

#### **4.调用**

    $hashTable = new HashTable(10);
    $hashTable->initHashTable($item);
    $hashTable->printHashTable();
    var_dump($hashTable->deleteNode('100'));
    var_dump($hashTable->searchNode('100'));
    //$hashTable->printHashTable();

## **结果**

![][4]



哈希表


[1]: http://blog.csdn.net/v_july_v/article/details/6256463
[2]: http://blog.sina.com.cn/s/blog_64e21a0a0100hy8a.html
[3]: http://blog.csdn.net/jdh99/article/details/8490704
[4]: http://upload-images.jianshu.io/upload_images/301894-3c738f548860a394?imageMogr2/auto-orient/strip%7CimageView2/2/w/1240