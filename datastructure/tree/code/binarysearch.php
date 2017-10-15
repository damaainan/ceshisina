<?php
//bst.php 文件
/**
 * author:zhongjin
 * time:2016/10/20 11:53
 * description: 二叉查找树
 */
//结点
class Node
{
    public $key;
    public $parent;
    public $left;
    public $right;

    public function __construct($key)
    {
        $this->key = $key;
        $this->parent = NULL;
        $this->left = NULL;
        $this->right = NULL;
    }
}

//二叉搜索树
class Bst
{
    public $root;

    /**
     * 初始化树结构
     * @param $arr 初始化树结构的数组
     * @return null
     */
    public function init($arr)
    {
        $this->root = new Node($arr[0]);
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
            echo $root->key . " ";
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
        return $current;
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
        if ($x->right != NULL) {
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
    function Insert($key)
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
        //return $root;
    }

    /**
     * 在树中删除$key对应的节点
     * @param $key 待删除节点的数字
     * @return null
     */
    function Delete($key)
    {
        if (is_null($this->search($key))) {
            throw new Exception('结点' . $key . "不存在，删除失败！");
        }
        $root = $this->root;
        $dnode = $this->search($key);
        if ($dnode->left == NULL || $dnode->right == NULL) { #如果待删除结点无子节点或只有一个子节点，则c = dnode
            $c = $dnode;
        } else { #如果待删除结点有两个子节点，c置为dnode的直接后继，以待最后将待删除结点的值换为其后继的值
            $c = $this->successor($dnode);
        }

        //无论前面情况如何，到最后c只剩下一边子结点
        if ($c->left != NULL) {
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

        #如果c!=dnode，说明c是dnode的后继结点，交换c和dnode的key值
        if ($c != $dnode) {
            $dnode->key = $c->key;
        }

        #返回根节点
//        return $root;
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