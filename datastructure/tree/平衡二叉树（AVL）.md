# [PHP二叉树（二）：平衡二叉树（AVL）][0]

 标签： [php][1][二叉树][2][数据结构][3]

2016-10-26 21:43  491人阅读  



版权声明：本文为博主原创文章，未经博主允许不得转载。

关于平衡二叉树的原理网上的资源就挺多的，而且情况有点小复杂，所以在这里我就不再陈述了，直接上代码吧：

```php
<?php
/**
 * author:zhongjin
 * time:2016/10/20 11:53
 * description: 平衡二叉树
 */
//结点
class Node
{
    public $key;
    public $parent;
    public $left;
    public $right;
    public $bf; //平衡因子

    public function __construct($key)
    {
        $this->key = $key;
        $this->parent = NULL;
        $this->left = NULL;
        $this->right = NULL;
        $this->bf = 0;
    }
}

//平衡二叉树
class Avl
{
    public $root;
    const LH = +1;  //左高
    const EH = 0;   //等高
    const RH = -1;  //右高

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
            echo $root->key . "-" . $root->bf . "  ";
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
     * 将以$root为根节点的最小不平衡二叉树做右旋处理
     * @param $root（树或子树）根节点
     * @return null
     */
    private function R_Rotate($root)
    {
        $L = $root->left;
        if (!is_NULL($root->parent)) {
            $P = $root->parent;
            if ($root == $P->left) {
                $P->left = $L;
            } else {
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
        if (!is_NULL($root->parent)) {
            $P = $root->parent;
            if ($root == $P->left) {
                $P->left = $R;
            } else {
                $P->right = $R;
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
     * 对以$root所指结点为根节点的二叉树作左平衡处理
     * @param $root（树或子树）根节点
     * @return null
     */
    public function LeftBalance($root)
    {
        $L = $root->left;
        $L_bf = $L->bf;
        switch ($L_bf) {
            //检查root的左子树的平衡度，并作相应的平衡处理
            case self::LH:    //新结点插入在root的左孩子的左子树上，要做单右旋处理
                $root->bf = $L->bf = self::EH;
                $this->R_Rotate($root);
                break;
            case self::RH:    //新节点插入在root的左孩子的右子树上，要做双旋处理
                $L_r = $L->right;   //root左孩子的右子树根
                $L_r_bf = $L_r->bf;
                //修改root及其左孩子的平衡因子
                switch ($L_r_bf) {
                    case self::LH:
                        $root->bf = self::RH;
                        $L->bf = self::EH;
                        break;
                    case self::EH:
                        $root->bf = $L->bf = self::EH;
                        break;
                    case self::RH:
                        $root->bf = self::EH;
                        $L->bf = self::LH;
                        break;
                }
                $L_r->bf = self::EH;
                //对root的左子树作左平衡处理
                $this->L_Rotate($L);
                //对root作右平衡处理
                $this->R_Rotate($root);
        }
    }

    /**
     * 对以$root所指结点为根节点的二叉树作右平衡处理
     * @param $root（树或子树）根节点
     * @return null
     */
    public function RightBalance($root)
    {
        $R = $root->right;
        $R_bf = $R->bf;
        switch ($R_bf) {
            //检查root的右子树的平衡度，并作相应的平衡处理
            case self::RH:    //新结点插入在root的右孩子的右子树上，要做单左旋处理
                $root->bf = $R->bf = self::EH;
                $this->L_Rotate($root);
                break;
            case self::LH:    //新节点插入在root的右孩子的左子树上，要做双旋处理
                $R_l = $R->left;   //root右孩子的左子树根
                $R_l_bf = $R_l->bf;
                //修改root及其右孩子的平衡因子
                switch ($R_l_bf) {
                    case self::RH:
                        $root->bf = self::LH;
                        $R->bf = self::EH;
                        break;
                    case self::EH:
                        $root->bf = $R->bf = self::EH;
                        break;
                    case self::LH:
                        $root->bf = self::EH;
                        $R->bf = self::RH;
                        break;
                }
                $R_l->bf = self::EH;
                //对root的右子树作右平衡处理
                $this->R_Rotate($R);
                //对root作左平衡处理
                $this->L_Rotate($root);
        }
    }

    /**
     * 查找树中是否存在$key对应的节点
     * @param $key 待搜索数字
     * @return $key对应的节点
     */
    public function search($key)
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
    private function predecessor($x)
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
    private function successor($x)
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
     * （对内）插入结点，如果结点不存在则插入，失去平衡要做平衡处理
     * @param $root 根节点 $key 待插入树的数字
     * @return null
     */
    private function insert_node(&$root, $key)
    {
        //找到了插入的位置，插入新节点
        if (is_null($root)) {
            $root = new Node($key);
            //插入结点成功
            return TRUE;
        } else {
            //在树中已经存在和$key相等的结点
            if ($key == $root->key) {
                //插入节点失败
                return FALSE;
            } //在root的左子树中继续搜索
            elseif ($key < $root->key) {
                //插入左子树失败
                if (!($this->insert_node($root->left, $key))) {
                    //树未长高
                    return FALSE;
                }

                //成功插入,修改平衡因子
                if (is_null($root->left->parent)) {
                    $root->left->parent = $root;
                }

                switch ($root->bf) {
                    //原来左右子树等高，现在左子树增高而树增高
                    case self::EH:
                        $root->bf = self::LH;
                        //树长高
                        return TRUE;
                        break;
                    //原来左子树比右子树高，需要做左平衡处理
                    case self::LH:
                        $this->LeftBalance($root);
                        //平衡后，树并未长高
                        return FALSE;
                        break;
                    //原来右子树比左子树高，现在左右子树等高
                    case self::RH:
                        $root->bf = self::EH;
                        //树并未长高
                        return FALSE;
                        break;
                }
            } //在root的右子树中继续搜索
            else {
                //插入右子树失败
                if (!$this->insert_node($root->right, $key)) {
                    //树未长高
                    return FALSE;
                }
                //成功插入,修改平衡因子
                if (is_null($root->right->parent)) {
                    $root->right->parent = $root;
                }
                switch ($root->bf) {
                    //原来左右子树等高，现在右子树增高而树增高
                    case self::EH:
                        $root->bf = self::RH;
                        //树长高
                        return TRUE;
                        break;
                    //原来左子树比右子树高，现在左右子树等高
                    case self::LH:
                        $root->bf = self::EH;
                        return FALSE;
                        break;
                    //原来右子树比左子树高，要做右平衡处理
                    case self::RH:
                        $this->RightBalance($root);
                        //树并未长高
                        return FALSE;
                        break;
                }
            }
        }
    }

    /**
     * （对外）将$key插入树中
     * @param $key 待插入树的数字
     * @return null
     */
    public function Insert($key)
    {
        $this->insert_node($this->root, $key);
    }

    /**
     * 获取待删除的节点（删除的最终节点）
     * @param $key 待删除的数字
     * @return 最终被删除的节点
     */
    private function get_del_node($key)
    {
        $dnode = $this->search($key);
        if ($dnode == NULL) {
            throw new Exception("结点不存在！");
            return;
        }
        if ($dnode->left == NULL || $dnode->right == NULL) { #如果待删除结点无子节点或只有一个子节点，则c = dnode
            $c = $dnode;
        } else { #如果待删除结点有两个子节点，c置为dnode的直接后继，以待最后将待删除结点的值换为其后继的值
            $c = $this->successor($dnode);
        }

        $dnode->key = $c->key;
        return $c;
    }


    /**
     * （对内）删除指定节点，处理该结点往上结点的平衡因子
     * @param $node 最终该被删除的节点
     * @return null
     */
    private function del_node($node)
    {
        if ($node == $this->root) {
            $this->root = NULL;
            return;
        }

        $current = $node;
        //现在的node只有两种情况，要么只有一个子节点，要么没有子节点
        $P = $current->parent;
        //删除一个结点，第一个父节点的平衡都肯定会发生变化
        $lower = TRUE;
        while ($lower == TRUE && !is_null($P)) {
            //待删除结点是左节点
            if ($current == $P->left) {
                if($current == $node){
                    if (!is_null($current->left)) {
                        $P->left = $current->left;
                    } else {
                        $P->left = $current->left;
                    }
                }
                $P_bf = $P->bf;
                switch ($P_bf) {
                    case self::LH:
                        $P->bf = self::EH;
                        $lower = TRUE;
                        $current = $P;
                        $P = $current->parent;
                        break;
                    case self::EH:
                        $P->bf = self::RH;
                        $lower = FALSE;
                        break;
                    case self::RH:
                        $this->RightBalance($P);
                        $lower = TRUE;
                        $current = $P->parent;
                        $P = $current->parent;
                        break;
                }
            } //右结点
            else {
                if($current == $node){
                    if (!is_null($current->left)) {
                        $P->right = $current->left;
                    } else {
                        $P->right = $current->left;
                    }
                }
                $P_bf = $P->bf;
                switch ($P_bf) {
                    case self::LH:
                        $this->LeftBalance($P);
                        $lower = TRUE;
                        $current = $P->parent;
                        $P = $current->parent;
                        break;
                    case self::EH:
                        $P->bf = self::LH;
                        $lower = FALSE;
                        break;
                    case self::RH:
                        $P->bf = self::LH;
                        $lower = TRUE;
                        $current = $P;
                        $P = $current->parent;
                        break;
                }
            }
        }
    }

    /**
     * （对外）删除指定节点
     * @param $key 删除节点的key值
     * @return null
     */
    public function Delete($key)
    {
        $del_node = $this->get_del_node($key);
        $this->del_node($del_node);
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
```

调试的时候你们可以调用中序遍历来做，我在上一篇博客中提供了[PHP][8]实现的二叉树图形化，有了视觉上的帮助就能更好的帮助我们进行调试，详细大家可以访问我的上一篇博客：[《利用PHP实现二叉树的图形显示》][9]

[0]: http://blog.csdn.net/baidu_30000217/article/details/52938545
[1]: http://www.csdn.net/tag/php
[2]: http://www.csdn.net/tag/%e4%ba%8c%e5%8f%89%e6%a0%91
[3]: http://www.csdn.net/tag/%e6%95%b0%e6%8d%ae%e7%bb%93%e6%9e%84
[8]: http://lib.csdn.net/base/php
[9]: http://blog.csdn.net/baidu_30000217/article/details/52880578