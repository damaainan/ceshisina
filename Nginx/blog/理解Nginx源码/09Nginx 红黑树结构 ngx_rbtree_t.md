### 概述

       有关红黑树的基础知识在前面文章中已经做了介绍，想要更详细的了解红黑树可以参考文章《[数据结构-红黑树](http://blog.csdn.net/chenhanzhun/article/details/38405041)》，在这里只是单纯对 Nginx 中红黑树源码的解析，Nginx 红黑树源码是实现跟算法导论中的讲解是一样的。

### 红黑树结构

```c
typedef ngx_uint_t  ngx_rbtree_key_t;
typedef ngx_int_t   ngx_rbtree_key_int_t;

/* 红黑树节点结构 */
typedef struct ngx_rbtree_node_s  ngx_rbtree_node_t;

struct ngx_rbtree_node_s {
    ngx_rbtree_key_t       key;     /* 节点的键值 */
    ngx_rbtree_node_t     *left;    /* 节点的左孩子 */
    ngx_rbtree_node_t     *right;   /* 节点的右孩子 */
    ngx_rbtree_node_t     *parent;  /* 节点的父亲 */
    u_char                 color;   /* 节点的颜色 */
    u_char                 data;    /* */
};

typedef struct ngx_rbtree_s  ngx_rbtree_t;

typedef void (*ngx_rbtree_insert_pt) (ngx_rbtree_node_t *root,
    ngx_rbtree_node_t *node, ngx_rbtree_node_t *sentinel);

/* 红黑树结构 */
struct ngx_rbtree_s {
    ngx_rbtree_node_t     *root;    /* 指向树的根节点 */
    ngx_rbtree_node_t     *sentinel;/* 指向树的叶子节点NIL */
    ngx_rbtree_insert_pt   insert;  /* 添加元素节点的函数指针，解决具有相同键值，但不同颜色节点的冲突问题；
                                     * 该函数指针决定新节点的行为是新增还是替换原始某个节点*/
};

```
  

### 红黑树的操作

#### 初始化操作

```c
/* 给节点着色，1表示红色，0表示黑色  */
#define ngx_rbt_red(node)               ((node)->color = 1)
#define ngx_rbt_black(node)             ((node)->color = 0)
/* 判断节点的颜色 */
#define ngx_rbt_is_red(node)            ((node)->color)
#define ngx_rbt_is_black(node)          (!ngx_rbt_is_red(node))
/* 复制某个节点的颜色 */
#define ngx_rbt_copy_color(n1, n2)      (n1->color = n2->color)

/* 节点着黑色的宏定义 */
/* a sentinel must be black */

#define ngx_rbtree_sentinel_init(node)  ngx_rbt_black(node)

/* 初始化红黑树，即为空的红黑树 */
/* tree 是指向红黑树的指针，
 * s 是红黑树的一个NIL节点，
 * i 表示函数指针，决定节点是新增还是替换
 */
#define ngx_rbtree_init(tree, s, i)                                           \
    ngx_rbtree_sentinel_init(s);                                              \
    (tree)->root = s;                                                         \
    (tree)->sentinel = s;                                                     \
    (tree)->insert = i

```
  

#### 旋转操作

```c
/* 左旋转操作 */
static ngx_inline void
ngx_rbtree_left_rotate(ngx_rbtree_node_t **root, ngx_rbtree_node_t *sentinel,
    ngx_rbtree_node_t *node)
{
    ngx_rbtree_node_t  *temp;

    temp = node->right;/* temp为node节点的右孩子 */
    node->right = temp->left;/* 设置node节点的右孩子为temp的左孩子 */

    if (temp->left != sentinel) {
        temp->left->parent = node;
    }

    temp->parent = node->parent;

    if (node == *root) {
        *root = temp;

    } else if (node == node->parent->left) {
        node->parent->left = temp;

    } else {
        node->parent->right = temp;
    }

    temp->left = node;
    node->parent = temp;
}

static ngx_inline void
ngx_rbtree_right_rotate(ngx_rbtree_node_t **root, ngx_rbtree_node_t *sentinel,
    ngx_rbtree_node_t *node)
{
    ngx_rbtree_node_t  *temp;

    temp = node->left;
    node->left = temp->right;

    if (temp->right != sentinel) {
        temp->right->parent = node;
    }

    temp->parent = node->parent;

    if (node == *root) {
        *root = temp;

    } else if (node == node->parent->right) {
        node->parent->right = temp;

    } else {
        node->parent->left = temp;
    }

    temp->right = node;
    node->parent = temp;
}

```
  

#### 插入操作

```c
/* 获取红黑树键值最小的节点 */
static ngx_inline ngx_rbtree_node_t *
ngx_rbtree_min(ngx_rbtree_node_t *node, ngx_rbtree_node_t *sentinel)
{
    while (node->left != sentinel) {
        node = node->left;
    }

    return node;
}

/* 插入节点 */
/* 插入节点的步骤：
 * 1、首先按照二叉查找树的插入操作插入新节点；
 * 2、然后把新节点着色为红色（避免破坏红黑树性质5）；
 * 3、为维持红黑树的性质，调整红黑树的节点（着色并旋转），使其满足红黑树的性质；
 */
void
ngx_rbtree_insert(ngx_thread_volatile ngx_rbtree_t *tree,
    ngx_rbtree_node_t *node)
{
    ngx_rbtree_node_t  **root, *temp, *sentinel;

    /* a binary tree insert */

    root = (ngx_rbtree_node_t **) &amp;tree->root;
    sentinel = tree->sentinel;

    /* 若红黑树为空，则比较简单，把新节点作为根节点，
     * 并初始化该节点使其满足红黑树性质
     */
    if (*root == sentinel) {
        node->parent = NULL;
        node->left = sentinel;
        node->right = sentinel;
        ngx_rbt_black(node);
        *root = node;

        return;
    }

    /* 若红黑树不为空，则按照二叉查找树的插入操作进行
     * 该操作由函数指针提供
     */
    tree->insert(*root, node, sentinel);

    /* re-balance tree */

    /* 调整红黑树，使其满足性质，
     * 其实这里只是破坏了性质4：若一个节点是红色，则孩子节点都为黑色；
     * 若破坏了性质4，则新节点 node 及其父亲节点 node->parent 都为红色；
     */
    while (node != *root &amp;&amp; ngx_rbt_is_red(node->parent)) {

        /* 若node的父亲节点是其祖父节点的左孩子 */
        if (node->parent == node->parent->parent->left) {
            temp = node->parent->parent->right;/* temp节点为node的叔叔节点 */

            /* case1：node的叔叔节点是红色 */
            /* 此时，node的父亲及叔叔节点都为红色；
             * 解决办法：将node的父亲及叔叔节点着色为黑色，将node祖父节点着色为红色；
             * 然后沿着祖父节点向上判断是否会破会红黑树的性质；
             */
            if (ngx_rbt_is_red(temp)) {
                ngx_rbt_black(node->parent);
                ngx_rbt_black(temp);
                ngx_rbt_red(node->parent->parent);
                node = node->parent->parent;

            } else {
                /* case2：node的叔叔节点是黑色且node是父亲节点的右孩子 */
                /* 则此时，以node父亲节点进行左旋转，使case2转变为case3；
                 */
                if (node == node->parent->right) {
                    node = node->parent;
                    ngx_rbtree_left_rotate(root, sentinel, node);
                }

                /* case3：node的叔叔节点是黑色且node是父亲节点的左孩子 */
                /* 首先，将node的父亲节点着色为黑色，祖父节点着色为红色；
                 * 然后以祖父节点进行一次右旋转；
                 */
                ngx_rbt_black(node->parent);
                ngx_rbt_red(node->parent->parent);
                ngx_rbtree_right_rotate(root, sentinel, node->parent->parent);
            }

        } else {/* 若node的父亲节点是其祖父节点的右孩子 */
            /* 这里跟上面的情况是对称的，就不再进行讲解了
             */
            temp = node->parent->parent->left;

            if (ngx_rbt_is_red(temp)) {
                ngx_rbt_black(node->parent);
                ngx_rbt_black(temp);
                ngx_rbt_red(node->parent->parent);
                node = node->parent->parent;

            } else {
                if (node == node->parent->left) {
                    node = node->parent;
                    ngx_rbtree_right_rotate(root, sentinel, node);
                }

                ngx_rbt_black(node->parent);
                ngx_rbt_red(node->parent->parent);
                ngx_rbtree_left_rotate(root, sentinel, node->parent->parent);
            }
        }
    }

    /* 根节点必须为黑色 */
    ngx_rbt_black(*root);
}

/* 这里只是将节点插入到红黑树中，并没有判断是否满足红黑树的性质；
 * 类似于二叉查找树的插入操作，这个函数为红黑树插入操作的函数指针；
 */
void
ngx_rbtree_insert_value(ngx_rbtree_node_t *temp, ngx_rbtree_node_t *node,
    ngx_rbtree_node_t *sentinel)
{
    ngx_rbtree_node_t  **p;

    for ( ;; ) {

        /* 判断node节点键值与temp节点键值的大小，以决定node插入到temp节点的左子树还是右子树 */
        p = (node->key < temp->key) ? &amp;temp->left : &amp;temp->right;

        if (*p == sentinel) {
            break;
        }

        temp = *p;
    }

    /* 初始化node节点，并着色为红色 */
    *p = node;
    node->parent = temp;
    node->left = sentinel;
    node->right = sentinel;
    ngx_rbt_red(node);
}

void
ngx_rbtree_insert_timer_value(ngx_rbtree_node_t *temp, ngx_rbtree_node_t *node,
    ngx_rbtree_node_t *sentinel)
{
    ngx_rbtree_node_t  **p;

    for ( ;; ) {

        /*
         * Timer values
         * 1) are spread in small range, usually several minutes,
         * 2) and overflow each 49 days, if milliseconds are stored in 32 bits.
         * The comparison takes into account that overflow.
         */

        /*  node->key < temp->key */

        p = ((ngx_rbtree_key_int_t) (node->key - temp->key) < 0)
            ? &amp;temp->left : &amp;temp->right;

        if (*p == sentinel) {
            break;
        }

        temp = *p;
    }

    *p = node;
    node->parent = temp;
    node->left = sentinel;
    node->right = sentinel;
    ngx_rbt_red(node);
}

```
  

#### 删除操作

```c
/* 删除节点 */
void
ngx_rbtree_delete(ngx_thread_volatile ngx_rbtree_t *tree,
    ngx_rbtree_node_t *node)
{
    ngx_uint_t           red;
    ngx_rbtree_node_t  **root, *sentinel, *subst, *temp, *w;

    /* a binary tree delete */

    root = (ngx_rbtree_node_t **) &amp;tree->root;
    sentinel = tree->sentinel;

    /* 下面是获取temp节点值，temp保存的节点是准备替换节点node ；
     * subst是保存要被替换的节点的后继节点；
     */

    /* case1：若node节点没有左孩子（这里包含了存在或不存在右孩子的情况）*/
    if (node->left == sentinel) {
        temp = node->right;
        subst = node;

    } else if (node->right == sentinel) {/* case2：node节点存在左孩子，但是不存在右孩子 */
        temp = node->left;
        subst = node;

    } else {/* case3：node节点既有左孩子，又有右孩子 */
        subst = ngx_rbtree_min(node->right, sentinel);/* 获取node节点的后续节点 */

        if (subst->left != sentinel) {
            temp = subst->left;
        } else {
            temp = subst->right;
        }
    }

    /* 若被替换的节点subst是根节点，则temp直接替换subst称为根节点 */
    if (subst == *root) {
        *root = temp;
        ngx_rbt_black(temp);

        /* DEBUG stuff */
        node->left = NULL;
        node->right = NULL;
        node->parent = NULL;
        node->key = 0;

        return;
    }

    /* red记录subst节点的颜色 */
    red = ngx_rbt_is_red(subst);

    /* temp节点替换subst 节点 */
    if (subst == subst->parent->left) {
        subst->parent->left = temp;

    } else {
        subst->parent->right = temp;
    }

    /* 根据subst是否为node节点进行处理 */
    if (subst == node) {

        temp->parent = subst->parent;

    } else {

        if (subst->parent == node) {
            temp->parent = subst;

        } else {
            temp->parent = subst->parent;
        }

        /* 复制node节点属性 */
        subst->left = node->left;
        subst->right = node->right;
        subst->parent = node->parent;
        ngx_rbt_copy_color(subst, node);

        if (node == *root) {
            *root = subst;

        } else {
            if (node == node->parent->left) {
                node->parent->left = subst;
            } else {
                node->parent->right = subst;
            }
        }

        if (subst->left != sentinel) {
            subst->left->parent = subst;
        }

        if (subst->right != sentinel) {
            subst->right->parent = subst;
        }
    }

    /* DEBUG stuff */
    node->left = NULL;
    node->right = NULL;
    node->parent = NULL;
    node->key = 0;

    if (red) {
        return;
    }

    /* 下面开始是调整红黑树的性质 */
    /* a delete fixup */

    /* 根据temp节点进行处理 ，若temp不是根节点且为黑色 */
    while (temp != *root &amp;&amp; ngx_rbt_is_black(temp)) {

        /* 若temp是其父亲节点的左孩子 */
        if (temp == temp->parent->left) {
            w = temp->parent->right;/* w为temp的兄弟节点 */

            /* case A：temp兄弟节点为红色 */
            /* 解决办法：
             * 1、改变w节点及temp父亲节点的颜色；
             * 2、对temp父亲节的做一次左旋转，此时，temp的兄弟节点是旋转之前w的某个子节点，该子节点颜色为黑色；
             * 3、此时，case A已经转换为case B、case C 或 case D；
             */
            if (ngx_rbt_is_red(w)) {
                ngx_rbt_black(w);
                ngx_rbt_red(temp->parent);
                ngx_rbtree_left_rotate(root, sentinel, temp->parent);
                w = temp->parent->right;
            }

            /* case B：temp的兄弟节点w是黑色，且w的两个子节点都是黑色 */
            /* 解决办法：
             * 1、改变w节点的颜色；
             * 2、把temp的父亲节点作为新的temp节点；
             */
            if (ngx_rbt_is_black(w->left) &amp;&amp; ngx_rbt_is_black(w->right)) {
                ngx_rbt_red(w);
                temp = temp->parent;

            } else {/* case C：temp的兄弟节点是黑色，且w的左孩子是红色，右孩子是黑色 */
                /* 解决办法：
                 * 1、将改变w及其左孩子的颜色；
                 * 2、对w节点进行一次右旋转；
                 * 3、此时，temp新的兄弟节点w有着一个红色右孩子的黑色节点，转为case D；
                 */
                if (ngx_rbt_is_black(w->right)) {
                    ngx_rbt_black(w->left);
                    ngx_rbt_red(w);
                    ngx_rbtree_right_rotate(root, sentinel, w);
                    w = temp->parent->right;
                }

                /* case D：temp的兄弟节点w为黑色，且w的右孩子为红色 */
                /* 解决办法：
                 * 1、将w节点设置为temp父亲节点的颜色，temp父亲节点设置为黑色；
                 * 2、w的右孩子设置为黑色；
                 * 3、对temp的父亲节点做一次左旋转；
                 * 4、最后把根节点root设置为temp节点；*/
                ngx_rbt_copy_color(w, temp->parent);
                ngx_rbt_black(temp->parent);
                ngx_rbt_black(w->right);
                ngx_rbtree_left_rotate(root, sentinel, temp->parent);
                temp = *root;
            }

        } else {/* 这里针对的是temp节点为其父亲节点的左孩子的情况 */
            w = temp->parent->left;

            if (ngx_rbt_is_red(w)) {
                ngx_rbt_black(w);
                ngx_rbt_red(temp->parent);
                ngx_rbtree_right_rotate(root, sentinel, temp->parent);
                w = temp->parent->left;
            }

            if (ngx_rbt_is_black(w->left) &amp;&amp; ngx_rbt_is_black(w->right)) {
                ngx_rbt_red(w);
                temp = temp->parent;

            } else {
                if (ngx_rbt_is_black(w->left)) {
                    ngx_rbt_black(w->right);
                    ngx_rbt_red(w);
                    ngx_rbtree_left_rotate(root, sentinel, w);
                    w = temp->parent->left;
                }

                ngx_rbt_copy_color(w, temp->parent);
                ngx_rbt_black(temp->parent);
                ngx_rbt_black(w->left);
                ngx_rbtree_right_rotate(root, sentinel, temp->parent);
                temp = *root;
            }
        }
    }

    ngx_rbt_black(temp);
}

```