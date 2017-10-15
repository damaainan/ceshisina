<?php
/**
 * author:zhongjin
 * time:2016/10/20 11:53
 * description: 红黑树
 */
//结点
class Node
{
    public $key;
    public $parent;
    public $left;
    public $right;
    public $IsRed;  //分辨红节点或黑节点

    public function __construct($key, $IsRed = TRUE)
    {
        $this->key = $key;
        $this->parent = NULL;
        $this->left = NULL;
        $this->right = NULL;
        //插入结点默认是红色
        $this->IsRed = $IsRed;
    }
}

//红黑树
class Rbt
{
    public $root;

    /**
     * 初始化树结构
     * @param $arr 初始化树结构的数组
     * @return null
     */
    public function init($arr)
    {
        //根节点必须是黑色
        $this->root = new Node($arr[0], FALSE);
        for ($i = 1; $i < count($arr); $i++) {
            $this->Insert($arr[$i]);
        }
    }

    /**
     * （对内）中序遍历
     * @param $root （树或子树的）根节点
     * @return null
     */
    private function mid_order($root)
    {
        if ($root != NULL) {
            $this->mid_order($root->left);
            echo $root->key . "-" . ($root->IsRed ? 'r' : 'b') . '  ';
            $this->mid_order($root->right);
        }
    }

    /**
     * （对外）中序遍历
     * @param null
     * @return null
     */
    public function MidOrder()
    {
        $this->mid_order($this->root);
    }

    /**
     * 查找树中是否存在$key对应的节点
     * @param $key 待搜索数字
     * @return $key对应的节点
     */
    function search($key)
    {
        $current = $this->root;
        while ($current != NULL) {
            if ($current->key == $key) {
                return $current;
            } elseif ($current->key > $key) {
                $current = $current->left;
            } else {
                $current = $current->right;
            }
        }
        //结点不存在
        return $current;
    }

    /**
     * 将以$root为根节点的最小不平衡二叉树做右旋处理
     * @param $root（树或子树）根节点
     * @return null
     */
    private function R_Rotate($root)
    {
        $L = $root->left;
        if (!is_null($root->parent)) {
            $P = $root->parent;
            if($root == $P->left){
                $P->left = $L;
            }else{
                $P->right = $L;
            }
            $L->parent = $P;
        } else {
            $L->parent = NULL;
        }
        $root->parent = $L;
        $root->left = $L->right;
        $L->right = $root;
        //这句必须啊！
        if ($L->parent == NULL) {
            $this->root = $L;
        }
    }

    /**
     * 将以$root为根节点的最小不平衡二叉树做左旋处理
     * @param $root（树或子树）根节点
     * @return null
     */
    private function L_Rotate($root)
    {
        $R = $root->right;
        if (!is_null($root->parent)) {
            $P = $root->parent;
            if($root == $P->right){
                $P->right = $R;
            }else{
                $P->left = $R;
            }
            $R->parent = $P;
        } else {
            $R->parent = NULL;
        }
        $root->parent = $R;
        $root->right = $R->left;
        $R->left = $root;
        //这句必须啊！
        if ($R->parent == NULL) {
            $this->root = $R;
        }
    }

    /**
     * 查找树中的最小关键字
     * @param $root 根节点
     * @return 最小关键字对应的节点
     */
    function search_min($root)
    {
        $current = $root;
        while ($current->left != NULL) {
            $current = $current->left;
        }
        return $current;
    }

    /**
     * 查找树中的最大关键字
     * @param $root 根节点
     * @return 最大关键字对应的节点
     */
    function search_max($root)
    {
        $current = $root;
        while ($current->right != NULL) {
            $current = $current->right;
        }
        return $current;
    }

    /**
     * 查找某个$key在中序遍历时的直接前驱节点
     * @param $x 待查找前驱节点的节点引用
     * @return 前驱节点引用
     */
    function predecessor($x)
    {
        //左子节点存在，直接返回左子节点的最右子节点
        if ($x->left != NULL) {
            return $this->search_max($x->left);
        }
        //否则查找其父节点，直到当前结点位于父节点的右边
        $p = $x->parent;
        //如果x是p的左孩子，说明p是x的后继，我们需要找的是p是x的前驱
        while ($p != NULL && $x == $p->left) {
            $x = $p;
            $p = $p->parent;
        }
        return $p;
    }

