<?php
 /**
  * 树
  */
 
class Node{
    public $data ;
    public $left ;
    public $right;
	public function __construct($data,$left,$right){
        $this->data = $data;
        $this->left = $left;
        $this->right = $right;
    }


	function show() {
	    return $this->data;
	} 
}
class BST{
    public $root;
    public function __construct()
    {
        $this->root = null;
    }

	function insert($data) {
	    $n = new Node($data, null, null);
	    if ($this->root === null) {
	        $this->root = $n;
	    } else {
	        $current = $this->root;
	        $parent;
	        while (true) {
	            $parent = $current;
	            if ($data < $current->data) {
	                $current = $current->left;
	                if ($current === null) {
	                    $parent->left = $n;
	                    break;
	                }
	            } else {
	                $current = $current->right;
	                if ($current === null) {
	                    $parent->right = $n;
	                    break;
	                }
	            }
	        }
	    }
	}

	function inOrder($node) {
	    if ($node !== null) {
	        $this->inOrder($node->left);
	        var_dump($node->show() + " ");
	        $this->inOrder($node->right);
	    }
	}

	function preOrder($node) {
	    if ($node !== null) {
	        var_dump($node->show() + " ");
	        $this->preOrder($node->left);
	        $this->preOrder($node->right);
	    }
	}

	function postOrder($node) {
	    if ($node !== null) {
	        $this->postOrder($node->left);
	        $this->postOrder($node->right);
	        var_dump($node->show() + " ");
	    }
	}

	function remove($data) {
	    $root = $this->removeNode($this->root, $data);
	}

	function removeNode($node, $data) {
	    if ($node === null) {
	        return null;
	    }
	    if ($data == $node->data) {
	        // 没有子节点的节点
	        if ($node->left === null && $node->right === null) {
	            return null;
	        } // 没有左子节点的节点
	        if ($node->left === null) {
	            return $node->right;
	        } // 没有右子节点的节点
	        if ($node->right === null) {
	            return $node->left;
	        } // 有两个子节点的节点
	        // var_dump($node->right);die;
	        $tempNode = $this->getSmallest($node->right);//查找最小子树  右子树最小值   左子树最大值
	        $node->data = $tempNode->data;
	        $node->right = $this->removeNode($node->right, $tempNode->data);
	        return $node;
	    } else if ($data < $node->data) {
	        $node->left = $this->removeNode($node->left, $data);
	        return $node;
	    } else {
	        $node->right = $this->removeNode($node->right, $data);
	        return $node;
	    }
	}

	function getSmallest($tree) {//查找最小子树
	    $current=$tree;
        while (!($current->right == null)) {
            $current = $current->right;
        }
	    return $current;
	}
	function getMin() {
	    $current = $this->root;
        while (!($current->left == null)) {
            $current = $current->left;
        }
	    return $current->data;
	}
	function getMax() {
	    $current = $this->root;
        while (!($current->right == null)) {
            $current = $current->right;
        } 
	    return $current->data;
	}
	function find($data) {
	    $current = $this->root;
	    while ($current != null) {
	        if ($current->data == $data) {
	            return $current;
	        }else if ($data < $current->data) {
	            $current = $current->left;
	        }else {
	            $current = $current->right;
	        }
	    } 
	    return null;
	}

}



$nums = new BST();
$nums->insert(23);
$nums->insert(45);
$nums->insert(16);
$nums->insert(37);
$nums->insert(3);
$nums->insert(99);
$nums->insert(22);
var_dump("Inorder traversal: ");
$nums->inOrder($nums->root);
var_dump("preOrder traversal: ");
$nums->preOrder($nums->root);
var_dump("postOrder traversal: ");
$nums->postOrder($nums->root);
$nums->remove(23);
echo "remove";
$nums->postOrder($nums->root);
//计算一个节点到另一个节点的层数
$arr=$nums->find(99);
var_dump($arr);
$narr=new BST();
$narr=create($narr,$arr);

var_dump($narr->find(37));


function create($new,$old){
		if(null!=$old->left){
			$new->insert($old->left->data);
			$new=create($new,$old->left);
		}
		if(null!=$old->right){
			$new->insert($old->right->data);
			$new=create($new,$old->right);
		}
		return $new; 
	}