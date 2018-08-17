## 树 - （二叉查找树，红黑树，B 树）- BST

虽是读书笔记，但是如转载请注明出处 [http://segmentfault.com/blog/exploring/][0]  
.. 拒绝伸手复制党

关于二叉树的基本知识，可以参见：[Java 实现基本数据结构 2(树)][1]

以下是算法导论第十二章的学习笔记

- - -

### 二叉查找树 BST

查找树是一种数据结构，支持动态集合操作。在二叉查找树上执行基本操作的时间与**树的高度**成正比。对已 n 个节点的完全二叉树，各种操作的最坏情况运行时间O(logn). 但是如果二叉查找树退化成含 n 个节点的线性链，各种操作的最坏情况运行时间O(n)。 一颗随机构造的二叉查找树的操作平均时间是O(logn).

![][2]

#### 性质：

对于任何节点x，其左子树的关键字最大不超过key[x], 其右子树的关键字最小不小于key[x]. 因此可以使用**中序遍历**算法，输出有序的树的所有关键字。

不同的二叉查找树可以表示同一组值。

查找二叉查找树的时间可以在O(h) = O(logn)的时间内完成。

关于二叉树的一些数学性质：  
1. 在二叉树的第 i 层上至多有 2(i-1) 个节点 (i>=1)  
2. 深度为 k 的二叉树至多有 2k-1 个节点  
3. 对于任何一棵二叉树 T，如果其叶子节点数为 n0，度为 2 的节点数为 n2, 则 n0=n2+1.

#### 操作及代码

```java
            // 返回指向包含关键字k的节点的指针
        public TreeNode search(TreeNode root, int k){
            if(root==null){
                return null;
            }
            if(root.val == k)
                return root;
            if(root.val > k){
                return search(root.left,k);
            }
            else
                return search(root.right,k);
        }
    
        //非递归 - 返回指向包含关键字k的节点的指针
        public TreeNode searchIterative(TreeNode root, int k){
            while(root!=null){
                if(root==null || root.val == k){
                    return root;
                    }
                if(root.val>k)
                {
                    root = root.left;
                }
                else
                    root = root.right;
                }
            return root;
        }
        // 返回最小值节点
        public TreeNode minimal(TreeNode root){
            if(root ==null){
                return null;
            }
            while(root.left!=null){
                root = root.left;
            }
            return root;
        }
        // 返回最大值节点
        public TreeNode maximal(TreeNode root){
            if(root ==null){
                return null;
            }
            while(root.right!=null){
                root = root.right;
            }
            return root;
        }
```

##### 查找前驱和后继：

所谓前驱和后继是指，指定元素在所有元素顺序排列模式下的前一个元素或后一个元素。

要获取一个二叉搜索树中指定结点的后继的直观的办法是，找到所有比指定结点大的结点中最小的。根据二叉搜索树的属性，找比某结点大的元素，可以往两个两个方向走：

往右子树方向走，结点右子树的元素都不小于本身；  
往父结点方向走，指定的结点有可能处于其它结点的左子树中。  
当指定结点拥有右子树时，那么其后继必存在于其右子树中。因往父结点方向找到的比指定结点大的元素大于指定结点右子树的所有元素。**如果指定结点没有右孩子呢？那么沿着父结点的方向找到第一个节点的左子树包含指定结点的结点，这个结点就是指定结点的后继。**

```java
    // 寻找前驱后继
        public TreeNode successor(TreeNode root){
            if(root ==null || root.right == null){
                return null;
            }
            // 如果该节点有右孩子，则输出右孩子的最左 -- minimal
            if(root.right != null){
                return minimal(root.right);
            }
            else{
                TreeNode y = root.parent;
                while(y!=null && root == y.right){
                    root = y;
                    y = y.parent;
                }
                return y;
            }
        }
```

##### 插入

插入：从根结点开始，沿树下降。指针 x 跟踪这条路径，而 y 始终指向 x 的父结点。根据 key[z] 与 key[x] 的比较结果，决定向左向右转。直到 x 成为 NIL 为止。这个 NIL 所占位置及我们想插入 z 的地方，y 即为 z 的父结点。

```java
            // 插入
        public TreeNode insert (TreeNode root, TreeNode x){
            TreeNode p = root;
            TreeNode y = new TreeNode();
            if(p==null){
                root = x;
                return root;
            }
            while(p!=null)      
            {    
                if(p.val >= x.val){
                    y = p;
                    p = p.left;
                }
                else{
                    y = p;
                    p =p.right;
                }
            }
            // 树本来没有节点的时候
            x.parent = y;
            if(x.val <= y.val){
                y.left = x;
            }
            else{
                y.right = x;
            }
            return root;
        }
```

##### 删除：

一个规律：如果 BST 的某个节点有两个子女，则其后继没有左子女，其前驱没有右子女。

以指向 z 的指针为参数，考虑三种情况。  
[youtu20 分钟短视频讲解][3]  
1. 若 z 没有子女，则修改其父结点 p[z]，是 NIL 为其子女；  
2. 如果结点 z 只有一个子女，则可以通过在其子结点与父结点之间建立一条链来删除 z;  
3. 如果结点 z 有两个子女，先删除 z 的后继 y（它没有左子女），再用 y 的内容来替代 z 的内容。

![][4]

  
以下是删除操作的伪码：  
注意：伪码中的 TRANSPLANT，只修改 v 与 u 的父亲之间的关系，而不修改与 u 孩子的关系。      

```
TREE-DELETE(T,z)
        if z.left == NIL            
           TRANSPLANT(T,z,z.right)
        else if z.right == NIL       
           TRANSPLANT(T,z,z.left)
        else y = TREE-MINIMUM(z.right)
           if y.p ≠ z              
              TRANSPLANT(T,y,y.right)  
              y.right = z.right
              y.right.p = y
           TRANSPLANT(T,z,y)          
           y.left = z.left       
           y.left.p = y
    TRANSPLANT(T,u,v)
      if u.p == NIL       
           T.root = v
      else if u == u.p.left   
           u.p.left = v
      else u.p.right = v      
      if v ≠ NIL
           v.p = u.p
```

对于高度为h的二叉查找树，动态集合操作 INSERT 和DELETE 的运行时间为 O(h)。

#### 实际用途

[stackoverflow 的解答][5]  
在**搜索**应用中使用，尤其是数据频繁**插入和删除**等等更改操作。比如set和map。

虽然 BST 的操作用数组完全可以实现，但是数组只适合那种 write once, read many times 的操作。  
然而当要进行操作诸如 插入,删除，交换的时候，BST 的性能远远超过了数组。BST 是 node based 数据结构，  
而数组是 contiguous chunk of memory, 即基于连续内存的数据结构，插入,删除，交换要 BST 更好。

举个例子  
**BST 和哈希表有何区别？ 存储手机上的通讯录用哪个数据结构好？**  
哈希表可以O(1)时间进行搜索和插入。  
BST 可以O(nlogn)时间进行搜索和插入。 在这一点 BST 稍慢。  
但是二者最大的区别是**哈希表是一个无序的 DS，while, BSTs 是有序的 DS。**  
当设计手机通讯录这种对内存要求很高的应用时候，需要考虑存储空间而且手机通讯录需要元素有序。哈希表无序，需要额外的空间和时间去排序，而 BST 就不需要额外的空间去  
排序，而且在n<5000条记录的时候，BST 的O(nlogn)足够快。

所以应该采用 BST。

[0]: http://segmentfault.com/blog/exploring/
[1]: http://segmentfault.com/blog/exploring/1190000002606302#articleHeader14
[2]: ./img/bVk8j3.png
[3]: https://www.youtube.com/watch?v=gcULXE7ViZw
[4]: ./img/bVk8kC.png
[5]: https://stackoverflow.com/questions/2130416/what-are-the-applications-of-binary-trees