    /**
     * 查找某个$key在中序遍历时的直接后继节点
     * @param $x 待查找后继节点的节点引用
     * @return 后继节点引用
     */
    function successor($x)
    {
        if ($x->left != NULL) {
            return $this->search_min($x->right);
        }
        $p = $x->parent;
        while ($p != NULL && $x == $p->right) {
            $x = $p;
            $p = $p->parent;
        }
        return $p;
    }

    /**
     * 将$key插入树中
     * @param $key 待插入树的数字
     * @return null
     */
    public function Insert($key)
    {
        if (!is_null($this->search($key))) {
            throw new Exception('结点' . $key . '已存在，不可插入！');
        }
        $root = $this->root;
        $inode = new Node($key);
        $current = $root;
        $prenode = NULL;
        //为$inode找到合适的插入位置
        while ($current != NULL) {
            $prenode = $current;
            if ($current->key > $inode->key) {
                $current = $current->left;
            } else {
                $current = $current->right;
            }
        }

        $inode->parent = $prenode;
        //如果$prenode == NULL， 则证明树是空树
        if ($prenode == NULL) {
            $this->root = $inode;
        } else {
            if ($inode->key < $prenode->key) {
                $prenode->left = $inode;
            } else {
                $prenode->right = $inode;
            }
        }

        //将它重新修正为一颗红黑树
        $this->InsertFixUp($inode);
    }

    /**
     * 对插入节点的位置及往上的位置进行颜色调整
     * @param $inode 插入的节点
     * @return null
     */
    private function InsertFixUp($inode)
    {
        //情况一：需要调整条件，父节点存在且父节点的颜色是红色
        while (($parent = $inode->parent) != NULL && $parent->IsRed == TRUE) {
            //祖父结点：
            $gparent = $parent->parent;

            //如果父节点是祖父结点的左子结点，下面的else与此相反
            if ($parent == $gparent->left) {
                //叔叔结点
                $uncle = $gparent->right;

                //case1:叔叔结点也是红色
                if ($uncle != NULL && $uncle->IsRed == TRUE) {
                    //将父节点和叔叔结点都涂黑，将祖父结点涂红
                    $parent->IsRed = FALSE;
                    $uncle->IsRed = FALSE;
                    $gparent->IsRed = TRUE;
                    //将新节点指向祖父节点（现在祖父结点变红，可以看作新节点存在）
                    $inode = $gparent;
                    //继续while循环，重新判断
                    continue;   //经过这一步之后，组父节点作为新节点存在（跳到case2）
                }

                //case2:叔叔结点是黑色，且当前结点是右子节点
                if ($inode == $parent->right) {
                    //以父节点作为旋转结点做左旋转处理
                    $this->L_Rotate($parent);
                    //在树中实际上已经转换，但是这里的变量的指向还没交换，
                    //将父节点和字节调换一下，为下面右旋做准备
                    $temp = $parent;
                    $parent = $inode;
                    $inode = $temp;
                }

                //case3:叔叔结点是黑色，而且当前结点是父节点的左子节点
                $parent->IsRed = FALSE;
                $gparent->IsRed = TRUE;
                $this->R_Rotate($gparent);
            } //如果父节点是祖父结点的右子结点，与上面完全相反
            else {
                //叔叔结点
                $uncle = $gparent->left;

                //case1:叔叔结点也是红色
                if ($uncle != NULL && $uncle->IsRed == TRUE) {
                    //将父节点和叔叔结点都涂黑，将祖父结点涂红
                    $parent->IsRed = FALSE;
                    $uncle->IsRed = FALSE;
                    $gparent->IsRed = TRUE;
                    //将新节点指向祖父节点（现在祖父结点变红，可以看作新节点存在）
                    $inode = $gparent;
                    //继续while循环，重新判断
                    continue;   //经过这一步之后，组父节点作为新节点存在（跳到case2）
                }

                //case2:叔叔结点是黑色，且当前结点是左子节点
                if ($inode == $parent->left) {
                    //以父节点作为旋转结点做右旋转处理
                    $this->R_Rotate($parent);
                    //在树中实际上已经转换，但是这里的变量的指向还没交换，
                    //将父节点和字节调换一下，为下面右旋做准备
                    $temp = $parent;
                    $parent = $inode;
                    $inode = $temp;
                }

                //case3:叔叔结点是黑色，而且当前结点是父节点的右子节点
                $parent->IsRed = FALSE;
                $gparent->IsRed = TRUE;
                $this->L_Rotate($gparent);
            }
        }
        //情况二：原树是根节点（父节点为空），则只需将根节点涂黑
        if ($inode == $this->root) {
            $this->root->IsRed = FALSE;
            return;
        }

        //情况三：插入节点的父节点是黑色，则什么也不用做
        if ($inode->parent != NULL && $inode->parent->IsRed == FALSE) {
            return;
        }
    }

