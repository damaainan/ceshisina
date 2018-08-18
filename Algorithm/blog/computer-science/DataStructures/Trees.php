<?php

# There are 4 general strategies used to traverse a tree:
# - pre-order – process the current node and then traverse the left and right sub-trees.
# - in-order (symmetric) – traverse left first, process the current node, and then traverse right.
# - post-order – traverse left and right first and then process the current node.
# - level-order (breadth-first) – process the current node, then process all sibling nodes before traversing nodes on the next level.
# The first three strategies are also known as a depth-first or depth-order search – in which one starts at the root 
# (or an arbitrary node designated as the root) and traverses as far down a branch as possible, before backtracking. 
# Each of these strategies are used in different operational contexts and situations, for example, pre-order traversal is 
# suited to node insertions (as in our example) and sub-tree cloning (grafting). In-order traversal is commonly used 
# for searching binary trees, while post-order is better suited for deleting (pruning) nodes.

// http://www.sitepoint.com/data-structures-2/

class BinaryNode
{
    public $value;    // contains the node item
    public $left;     // the left child BinaryNode
    public $right;     // the right child BinaryNode

    public function __construct($item) {
        $this->value = $item;
        // new nodes are leaf nodes
        $this->left = null;
        $this->right = null;
    }
    
    public function dump() {
        if ($this->left !== null) {
            $this->left->dump();
        }
        var_dump($this->value);
        if ($this->right !== null) {
            $this->right->dump();
        }
    }
}

class BinaryTree
{
    protected $root; // the root node of our tree

    public function __construct() {
        $this->root = null;
    }

    public function isEmpty() {
        return $this->root === null;
    }
    
    public function insert($item) {
        $node = new BinaryNode($item);
        if ($this->isEmpty()) {
            // special case if tree is empty
            $this->root = $node;
        }
        else {
            // insert the node somewhere in the tree starting at the root
            $this->insertNode($node, $this->root);
        }
    }
  
    protected function insertNode($node, &$subtree) {
        if ($subtree === null) {
            // insert node here if subtree is empty
            $subtree = $node;
        }
        else {
            if ($node->value > $subtree->value) {
                // keep trying to insert right
                $this->insertNode($node, $subtree->right);
            }
            else if ($node->value < $subtree->value) {
                // keep trying to insert left
                $this->insertNode($node, $subtree->left);
            }
            else {
                // reject duplicates
            }
        }
    }
    
    public function traverse() {
        // dump the tree rooted at "root"
        $this->root->dump();
    }
}

# method traverse() implements in-order (symmetric) – traverse
