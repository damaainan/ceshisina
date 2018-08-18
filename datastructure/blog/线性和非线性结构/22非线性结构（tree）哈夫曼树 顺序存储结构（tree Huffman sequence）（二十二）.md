# [非线性结构（tree）☆============哈夫曼树 顺序存储结构（tree Huffman sequence）（二十二）][0]

2013-10-08 11:19  39580人阅读  

版权声明：本文为博主原创文章，未经博主允许不得转载。

**哈夫曼树 (Huffman Tree)**  
给定n个权值作为n个叶子结点，构造一棵二叉树，若带权路径长度达到最小，称这样的二叉树为最优二叉树，也称为哈夫曼树(Huffman tree)。

**哈夫曼树（霍夫曼树）又称为最优树.**  
1、路径和路径长度  
在一棵树中，从一个结点往下可以达到的孩子或孙子结点之间的通路，称为路径。通路中分支的数目称为路径长度。若规定根结点的层数为1，则从根结点到第L层结点的路径长度为L-1。  
2、结点的权及带权路径长度  
若将树中结点赋给一个有着某种含义的数值，则这个数值称为该结点的权。结点的带权路径长度为：从根结点到该结点之间的路径长度与该结点的权的乘积。  
3、树的带权路径长度  
树的带权路径长度规定为所有叶子结点的带权路径长度之和，记为WPL。

**哈夫曼树构造过程**  
假设有n个权值，则构造出的哈夫曼树有n个叶子结点。 n个权值分别设为 w1、w2、…、wn，则哈夫曼树的构造规则为：

* (1) 将w1、w2、…，wn看成是有n 棵树的森林(每棵树仅有一个结点)；
* (2) 在森林中选出两个根结点的权值最小的树合并，作为一棵新树的左、右子树，且新树的根结点权值为其左、右子树根结点权值之和；
* (3)从森林中删除选取的两棵树，并将新树加入森林；
* (4)重复(2)、(3)步，直到森林中只剩一棵树为止，该树即为所求得的哈夫曼树。
 ![][10]

构造过程中，需要使用优先级队列，由于优先级队列存储的仅仅只是指针，因此需要使用**伪函数**(操作符operator () )自定义排序。

**函数对象（伪函数）**

实现了一个"()"操作符，这样就允许把这个类像函数一样使用，我们把这样的类称为函数对象，或称做伪函数。可以把实例当作一个函数来使用。  
优点：使用仿函数就像使用一个普通的函数一样，但是它的实现可以访问仿函数中所有的成员变量来进行通行；而普通函数若要通信就只能依靠全局变量了。  
  
