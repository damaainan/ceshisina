# [6天通吃树结构—— 第三天 Treap树][0]

我们知道，二叉查找树相对来说比较容易形成最坏的链表情况，所以前辈们想尽了各种优化策略，包括AVL，红黑，以及今天

要讲的Treap树。

Treap树算是一种简单的优化策略，这名字大家也能猜到，树和堆的合体，其实原理比较简单，在树中维护一个"优先级“，”优先级“

采用随机数的方法，但是”优先级“必须满足根堆的性质，当然是“大根堆”或者“小根堆”都无所谓，比如下面的一棵树：

![][1]

从树中我们可以看到：

①：节点中的key满足“二叉查找树”。

②：节点中的“优先级”满足小根堆。

一：基本操作

1：定义

 

```csharp
#region Treap树节点
/// <summary>
/// Treap树
/// </summary>
/// <typeparam name="K"></typeparam>
/// <typeparam name="V"></typeparam>
public class TreapNode<K, V>
{
    /// <summary>
    /// 节点元素
    /// </summary>
    public K key;

    /// <summary>
    /// 优先级（采用随机数）
    /// </summary>
    public int priority;

    /// <summary>
    /// 节点中的附加值
    /// </summary>
    public HashSet<V> attach = new HashSet<V>();

    /// <summary>
    /// 左节点
    /// </summary>
    public TreapNode<K, V> left;

    /// <summary>
    /// 右节点
    /// </summary>
    public TreapNode<K, V> right;

    public TreapNode() { }

    public TreapNode(K key, V value, TreapNode<K, V> left, TreapNode<K, V> right)
    {
        //KV键值对
        this.key = key;
        this.priority = new Random(DateTime.Now.Millisecond).Next(0,int.MaxValue);
        this.attach.Add(value);

        this.left = left;
        this.right = right;
    }
}
#endregion
```

节点里面定义了一个priority作为“堆定义”的旋转因子，因子采用“随机数“。

2：添加

首先我们知道各个节点的“优先级”是采用随机数的方法，那么就存在一个问题，当我们插入一个节点后，优先级不满足“堆定义"的

时候我们该怎么办，前辈说此时需要旋转，直到满足堆定义为止。

旋转有两种方式，如果大家玩转了AVL，那么对Treap中的旋转的理解轻而易举。

①： 左左情况旋转

![][2]

从图中可以看出，当我们插入“节点12”的时候，此时“堆性质”遭到破坏，必须进行旋转，我们发现优先级是6<9，所以就要进行

左左情况旋转，最终也就形成了我们需要的结果。

②： 右右情况旋转

![][3]

既然理解了”左左情况旋转“，右右情况也是同样的道理，优先级中发现“6<9"，进行”右右旋转“最终达到我们要的效果。

 

```csharp
#region 添加操作
/// <summary>
/// 添加操作
/// </summary>
/// <param name="key"></param>
/// <param name="value"></param>
public void Add(K key, V value)
{
    node = Add(key, value, node);
}
#endregion

#region 添加操作
/// <summary>
/// 添加操作
/// </summary>
/// <param name="key"></param>
/// <param name="value"></param>
/// <param name="tree"></param>
/// <returns></returns>
public TreapNode<K, V> Add(K key, V value, TreapNode<K, V> tree)
{
    if (tree == null)
        tree = new TreapNode<K, V>(key, value, null, null);

    //左子树
    if (key.CompareTo(tree.key) < 0)
    {
        tree.left = Add(key, value, tree.left);

        //根据小根堆性质，需要”左左情况旋转”
        if (tree.left.priority < tree.priority)
        {
            tree = RotateLL(tree);
        }
    }

    //右子树
    if (key.CompareTo(tree.key) > 0)
    {
        tree.right = Add(key, value, tree.right);

        //根据小根堆性质，需要”右右情况旋转”
        if (tree.right.priority < tree.priority)
        {
            tree = RotateRR(tree);
        }
    }

    //将value追加到附加值中（也可对应重复元素）
    if (key.CompareTo(tree.key) == 0)
        tree.attach.Add(value);

    return tree;
}
#endregion
```

