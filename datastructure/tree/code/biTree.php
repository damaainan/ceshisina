<?php 

/**
   * PHP实现二叉树
   *
   * @author zhaojiangwei
   * @since 2011/10/25 10:32
   */
  //结点类
  class Node{
    private $data = NULL;
    private $left = NULL;
    private $right = NULL;
    private $lTag = 0;
    private $rTag = 0;
    
    public function __construct($data = false){
      $this->data = $data;
    }
    //我不喜欢使用魔术方法
    public function getData(){
      return $this->data;
    }
    public function setData($data){
      $this->data = $data;
    }
    public function getLeft(){
      return $this->left;
    }
    public function setLeft($left){
      $this->left = $left;
    }
    public function getRight(){
      return $this->right;
    }
    public function setRight($right){
      $this->right = $right;
    }
    public function getLTag(){
      return $this->lTag;
    }
    public function setLTag($tag){
      $this->lTag = $tag;
    }
    public function getRTag(){
      return $this->rTag;
    }
    public function setRTag($tag){
      $this->rTag = $tag;
    }
  }
  //线索二叉树类
  class BiTree{
    private $datas = NULL;//要导入的字符串;
    private $root = NULL; //根结点
    private $leafCount = 0;//叶子结点个数
    private $headNode = NULL; //线索二叉树的头结点
    private $preNode = NULL;//遍历线索化二叉树时保存前一个遍历的结点
    
    public function __construct($datas){
      is_array($datas) || $datas = str_split($datas);
      $this->datas = $datas;
      $this->backupData = $this->datas;
      $this->createTree(TRUE);
    }
    //前序遍历创建树
    //$root 判断是不是要创建根结点
    public function createTree($root = FALSE){
      if(empty($this->datas)) return NULL;
      $first = array_shift($this->datas);
      if($first == '#'){
        return NULL;
      }else{
        $node = new Node($first);
        $root && $this->root = $node;
        $node->setLeft($this->createTree());
        $node->setRight($this->createTree());
        return $node;
      }
    }
    //返回二叉树叶子结点的个数
    public function getLeafCount(){
      $this->figureLeafCount($this->root);
      return $this->leafCount;
    }
    private function figureLeafCount($node){
      if($node == NULL)
        return false;
      if($this->checkEmpty($node)){
        $this->leafCount ++;
      }else{
        $this->figureLeafCount($node->getLeft());
        $this->figureLeafCount($node->getRight());
      }
    }
    //判断结点是不是叶子结点
    private function checkEmpty($node){
      if($node->getLeft() == NULL && $node->getRight() == NULL){
        return true;
      }
      return false;
    }
    //返回二叉树深度
    public function getDepth(){
      return $this->traversDepth($this->root);
    }
    //遍历求二叉树深度
    public function traversDepth($node){
      if($node == NULL){
        return 0;
      }
      $u = $this->traversDepth($node->getLeft()) + 1;
      $v = $this->traversDepth($node->getRight()) + 1;
      return $u > $v ? $u : $v;
    }
    //返回遍历结果,以字符串的形式
    //$order 按遍历形式返回,前中后
    public function getList($order = 'front'){
      if($this->root == NULL) return NULL;
      $nodeList = array();
      switch ($order){
        case "front":
          $this->frontList($this->root, $nodeList);
          break;
        case "middle":
          $this->middleList($this->root, $nodeList);
          break;
        case "last":
          $this->lastList($this->root, $nodeList);
          break;
        default:
          $this->frontList($this->root, $nodeList);
          break;
      }
      return implode($nodeList);
    }
    //创建线索二叉树
    public function createThreadTree(){
      $this->headNode = new Node();
      $this->preNode = & $this->headNode;
      $this->headNode->setLTag(0);
      $this->headNode->setLeft($this->root);
      $this->headNode->setRTag(1);
      $this->threadTraverse($this->root);
      $this->preNode->setRight($this->headNode);
      $this->preNode->setRTag(1);
      $this->headNode->setRight($this->preNode);
    }
    //线索化二叉树
    private function threadTraverse($node){
      if($node != NULL){
        if($node->getLeft() == NULL){
          $node->setLTag(1);
          $node->setLeft($this->preNode);
        }else{
          $this->threadTraverse($node->getLeft());
        }
        if($this->preNode != $this->headNode && $this->preNode->getRight() == NULL){
          $this->preNode->setRTag(1);
          $this->preNode->setRight($node);
        }
        $this->preNode = & $node;//注意传引用
        $this->threadTraverse($node->getRight());
      }
    }
    //从第一个结点遍历中序线索二叉树
    public function threadList(){
      $arr = array();
      for($node = $this->getFirstThreadNode($this->root); $node != $this->headNode; $node = $this->getNextNode($node)){
        $arr[] = $node->getData();
      }
      return implode($arr);
    }
    //从尾结点反向遍历中序线索二叉树
    public function threadListReserv(){
      $arr = array();
      for($node = $this->headNode->getRight(); $node != $this->headNode; $node = $this->getPreNode($node)){
        $arr[] = $node->getData();
      }
      return implode($arr);
    }
    //返回某个结点的前驱
    public function getPreNode($node){
      if($node->getLTag() == 1){
        return $node->getLeft();
      }else{
        return $this->getLastThreadNode($node->getLeft());
      }
    }
    //返回某个结点的后继
    public function getNextNode($node){
      if($node->getRTag() == 1){
        return $node->getRight();
      }else{
        return $this->getFirstThreadNode($node->getRight());
      }
    }
    //返回中序线索二叉树的第一个结点
    public function getFirstThreadNode($node){
      while($node->getLTag() == 0){
        $node = $node->getLeft();
      }
      return $node;
    }
    //返回中序线索二叉树的最后一个结点
    public function getLastThreadNode($node){
      while($node->getRTag() == 0){
        $node = $node->getRight();
      }
      return $node;
    }
    //前序遍历
    private function frontList($node, & $nodeList){
      if($node !== NULL){
        $nodeList[] = $node->getData();
        $this->frontList($node->getLeft(), $nodeList);
        $this->frontList($node->getRight(), $nodeList);
      }
    }
    //中序遍历
    private function middleList($node, & $nodeList){
      if($node != NULL){
        $this->middleList($node->getLeft(), $nodeList);
        $nodeList[] = $node->getData();
        $this->middleList($node->getRight(), $nodeList);
      }
    }
    //后序遍历
    private function lastList($node, & $nodeList){
      if($node != NULL){
        $this->lastList($node->getLeft(), $nodeList);
        $this->lastList($node->getRight(), $nodeList);
        $nodeList[] = $node->getData();
      }
    }
    public function getData(){
      return $this->data;
    }
    public function getRoot(){
      return $this->root;
    }
  }