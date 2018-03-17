## 邻接表有向图(三)之 Java详解

来源：[http://www.cnblogs.com/skywang12345/p/3707626.html](http://www.cnblogs.com/skywang12345/p/3707626.html)

2014-05-13 00:06


前面分别介绍了邻接表有向图的C和C++实现，本文通过Java实现邻接表有向图。

 **`目录`**  
 **`1`** . [邻接表有向图的介绍][100] 
 **`2`** . [邻接表有向图的代码说明][101] 
 **`3`** . [邻接表有向图的完整源码][102]

转载请注明出处：[http://www.cnblogs.com/skywang12345/][103]

更多内容：[数据结构与算法系列 目录][104]


 

<a name="anchor1"></a>

### **`邻接表有向图的介绍 `** 

邻接表有向图是指通过邻接表表示的有向图。

![][0]

上面的图G2包含了"A,B,C,D,E,F,G"共7个顶点，而且包含了"<A,B>,<B,C>,<B,E>,<B,F>,<C,E>,<D,C>,<E,B>,<E,D>,<F,G>"共9条边。

上图右边的矩阵是G2在内存中的邻接表示意图。每一个顶点都包含一条链表，该链表记录了"该顶点所对应的出边的另一个顶点的序号"。例如，第1个顶点(顶点B)包含的链表所包含的节点的数据分别是"2,4,5"；而这"2,4,5"分别对应"C,E,F"的序号，"C,E,F"都属于B的出边的另一个顶点。

<a name="anchor2"></a>

### **`邻接表有向图的代码说明 `** 

 **`1. 基本定义 `** 



```java
public class ListDG {
    // 邻接表中表对应的链表的顶点
    private class ENode {
        int ivex;       // 该边所指向的顶点的位置
        ENode nextEdge; // 指向下一条弧的指针
    }

    // 邻接表中表的顶点
    private class VNode {
        char data;          // 顶点信息
        ENode firstEdge;    // 指向第一条依附该顶点的弧
    };

    private VNode[] mVexs;  // 顶点数组

    ...
}

```



 **`(01)`**  ListDG是邻接表对应的结构体。 mVexs则是保存顶点信息的一维数组。 
 **`(02)`**  VNode是邻接表顶点对应的结构体。 data是顶点所包含的数据，而firstEdge是该顶点所包含链表的表头指针。 
 **`(03)`**  ENode是邻接表顶点所包含的链表的节点对应的结构体。 ivex是该节点所对应的顶点在vexs中的索引，而nextEdge是指向下一个节点的。

 **`2. 创建矩阵 `** 

这里介绍提供了两个创建矩阵的方法。一个是 **`用已知数据`** ，另一个则需要 **`用户手动输入数据`** 。

 **`2.1 创建图(用已提供的矩阵) `** 



```java
/*
 * 创建图(用已提供的矩阵)
 *
 * 参数说明：
 *     vexs  -- 顶点数组
 *     edges -- 边数组
 */
public ListDG(char[] vexs, char[][] edges) {

    // 初始化"顶点数"和"边数"
    int vlen = vexs.length;
    int elen = edges.length;

    // 初始化"顶点"
    mVexs = new VNode[vlen];
    for (int i = 0; i < mVexs.length; i++) {
        mVexs[i] = new VNode();
        mVexs[i].data = vexs[i];
        mVexs[i].firstEdge = null;
    }

    // 初始化"边"
    for (int i = 0; i < elen; i++) {
        // 读取边的起始顶点和结束顶点
        char c1 = edges[i][0];
        char c2 = edges[i][1];
        // 读取边的起始顶点和结束顶点
        int p1 = getPosition(edges[i][0]);
        int p2 = getPosition(edges[i][1]);

        // 初始化node1
        ENode node1 = new ENode();
        node1.ivex = p2;
        // 将node1链接到"p1所在链表的末尾"
        if(mVexs[p1].firstEdge == null)
          mVexs[p1].firstEdge = node1;
        else
            linkLast(mVexs[p1].firstEdge, node1);
    }
}

```



该函数的作用是创建一个邻接表有向图。实际上，该方法创建的有向图，就是上面的图G2。该函数的调用方法如下：



```java
    char[] vexs = {'A', 'B', 'C', 'D', 'E', 'F', 'G'};
    char[][] edges = new char[][]{
        {'A', 'B'}, 
        {'B', 'C'}, 
        {'B', 'E'}, 
        {'B', 'F'}, 
        {'C', 'E'}, 
        {'D', 'C'}, 
        {'E', 'B'}, 
        {'E', 'D'}, 
        {'F', 'G'}}; 
    ListDG pG;

    pG = new ListDG(vexs, edges);

```



 **`2.2 创建图(自己输入) `** 



```java
/* 
 * 创建图(自己输入数据)
 */
public ListDG() {

    // 输入"顶点数"和"边数"
    System.out.printf("input vertex number: ");
    int vlen = readInt();
    System.out.printf("input edge number: ");
    int elen = readInt();
    if ( vlen < 1 || elen < 1 || (elen > (vlen*(vlen - 1)))) {
        System.out.printf("input error: invalid parameters!\n");
        return ;
    }

    // 初始化"顶点"
    mVexs = new VNode[vlen];
    for (int i = 0; i < mVexs.length; i++) {
        System.out.printf("vertex(%d): ", i);
        mVexs[i] = new VNode();
        mVexs[i].data = readChar();
        mVexs[i].firstEdge = null;
    }

    // 初始化"边"
    //mMatrix = new int[vlen][vlen];
    for (int i = 0; i < elen; i++) {
        // 读取边的起始顶点和结束顶点
        System.out.printf("edge(%d):", i);
        char c1 = readChar();
        char c2 = readChar();
        int p1 = getPosition(c1);
        int p2 = getPosition(c2);
        // 初始化node1
        ENode node1 = new ENode();
        node1.ivex = p2;
        // 将node1链接到"p1所在链表的末尾"
        if(mVexs[p1].firstEdge == null)
          mVexs[p1].firstEdge = node1;
        else
            linkLast(mVexs[p1].firstEdge, node1);
    }
}

```



<a name="anchor3"></a>

### **`邻接表有向图的完整源码 `** 

点击查看：[源代码][105]

[0]: ../img/08.jpg
[100]: #anchor1
[101]: #anchor2
[102]: #anchor3
[103]: http://www.cnblogs.com/skywang12345/
[104]: http://www.cnblogs.com/skywang12345/p/3603935.html
[105]: https://github.com/wangkuiwu/datastructs_and_algorithm/blob/master/source/graph/basic/dg/java/ListDG.java