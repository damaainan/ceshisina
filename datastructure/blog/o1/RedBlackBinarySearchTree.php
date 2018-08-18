<?php

# - each node stores color (red / black)
# - root is always black
# - children of a red node must be black
# - every path from the root a leaf (having < 2 children) (unsuccessfull search) will pass throug exectly the same number of black nodes
# 
# all this rules needed to keep the tree as small in heaight as possible
#
# the tree should be reformated on every insertion / update : recoloring of nodes, rotation. It should keep the same retreival order
# as before
#
# if both child and parent are red, push blackness down from grandparent
# rotate if both and parent are the only children of the same side (rotate left for right children, right for left)
# many other resons for rotation, can rotate parent nodes up to the root
#
# The successor of a node of value X is the node of the tree whose value is the least that is greater than X
# The predecessor of a node of value X is a node of the tree whose value is the greatest that is less than X
# Each is easy to find: just do a depth first search on leftmost child, in the case of successor, and 
# rightmost child, in the case of predecessor. The search ends when a leftmost or rightmost child does not exist
#
# print order: all leftmost children, after the node, after the right children, recursively
#
# Time Complexity:
# insert: O(log n)
# find: O(log n)
#
# http://gauss.ececs.uc.edu/RedBlack/redblack.html
#
# Animation:
# https://www.cs.usfca.edu/~galles/visualization/RedBlack.html


class RBNode {
    
    const COLOR_BLACK = 0;
    
    const COLOR_RED = 1;
    
    public $color = self::COLOR_BLACK;
    
    public $key = null;
    
    public $value = null;
    
    public $left = null;
    
    public $right = null;
    
    public $parent = null;
    
    
    
}

class RBTree {
    public $debug = false;
    
    protected $root = null;
    
    // NIL node
    protected $nil = null;
    
    public function __construct(){
        $this->nil = new RBNode();
        //has itself as a parent and children
        $this->nil->left = $this->nil->right = $this->nil->parent = $this->nil;
        $this->root = $this->nil;
    }
    
    public function isNil(RBNode $x){
        return ($this->nil === $x);
    }
    
    /**
     * @return RBNode
     */
    public function insert(RBNode $x){
        $this->binaryTreeInsert($x);
        
        $newNode = $x;
        $x->color = RBNode::COLOR_RED;
        while($x->parent->color === RBNode::COLOR_RED){
            if($x->parent == $x->parent->parent->left){ // parent of x is a left child
                $y = $x->parent->parent->right;
                if($y->color === RBNode::COLOR_RED){
                    $x->parent->color = RBNode::COLOR_BLACK; // repaint
                    $y->color = RBNode::COLOR_BLACK;
                    $x->parent->parent->color = RBNode::COLOR_RED;
                    $x = $x->parent->parent;
                }
                else{
                    if($x === $x->parent->right){ // x is a right child
                        $x = $x->parent;
                        $this->leftRotate($x);
                    }
                    $x->parent->color = RBNode::COLOR_BLACK;
                    $x->parent->parent->color = RBNode::COLOR_RED;
                    $this->rightRotate($x->parent->parent);
                }
            }
            else{
                $y = $x->parent->parent->left;
                if($y->color === RBNode::COLOR_RED){
                    $x->parent->color = RBNode::COLOR_BLACK;
                    $y->color = RBNode::COLOR_BLACK;
                    $x->parent->parent->color = RBNode::COLOR_RED;
                    $x = $x->parent->parent;
                }
                else{
                    if($x === $x->parent->left){
                        $x = $x->parent;
                        $this->rightRotate($x);
                    }
                    $x->parent->color = RBNode::COLOR_BLACK;
                    $x->parent->parent->color = RBNode::COLOR_RED;
                    $this->leftRotate($x->parent->parent);
                }
            }
        }
        
        $this->root->left->color = RBNode::COLOR_BLACK;
        
        if($this->debug){
            assert($this->nil->color === RBNode::COLOR_BLACK);
            assert($this->root->color === RBNode::COLOR_BLACK);
        }
        
        return $newNode;
    }
    
