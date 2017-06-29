# [非线性结构（tree）☆============二叉搜索树（二叉查找树） 顺序存储结构（tree Binary Search sequence）（二十四）][0]

2013-10-18 11:52  22724人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

**二叉搜索树（二叉查找树）**  
 二叉查找树（Binary Search Tree），或者是一棵空树，或者是具有下列性质的二叉树： 若它的左子树不空，则左子树上所有结点的值均小于它的根结点的值； 若它的右子树不空，则右子树上所有结点的值均大于它的根结点的值； 它的左、右子树也分别为二叉排序树。

**原理**

 二叉排序树的查找过程和次优二叉树类似，通常采取二叉链表作为二叉排序树的存储结构。中序遍历二叉排序树可得到一个关键字的有序序列，一个无序序列可以通过构造一棵二叉排序树变成一个有序序列，构造树的过程即为对无序序列进行排序的过程。每次插入的新的结点都是二叉排序树上新的叶子结点，在进行插入操作时，不必移动其它结点，只需改动某个结点的指针，由空变为非空即可。搜索,插入,删除的复杂度等于树高，O(log(n)).  
  
**[算法][10]**

**查找算法**  
 在二叉排序树b中查找x的过程为：   
若b是空树，则搜索失败，否则：  
若x等于b的根结点的数据域之值，则查找成功；否则：  
若x小于b的根结点的数据域之值，则搜索左子树；否则：  
查找右子树。



    /**
    * GetNode
    *
    * @param    const AL_TreeNodeBinSearchSeq<T, KEY>* pCurTreeNode <IN>
    * @param    const KEY& tKey <IN> 
    * @return   const AL_TreeNodeBinSearchSeq<T, KEY>* 
    * @note     for Recursion search
    * @attention
    */
    template<typename T, typename KEY> const AL_TreeNodeBinSearchSeq<T, KEY>* 
    AL_TreeBinSearchSeq<T, KEY>::GetNode(const AL_TreeNodeBinSearchSeq<T, KEY>* pCurTreeNode, const KEY& tKey)
    {
        if (NULL == pCurTreeNode) {
            return NULL;
        }
    
        if (tKey < pCurTreeNode->GetKey()) {
            //search the left child
            return GetNode(pCurTreeNode->GetChildLeft(), tKey);
        }
        else if (pCurTreeNode->GetKey() < tKey) {
            //search the right child
            return GetNode(pCurTreeNode->GetChildRight(), tKey);
        }
        else {
            //find it, pCurTreeNode->GetKey() == tKey
            return pCurTreeNode;
        }
        
        //Recursion End
        return NULL;
    }

  
  
 **插入算法**  
 向一个二叉排序树b中插入一个结点s的算法，过程为：   
若b是空树，则将s所指结点作为根结点插入，否则：  
若s->data等于b的根结点的数据域之值，则返回，否则：  
若s->data小于b的根结点的数据域之值，则把s所指结点插入到左子树中，否则：  
把s所指结点插入到右子树中。

* 1.若当前的二叉查找树为空，则插入的元素为根节点，
* 2.若插入的元素值小于根节点值，则将元素插入到左子树中，
* 3.若插入的元素值不小于根节点值，则将元素插入到右子树中。
```
    /**
    * Insert
    *
    * @param    const AL_TreeNodeBinSearchSeq<T, KEY>* pRecursionNode <IN> 
    * @param    const T& tData <IN> 
    * @param    const KEY& tKey <IN> 
    * @return   BOOL
    * @note     for Recursion Insert
    * @attention if pRecursionNode may be NULL
    */
    template<typename T, typename KEY> BOOL 
    AL_TreeBinSearchSeq<T, KEY>::Insert(const AL_TreeNodeBinSearchSeq<T, KEY>* pRecursionNode, const T& tData, const KEY& tKey)
    {
        if (TRUE == IsEmpty()) {
            if (NULL != pRecursionNode) {
                //empty, but has the node
                return FALSE;
            }
            //has no root node, insert as root node
            return InsertAtNode(NULL, 0x00, tData, tKey);
        }
        
        static const AL_TreeNodeBinSearchSeq<T, KEY>* pRecursionNodePre = NULL;     //store the previous node of recursion
        if (NULL == pRecursionNode) {
            if (NULL == pRecursionNodePre) {
                //some thing wrong
                return FALSE;
            }
            //inset to the current tree node
            if (NULL == pRecursionNodePre->GetChildLeft() && NULL == pRecursionNodePre->GetChildRight()) {
                //left and right all NULL
                if (tKey < pRecursionNodePre->GetKey()) {
                    //insert the left child
                    return InsertLeftAtNode(const_cast<AL_TreeNodeBinSearchSeq<T, KEY>*>(pRecursionNodePre), tData, tKey);
                }
                else if (pRecursionNodePre->GetKey() < tKey) {
                    //insert the right child
                    return InsertRightAtNode(const_cast<AL_TreeNodeBinSearchSeq<T, KEY>*>(pRecursionNodePre), tData, tKey);
                }
                else {
                    //error, can not have the same key
                    return FALSE;
                }
            }
            else if (NULL == pRecursionNodePre->GetChildLeft() && NULL != pRecursionNodePre->GetChildRight()) {
                //left NULL, right not NULL
                if (tKey < pRecursionNodePre->GetKey()) {
                    //insert the left child
                    return InsertLeftAtNode(const_cast<AL_TreeNodeBinSearchSeq<T, KEY>*>(pRecursionNodePre), tData, tKey);
                }
                else {
                    //error, can not have the same key
                    return FALSE;
                }
            }
            else if (NULL != pRecursionNodePre->GetChildLeft() && NULL == pRecursionNodePre->GetChildRight()) {
                //left not NULL, right NULL
                if (pRecursionNodePre->GetKey() < tKey) {
                    return InsertRightAtNode(const_cast<AL_TreeNodeBinSearchSeq<T, KEY>*>(pRecursionNodePre), tData, tKey);
                }
                else {
                    return FALSE;
                }
            }
            else {
                //left not NULL, right not NULL
                return FALSE;
            }
        }
        pRecursionNodePre = pRecursionNode;
        if (tKey < pRecursionNode->GetKey()) {
            //recursion the left child (Insert)
            return Insert(pRecursionNode->GetChildLeft(), tData, tKey);
        }
        else if (pRecursionNode->GetKey() < tKey) {
            //recursion the right child (Insert)
            return Insert(pRecursionNode->GetChildRight(), tData, tKey);
        }
        else {
            //error, can not have the same key
            return FALSE;
        }
    
        //Recursion End
        return FALSE;
    }
    
```
  
 **删除算法**  在二叉排序树删去一个结点，分三种情况讨论：  