3:删除

跟普通的二叉查找树一样，删除结点存在三种情况。

①：叶子结点

跟普通查找树一样，直接释放本节点即可。

②：单孩子结点

跟普通查找树一样操作。

③：满孩子结点

其实在treap中删除满孩子结点有两种方式。

第一种：跟普通的二叉查找树一样，找到“右子树”的最左结点（15），拷贝元素的值，但不拷贝元素的优先级，然后在右子树中

删除“结点15”即可，最终效果如下图。

![][4]

第二种：将”结点下旋“，直到该节点不是”满孩子的情况“，该赋null的赋null，该将孩子结点顶上的就顶上，如下图：

![][5]

当然从理论上来说，第二种删除方法更合理，这里我写的就是第二种情况的代码。

 

```csharp
#region 删除当前树中的节点
/// <summary>
/// 删除当前树中的节点
/// </summary>
/// <param name="key"></param>
/// <returns></returns>
public void Remove(K key, V value)
{
    node = Remove(key, value, node);
}
#endregion

#region 删除当前树中的节点
/// <summary>
/// 删除当前树中的节点
/// </summary>
/// <param name="key"></param>
/// <param name="tree"></param>
/// <returns></returns>
public TreapNode<K, V> Remove(K key, V value, TreapNode<K, V> tree)
{
    if (tree == null)
        return null;

    //左子树
    if (key.CompareTo(tree.key) < 0)
    {
        tree.left = Remove(key, value, tree.left);
    }
    //右子树
    if (key.CompareTo(tree.key) > 0)
    {
        tree.right = Remove(key, value, tree.right);
    }
    /*相等的情况*/
    if (key.CompareTo(tree.key) == 0)
    {
        //判断里面的HashSet是否有多值
        if (tree.attach.Count > 1)
        {
            //实现惰性删除
            tree.attach.Remove(value);
        }
        else
        {
            //有两个孩子的情况
            if (tree.left != null && tree.right != null)
            {
                //如果左孩子的优先级低就需要“左旋”
                if (tree.left.priority < tree.right.priority)
                {
                    tree = RotateLL(tree);
                }
                else
                {
                    //否则“右旋”
                    tree = RotateRR(tree);
                }

                //继续旋转
                tree = Remove(key, value, tree);
            }
            else
            {
                //如果旋转后已经变成了叶子节点则直接删除
                if (tree == null)
                    return null;

                //最后就是单支树
                tree = tree.left == null ? tree.right : tree.left;
            }
        }
    }

    return tree;
}
#endregion
```

4:总结

treap树在CURD中是期望的logN，由于我们加了”优先级“,所以会出现”链表“的情况几乎不存在，但是他的Add和Remove相比严格的

平衡二叉树有更少的旋转操作，可以说性能是在”普通二叉树“和”平衡二叉树“之间。

最后是总运行代码，不过这里我就不做测试了。


