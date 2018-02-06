## 邻接矩阵无向图(三)之 Java详解

来源：[http://www.cnblogs.com/skywang12345/p/3707604.html](http://www.cnblogs.com/skywang12345/p/3707604.html)

2014-05-08 09:08


前面分别介绍了邻接矩阵无向图的C和C++实现，本文通过Java实现邻接矩阵无向图。

**`目录`**  
 **`1`** . [邻接矩阵无向图的介绍](#anchor1) 
 **`2`** . [邻接矩阵无向图的代码说明](#anchor2) 
 **`3`** . [邻接矩阵无向图的完整源码](#anchor3)  

转载请注明出处：[http://www.cnblogs.com/skywang12345/](http://www.cnblogs.com/skywang12345/)

更多内容：[数据结构与算法系列 目录](http://www.cnblogs.com/skywang12345/p/3603935.html)


 

<a name="anchor1"></a>

###  **`邻接矩阵无向图的介绍`** 

邻接矩阵无向图是指通过邻接矩阵表示的无向图。

[![](../pictures/graph/basic/05.jpg)](../pictures/graph/basic/05.jpg)

上面的图G1包含了"A,B,C,D,E,F,G"共7个顶点，而且包含了"(A,C),(A,D),(A,F),(B,C),(C,D),(E,G),(F,G)"共7条边。由于这是无向图，所以边(A,C)和边(C,A)是同一条边；这里列举边时，是按照字母先后顺序列举的。

上图右边的矩阵是G1在内存中的邻接矩阵示意图。A[i][j]=1表示第i个顶点与第j个顶点是邻接点，A[i][j]=0则表示它们不是邻接点；而A[i][j]表示的是第i行第j列的值；例如，A[1,2]=1，表示第1个顶点(即顶点B)和第2个顶点(C)是邻接点。

<a name="anchor2"></a>

###  **`邻接矩阵无向图的代码说明`** 

**`1. 基本定义`** 


```java
public class MatrixUDG {

    private char[] mVexs;       // 顶点集合
    private int[][] mMatrix;    // 邻接矩阵

    ...
}

```



MatrixUDG是邻接矩阵对应的结构体。mVexs用于保存顶点，mMatrix则是用于保存矩阵信息的二维数组。例如，mMatrix[i][j]=1，则表示"顶点i(即mVexs[i])"和"顶点j(即mVexs[j])"是邻接点；mMatrix[i][j]=0，则表示它们不是邻接点。

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
public MatrixUDG(char[] vexs, char[][] edges) {

    // 初始化"顶点数"和"边数"
    int vlen = vexs.length;
    int elen = edges.length;

    // 初始化"顶点"
    mVexs = new char[vlen];
    for (int i = 0; i < mVexs.length; i++)
        mVexs[i] = vexs[i];

    // 初始化"边"
    mMatrix = new int[vlen][vlen];
    for (int i = 0; i < elen; i++) {
        // 读取边的起始顶点和结束顶点
        int p1 = getPosition(edges[i][0]);
        int p2 = getPosition(edges[i][1]);

        mMatrix[p1][p2] = 1;
        mMatrix[p2][p1] = 1;
    }
}

```



该函数的作用是利用已知数据来创建一个邻接矩阵无向图。 实际上，在本文的测试程序源码中，该方法创建的无向图就是上面图G1。具体的调用代码如下：


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
    MatrixUDG pG;

    pG = new MatrixUDG(vexs, edges);

```



 

**`2.2 创建图(自己输入)`** 


```java
/* 
 * 创建图(自己输入数据)
 */
public MatrixUDG() {

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
    mVexs = new char[vlen];
    for (int i = 0; i < mVexs.length; i++) {
        System.out.printf("vertex(%d): ", i);
        mVexs[i] = readChar();
    }

    // 初始化"边"
    mMatrix = new int[vlen][vlen];
    for (int i = 0; i < elen; i++) {
        // 读取边的起始顶点和结束顶点
        System.out.printf("edge(%d):", i);
        char c1 = readChar();
        char c2 = readChar();
        int p1 = getPosition(c1);
        int p2 = getPosition(c2);

        if (p1==-1 || p2==-1) {
            System.out.printf("input error: invalid edge!\n");
            return ;
        }

        mMatrix[p1][p2] = 1;
        mMatrix[p2][p1] = 1;
    }
}

```



该函数是通过读取用户的输入，而将输入的数据转换成对应的无向图。

<a name="anchor3"></a>

###  **`邻接矩阵无向图的完整源码`** 

点击查看：[源代码](../source/graph/basic/udg/java/MatrixUDG.java)
