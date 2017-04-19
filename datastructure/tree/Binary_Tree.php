<?php 
//二叉树及其变体是数据结构家族里的重要组成部分。最为链表的一种变体，二叉树最适合处理需要以特定次序快速组织和检索的数据。
// 三种顺序遍历
// Define a class to implement a binary tree
class Binary_Tree_Node {
    // Define the variable to hold our data:
    public $data;
    // And a variable to hold the left and right objects:
    public $left;
    public $right;

    // A constructor method that allows for data to be passed in
    public function __construct($d = NULL) {
        $this->data = $d;
    }

    // Traverse the tree, left to right, in pre-order, returning an array
    // Preorder means that each node's value preceeds its children.
    public function traversePreorder() {
        // Prep some variables.
        $l = array();
        $r = array();
        // Read in the left and right children appropriately traversed:
        if ($this->left) { $l = $this->left->traversePreorder(); }
        if ($this->right) { $r = $this->right->traversePreorder(); }

        // Return a merged array of the current value, left, and right:
        return array_merge(array($this->data), $l, $r); 
    }
    // Traverse the tree, left to right, in postorder, returning an array
    // Postorder means that each node's value follows its children.
    public function traversePostorder() {
        // Prep some variables.
        $l = array();
        $r = array();
        // Read in the left and right children appropriately traversed:
        if ($this->left) { $l = $this->left->traversePostorder(); }
        if ($this->right) { $r = $this->right->traversePostorder(); }

        // Return a merged array of the current value, left, and right:
        return array_merge($l, $r, array($this->data)); 
    }
    // Traverse the tree, left to right, in-order, returning an array.
    // In-order means that values are ordered as left children, then the
    //  node value, then the right children.
    public function traverseInorder() {
        // Prep some variables.
        $l = array();
        $r = array();
        // Read in the left and right children appropriately traversed:
        if ($this->left) { $l = $this->left->traverseInorder(); }
        if ($this->right) { $r = $this->right->traverseInorder(); }

        // Return a merged array of the current value, left, and right:
        return array_merge($l, array($this->data), $r); 
    }
}
// Let's create a binary tree that will equal the following:    3
//                                                             / /      
//                                                            h   9      
//                                                               / /     
// Create the tree:                                             6   a    
$tree = new Binary_Tree_Node(3);
$tree->left = new Binary_Tree_Node('h');
$tree->right = new Binary_Tree_Node(9);
$tree->right->left = new Binary_Tree_Node(6);
$tree->right->right = new Binary_Tree_Node('a');
// Now traverse this tree in all possible orders and display the results:
// Pre-order: 3, h, 9, 6, a
echo '<p>', implode(', ', $tree->traversePreorder()), '</p>';
// Post-order: h, 9, 6, a, 3
echo '<p>', implode(', ', $tree->traversePostorder()), '</p>';
// In-order: h, 3, 6, 9, a
echo '<p>', implode(', ', $tree->traverseInorder()), '</p>';