    /**
     * Find the successor of a given node
     * Finds the left recursive leaf
     * If called on a leaf, returns it's parent if it's a left child, or the $nil
     * @return RBNode
     */
    public function treeSuccessor(RBNode $x){
        $nil = $this->nil;
        $root = $this->root;
        if(($y = $x->right) !== $nil){ //if it's not a leaf (right is not empty)
            while($y->left !== $nil){ //go left until reach a node without the left child (leaf)
                $y = $y->left;
            }
            return $y;
        }
        else{ //if it's a leaf
            $y = $x->parent;
            while( $x === $y->right ){// go up until reach a root or a node where x is a left child
                $x = $y;
                $y = $y->parent;
            }
            if($y === $root){
                return $nil;
            }
            
            return $y;
        }
    }
    
    /**
     * @return RbNode
     */
    public function treePredecessor( RbNode $x ){
        $nil = $this->nil;
        $root = $this->root;
        if(($y = $x->left) !== $nil){ //if it's not a leaf (left is not empty)
            while($y->right !== $nil){ //go right until reach a node without the left child (leaf)
                $y = $y->right;
            }
            return $y;
        }
        else{
            $y = $x->parent;
            while($x === $y->left){// go up until reach a root or a node where x is a right child
                if($y === $root){
                    return $nil;
                }
                $x = $y;
                $y = $y->parent;
            }
            return $y;
        }
    }
    
    /**
     * Do a inorder tree walk and print key/value
     * @param  RbNode
     */
    public function inorderTreePrint( RBNode $x ) {

        $nil = $this->nil;
        $root = $this->root;

        if ( $x !== $this->nil ) {
            $this->inorderTreePrint( $x->left );

            echo "info=  key=".var_export( $x->key, true );

            echo "  l->key=";
            if ( $x->left === $nil ) {
                echo "NULL";
            }
            else {
                echo var_export( $x->left->key, true );
            }

            echo "  r->key=";
            if ( $x->right === $nil ) {
                echo "NULL";
            }
            else {
                echo var_export( $x->right->key, true );
            }

            echo "  p->key=";
            if ( $x->parent === $root ) {
                echo "NULL";
            }
            else {
                echo var_export( $x->parent->key, true );
            }

            echo "  red=";
            if ( $x->color === RBNode::COLOR_RED ) {
                echo "1";
            }
            else {
                echo "0";
            }

            echo "\n";
            $this->inorderTreePrint( $x->right );
        }
    }

    /**
     * Print the key and values stored in a red-black tree
     */
    public function printTree( ) {
        $this->inorderTreePrint( $this->root->left );
    }

    /**
     * Find the highest [in the tree] matching node with a given key
     * @param  mixed
     * @return FALSE|RBNode
     */
    public function findKey( $q ) {
        $x = $this->root->left;
        $nil = $this->nil;

        if ( $x === $nil )
            return false;

        $isEqual = $this->compare( $x->key, $q );

        while ( $isEqual !== 0 ) {
            if ( $isEqual === 1 ) {
                $x = $x->left;
            }
            else {
                $x = $x->right;
            }

            if ( $x === $nil )
                return false;

            $isEqual = $this->compare( $x->key, $q );
        }

        return $x;
    }

    /**
     * Delete a node from the tree
     * @param  RBNode
     */
    public function delete( RBNode $z ) {
        $nil = $this->nil;
        $root = $this->root;

        if ( ( $z->left === $nil ) || ( $z->right === $nil ) ) {
            $y = $z;
        }
        else {
            $y = $this->treeSuccessor( $z );
        }

        if ( $y->left === $nil ) {
            $x = $y->right;
        }
        else {
            $x = $y->left;
        }

        if ( $root === ( $x->parent = $y->parent ) ) {
            $root->left = $x;
        }
        else {
            if ( $y === $y->parent->left ) {
                $y->parent->left = $x;
            }
            else {
                $y->parent->right = $x;
            }
        }

        if ( $y !== $z ) {

            if ( $this->debug ) {
                assert( $y !== $this->nil );
            }

            if ( $y->color === RBNode::COLOR_BLACK )
                $this->deleteFixUp( $x );

            $y->left = $z->left;
            $y->right = $z->right;
            $y->parent = $z->parent;
            $y->color = $z->color;
            $z->left->parent = $z->right->parent = $y;

            if ( $z === $z->parent->left )
            {
                $z->parent->left = $y;
            }
            else
            {
                $z->parent->right = $y;
            }
            $z = null;
            unset( $z );
        }
        else {
            if ( $y->color === RBNode::COLOR_BLACK )
                $this->deleteFixUp( $x );

            $y = null;
            unset( $y );
        }

        if ( $this->debug ) {
            assert( $this->nil->color === RBNode::COLOR_BLACK );
        }
    }