函数对象顾名思义，就是在某种方式上表现的象一个函数的对象。典型的，它是指一个类的实例，这个类定义了应用操作符operator()。  
函数对象是比函数更加通用的概念，因为函数对象可以定义跨越多次调用的可持久的部分（类似静态局部变量），同时又能从对象的外面进行初始化和检查（和静态局部变量不同）



    /**
    * ConvertToHuffman
    *
    * @param
    * @return   BOOL
    * @note Convert current binary tree to Huffman tree (binary tree)
            Suppose there are n weights, then construct a Huffman tree with n leaf nodes. n-weights are set to w1, w2, ..., wn, the Huffman tree structure rules:
                (1) The w1, w2, ..., wn as a forest tree with n (each tree has only one node);
                (2) in the forest root weights elect two smallest tree merge as a new tree in the left and right sub-tree, and the new root of the tree weight of its left and right sub-tree root node weights sum;
                (3) Delete selected from the forest two trees and new trees added to the forest;
                (4) Repeat (2), (3) step until only one tree in the forest until the tree is that obtained from Huffman tree.
    * @attention
    */
    template<typename T> BOOL 
    AL_TreeHuffmanSeq<T>::ConvertToHuffman()
    {
        if (TRUE == IsEmpty()) {
            return FALSE;
        }
        if (NULL == m_pRootNode) {
            return FALSE;
        }
        
        //Get Descendant node
        //(1) The w1, w2, ..., wn as a forest tree with n (each tree has only one node);
        AL_ListSeq<AL_TreeNodeBinSeq<T>*> listTreeNodeDescendant;
        if (FALSE == m_pRootNode->GetDescendant(listTreeNodeDescendant)) {
            return FALSE;
        }
        
        AL_QueuePrioritySeq<AL_TreeNodeBinSeq<T>*, AL_TreeNodeBinSeq<T> > cQueuePrioritySeq;
        AL_TreeNodeBinSeq<T>* pTreeNodeDescendant = NULL;
        m_pRootNode->SetLevel(0x00);
        m_pRootNode->RemoveParent();
        m_pRootNode->RemoveLeft();
        m_pRootNode->RemoveRight();
        cQueuePrioritySeq.Push(m_pRootNode);
        for (DWORD dwDescendantCnt=0x00; dwDescendantCnt<listTreeNodeDescendant.Length(); dwDescendantCnt++) {
            if (TRUE == listTreeNodeDescendant.Get(dwDescendantCnt, pTreeNodeDescendant)) {
                if (NULL != pTreeNodeDescendant) {
                    pTreeNodeDescendant->SetLevel(0x00);
                    pTreeNodeDescendant->RemoveParent();
                    pTreeNodeDescendant->RemoveLeft();
                    pTreeNodeDescendant->RemoveRight();
                    cQueuePrioritySeq.Push(pTreeNodeDescendant);
                }
                else {
                    return FALSE;
                }
            }
            else {
                return FALSE;
            }
        }
    
        //Rebuilt Huffman Tree
        m_dwDegree = 0x00;
        m_dwHeight = TREEHUFFMANSEQ_HEIGHTINVALID;
        m_pRootNode = NULL;
        AL_TreeNodeBinSeq<T>* pTreeNodeSmallest = NULL;
        AL_TreeNodeBinSeq<T>* pTreeNodeSmaller = NULL;
    
        while (0x01 < cQueuePrioritySeq.Size()) {
            //loop all, until only one tree in the priority queue 
            //(2) in the forest root weights elect two smallest tree merge as a new tree in the left and right sub-tree, and the new root of the tree weight of its left and right sub-tree root node weights sum;
            if (TRUE == cQueuePrioritySeq.Pop(pTreeNodeSmallest) && TRUE == cQueuePrioritySeq.Pop(pTreeNodeSmaller)){
                if (NULL != pTreeNodeSmallest && NULL != pTreeNodeSmaller) {
                    //rebuilt the root node
                    m_pTreeNode[m_dwUsed].SetLevel(0x00);
                    m_pTreeNode[m_dwUsed].SetWeight(pTreeNodeSmallest->GetWeight() + pTreeNodeSmaller->GetWeight());
                    m_pRootNode = &m_pTreeNode[m_dwUsed];
    
                    if (TRUE == m_pRootNode->InsertLeft(pTreeNodeSmallest) 
                        && TRUE == m_pRootNode->InsertRight(pTreeNodeSmaller)) {
                            //(3) Delete selected from the forest two trees and new trees added to the forest;
                            m_dwUsed++;
                            m_dwNumNodes++;
                            cQueuePrioritySeq.Push(m_pRootNode);
                    }
                    else {
                        return FALSE;
                    }
                }
                else {
                    return FALSE;
                }
            }
            else{
                return FALSE;
            }
            //(4) Repeat (2), (3) step until only one tree in the forest until the tree is that obtained from Huffman tree.
        }
        
        if (FALSE == RecalcDegreeHeight()) {
            return FALSE;
        }
        return TRUE;
    }
    

**知识扩展**

**多叉哈夫曼树**  
   哈夫曼树也可以是k叉的，只是在构造k叉哈夫曼树时需要先进行一些调整。构造哈夫曼树的思想是每次选k个权重最小的元素来合成一个新的元素，该元素权重为k个元素权重之和。但是当k大于2时，按照这个步骤做下去可能到最后剩下的元素少于k个。解决这个问题的办法是假设已经有了一棵哈夫曼树(且为一棵满k叉树)，则可以计算出其叶节点数目为(k-1)nk+1,式子中的nk表示子节点数目为k的节点数目。于是对给定的n个权值构造k叉哈夫曼树时,可以先考虑增加一些权值为0的叶子节点，使得叶子节点总数为(k-1)nk+1这种形式,然后再按照哈夫曼树的方法进行构造即可。

