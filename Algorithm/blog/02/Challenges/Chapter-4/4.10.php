<?php

// is subtree (identical structure and values)

class TreeNode {
  public $value = null;
  public $left = null;
  public $right = null;
  public function __construct($value){
    $this->value = $value;
  }
}

function checkNodeSubtree($needle_tree_root, $haystack_tree_root){
  if($needle_tree_root == null && $haystack_tree_root == null){
     return true;
  }
  if(($needle_tree_root == null || $haystack_tree_root == null) || $needle_tree_root->value !== $haystack_tree_root->value){
    return false;
  }
  
  return checkNodeSubtree($needle_tree_root->left, $haystack_tree_root->left) && checkNodeSubtree($needle_tree_root->right, $haystack_tree_root->right);
}

function preOrderTraversal($needle_tree_root, $haystack_tree_root){
  if($haystack_tree_root == null){
    return false;
  }
  if(checkNodeSubtree($needle_tree_root, $haystack_tree_root)){
    return true;
  }
  
  return preOrderTraversal($needle_tree_root, $haystack_tree_root->left) || preOrderTraversal($needle_tree_root, $haystack_tree_root->right);
}

function findIfIsASubtree(TreeNode $needle_tree_root, TreeNode $haystack_tree_root){
  return preOrderTraversal($needle_tree_root, $haystack_tree_root);
}


$t1_node_a = new TreeNode('A');
$t1_node_b = new TreeNode('B');
$t1_node_c = new TreeNode('C');
$t1_node_a->left = $t1_node_b;
$t1_node_a->right = $t1_node_c;

$t2_node_a = new TreeNode('D');
$t2_node_b = new TreeNode('E');
$t2_node_c = new TreeNode('F');
$t2_node_d = new TreeNode('A');
$t2_node_e = new TreeNode('B');
$t2_node_f = new TreeNode('C');
$t2_node_a->left = $t2_node_b;
$t2_node_a->right = $t2_node_c;
$t2_node_c->right = $t2_node_d;
$t2_node_d->left = $t2_node_e;


var_dump(findIfIsASubtree($t1_node_a, $t2_node_a));

$t2_node_d->right = $t2_node_f;

var_dump(findIfIsASubtree($t1_node_a, $t2_node_a));


// O(nm), m - size of T1
// Space O(log m + log n) // number of recursion levels on each step + number of recursion steps