    /**
     * （对外）删除指定节点
     * @param $key 删除节点的key值
     * @return null
     */
    function Delete($key)
    {
        if (is_null($this->search($key))) {
            throw new Exception('结点' . $key . "不存在，删除失败！");
        }
        $dnode = $this->search($key);
        if ($dnode->left == NULL || $dnode->right == NULL) { #如果待删除结点无子节点或只有一个子节点，则c = dnode
            $c = $dnode;
        } else { #如果待删除结点有两个子节点，c置为dnode的直接后继，以待最后将待删除结点的值换为其后继的值
            $c = $this->successor($dnode);
        }

        //为了后面颜色处理做准备
        $parent = $c->parent;

        //无论前面情况如何，到最后c只剩下一边子结点
        if ($c->left != NULL) {    //这里不会出现，除非选择的是删除结点的前驱
            $s = $c->left;
        } else {
            $s = $c->right;
        }

        if ($s != NULL) { #将c的子节点的父母结点置为c的父母结点，此处c只可能有1个子节点，因为如果c有两个子节点，则c不可能是dnode的直接后继
            $s->parent = $c->parent;
        }

        if ($c->parent == NULL) { #如果c的父母为空，说明c=dnode是根节点，删除根节点后直接将根节点置为根节点的子节点，此处dnode是根节点，且拥有两个子节点，则c是dnode的后继结点，c的父母就不会为空，就不会进入这个if
            $this->root = $s;
        } else if ($c == $c->parent->left) { #如果c是其父节点的左右子节点，则将c父母的左右子节点置为c的左右子节点
            $c->parent->left = $s;
        } else {
            $c->parent->right = $s;
        }

        $dnode->key = $c->key;

        $node = $s;

        //c的结点颜色是黑色，那么会影响路径上的黑色结点的数量，必须进行调整
        if ($c->IsRed == FALSE) {
            $this->DeleteFixUp($node,$parent);
        }
    }

