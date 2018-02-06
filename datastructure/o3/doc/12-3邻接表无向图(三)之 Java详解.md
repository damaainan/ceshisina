## 邻接表无向图(三)之 Java详解

来源：[http://www.cnblogs.com/skywang12345/p/3707612.html](http://www.cnblogs.com/skywang12345/p/3707612.html)

2014-05-09 10:07


前面分别介绍了邻接表无向图的C和C++实现，本文通过Java实现邻接表无向图。

**`目录`**  

**`1`** . [邻接表无向图的介绍](#anchor1) 
 **`2`** . [邻接表无向图的代码说明](#anchor2) 
 **`3`** . [邻接表无向图的完整源码](#anchor3)

转载请注明出处：[http://www.cnblogs.com/skywang12345/](http://www.cnblogs.com/skywang12345/)

更多内容：[数据结构与算法系列 目录](http://www.cnblogs.com/skywang12345/p/3603935.html)




 

<a name="anchor1"></a>

###  **`邻接表无向图的介绍`** 

邻接表无向图是指通过邻接表表示的无向图。

[![](../pictures/graph/basic/07.jpg)](../pictures/graph/basic/07.jpg)

上面的图G1包含了"A,B,C,D,E,F,G"共7个顶点，而且包含了"(A,C),(A,D),(A,F),(B,C),(C,D),(E,G),(F,G)"共7条边。

上图右边的矩阵是G1在内存中的邻接表示意图。每一个顶点都包含一条链表，该链表记录了"该顶点的邻接点的序号"。例如，第2个顶点(顶点C)包含的链表所包含的节点的数据分别是"0,1,3"；而这"0,1,3"分别对应"A,B,D"的序号，"A,B,D"都是C的邻接点。就是通过这种方式记录图的信息的。

<a name="anchor2"></a>

###  **`邻接表无向图的代码说明`** 

**`1. 基本定义`** 


```java
public class ListUDG {
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



**`(01)`**  ListUDG是邻接表对应的结构体。mVexs则是保存顶点信息的一维数组。 
 **`(02)`**  VNode是邻接表顶点对应的结构体。 data是顶点所包含的数据，而firstEdge是该顶点所包含链表的表头指针。 
 **`(03)`**  ENode是邻接表顶点所包含的链表的节点对应的结构体。 ivex是该节点所对应的顶点在vexs中的索引，而nextEdge是指向下一个节点的。

**`2. 创建矩阵`** 

这里介绍提供了两个创建矩阵的方法。一个是 **`用已知数据`** ，另一个则 **`需要用户手动输入数据`** 。

**`2.1 创建图(用已提供的矩阵)`** 


```java
/*
 * 创建图(用已提供的矩阵)
 *
 * 参数说明：
 *     vexs  -- 顶点数组
 *     edges -- 边数组
 */
public ListUDG(char[] vexs, char[][] edges) {

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
        // 初始化node2
        ENode node2 = new ENode();
        node2.ivex = p1;
        // 将node2链接到"p2所在链表的末尾"
        if(mVexs[p2].firstEdge == null)
          mVexs[p2].firstEdge = node2;
        else
            linkLast(mVexs[p2].firstEdge, node2);

    }
}

```



该函数的作用是创建一个邻接表无向图。实际上，该方法创建的无向图，就是上面图G1。调用代码如下： 


```java
char[] vexs = {'A', 'B', 'C', 'D', 'E', 'F', 'G'};
char[][] edges = new char[][]{
    {'A', 'C'}, 
    {'A', 'D'}, 
    {'A', 'F'}, 
    {'B', 'C'}, 
    {'C', 'D'}, 
    {'E', 'G'}, 
    {'F', 'G'}};
ListUDG pG;

pG = new ListUDG(vexs, edges);

```



**`2.2 创建图(自己输入)`** 


```java
/* 
 * 创建图(自己输入数据)
 */
public ListUDG() {

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
        // 初始化node2
        ENode node2 = new ENode();
        node2.ivex = p1;
        // 将node2链接到"p2所在链表的末尾"
        if(mVexs[p2].firstEdge == null)
          mVexs[p2].firstEdge = node2;
        else
            linkLast(mVexs[p2].firstEdge, node2);
    }
}

```



该函数是读取用户的输入，将输入的数据转换成对应的无向图。

<a name="anchor3"></a>

###  **`邻接表无向图的完整源码`** 

点击查看：[源代码](../source/graph/basic/udg/java/ListUDG.java)
