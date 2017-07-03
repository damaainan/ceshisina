<?php 

header("Content-type:text/html; Charset=utf-8");

    //定义一个二叉树
    class BTree{
        protected $key   = null;//当前节点值
        protected $left  = null;//左子树
        protected $right = null;//右子树
    
        //构造函数
        public function __construct($i_key=null,$i_left=null,$i_right=null){
            $this->key = $i_key;
            $this->left = $i_left;
            $this->right = $i_right;
        }
        //析构函数
        public function __destruct(){
            $this->key = null;
            $this->left = null;
            $this->right = null;
        }
    
        //先序遍历，非递归实现
        public function preOrderTraversal(){
            $arr = array();
            $stack=array();
            $temp_tree = $this;
            while($temp_tree != null){
                $arr[] = $temp_tree->key;
                array_push($stack,$temp_tree);
                $temp_tree = $temp_tree->left;
            }
            while(!empty($stack)){
                $temp_tree = array_pop($stack);
                $temp_tree = $temp_tree->right;
                while($temp_tree != null){
                    $arr[] = $temp_tree->key;
                    array_push($stack,$temp_tree);
                    $temp_tree = $temp_tree->left;
                }
            }
            return $arr;
        }
    
        //中序遍历，非递归实现
        public function inOrderTraversal(){
            $arr= array();  //存放遍历结果
            $stack = array();//存放节点栈
            $temp_tree =$this;
            while($temp_tree != null || !empty($stack)){
                while($temp_tree != null){
                    array_push($stack,$temp_tree);
                    $temp_tree = $temp_tree->left;
                }
                if(!empty($stack)){
                    $temp_tree = array_pop($stack);
                    $arr[]=$temp_tree->key;
                    $temp_tree = $temp_tree->right;
                }
            }
            return $arr;
        }
    
        //后续遍历，非递归实现
        public function postOrderTraversal(){
            $arr= array();  //存放遍历结果
            $stack = array();//存放节点栈
            $temp_tree =$this;
            $previsit =null;
            while($temp_tree != null || !empty($stack)){
                while($temp_tree !=null){
                    array_push($stack,$temp_tree);
                    $temp_tree =$temp_tree->left;
                }
    
                $temp_tree = array_pop($stack);
                if($temp_tree->right == null || $temp_tree->right == $previsit){
                    $arr[] = $temp_tree->key;
                    $previsit = $temp_tree;
                    $temp_tree = null;
                }else{
                    array_push($stack,$temp_tree);
                    $temp_tree = $temp_tree->right;
                }
            }
            return $arr;
        }
    }


    //定义二叉排序树
    class BinarySortTree extends BTree{
        //插入一个节点到当前树中
        public function insertNode($key){
            if($this->key == null){//如果是空树，插入到首节点。
                $this->key = $key;
                return;
            }
            $temp_tree =$this;//当前子树
            while($temp_tree !=null){
                if($temp_tree->key == $key){
                    break;
                }
                if($temp_tree->key > $key){//左子树
                    if($temp_tree->left == null){
                        $temp_tree->left = new BinarySortTree($key);
                        break;
                    }else{
                        $temp_tree = $temp_tree->left;
                    }
                }
                if($temp_tree->key < $key){//右子树插入
                    if($temp_tree->right == null){
                        $temp_tree->right = new BinarySortTree($key);
                        break;
                    }else{
                        $temp_tree = $temp_tree->right;
                    }
                }
            }
        }
    
        //查找一个节点,找到返回该节点及其子树，否则返回null
        public function searchNode($key){
            $temp_tree =$this;//当前子树
            while($temp_tree !=null){
                if($temp_tree->key == $key){
                    break;
                }
                if($temp_tree->key > $key){//左子树
                    if($temp_tree->left == null){
                        $temp_tree = null;
                        break;
                    }else{
                        $temp_tree = $temp_tree->left;
                    }
                }
                if($temp_tree->key < $key){//右子树插入
                    if($temp_tree->right == null){
                        $temp_tree=null;
                        break;
                    }else{
                        $temp_tree = $temp_tree->right;
                    }
                }
            }
            return $temp_tree;
        }
    
        //删除一个节点
        public function deleteNode($key){
            $parent_tree =null;//要删除节点的父节点树
            $temp_tree = $this;//要删除的节点
            $in_side = 0;       //要删除的节点在父节点树的哪边
    
            //找到要删除的节点极其父节点
            while($temp_tree !=null && ($temp_tree->key != $key)){
                if($temp_tree->key > $key){//左子树
                    if($temp_tree->left == null){
                        $temp_tree = null;
                        break;
                    }else{
                        $in_side =0;
                        $parent_tree = $temp_tree;
                        $temp_tree = $temp_tree->left;
                    }
                }else{//右子树
                    if($temp_tree->right == null){
                        $temp_tree=null;
                        break;
                    }else{
                        $in_side =1;
                        $parent_tree = $temp_tree;
                        $temp_tree = $temp_tree->right;
                    }
                }
            }
            //根据不同情况进行删除操作
            if($temp_tree != null){//当前节点存在
                $p_side =null;
                //开始删除
                if($temp_tree->left == null){
                    //如果要删除节点左边为空，就将右边赋给parent;
                    $p_side = $temp_tree->right;
                }else if($temp_tree->right == null){
                    //如果要删除节点右边边为空，就将左边赋给parent;
                    $p_side = $temp_tree->left;
                }else{
                    //都不为空，找到要删除节点左子树的最大的节点,极其该节点的父节点
                    $lMax =$temp_tree->right;//左子树最大节点
                    $p_lMax = $temp_tree;
                    while($lMax->right != null){
                        if($lMax->right->right == null){
                            $p_lMax = $lMax;
                        }
                        $lMax = $lMax->right;
                    }
                    $p_lMax->right = $lMax->left;
                    $lMax->left = $temp_tree->left;
                    $lMax->right = $temp_tree->right;
                    $p_side = $lMax;
                }
                //设置父节点
                if($parent_tree != null){//不是根节点删除
                    //引用当前父节点的某一边。
                    if($in_side == 0){
                        $parent_tree->left  = $p_side;
                    }else{
                        $parent_tree->right = $p_side;
                    }
                }else{
                    $this->key = $p_side->key;
                    $this->left = $p_side->left;
                    $this->right = $p_side->right;
                }
            }
        }
    }
    
    $item = array(50, 30, 20,35,33,40,36, 100, 56, 78);
    $root = new BinarySortTree();
    foreach($item as $key){
        $root->insertNode($key);
    }
    var_dump($root);
    echo '先序遍历:'.implode(',',$root->preOrderTraversal()).'<br>';
    echo '中序遍历:'.implode(',',$root->inOrderTraversal()).'<br>';
    echo '后序遍历:'.implode(',',$root->postOrderTraversal()).'<br>';
    
    $root->deleteNode('30');
    echo '删除节点后的先序遍历:'.implode(',',$root->preOrderTraversal()).'<br>';    