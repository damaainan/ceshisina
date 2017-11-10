<?php
/**
 * PHP根据树的前序遍历和中序遍历构造树并输出后序遍历的方法
 */
class BinaryTreeNode
{
    public $m_value;
    public $m_left;
    public $m_right;
}
function ConstructCore($preorder, $inorder)
{
    if (count($preorder) != count($inorder) || count($preorder) == 0 || count($inorder) == 0) {
        return null;
    }

    $headNode          = new BinaryTreeNode;
    $headNode->m_value = $preorder[0];
    if (count($preorder) == 1) {
        $headNode->m_left  = null;
        $headNode->m_right = null;
        return $headNode;
    }
    array_shift($preorder);
    $pos               = array_search($headNode->m_value, $inorder);
    $leftin            = array_slice($inorder, 0, $pos);
    $rightin           = array_slice($inorder, $pos + 1);
    $leftpre           = array_slice($preorder, 0, $pos);
    $rightpre          = array_slice($preorder, $pos);
    $headNode->m_left  = ConstructCore($leftpre, $leftin);
    $headNode->m_right = ConstructCore($rightpre, $rightin);
    return $headNode;
}
$pre  = array(1, 2, 4, 7, 3, 5, 6, 8);
$in   = array(4, 7, 2, 1, 5, 3, 8, 6);
$tree = ConstructCore($pre, $in);
function tail($tree)
{
    if ($tree->m_right != null) {
        echo tail($tree->m_right);
    }

    if ($tree->m_left != null) {
        echo tail($tree->m_left);
    }

    echo $tree->m_value;
}
tail($tree);