若*p结点为叶子结点，即PL(左子树)和PR(右子树)均为空树。由于删去叶子结点不破坏整棵树的结构，则只需修改其双亲结点的指针即可。  
若*p结点只有左子树PL或右子树PR，此时只要令PL或PR直接成为其双亲结点*f的左子树或右子树即可，作此修改也不破坏二叉排序树的特性。  
若*p结点的左子树和右子树均不空。在删去*p之后，为保持其它元素之间的相对位置不变，可按中序遍历保持有序进行调整，可以有两种做法：其一是令*p的左子树为*f的左子树，*s为*f左子树的最右下的结点，而*p的右子树为*s的右子树；其二是令*p的直接前驱（或直接后继）替代*p，然后再从二叉排序树中删去它的直接前驱（或直接后继）。

1.p为叶子节点，直接删除该节点，再修改其父节点的指针（注意分是根节点和不是根节点），如图a。  
![][12]

  
  
2.p为单支节点（即只有左子树或右子树）。让p的子树与p的父亲节点相连，删除p即可；（注意分是根节点和不是根节点）；如图b。  
![][13]

  
  
3.p的左子树和右子树均不空。找到p的后继y，因为y一定没有左子树，所以可以删除y，并让y的父亲节点成为y的右子树的父亲节点，并用y的值代替p的值；或者方法二是找到p的前驱x，x一定没有右子树，所以可以删除x，并让x的父亲节点成为y的左子树的父亲节点。如图c。

![][14]



    //insert current node's child to the replace node
            pChildLeft->RemoveParent();
            pChildRight->RemoveParent();
            if (pReplace != pChildLeft) {
                if (FALSE == pReplace->InsertLeft(pChildLeft)) {
                        return FALSE;
                }
            }
            if (pReplace != pChildRight) {
                if (FALSE == pReplace->InsertRight(pChildRight)) {
                    return FALSE;
                }
            }

  
 **ps: 注意替代的结点是否为删除结点的子结点。不然会将自己本身作为自己的子结点插入。**

 **顺序存储结构**

在计算机中用一组地址连续的存储单元依次存储线性表的各个数据元素,称作线性表的顺序存储结构.

  
顺序存储结构是存储结构类型中的一种，该结构是把逻辑上相邻的节点存储在物理位置上相邻的存储单元中，结点之间的逻辑关系由存储单元的邻接关系来体现。由此得到的存储结构为顺序存储结构，通常顺序存储结构是借助于计算机程序设计语言（例如c/c++）的数组来描述的。

  
顺序存储结构的主要优点是节省存储空间，因为分配给数据的存储单元全用存放结点的数据（不考虑c/c++语言中数组需指定大小的情况），结点之间的逻辑关系没有占用额外的存储空间。采用这种方法时，可实现对结点的随机存取，即每一个结点对应一个序号，由该序号可以直接计算出来结点的存储地址。但顺序存储方法的主要缺点是不便于修改，对结点的插入、删除运算时，可能要移动一系列的结点。

**优点：**

随机存取表中元素。缺点：插入和删除操作需要移动元素。




[0]: /xiaoting451292510/article/details/12850061
[10]: http://lib.csdn.net/base/datastructure
[11]: #
[12]: ./img/20131018134836125.png
[13]: ./img/20131018134850343.png
[14]: ./img/20131018134914000.png