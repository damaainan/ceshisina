<?php  


/**
 * php实现先序、中序、后序遍历二叉树
 *
 * 
 * 二叉树是每个节点最多有两个子树的树结构。通常子树被称作“左子树”（left subtree）和“右子树”（right subtree）。二叉树常被用于实现二叉查找树和二叉堆
 */

class Node{  
    public $value;  
    public $left;  
    public $right;  
}  
//先序遍历 根节点 ---> 左子树 ---> 右子树  
function preorder($root){  
    $stack=array();  
    array_push($stack,$root);  
    while(!empty($stack)){  
        $center_node=array_pop($stack);  
        echo $center_node->value.' ';//先输出根节点  
        if($center_node->right!=null){  
            array_push($stack,$center_node->right);//压入左子树  
        }  
        if($center_node->left!=null){  
            array_push($stack,$center_node->left);  
        }  
    }  
}  
//中序遍历，左子树---> 根节点 ---> 右子树  
function inorder($root){  
    $stack = array();  
    $center_node = $root;  
    while (!empty($stack) || $center_node != null) {  
             while ($center_node != null) {  
                 array_push($stack, $center_node);  
                 $center_node = $center_node->left;  
             }  
   
             $center_node = array_pop($stack);  
             echo $center_node->value . " ";  
   
             $center_node = $center_node->right;  
         }  
}  
//后序遍历，左子树 ---> 右子树 ---> 根节点  
function tailorder($root){  
    $stack=array();  
    $outstack=array();  
    array_push($stack,$root);  
    while(!empty($stack)){  
        $center_node=array_pop($stack);  
        array_push($outstack,$center_node);//最先压入根节点，最后输出  
        if($center_node->left!=null){  
            array_push($stack,$center_node->left);  
        }  
        if($center_node->right!=null){  
            array_push($stack,$center_node->right);  
        }  
    }  
      
    while(!empty($outstack)){  
        $center_node=array_pop($outstack);  
        echo $center_node->value.' ';  
    }  
      
}  
$a=new Node();  
$b=new Node();  
$c=new Node();  
$d=new Node();  
$e=new Node();  
$f=new Node();  
$a->value='A';  
$b->value='B';  
$c->value='C';  
$d->value='D';  
$e->value='E';  
$f->value='F';  
$a->left=$b;  
$a->right=$c;  
$b->left=$d;  
$c->left=$e;  
$c->right=$f;  
preorder($a);//A B D C E F   
echo '<hr/>';  
inorder($a);//D B A E C F  
echo '<hr/>';  
tailorder($a);//D B E F C A  