    /**
     * 删除节点后对接点周围的其他节点进行调整
     * @param $key 删除节点的子节点和父节点
     * @return null
     */
    private function DeleteFixUp($node,$parent)
    {
        //如果待删结点的子节点为红色，直接将子节点涂黑
        if ($node != NULL && $node->IsRed == TRUE) {
            $node->IsRed = FALSE;
            return;
        }


        //如果是根节点，那就直接将根节点置为黑色即可
        while (($node == NULL || $node->IsRed == FALSE) && ($node != $this->root)) {
            //node是父节点的左子节点，下面else与这里相反
            if ($node == $parent->left) {
                $brother = $parent->right;

                //case1:兄弟结点颜色是红色（父节点和兄弟孩子结点都是黑色）
                //将父节点涂红，将兄弟结点涂黑，然后对父节点进行左旋处理（经过这一步，情况转换为兄弟结点颜色为黑色的情况）
                if ($brother->IsRed == TRUE) {
                    $brother->IsRed = FALSE;
                    $parent->IsRed = TRUE;
                    $this->L_Rotate($parent);
                    //将情况转化为其他的情况
                    $brother = $parent->right;  //在左旋处理后，$parent->right指向的是原来兄弟结点的左子节点
                }

                //以下是兄弟结点为黑色的情况

                //case2:兄弟结点是黑色，且兄弟结点的两个子节点都是黑色
                //将兄弟结点涂红，将当前结点指向其父节点，将其父节点指向当前结点的祖父结点。
                if (($brother->left == NULL || $brother->left->IsRed == FALSE) && ($brother->right == NULL || $brother->right->IsRed == FALSE)) {
                    $brother->IsRed = TRUE;
                    $node = $parent;
                    $parent = $node->parent;
                } else {
                    //case3:兄弟结点是黑色，兄弟结点的左子节点是红色，右子节点为黑色
                    //将兄弟结点涂红，将兄弟节点的左子节点涂黑，然后对兄弟结点做右旋处理（经过这一步，情况转换为兄弟结点颜色为黑色，右子节点为红色的情况）
                    if ($brother->right == NULL || $brother->right->IsRed == FALSE) {
                        $brother->IsRed = TRUE;
                        $brother->left->IsRed = FALSE;

                        $this->R_Rotate($brother);
                        //将情况转换为其他情况
                        $brother = $parent->right;
                    }

                    //case4:兄弟结点是黑色，且兄弟结点的右子节点为红色，左子节点为任意颜色
                    //将兄弟节点涂成父节点的颜色，再把父节点涂黑，将兄弟结点的右子节点涂黑，然后对父节点做左旋处理
                    $brother->IsRed = $parent->IsRed;
                    $parent->IsRed = FALSE;

                    $brother->right->IsRed = FALSE;
                    $this->L_Rotate($parent);
                    //到了第四种情况，已经是最基本的情况了，可以直接退出了
                    $node = $this->root;
                    break;
                }
            } //node是父节点的右子节点
            else {
                $brother = $parent->left;

                //case1:兄弟结点颜色是红色（父节点和兄弟孩子结点都是黑色）
                //将父节点涂红，将兄弟结点涂黑，然后对父节点进行右旋处理（经过这一步，情况转换为兄弟结点颜色为黑色的情况）
                if ($brother->IsRed == TRUE) {
                    $brother->IsRed = FALSE;
                    $parent->IsRed = TRUE;
                    $this->R_Rotate($parent);
                    //将情况转化为其他的情况
                    $brother = $parent->left;  //在右旋处理后，$parent->left指向的是原来兄弟结点的右子节点
                }

                //以下是兄弟结点为黑色的情况

                //case2:兄弟结点是黑色，且兄弟结点的两个子节点都是黑色
                //将兄弟结点涂红，将当前结点指向其父节点，将其父节点指向当前结点的祖父结点。
                if (($brother->left == NULL || $brother->left->IsRed == FALSE) && ($brother->right == NULL || $brother->right->IsRed == FALSE)) {
                    $brother->IsRed = TRUE;
                    $node = $parent;
                    $parent = $node->parent;
                } else {
                    //case3:兄弟结点是黑色，兄弟结点的右子节点是红色，左子节点为黑色
                    //将兄弟结点涂红，将兄弟节点的左子节点涂黑，然后对兄弟结点做左旋处理（经过这一步，情况转换为兄弟结点颜色为黑色，右子节点为红色的情况）
                    if ($brother->left == NULL || $brother->left->IsRed == FALSE) {
                        $brother->IsRed = TRUE;
                        $brother->right = FALSE;
                        $this->L_Rotate($brother);
                        //将情况转换为其他情况
                        $brother = $parent->left;
                    }

                    //case4:兄弟结点是黑色，且兄弟结点的左子节点为红色，右子节点为任意颜色
                    //将兄弟节点涂成父节点的颜色，再把父节点涂黑，将兄弟结点的右子节点涂黑，然后对父节点左左旋处理
                    $brother->IsRed = $parent->IsRed;
                    $parent->IsRed = FALSE;
                    $brother->left->IsRed = FALSE;
                    $this->R_Rotate($parent);
                    $node = $this->root;
                    break;
                }
            }
        }
        if ($node != NULL) {
            $this->root->IsRed = FALSE;
        }
    }

    /**
     * （对内）获取树的深度
     * @param $root 根节点
     * @return 树的深度
     */
    private function getdepth($root)
    {
        if ($root == NULL) {
            return 0;
        }
        $dl = $this->getdepth($root->left);

        $dr = $this->getdepth($root->right);

        return ($dl > $dr ? $dl : $dr) + 1;
    }

    /**
     * （对外）获取树的深度
     * @param null
     * @return null
     */
    public function Depth()
    {
        return $this->getdepth($this->root);
    }
}