    //--------------------------------+
    // {{{ Protected instance methods |
    //--------------------------------+
    
    
    /**
     * Do a left rotate on a given tree with pivot node x
     * @param  RBNode
     */
    protected function leftRotate( RBNode $x ) {

        $nil = $this->nil;

        $y = $x->right;
        $x->right = $y->left;

        if ( $y->left !== $nil ) {
            $y->left->parent = $x;
        }

        $y->parent = $x->parent;

        if ( $x === $x->parent->left ) {
            $x->parent->left = $y;
        }
        else {
            $x->parent->right = $y;
        }

        $y->left = $x;
        $x->parent = $y;

        if ( $this->debug ) {
            assert( $this->nil->color === RBNode::COLOR_BLACK );
        }
    }

    /**
     * Do a right rotate on a given tree with pivot node y
     * @param  RbNode
     */
    protected function rightRotate( RBNode $y ) {

        $nil = $this->nil;

        $x = $y->left;
        $y->left = $x->right;

        if ( $x->right !== $nil ) {
            $x->right->parent = $y;
        }

        $x->parent = $y->parent;

        if ( $y === $y->parent->left ) {
            $y->parent->left = $x;
        }
        else {
            $y->parent->right = $x;
        }

        $x->right = $y;
        $y->parent = $x;

        if ( $this->debug ) {
            assert( $this->nil->color === RBNode::COLOR_BLACK );
        }
    }
    

    /**
     * Do a binary tree insert, basic insertion based on comparison of kay values
     * @param  RbNode
     */
    protected function binaryTreeInsert( RBNode $newNode ) {

        $nil = $this->nil;

        // Even though at instantiation, these are set to nil - make sure they still are ;-)
        $newNode->left = $newNode->right = $nil;

        $nodeToAppendTo = $this->root;
        $checkingNode = $this->root->left;

        //go down to the leaf where we can append the node
        while ( $checkingNode !== $nil ) {
            $nodeToAppendTo = $checkingNode;
            if ( $this->compare( $checkingNode->key, $newNode->key ) === 1 ) {
                $checkingNode = $checkingNode->left;
            }
            else {
                $checkingNode = $checkingNode->right;
            }
        }

        $newNode->parent = $nodeToAppendTo;

        if ( ( $nodeToAppendTo === $this->root ) || ( $this->compare( $nodeToAppendTo->key, $newNode->key ) === 1 ) ) {
            $nodeToAppendTo->left = $newNode;
        }
        else {
            $nodeToAppendTo->right = $newNode;
        }

        if ( $this->debug ) {
            assert( $this->nil->color === RBNode::COLOR_BLACK );
        }
    }
    
