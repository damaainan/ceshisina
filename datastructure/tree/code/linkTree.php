<?php

/********************************************************
 * 我写的PHP都是从C语言的数据结构中演化而来************************
 **************************************************************
/**
 *    ******二叉树图****
 *      A                    *
 *     * *                   *
 *    *   *                  *
 *   B     C                *
 *        *                   *
 *       *                    *
 *      D                    *
 *       *                    *
 *         *E                *
 ******************

 * PHP- 链式二叉树的遍历---先序遍历（根,左,右）-中序遍历(左,根,右)-后    序遍历(左,右，根)
 * 先 A B C D E
 * 中 B A D E C
 * 后 B E D C A
 * @Author 任孟洋
 * @time   2013-8-10
 ****/

Class BTreeNode {

    public $data; //数据域
    public $LeftHand = NULL; //左指针
    public $RightHand = NULL; //右指针

    public function __construct($data) {

        if (!empty($data)) {

            $this->data = $data;
        }
    }

    //先序遍历（根，左，右）递归实现
    public function PreTraverseBTree($BTree) {

        if (NULL !== $BTree) {
            var_dump($BTree->data); //根

            if (NULL !== $BTree->LeftHand) {
                $this->PreTraverseBTree($BTree->LeftHand); //递归遍历左树
            }

            if (NULL !== $BTree->RightHand) {
                $this->PreTraverseBTree($BTree->RightHand); //递归遍历右树
            }

        }
    }

    //中序遍历(左，根，右)递归实现
    public function InTraverseBTree($BTree) {

        if (NULL !== $BTree) {
            if (NULL !== $BTree->LeftHand) {
                $this->InTraverseBTree($BTree->LeftHand); //递归遍历左树
            }

            var_dump($BTree->data); //根

            if (NULL !== $BTree->RightHand) {
                $this->InTraverseBTree($BTree->RightHand); //递归遍历右树
            }

        }

    }

    //后序遍历(左，右，根)递归实现
    public function FexTarverseBTree($BTree) {

        if (NULL !== $BTree) {
            if (NULL !== $BTree->LeftHand) {
                $this->FexTarverseBTree($BTree->LeftHand); //    递归遍历左树
            }

            if (NULL !== $BTree->RightHand) {
                $this->FexTarverseBTree($BTree->RightHand); //    递归遍历右树
            }
            var_dump($BTree->data); //根
        }
    }
}

header("Content-Type:text/html;charset=utf-8");
echo '先的内存为' . var_dump(memory_get_usage());
echo "\n";

//创建五个节点
$A = new BTreeNode('A');
$B = new BTreeNode('B');
$C = new BTreeNode('C');
$D = new BTreeNode('D');
$E = new BTreeNode('E');

//连接形成一个二叉树

$A->LeftHand = $B;
$A->RightHand = $C;
$C->LeftHand = $D;
$D->RightHand = $E;

//先序遍历
echo '先序遍历的结果' . "\n";
$A->PreTraverseBTree($A);

echo "\r\n中序遍历的结果" . "\n";
$A->InTraverseBTree($A);

echo "\r\n后序列遍历的结果" . "\n";
$A->FexTarverseBTree($A);

echo "\n";
echo '后的内存为' . var_dump(memory_get_usage());