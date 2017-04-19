<?php
/**
 *  PHP 自排序二叉树的深入解析
 * 在节点之间再应用一些排序逻辑，二叉树就能提供出色的组织方式。对于每个节点，都让满足所有特定条件的元素都位于左节点及其子节点。在插入新元素时，我们需要从树的第一个节 点(根节点)开始，判断它属于哪一侧的节点，然后沿着这一侧找到恰当的位置，类似地，在读取数据时，只需要使用按序遍历方法来遍历二叉树
 */
ob_start();
// Here we need to include the binary tree class
class Binary_Tree_Node {
   // You can find the details at
};
ob_end_clean();
// Define a class to implement self sorting binary tree
class Sorting_Tree {
    // Define the variable to hold our tree:
    public $tree;
    // We need a method that will allow for inserts that automatically place
    // themselves in the proper order in the tree
    public function insert($val) {
        // Handle the first case:
        if (!(isset($this->tree))) {
            $this->tree = new Binary_Tree_Node($val);
        } else {
            // In all other cases:
            // Start a pointer that looks at the current tree top:
            $pointer = $this->tree;
            // Iteratively search the tree for the right place:
            for(;;) {
                // If this value is less than, or equal to the current data:
                if ($val <= $pointer->data) {
                    // We are looking to the left ... If the child exists:
                    if ($pointer->left) {
                        // Traverse deeper:
                        $pointer = $pointer->left;
                    } else {
                        // Found the new spot: insert the new element here:
                        $pointer->left = new Binary_Tree_Node($val);
                        break;
                    }
                } else {
                    // We are looking to the right ... If the child exists:
                    if ($pointer->right) {
                        // Traverse deeper:
                        $pointer = $pointer->right;
                    } else {
                        // Found the new spot: insert the new element here:
                        $pointer->right = new Binary_Tree_Node($val);
                        break;
                    }
                }
            }
        }
    }

    // Now create a method to return the sorted values of this tree.
    // All it entails is using the in-order traversal, which will now
    // give us the proper sorted order.
    public function returnSorted() {
        return $this->tree->traverseInorder();
    }
}
// Declare a new sorting tree:
$sort_as_you_go = new Sorting_Tree();
// Let's randomly create 20 numbers, inserting them as we go:
for ($i = 0; $i < 20; $i++) {
    $sort_as_you_go->insert(rand(1,100));
}
// Now echo the tree out, using in-order traversal, and it will be sorted:
// Example: 1, 2, 11, 18, 22, 26, 32, 32, 34, 43, 46, 47, 47, 53, 60, 71,
//   75, 84, 86, 90
echo '<p>', implode(', ', $sort_as_you_go->returnSorted()), '</p>';