    /**
     * DeleteFixUp
     * @param  RbNode
     */
    protected function deleteFixUp( RBNode $x ) {
        $root = $this->root->left;

        while ( ( $x->color === RBNode::COLOR_BLACK ) && ( $root !== $x ) ) {
            if ( $x === $x->parent->left ) {
                $w = $x->parent->right;
                if ( $w->color === RBNode::COLOR_RED ) {
                    $w->color = RBNode::COLOR_BLACK;
                    $x->parent->color = RBNode::COLOR_RED;
                    $this->leftRotate( $x->parent );
                    $w = $x->parent->right;
                }

                if ( ( $w->right->color === RBNode::COLOR_BLACK ) &&
                     ( $w->left->color === RBNode::COLOR_BLACK ) ) {
                    $w->color = RBNode::COLOR_RED;
                    $x = $x->parent;
                }
                else {
                    if ( $w->right->color === RBNode::COLOR_BLACK ) {
                        $w->left->color = RBNode::COLOR_BLACK;
                        $w->color = RBNode::COLOR_RED;
                        $this->rightRotate( $w );
                        $w = $x->parent->right;
                    }
                    $w->color = $x->parent->color;
                    $x->parent->color = RBNode::COLOR_BLACK;
                    $w->right->color = RBNode::COLOR_BLACK;
                    $this->leftRotate( $x->parent );
                    $x = $root;
                }
            }
            else {
                $w = $x->parent->left;
                if ( $w->color === RBNode::COLOR_RED ) {
                    $w->color = RBNode::COLOR_BLACK;
                    $x->parent->color = RBNode::COLOR_RED;
                    $this->rightRotate( $x->parent );
                    $w = $x->parent->left;
                }

                if ( ( $w->right->color === RBNode::COLOR_BLACK ) &&
                     ( $w->left->color === RBNode::COLOR_BLACK ) ) {
                    $w->color = RBNode::COLOR_RED;
                    $x = $x->parent;
                }
                else {
                    if ( $w->left->color === RBNode::COLOR_BLACK ) {
                        $w->right->color = RBNode::COLOR_BLACK;
                        $w->color = RBNode::COLOR_RED;
                        $this->leftRotate( $w );
                        $w = $x->parent->left;
                    }
                    $w->color = $x->parent->color;
                    $x->parent->color = RBNode::COLOR_BLACK;
                    $w->left->color = RBNode::COLOR_BLACK;
                    $this->rightRotate( $x->parent );
                    $x = $root;
                }
            }
        }
        $x->color = RBNode::COLOR_BLACK;

        if ( $this->debug ) {
            assert( $this->nil->color === RBNode::COLOR_BLACK );
        }
    }
    
    /**
     * Compare two values
     *
     * <ul>
     *   <li> !! WARNING !! If this method is not overridden, then
     *        all the keys should either be numeric or not a numeric,
     *        otherwise a valid tree can not be formed, since string
     *        comparison would not make sense on integers e.g strcmp("3","10").
     *        In addition, values can not be compared in a mixed fashion
     *        i.e. some values compared using string comparison, and
     *        others using numeric comparison
     * </ul>
     *
     * @access protected
     * @param  mixed
     * @param  mixed
     * @return integer Return <i>integer</i> 1, if key1 is greater than key2
     *                        <i>integer</i> 0, if key1 is equal to key2
     *                        <i>integer</i> -1, if key1 is less than key2
     * @throws InvalidArgumentException
     */
    protected function compare( $key1, $key2 )
    {
        if (is_bool( $key1 ) || is_bool( $key2 ) )// || !is_scalar( $key2 ) !is_scalar( $key1 ) || 
            throw new InvalidArgumentException( __METHOD__.'() keys must be a string or numeric' );

        $returnValue = null;

        switch ( true ) {
            case ( is_numeric( $key1 ) && is_numeric( $key2 ) ):
                if ( $key1 > $key2 ) {
                    $returnValue = 1;
                }
                else {
                    $returnValue = ( $key1 === $key2 ) ? 0 : -1;
                }
                return $returnValue;

                // Add more cases here...
        }

        // Unfortunately if either of the keys is not a numeric, then
        // the most logical comparison method is by their string values
        $returnValue = strcmp( "$key1", "$key2" );

        // make sure these are the exact return values, even though PHP seems to always return
        // -1,0,1 but the documentation does not explicity say it
        if ( $returnValue > 0 )
            return 1;

        if ( $returnValue < 0 )
            return -1;

        return 0;
    }
    
}

$tree = new RBTree();
$values = [
    50=>"first",25=>"second",100=>"third",10=>"fourth",30=>"fifth",
    1000=>"sixth",75=>"seventh",2000=>"eighth",3000=>"nineth"
];
foreach($values as $key => $value){
    $newNode = new RBNode();
    $newNode->key = $key;
    $newNode->value = $value;
    $tree->insert($newNode);
}

$tree->printTree();
echo $tree->findKey(1000)->value;