```csharp
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace DataStruct
{
    #region Treap树节点
    /// <summary>
    /// Treap树
    /// </summary>
    /// <typeparam name="K"></typeparam>
    /// <typeparam name="V"></typeparam>
    public class TreapNode<K, V>
    {
        /// <summary>
        /// 节点元素
        /// </summary>
        public K key;

        /// <summary>
        /// 优先级（采用随机数）
        /// </summary>
        public int priority;

        /// <summary>
        /// 节点中的附加值
        /// </summary>
        public HashSet<V> attach = new HashSet<V>();

        /// <summary>
        /// 左节点
        /// </summary>
        public TreapNode<K, V> left;

        /// <summary>
        /// 右节点
        /// </summary>
        public TreapNode<K, V> right;

        public TreapNode() { }

        public TreapNode(K key, V value, TreapNode<K, V> left, TreapNode<K, V> right)
        {
            //KV键值对
            this.key = key;
            this.priority = new Random(DateTime.Now.Millisecond).Next(0,int.MaxValue);
            this.attach.Add(value);

            this.left = left;
            this.right = right;
        }
    }
    #endregion

    public class TreapTree<K, V> where K : IComparable
    {
        public TreapNode<K, V> node = null;

        #region 添加操作
        /// <summary>
        /// 添加操作
        /// </summary>
        /// <param name="key"></param>
        /// <param name="value"></param>
        public void Add(K key, V value)
        {
            node = Add(key, value, node);
        }
        #endregion

        #region 添加操作
        /// <summary>
        /// 添加操作
        /// </summary>
        /// <param name="key"></param>
        /// <param name="value"></param>
        /// <param name="tree"></param>
        /// <returns></returns>
        public TreapNode<K, V> Add(K key, V value, TreapNode<K, V> tree)
        {
            if (tree == null)
                tree = new TreapNode<K, V>(key, value, null, null);

            //左子树
            if (key.CompareTo(tree.key) < 0)
            {
                tree.left = Add(key, value, tree.left);

                //根据小根堆性质，需要”左左情况旋转”
                if (tree.left.priority < tree.priority)
                {
                    tree = RotateLL(tree);
                }
            }

            //右子树
            if (key.CompareTo(tree.key) > 0)
            {
                tree.right = Add(key, value, tree.right);

                //根据小根堆性质，需要”右右情况旋转”
                if (tree.right.priority < tree.priority)
                {
                    tree = RotateRR(tree);
                }
            }

            //将value追加到附加值中（也可对应重复元素）
            if (key.CompareTo(tree.key) == 0)
                tree.attach.Add(value);

            return tree;
        }
        #endregion

        #region 第一种：左左旋转（单旋转）
        /// <summary>
        /// 第一种：左左旋转（单旋转）
        /// </summary>
        /// <param name="node"></param>
        /// <returns></returns>
        public TreapNode<K, V> RotateLL(TreapNode<K, V> node)
        {
            //top：需要作为顶级节点的元素
            var top = node.left;

            //先截断当前节点的左孩子
            node.left = top.right;

            //将当前节点作为temp的右孩子
            top.right = node;

            return top;
        }
        #endregion

        #region 第二种：右右旋转（单旋转）
        /// <summary>
        /// 第二种：右右旋转（单旋转）
        /// </summary>
        /// <param name="node"></param>
        /// <returns></returns>
        public TreapNode<K, V> RotateRR(TreapNode<K, V> node)
        {
            //top：需要作为顶级节点的元素
            var top = node.right;

            //先截断当前节点的右孩子
            node.right = top.left;

            //将当前节点作为temp的右孩子
            top.left = node;

            return top;
        }
        #endregion

        #region 树的指定范围查找
        /// <summary>
        /// 树的指定范围查找
        /// </summary>
        /// <param name="min"></param>
        /// <param name="max"></param>
        /// <returns></returns>
        public HashSet<V> SearchRange(K min, K max)
        {
            HashSet<V> hashSet = new HashSet<V>();

            hashSet = SearchRange(min, max, hashSet, node);

            return hashSet;
        }
        #endregion

        #region 树的指定范围查找
        /// <summary>
        /// 树的指定范围查找
        /// </summary>
        /// <param name="range1"></param>
        /// <param name="range2"></param>
        /// <param name="tree"></param>
        /// <returns></returns>
        public HashSet<V> SearchRange(K min, K max, HashSet<V> hashSet, TreapNode<K, V> tree)
        {
            if (tree == null)
                return hashSet;

            //遍历左子树（寻找下界）
            if (min.CompareTo(tree.key) < 0)
                SearchRange(min, max, hashSet, tree.left);

            //当前节点是否在选定范围内
            if (min.CompareTo(tree.key) <= 0 && max.CompareTo(tree.key) >= 0)
            {
                //等于这种情况
                foreach (var item in tree.attach)
                    hashSet.Add(item);
            }

            //遍历右子树（两种情况：①:找min的下限 ②：必须在Max范围之内）
            if (min.CompareTo(tree.key) > 0 || max.CompareTo(tree.key) > 0)
                SearchRange(min, max, hashSet, tree.right);

            return hashSet;
        }
        #endregion

        #region 找到当前树的最小节点
        /// <summary>
        /// 找到当前树的最小节点
        /// </summary>
        /// <returns></returns>
        public TreapNode<K, V> FindMin()
        {
            return FindMin(node);
        }
        #endregion

        #region 找到当前树的最小节点
        /// <summary>
        /// 找到当前树的最小节点
        /// </summary>
        /// <param name="tree"></param>
        /// <returns></returns>
        public TreapNode<K, V> FindMin(TreapNode<K, V> tree)
        {
            if (tree == null)
                return null;

            if (tree.left == null)
                return tree;

            return FindMin(tree.left);
        }
        #endregion

        #region 找到当前树的最大节点
        /// <summary>
        /// 找到当前树的最大节点
        /// </summary>
        /// <returns></returns>
        public TreapNode<K, V> FindMax()
        {
            return FindMin(node);
        }
        #endregion

        #region 找到当前树的最大节点
        /// <summary>
        /// 找到当前树的最大节点
        /// </summary>
        /// <param name="tree"></param>
        /// <returns></returns>
        public TreapNode<K, V> FindMax(TreapNode<K, V> tree)
        {
            if (tree == null)
                return null;

            if (tree.right == null)
                return tree;

            return FindMax(tree.right);
        }
        #endregion

        #region 删除当前树中的节点
        /// <summary>
        /// 删除当前树中的节点
        /// </summary>
        /// <param name="key"></param>
        /// <returns></returns>
        public void Remove(K key, V value)
        {
            node = Remove(key, value, node);
        }
        #endregion

        #region 删除当前树中的节点
        /// <summary>
        /// 删除当前树中的节点
        /// </summary>
        /// <param name="key"></param>
        /// <param name="tree"></param>
        /// <returns></returns>
        public TreapNode<K, V> Remove(K key, V value, TreapNode<K, V> tree)
        {
            if (tree == null)
                return null;

            //左子树
            if (key.CompareTo(tree.key) < 0)
            {
                tree.left = Remove(key, value, tree.left);
            }
            //右子树
            if (key.CompareTo(tree.key) > 0)
            {
                tree.right = Remove(key, value, tree.right);
            }
            /*相等的情况*/
            if (key.CompareTo(tree.key) == 0)
            {
                //判断里面的HashSet是否有多值
                if (tree.attach.Count > 1)
                {
                    //实现惰性删除
                    tree.attach.Remove(value);
                }
                else
                {
                    //有两个孩子的情况
                    if (tree.left != null && tree.right != null)
                    {
                        //如果左孩子的优先级低就需要“左旋”
                        if (tree.left.priority < tree.right.priority)
                        {
                            tree = RotateLL(tree);
                        }
                        else
                        {
                            //否则“右旋”
                            tree = RotateRR(tree);
                        }

                        //继续旋转
                        tree = Remove(key, value, tree);
                    }
                    else
                    {
                        //如果旋转后已经变成了叶子节点则直接删除
                        if (tree == null)
                            return null;

                        //最后就是单支树
                        tree = tree.left == null ? tree.right : tree.left;
                    }
                }
            }

            return tree;
        }
        #endregion
    }
}
```
[0]: http://www.cnblogs.com/huangxincheng/archive/2012/07/30/2614484.html
[1]: ./img/2012073000522678.png
[2]: ./img/2012073001112626.png
[3]: ./img/2012073001162452.png
[4]: ./img/2012073001332339.png
[5]: ./img/2012073001450711.png