**哈夫曼树应用**

**1、哈夫曼编码**  
   在数据通信中，需要将传送的文字转换成二进制的字符串，用0，1码的不同排列来表示字符。例如，需传送的报文为“AFTER DATA EAR ARE ART AREA”，这里用到的字符集为“A，E，R，T，F，D”，各字母出现的次数为{8，4，5，3，1，1}。现要求为这些字母设计编码。要区别6个字母，最简单的二进制编码方式是等长编码，固定采用3位二进制，可分别用000、001、010、011、100、101对“A，E，R，T，F，D”进行编码发送，当对方接收报文时再按照三位一分进行译码。显然编码的长度取决报文中不同字符的个数。若报文中可能出现26个不同字符，则固定编码长度为5。然而，传送报文时总是希望总长度尽可能短。在实际应用中，各个字符的出现频度或使用次数是不相同的，如A、B、C的使用频率远远高于X、Y、Z，自然会想到设计编码时，让使用频率高的用短码，使用频率低的用长码，以优化整个报文编码。  
为使不等长编码为前缀编码(即要求一个字符的编码不能是另一个字符编码的前缀)，可用字符集中的每个字符作为叶子结点生成一棵编码二叉树，为了获得传送报文的最短长度，可将每个字符的出现频率作为字符结点的权值赋予该结点上，显然字使用频率越小权值越小，权值越小叶子就越靠下，于是频率小编码长，频率高编码短，这样就保证了此树的最小带权路径长度效果上就是传送报文的最短长度。因此，求传送报文的最短长度问题转化为求由字符集中的所有字符作为叶子结点，由字符出现频率作为其权值所产生的哈夫曼树的问题。利用哈夫曼树来设计二进制的前缀编码，既满足前缀编码的条件，又保证报文编码总长最短。  
哈夫曼静态编码：它对需要编码的数据进行两遍扫描：第一遍统计原数据中各字符出现的频率，利用得到的频率值创建哈夫曼树，并必须把树的信息保存起来，即把字符0-255(2^8=256)的频率值以2-4BYTES的长度顺序存储起来，（用4Bytes的长度存储频率值，频率值的表示范围为0--2^32-1，这已足够表示大文件中字符出现的频率了）以便解压时创建同样的哈夫曼树进行解压；第二遍则根据第一遍扫描得到的哈夫曼树进行编码，并把编码后得到的码字存储起来。  
哈夫曼动态编码：动态哈夫曼编码使用一棵动态变化的哈夫曼树，对第t+1个字符的编码是根据原始数据中前t个字符得到的哈夫曼树来进行的，编码和解码使用相同的初始哈夫曼树，每处理完一个字符，编码和解码使用相同的方法修改哈夫曼树，所以没有必要为解码而保存哈夫曼树的信息。编码和解码一个字符所需的时间与该字符的编码长度成正比，所以动态哈夫曼编码可实时进行。

  
**2、哈夫曼译码**  
在通信中，若将字符用哈夫曼编码形式发送出去，对方接收到编码后，将编码还原成字符的过程，称为哈夫曼译码。

**顺序存储结构**

在计算机中用一组地址连续的存储单元依次存储线性表的各个数据元素,称作线性表的顺序存储结构.

  
顺序存储结构是存储结构类型中的一种，该结构是把逻辑上相邻的节点存储在物理位置上相邻的存储单元中，结点之间的逻辑关系由存储单元的邻接关系来体现。由此得到的存储结构为顺序存储结构，通常顺序存储结构是借助于计算机程序设计语言（例如c/c++）的数组来描述的。

  
顺序存储结构的主要优点是节省存储空间，因为分配给数据的存储单元全用存放结点的数据（不考虑c/c++语言中数组需指定大小的情况），结点之间的逻辑关系没有占用额外的存储空间。采用这种方法时，可实现对结点的随机存取，即每一个结点对应一个序号，由该序号可以直接计算出来结点的存储地址。但顺序存储方法的主要缺点是不便于修改，对结点的插入、删除运算时，可能要移动一系列的结点。

**优点：**

随机存取表中元素。缺点：插入和删除操作需要移动元素。




[0]: /xiaoting451292510/article/details/12394617
[10]: ./img/20131008111836343.png
[11]: #